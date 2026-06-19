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

        $students = StudentProfile::with(['user', 'schoolClass', 'parent.user', 'feePayments'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        return view('admin.fees.index', compact('students', 'search'));
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
}
