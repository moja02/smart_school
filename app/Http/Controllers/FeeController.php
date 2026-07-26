<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentProfile;
use App\Models\FeePayment;
use App\Models\User;
use Illuminate\Support\Str;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $grade_id = $request->input('grade_id');
        $payment_status = $request->input('payment_status');

        $schoolId = auth()->user()->school_id;

        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        $students = StudentProfile::with(['user', 'schoolClass', 'parent.user', 'feePayments'])
            ->whereHas('user', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($grade_id, function($query, $grade_id) {
                return $query->whereHas('schoolClass', function($q) use ($grade_id) {
                    $q->where('grade_id', $grade_id);
                });
            })
            ->when($payment_status, function($query, $status) {
                if ($status == 'full') {
                    return $query->whereRaw('paid_fees >= total_fees')->where('total_fees', '>', 0);
                } elseif ($status == 'partial') {
                    return $query->where('paid_fees', '>', 0)->whereRaw('paid_fees < total_fees');
                } elseif ($status == 'none') {
                    return $query->where(function($q) {
                        $q->where('paid_fees', 0)->orWhereNull('paid_fees');
                    });
                }
            })
            ->paginate(15);

        return view('admin.fees.index', compact('students', 'search', 'grades', 'grade_id', 'payment_status'));
    }

    public function updateTotalFees(Request $request, $id)
    {
        $request->validate([
            'total_fees' => 'required|numeric|min:0'
        ]);

        $student = StudentProfile::findOrFail($id);
        $student->update([
            'total_fees' => $request->total_fees
        ]);

        return back()->with('success', 'تم تحديث إجمالي الرسوم بنجاح');
    }

    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ]);

        $student = StudentProfile::findOrFail($id);

        $payment = FeePayment::create([
            'student_id' => $student->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
            'receipt_number' => 'RCP-' . strtoupper(Str::random(8)),
        ]);

        // Update paid_fees
        $student->increment('paid_fees', $request->amount);

        return back()->with('success', 'تم تسجيل الدفعة بنجاح. رقم الإيصال: ' . $payment->receipt_number);
    }

    public function printReceipt($id)
    {
        $payment = FeePayment::with('student.user')->findOrFail($id);
        return view('admin.fees.receipt', compact('payment'));
    }

    public function toggleSuspend($id)
    {
        $studentProfile = StudentProfile::with(['user', 'parent.user'])->findOrFail($id);
        
        $studentUser = $studentProfile->user;
        
        // Toggle student ban status
        $studentUser->is_banned = !$studentUser->is_banned;
        $studentUser->save();

        // Toggle parent ban status if exists
        if ($studentProfile->parent && $studentProfile->parent->user) {
            $parentUser = $studentProfile->parent->user;
            // Ensure parent is banned if student is banned, or just toggle it.
            // If the goal is to suspend both, we align their status with the student's new status.
            $parentUser->is_banned = $studentUser->is_banned;
            $parentUser->save();
        }

        $status = $studentUser->is_banned ? 'تعطيل' : 'تفعيل';
        return back()->with('success', "تم {$status} حساب الطالب وولي الأمر بنجاح");
    }

    public function notifyParents(Request $request)
    {
        $search = $request->input('search');
        $grade_id = $request->input('grade_id');
        $payment_status = $request->input('payment_status');

        $schoolId = auth()->user()->school_id;

        $studentsQuery = StudentProfile::with(['user', 'schoolClass'])
            ->whereHas('user', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($grade_id, function($query, $grade_id) {
                return $query->whereHas('schoolClass', function($q) use ($grade_id) {
                    $q->where('grade_id', $grade_id);
                });
            })
            ->when($payment_status, function($query, $status) {
                if ($status == 'full') {
                    return $query->whereRaw('paid_fees >= total_fees')->where('total_fees', '>', 0);
                } elseif ($status == 'partial') {
                    return $query->where('paid_fees', '>', 0)->whereRaw('paid_fees < total_fees');
                } elseif ($status == 'none') {
                    return $query->where(function($q) {
                        $q->where('paid_fees', 0)->orWhereNull('paid_fees');
                    });
                }
            });

        $studentIds = $studentsQuery->pluck('id');

        if ($studentIds->isEmpty()) {
            return back()->with('error', 'لا يوجد طلاب مطابقين للفلترة المحددة.');
        }

        $parentIds = \Illuminate\Support\Facades\DB::table('parent_student')
                        ->whereIn('student_id', $studentIds)
                        ->pluck('parent_id')
                        ->unique();

        if ($parentIds->isEmpty()) {
            return back()->with('error', 'الطلاب المعروضين ليس لديهم حسابات أولياء أمور مسجلة.');
        }

        $senderId = auth()->id();
        $count = 0;
        
        foreach ($parentIds as $parentId) {
            \App\Models\Message::create([
                'sender_id' => $senderId,
                'receiver_id' => $parentId,
                'message' => 'تنبيه إداري: يرجى مراجعة حالة الرسوم الدراسية لأبنائكم في النظام واستكمال تسديد أي مبالغ مستحقة. شكراً لتعاونكم.',
            ]);
            $count++;
        }

        return back()->with('success', "تم إرسال التنبيه الجماعي بنجاح إلى {$count} ولي أمر.");
    }
}
