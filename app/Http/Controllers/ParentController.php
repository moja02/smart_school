<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\FeePayment;
use App\Models\Homework;
use App\Models\LeaveRequest;
use App\Models\WalletTransaction;

class ParentController extends Controller
{
    // 1. الداشبورد: عرض الأبناء ودرجاتهم التفصيلية
    public function dashboard()
    {
        $parentId = Auth::id();

        // ✅ التعديل هنا: استخدام user_id بدلاً من id لضمان جلب الابن الصحيح
        $children = \App\Models\StudentProfile::whereIn('user_id', function($query) use ($parentId) {
            $query->select('student_id')
                  ->from('parent_student')
                  ->where('parent_id', $parentId);
        })->with(['user', 'schoolClass'])->get();

        // مصفوفة لتخزين كل تفاصيل الدرجات والتقييمات لكل ابن
        $childrenDetails = [];

        foreach ($children as $child) {
            if ($child->user_id) {
                // جلب بيانات الفصل لمعرفة المرحلة
                $class_id = $child->class_id ?? $child->section_id ?? null;
                $class = $class_id ? \App\Models\SchoolClass::find($class_id) : null;
                
                // جلب المواد الخاصة بمرحلة الابن
                $subjects = \App\Models\Subject::where('grade_id', $class->grade_id ?? null)->get();

                $subjectsData = collect();
                $totalStudentScore = 0;
                $totalMaxScore = 0;

                foreach ($subjects as $subject) {
                    // جلب التقييمات التفصيلية التي أضافها المعلم لهذه المادة
                    $assessments = \App\Models\Assessment::where('subject_id', $subject->id)->get();
                    $detailedMarks = [];
                    $subjectTotal = 0;
                    $subjectMaxTotal = 0;

                    foreach ($assessments as $assessment) {
                        // البحث عن درجة الابن في هذا التقييم بالتحديد
                        $markRecord = \App\Models\AssessmentMark::where('student_id', $child->user_id)
                                                    ->where('assessment_id', $assessment->id)
                                                    ->first();
                        
                        $score = $markRecord ? ($markRecord->marks ?? $markRecord->score ?? $markRecord->mark ?? 0) : null;
                        $assessmentMax = $assessment->max_score ?? $assessment->full_mark ?? 0;
                        
                        $detailedMarks[] = (object)[
                            'title'     => $assessment->title ?? $assessment->name ?? 'تقييم',
                            'max_score' => $assessmentMax > 0 ? $assessmentMax : '--',
                            'score'     => $score,
                            'notes'     => $markRecord ? ($markRecord->notes ?? $markRecord->remarks ?? '') : ''
                        ];
                        
                        // تجميع المجموع إذا كانت الدرجة مرصودة
                        if ($score !== null) {
                            $subjectTotal += $score;
                        }
                        // حساب المجموع الكلي للتقييمات لهذه المادة (التقييمات المنشأة فعلياً)
                        $subjectMaxTotal += $assessmentMax;
                    }

                    $subjectsData->push((object)[
                        'subject_name'   => $subject->name,
                        'total_score'    => $subjectTotal,
                        'max_total'      => $subjectMaxTotal,
                        'detailed_marks' => $detailedMarks
                    ]);

                    $totalStudentScore += $subjectTotal;
                    $totalMaxScore += $subjectMaxTotal;
                }

                // حساب النسبة المئوية للابن
                $percentage = $totalMaxScore > 0 ? round(($totalStudentScore / $totalMaxScore) * 100, 1) : 0;

                // 🌟 إضافة بيانات الحضور للرسم البياني 🌟
                $attendanceStats = [
                    'present' => \DB::table('attendances')->where('student_id', $child->user_id)->where('status', 'present')->count(),
                    'absent'  => \DB::table('attendances')->where('student_id', $child->user_id)->where('status', 'absent')->count(),
                    'late'    => \DB::table('attendances')->where('student_id', $child->user_id)->where('status', 'late')->count(),
                ];

                // 🌟 تجهيز بيانات الرسم الشبكي (الرادار) للأداء الأكاديمي 🌟
                $radarLabels = [];
                $radarData = [];
                foreach ($subjectsData as $sd) {
                    $radarLabels[] = $sd->subject_name;
                    $radarData[] = $sd->max_total > 0 ? round(($sd->total_score / $sd->max_total) * 100) : 0;
                }

                $childrenDetails[$child->user_id] = (object)[
                    'subjects'    => $subjectsData,
                    'percentage'  => $percentage,
                    'total_score' => $totalStudentScore,
                    'max_score'   => $totalMaxScore,
                    'wallet'      => $child->wallet_balance ?? 0,
                    'attendance'  => $attendanceStats,
                    'chartLabels' => json_encode($radarLabels),
                    'chartData'   => json_encode($radarData)
                ];

            } else {
                $childrenDetails[$child->user_id] = (object)[
                    'subjects' => collect(), 
                    'percentage' => 0, 
                    'total_score' => 0, 
                    'max_score' => 0,
                    'wallet' => 0,
                    'attendance' => ['present' => 0, 'absent' => 0, 'late' => 0],
                    'chartLabels' => '[]',
                    'chartData' => '[]'
                ];
            }
        }

        return view('parent.dashboard', compact('children', 'childrenDetails'));
    }

    // 2. عرض صفحة تعديل الملف الشخصي
    public function editProfile()
    {
        return view('parent.profile');
    }

    // 3. حفظ التعديلات
    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6|confirmed', // كلمة المرور اختيارية
        ]);

        $user->name = $request->name;

        // تحديث كلمة المرور فقط إذا تم إدخالها
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'تم تحديث بياناتك بنجاح.');
    }

    public function attendance()
    {
        $parentId = Auth::id();

        // ✅ التعديل هنا أيضاً: استخدام user_id بدلاً من id
        $children = \App\Models\StudentProfile::whereIn('user_id', function($q) use ($parentId) {
                        $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
                    })->get();

        // 2. جلب سجلات الغياب لكل ابن
        $attendanceData = [];
        foreach($children as $child) {
            $attendanceData[$child->id] = \App\Models\Attendance::where('student_id', $child->id)
                                            ->orderBy('attendance_date', 'desc')
                                            ->get();
        }

        return view('parent.attendance', compact('children', 'attendanceData'));
    }
    /**
     * عرض جدول الامتحانات (الـ 30 يوماً القادمة) لجميع الأبناء
     */
    public function examsCalendar()
    {
        $parent = auth()->user();
        
        // جلب الأبناء المرتبطين بولي الأمر
        $children = $parent->children()->with('studentProfile.schoolClass')->get();

        if ($children->isEmpty()) {
            return redirect()->route('parent.dashboard')->with('error', 'لا يوجد أبناء مرتبطين بحسابك لعرض امتحاناتهم.');
        }

        $allExams = collect();

        // الدوران على الأبناء لجلب امتحانات كل ابن
        foreach ($children as $child) {
            $class_id = $child->studentProfile->class_id ?? $child->studentProfile->section_id ?? null;
            
            if ($class_id) {
                $class = \App\Models\SchoolClass::find($class_id);
                $subjectIds = \App\Models\Subject::where('grade_id', $class->grade_id ?? null)->pluck('id');

                $childExams = \App\Models\Exam::whereIn('subject_id', $subjectIds)
                                    ->whereDate('exam_date', '>=', now())
                                    ->with('subject')
                                    ->get();

                // إضافة اسم الابن لكائن الامتحان لتمييزه في الواجهة
                foreach ($childExams as $exam) {
                    $exam->child_name = $child->name;
                    $allExams->push($exam);
                }
            }
        }

        // تجميع الامتحانات بناءً على التاريخ
        $examsByDate = $allExams->groupBy(function($exam) {
            return \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d');
        });

        // إنشاء مصفوفة للأيام الثلاثين القادمة
        $calendarDays = [];
        $today = \Carbon\Carbon::now();
        
        for ($i = 0; $i < 30; $i++) {
            $currentDate = $today->copy()->addDays($i);
            $dateStr = $currentDate->format('Y-m-d');
            
            $calendarDays[] = [
                'date' => $currentDate,
                'day_name' => $currentDate->locale('ar')->translatedFormat('l'),
                'day_num' => $currentDate->format('d'),
                'month_name' => $currentDate->locale('ar')->translatedFormat('F'),
                'has_exam' => isset($examsByDate[$dateStr]),
                'exams' => $examsByDate[$dateStr] ?? collect()
            ];
        }

        return view('parent.exams', compact('calendarDays'));
    }

    // 1. الرسوم والدفعات
    public function fees()
    {
        $parentId = Auth::id();
        $children = StudentProfile::whereIn('user_id', function($q) use ($parentId) {
                        $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
                    })->with('feePayments')->get();

        return view('parent.fees', compact('children'));
    }

    // 2. الواجبات المدرسية
    public function homeworks()
    {
        $parentId = Auth::id();
        $children = StudentProfile::whereIn('user_id', function($q) use ($parentId) {
                        $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
                    })->get();

        $classIds = $children->pluck('class_id')->filter()->unique();

        $homeworks = Homework::whereIn('class_id', $classIds)
            ->with(['subject', 'teacher', 'schoolClass'])
            ->orderBy('due_date', 'asc')
            ->get();

        return view('parent.homeworks', compact('children', 'homeworks'));
    }

    // 3. طلبات الاستئذان
    public function leaves()
    {
        $parentId = Auth::id();
        $leaves = LeaveRequest::where('parent_id', $parentId)->with('student.user')->orderBy('created_at', 'desc')->get();
        $children = StudentProfile::whereIn('user_id', function($q) use ($parentId) {
                        $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
                    })->with('user')->get();

        return view('parent.leaves', compact('leaves', 'children'));
    }

    public function storeLeave(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'type' => 'required|in:absence,early_leave',
            'leave_date' => 'required|date',
            'reason' => 'required|string|max:500',
        ]);

        LeaveRequest::create([
            'parent_id' => Auth::id(),
            'student_id' => $request->student_id,
            'type' => $request->type,
            'leave_date' => $request->leave_date,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return back()->with('success', 'تم إرسال طلب الاستئذان للإدارة بنجاح.');
    }

    public function updateWalletPin(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'wallet_pin' => 'nullable|digits:4'
        ]);

        $parentId = Auth::id();
        
        // Verify child belongs to parent
        $isChild = \DB::table('parent_student')
            ->join('student_profiles', 'parent_student.student_id', '=', 'student_profiles.user_id')
            ->where('parent_student.parent_id', $parentId)
            ->where('student_profiles.id', $request->student_id)
            ->exists();
            
        if (!$isChild) {
            abort(403, 'غير مصرح لك بتعديل بيانات هذا الطالب.');
        }

        $profile = StudentProfile::findOrFail($request->student_id);
        $profile->wallet_pin = $request->wallet_pin;
        $profile->save();

        return back()->with('success', 'تم تحديث الرقم السري للمحفظة بنجاح.');
    }

    // 6. المحفظة الرقمية
    public function wallet()
    {
        $parentId = Auth::id();
        $children = StudentProfile::whereIn('user_id', function($q) use ($parentId) {
                        $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
                    })->with('user')->get();

        $transactions = [];
        foreach($children as $child) {
            $transactions[$child->id] = WalletTransaction::where('student_id', $child->id)
                                        ->orderBy('created_at', 'desc')
                                        ->get();
        }

        return view('parent.wallet', compact('children', 'transactions'));
    }
    // عرض أبناء ولي الأمر
    public function children()
    {
        $parentId = Auth::id();
        $children = StudentProfile::whereIn('user_id', function($q) use ($parentId) {
            $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
        })->with('user', 'schoolClass')->get();

        return view('parent.children', compact('children'));
    }

    // عرض درجات الأبناء
    public function grades()
    {
        $parentId = Auth::id();
        $children = StudentProfile::whereIn('user_id', function($q) use ($parentId) {
            $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
        })->with('user')->get();

        // تمرير المتغيرات المطلوبة في blade
        foreach ($children as $child) {
            $child->name = $child->user->name ?? 'غير معروف';
            $child->class_name = $child->schoolClass->name ?? 'غير محدد';
        }

        return view('parent.grades', compact('children'));
    }

    // عرض سلوك الأبناء
    public function behaviour()
    {
        $parentId = Auth::id();
        $children = StudentProfile::whereIn('user_id', function($q) use ($parentId) {
            $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
        })->with('user')->get();

        return view('parent.behaviour', compact('children'));
    }

    // عرض وإرسال رسائل
    public function messages(Request $request)
    {
        // إذا كان طلب POST، قم بحفظ الرسالة أو معالجتها
        if ($request->isMethod('post')) {
            // إضافة معالجة بسيطة في حالة الإرسال لتجنب الأخطاء
            $request->validate([
                'subject' => 'required|string|max:255',
                'body' => 'required|string|max:1000'
            ]);

            \App\Models\Message::create([
                'sender_id' => Auth::id(),
                'subject' => $request->subject,
                'body' => $request->body,
                'type' => 'parent_to_school'
            ]);

            return back()->with('success', 'تم إرسال الرسالة للإدارة بنجاح.');
        }

        // جلب الرسائل المستلمة لولي الأمر
        $messages = \App\Models\Message::where('receiver_id', Auth::id())
            ->orWhere(function($query) {
                $query->where('type', 'broadcast')->where('target_role', 'parent');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('parent.messages', compact('messages'));
    }
}