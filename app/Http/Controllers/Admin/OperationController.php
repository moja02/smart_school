<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationController extends Controller
{
    public function manageAdminsPermissions()
    {
        $user = auth()->user();

        if ($user->role !== 'manager') {
            return redirect()->route('admin.dashboard')->with('error', 'هذه الصفحة مخصصة لمدير المدرسة فقط.');
        }

        $schoolId = $user->school_id;

        $admins = \App\Models\User::role('admin')->where('school_id', $schoolId)->get();

        $availablePermissions = [
            'manage_users'      => 'إدارة المستخدمين (طلاب ومعلمين)',
            'manage_classes'    => 'إدارة الفصول والشعب',
            'manage_subjects'   => 'إدارة المواد وتوزيع الدرجات',
            'assign_teachers'   => 'إسناد المواد للمعلمين',
            'manage_schedules'  => 'إدارة الجدول الدراسي',
            'edit_marks'        => 'تعديل درجات الطلاب',
            'manage_reports'    => 'إصدار التقارير والشهادات',
            'toggle_grading'    => 'فتح وإغلاق الرصد',
            'manage_wallet_charge' => 'شحن المحفظة (الإدارة المالية)',
            'manage_wallet_sell'   => 'خصم من المحفظة (المبيعات والمقصف)',
            'manage_fees'       => 'إدارة الرسوم الدراسية',
            'manage_leaves'     => 'إدارة طلبات الاستئذان',
            'manage_admissions' => 'إدارة طلبات القبول والتسجيل',
            'manage_promotion'  => 'الترحيل ونهاية السنة الدراسية',
        ];

        foreach (array_keys($availablePermissions) as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        return view('admin.managers.permissions', compact('admins', 'availablePermissions'));
    }

    public function updateAdminsPermissions(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'permissions' => 'nullable|array' 
        ]);

        $admin = \App\Models\User::findOrFail($request->admin_id);

        if ($admin->school_id !== auth()->user()->school_id || $admin->role !== 'admin') {
            return back()->with('error', 'إجراء غير صالح.');
        }

        $permissions = $request->permissions ?? [];
        $admin->syncPermissions($permissions);

        return back()->with('success', 'تم تحديث صلاحيات المشرف (' . $admin->name . ') بنجاح ✅');
    }

    public function admissionsIndex()
    {
        $admissions = \App\Models\Admission::all();
        return view('admin.admissions.index', compact('admissions')); 
    }

    public function updateAdmissionStatus(Request $request, $id)
    {
        $admission = \App\Models\Admission::findOrFail($id);
        $admission->status = $request->status;
        $admission->save();
        return redirect()->back()->with('success', 'تم التحديث');
    }

    public function leavesIndex()
    {
        $leaves = \App\Models\LeaveRequest::with('student')->whereDate('leave_date', today())->get();
        return view('admin.leaves.index', compact('leaves'));
    }

    public function leavesHistory(Request $request)
    {
        $query = \App\Models\LeaveRequest::with(['student.user', 'student.schoolClass.grade']);

        if ($request->filled('reason')) {
            $query->where(function($q) use ($request) {
                $q->where('reason', 'like', '%' . $request->reason . '%')
                  ->orWhere('type', 'like', '%' . $request->reason . '%');
            });
        }

        if ($request->filled('student')) {
            $studentSearch = $request->student;
            $query->whereHas('student.user', function ($q) use ($studentSearch) {
                $q->where('name', 'like', '%' . $studentSearch . '%');
            });
        }

        if ($request->filled('class_id')) {
            $classId = $request->class_id;
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $leaves = $query->orderBy('leave_date', 'desc')->paginate(15);
        $classes = \App\Models\SchoolClass::with('grade')->get();

        return view('admin.leaves.history', compact('leaves', 'classes'));
    }

    public function updateLeaveStatus(Request $request, $id)
    {
        $leave = \App\Models\LeaveRequest::findOrFail($id);
        $leave->status = $request->status;
        $leave->save();
        return back()->with('success', 'تم التحديث');
    }

    public function walletIndex(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'manager' && !$user->hasPermissionTo('manage_wallet_charge') && !$user->hasPermissionTo('manage_wallet_sell')) {
            abort(403, 'ليس لديك صلاحية الوصول لهذه الصفحة.');
        }

        $search = $request->search;
        $students = \App\Models\User::role('student')
            ->when($search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->with('studentProfile')->get();
        return view('admin.wallet.index', compact('students'));
    }

    public function topupWallet(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'manager' && !$user->hasPermissionTo('manage_wallet_charge')) {
            abort(403, 'ليس لديك صلاحية شحن المحفظة.');
        }

        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'amount' => 'required|numeric|min:0.5'
        ]);
        $studentProfile = \App\Models\StudentProfile::findOrFail($request->student_id);
        $studentProfile->wallet_balance = ($studentProfile->wallet_balance ?? 0) + $request->amount;
        $studentProfile->save();
        return back()->with('success', 'تم شحن المحفظة');
    }

    public function purchaseWallet(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'manager' && !$user->hasPermissionTo('manage_wallet_sell')) {
            abort(403, 'ليس لديك صلاحية البيع من المقصف.');
        }

        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'amount' => 'required|numeric|min:0.5',
            'wallet_pin' => 'nullable|string|size:4'
        ]);
        $studentProfile = \App\Models\StudentProfile::findOrFail($request->student_id);
        
        if (!empty($studentProfile->wallet_pin)) {
            if ($request->wallet_pin !== $studentProfile->wallet_pin) {
                return back()->with('error', 'الرقم السري غير صحيح، لا يمكن إتمام عملية الشراء!');
            }
        }

        if ($studentProfile->daily_limit > 0) {
            $todaySpent = \App\Models\WalletTransaction::where('student_id', $studentProfile->id)
                ->where('type', 'purchase')
                ->whereDate('created_at', now()->toDateString())
                ->sum('amount');
            
            if ($todaySpent + $request->amount > $studentProfile->daily_limit) {
                $remaining = max(0, $studentProfile->daily_limit - $todaySpent);
                return back()->with('error', "تجاوز الطالب الحد اليومي للمصروفات ({$studentProfile->daily_limit} د.ل). المتبقي له لليوم: {$remaining} د.ل فقط!");
            }
        }

        if (($studentProfile->wallet_balance ?? 0) >= $request->amount) {
            $studentProfile->wallet_balance -= $request->amount;
            $studentProfile->save();

            \App\Models\WalletTransaction::create([
                'student_id' => $studentProfile->id,
                'amount' => $request->amount,
                'type' => 'purchase',
                'description' => 'شراء من المقصف المدرسي'
            ]);

            return back()->with('success', 'تم الخصم');
        }
        return back()->with('error', 'الرصيد غير كافٍ');
    }

    private function getIncompleteAssessments($schoolId, $gradeId)
    {
        $sections = \App\Models\SchoolClass::where('school_id', $schoolId)
            ->where('grade_id', $gradeId)
            ->get();

        $incomplete = [];

        foreach ($sections as $section) {
            $assignments = \DB::table('teacher_subject_section')
                ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
                ->where('teacher_subject_section.school_id', $schoolId)
                ->where('teacher_subject_section.section_id', $section->id)
                ->select('subjects.id as subject_id', 'subjects.name as subject_name')
                ->distinct()
                ->get();

            foreach ($assignments as $assignment) {
                $settings = \DB::table('school_subject_settings')
                    ->where('school_id', $schoolId)
                    ->where('subject_id', $assignment->subject_id)
                    ->first();

                $totalWorksScore = ($settings && isset($settings->works_score) && $settings->works_score > 0)
                    ? $settings->works_score
                    : 40;

                $maxPerSemester = $totalWorksScore / 2;

                $sumSem1 = Assessment::where('subject_id', $assignment->subject_id)
                    ->where('section_id', $section->id)
                    ->where('semester', 1)
                    ->sum('max_score');

                $sumSem2 = Assessment::where('subject_id', $assignment->subject_id)
                    ->where('section_id', $section->id)
                    ->where('semester', 2)
                    ->sum('max_score');

                $sem1Complete = (abs($sumSem1 - $maxPerSemester) < 0.01);
                $sem2Complete = (abs($sumSem2 - $maxPerSemester) < 0.01);

                if (!$sem1Complete || !$sem2Complete) {
                    $details = [];
                    if (!$sem1Complete) {
                        $details[] = "الفصل الأول: تم رصد {$sumSem1} من {$maxPerSemester}";
                    }
                    if (!$sem2Complete) {
                        $details[] = "الفصل الثاني: تم رصد {$sumSem2} من {$maxPerSemester}";
                    }

                    $incomplete[] = [
                        'section_name' => $section->name . ' - ' . ($section->section ?? ''),
                        'subject_name' => $assignment->subject_name,
                        'total_works_score' => $totalWorksScore,
                        'sum_sem1' => $sumSem1,
                        'sum_sem2' => $sumSem2,
                        'max_per_semester' => $maxPerSemester,
                        'details' => implode(' | ', $details),
                    ];
                }
            }
        }

        return $incomplete;
    }

    public function checkAssessmentCompleteness(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $gradeId = $request->grade_id;

        if (!$gradeId) {
            return response()->json(['success' => false, 'message' => 'يجب تحديد الصف!']);
        }

        $incomplete = $this->getIncompleteAssessments($schoolId, $gradeId);

        if (empty($incomplete)) {
            return response()->json([
                'success' => true,
                'complete' => true,
                'message' => 'جميع التقييمات مكتملة لهذا الصف ✅'
            ]);
        }

        return response()->json([
            'success' => true,
            'complete' => false,
            'message' => 'يوجد مواد لم تكتمل تقييماتها بعد!',
            'incomplete' => $incomplete
        ]);
    }

    public function showPromotion()
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->orderBy('order')->get();

        return view('admin.promotion', compact('school', 'grades'));
    }

    public function updateAcademicYear(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string|max:20'
        ]);

        $school = \App\Models\School::find(auth()->user()->school_id);
        $school->academic_year = $request->academic_year;
        $school->save();

        return redirect()->back()->with('success', 'تم تحديث السنة الدراسية الحالية بنجاح.');
    }

    public function previewPromotion(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $gradeId = $request->grade_id;
        $passPercentage = $request->pass_percentage ?? 50;

        if (!$gradeId) {
            return response()->json(['success' => false, 'message' => 'يجب تحديد الصف!']);
        }

        $currentGrade = \App\Models\Grade::find($gradeId);
        $nextGrade = \App\Models\Grade::where('stage', $currentGrade->stage)
                                      ->where('order', '>', $currentGrade->order)
                                      ->orderBy('order')
                                      ->first();

        $students = \App\Models\User::role('student')
            ->where('school_id', $schoolId)
            ->whereHas('studentProfile.schoolClass', function($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })
            ->with(['studentProfile.schoolClass'])
            ->get();

        $previewData = [];

        foreach ($students as $student) {
            $totalStudentScore = \DB::table('student_scores')
                                    ->where('student_id', $student->id)
                                    ->sum('total_score');

            $maxPossible = \App\Models\Assessment::whereIn('subject_id', function($q) use ($student) {
                                $q->select('subject_id')
                                  ->from('teacher_subject_section')
                                  ->where('section_id', $student->studentProfile->class_id);
                            })->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(max_score, full_mark, 0)'));

            $percentage = $maxPossible > 0 ? ($totalStudentScore / $maxPossible) * 100 : 0;
            $status = $percentage >= $passPercentage ? 'passed' : 'failed';
            $isGraduate = false;
            $newClassName = 'نفس الفصل';
            $newClassId = null;
            $newGradeName = null;

            if ($status === 'passed') {
                if ($nextGrade) {
                    $section = $student->studentProfile->schoolClass->section;
                    $nextClass = \App\Models\SchoolClass::where('school_id', $schoolId)
                                                        ->where('grade_id', $nextGrade->id)
                                                        ->where('section', $section)
                                                        ->first();
                    if ($nextClass) {
                        $newClassName = $nextClass->name . ' - ' . $nextClass->section;
                        $newClassId = $nextClass->id;
                        $newGradeName = $nextGrade->name;
                    } else {
                        $newClassName = 'يحتاج تعيين يدوي';
                    }
                } else {
                    $newClassName = 'متخرج من المرحلة';
                    $isGraduate = true;
                }
            }

            $previewData[] = [
                'id' => $student->id,
                'name' => $student->name,
                'current_class' => $student->studentProfile->schoolClass->name . ' - ' . $student->studentProfile->schoolClass->section,
                'total_score' => $totalStudentScore,
                'max_possible' => $maxPossible,
                'percentage' => round($percentage, 1),
                'status' => $status,
                'is_graduate' => $isGraduate,
                'new_class_name' => $newClassName,
                'new_class_id' => $newClassId,
                'new_grade_name' => $newGradeName,
                'old_class_id' => $student->studentProfile->class_id,
                'old_class_name' => $student->studentProfile->schoolClass->name,
                'old_grade_id' => $currentGrade->id,
                'old_grade_name' => $currentGrade->name
            ];
        }

        return response()->json([
            'success' => true,
            'students' => $previewData
        ]);
    }

    public function executePromotion(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $academicYear = $school->academic_year ?? 'غير محدد';
        
        $studentsData = $request->input('students', []);

        if (empty($studentsData)) {
            return response()->json(['success' => false, 'message' => 'لا يوجد طلاب محددين']);
        }

        $gradeId = $studentsData[0]['old_grade_id'] ?? null;
        if ($gradeId) {
            $incomplete = $this->getIncompleteAssessments($schoolId, $gradeId);
            if (!empty($incomplete)) {
                $subjectsList = collect($incomplete)->map(function($item) {
                    return $item['subject_name'] . ' (' . $item['section_name'] . ')';
                })->unique()->implode('، ');
                
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تنفيذ الترحيل! يوجد مواد لم تكتمل تقييماتها: ' . $subjectsList
                ]);
            }
        }

        \DB::transaction(function () use ($studentsData, $schoolId, $academicYear) {
            foreach ($studentsData as $data) {
                $scoresSnapshot = \DB::table('student_scores')->where('student_id', $data['id'])->get();

                \App\Models\PromotionArchive::create([
                    'school_id' => $schoolId,
                    'academic_year' => $academicYear,
                    'student_id' => $data['id'],
                    'student_name' => $data['name'],
                    'old_class_id' => $data['old_class_id'],
                    'old_class_name' => $data['old_class_name'],
                    'old_grade_id' => $data['old_grade_id'],
                    'old_grade_name' => $data['old_grade_name'],
                    'new_class_id' => $data['new_class_id'],
                    'new_grade_name' => $data['new_grade_name'],
                    'total_score' => $data['total_score'],
                    'max_possible_score' => $data['max_possible'],
                    'percentage' => $data['percentage'],
                    'status' => $data['status'],
                    'scores_snapshot' => $scoresSnapshot->toJson(),
                    'promoted_by' => auth()->id()
                ]);

                if ($data['status'] === 'passed' && !empty($data['new_class_id'])) {
                    \App\Models\StudentProfile::where('user_id', $data['id'])->update([
                        'class_id' => $data['new_class_id']
                    ]);
                }
                
                \DB::table('student_scores')->where('student_id', $data['id'])->delete();
                \DB::table('assessment_marks')
                    ->whereIn('assessment_id', function($q) use ($data) {
                        $q->select('id')->from('assessments')
                          ->where('section_id', $data['old_class_id']);
                    })
                    ->where('student_id', $data['id'])
                    ->delete();
            }
        });

        return response()->json(['success' => true, 'message' => 'تم تنفيذ الترحيل بنجاح وأرشفة النتائج وتنظيف الدرجات القديمة.']);
    }

    public function showCloseYear()
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        
        $stats = [
            'students_count' => \App\Models\User::role('student')->where('school_id', $schoolId)->count(),
            'scores_count' => \DB::table('student_scores')
                ->whereIn('student_id', \App\Models\User::where('school_id', $schoolId)->pluck('id'))
                ->count(),
            'assessments_count' => \App\Models\Assessment::whereIn('section_id', 
                \App\Models\SchoolClass::where('school_id', $schoolId)->pluck('id')
            )->count(),
            'schedules_count' => \App\Models\Schedule::whereIn('class_id', 
                \App\Models\SchoolClass::where('school_id', $schoolId)->pluck('id')
            )->count(),
            'attendance_count' => \App\Models\Attendance::whereIn('class_id', 
                \App\Models\SchoolClass::where('school_id', $schoolId)->pluck('id')
            )->count(),
            'promotions_done' => \App\Models\PromotionArchive::where('school_id', $schoolId)
                ->where('academic_year', $school->academic_year ?? '')->count(),
        ];

        return view('admin.close_year', compact('school', 'stats'));
    }

    public function executeCloseYear(Request $request)
    {
        $request->validate([
            'new_academic_year' => 'required|string|max:20',
            'confirm_text' => 'required|in:تأكيد'
        ]);

        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $oldYear = $school->academic_year ?? 'غير محدد';
        $newYear = $request->new_academic_year;

        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        $unpromotedGrades = [];
        foreach ($grades as $grade) {
            $studentsInGrade = \App\Models\User::role('student')
                ->where('school_id', $schoolId)
                ->whereHas('studentProfile.schoolClass', function($q) use ($grade) {
                    $q->where('grade_id', $grade->id);
                })->count();

            if ($studentsInGrade > 0) {
                $promotedCount = \App\Models\PromotionArchive::where('school_id', $schoolId)
                    ->where('academic_year', $oldYear)
                    ->where('old_grade_id', $grade->id)
                    ->count();
                
                if ($promotedCount === 0 && $studentsInGrade > 0) {
                    $unpromotedGrades[] = $grade->name;
                }
            }
        }

        if (!empty($unpromotedGrades)) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إغلاق السنة! يوجد صفوف لم تتم ترحيتها بعد: ' . implode('، ', $unpromotedGrades)
            ]);
        }

        \DB::transaction(function () use ($schoolId, $school, $newYear) {
            $schoolClassIds = \App\Models\SchoolClass::where('school_id', $schoolId)->pluck('id');
            $schoolStudentIds = \App\Models\User::where('school_id', $schoolId)->pluck('id');

            \DB::table('student_scores')->whereIn('student_id', $schoolStudentIds)->delete();

            $assessmentIds = \App\Models\Assessment::whereIn('section_id', $schoolClassIds)->pluck('id');
            \DB::table('assessment_marks')->whereIn('assessment_id', $assessmentIds)->delete();

            \App\Models\Assessment::whereIn('section_id', $schoolClassIds)->delete();

            \App\Models\Schedule::whereIn('class_id', $schoolClassIds)->delete();

            \App\Models\Attendance::whereIn('class_id', $schoolClassIds)->delete();

            \DB::table('teacher_subject_section')->where('school_id', $schoolId)->delete();

            \DB::table('quiz_attempts')->whereIn('student_id', 
                \App\Models\StudentProfile::whereIn('user_id', $schoolStudentIds)->pluck('id')
            )->delete();

            $school->academic_year = $newYear;
            $school->grading_locked_sem1 = false; 
            $school->grading_locked_sem2 = false; 
            $school->save();
        });

        return response()->json([
            'success' => true,
            'message' => "تم إغلاق السنة الدراسية بنجاح! 🎓\nتم تنظيف جميع البيانات المؤقتة وتحديث السنة إلى: $newYear\nالنظام جاهز للسنة الجديدة."
        ]);
    }

    public function toggleGrading($semester)
    {
        $school = \App\Models\School::find(auth()->user()->school_id);
        if ($semester == 1) {
            $school->grading_locked_sem1 = !$school->grading_locked_sem1;
        } else {
            $school->grading_locked_sem2 = !$school->grading_locked_sem2;
        }
        $school->save();
        return back()->with('success', 'تم تحديث حالة الرصد بنجاح.');
    }
}
