<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function showGradeReport(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        $selectedGradeId = $request->grade_id;
        $subjects = collect();
        if ($selectedGradeId) {
            $subjects = Subject::where('grade_id', $selectedGradeId)
                ->where(function($q) use ($schoolId) {
                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                })->get();
        }

        $reportData = collect();
        if ($selectedGradeId && $request->subject_id) {
            $studentsInGrade = StudentProfile::whereHas('schoolClass', function($q) use ($selectedGradeId, $schoolId) {
                $q->where('grade_id', $selectedGradeId)
                  ->where('school_id', $schoolId);
            })->with(['user', 'schoolClass'])->get();

            $scores = \DB::table('student_scores')
                        ->where('subject_id', $request->subject_id)
                        ->whereIn('student_id', $studentsInGrade->pluck('user_id'))
                        ->get()
                        ->keyBy('student_id');

            foreach ($studentsInGrade as $student) {
                $studentScore = $scores->get($student->user_id);
                $reportData->push([
                    'student_name' => $student->user->name ?? 'غير معروف',
                    'class_name' => $student->schoolClass->name ?? '-',
                    'works_sem1' => $studentScore->works_score_sem1 ?? 0,
                    'final_sem1' => $studentScore->final_score_sem1 ?? 0,
                    'works_sem2' => $studentScore->works_score_sem2 ?? 0,
                    'final_sem2' => $studentScore->final_score_sem2 ?? 0,
                    'total_score' => $studentScore->total_score ?? 0,
                ]);
            }
        }

        return view('admin.reports.grade_report', compact('grades', 'subjects', 'reportData'));
    }

    public function showReports(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $activeGradeIds = \DB::table('school_grade')
                            ->where('school_id', $schoolId)
                            ->pluck('grade_id')
                            ->toArray();

        $classes = SchoolClass::where('school_id', $schoolId)
                    ->whereIn('grade_id', $activeGradeIds)
                    ->get();
                    
        $students = collect();
        $selectedClass = $request->class_id;

        if ($selectedClass) {
            $students = User::role('student')
                ->whereHas('studentProfile', function($q) use ($selectedClass) {
                    $q->where('class_id', $selectedClass);
                })
                ->get();
        }

        return view('admin.reports.index', compact('classes', 'students', 'selectedClass'));
    }

    public function printReport($class_id)
    {
        $class = SchoolClass::findOrFail($class_id);
        $schoolId = auth()->user()->school_id;

        if ($class->school_id !== $schoolId) {
            abort(403);
        }

        $students = User::role('student')
            ->whereHas('studentProfile', function($q) use ($class_id) {
                $q->where('class_id', $class_id);
            })
            ->get();

        $school = DB::table('schools')->find($schoolId);

        return view('admin.print_report', compact('class', 'students', 'school'));
    }

    // ==========================================
    // Advanced Reports
    // ==========================================
    public function advancedDashboard()
    {
        return view('admin.reports.advanced');
    }

    public function topStudents(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->get();
        $selectedClass = $request->class_id;

        $topStudents = collect();
        if ($selectedClass) {
            $students = User::role('student')
                ->where('school_id', $schoolId)
                ->whereHas('studentProfile', function($q) use ($selectedClass) {
                    $q->where('class_id', $selectedClass);
                })
                ->with('studentProfile')
                ->get();

            foreach ($students as $student) {
                // Calculate total score for all subjects
                $totalScore = DB::table('student_scores')
                    ->where('student_id', $student->id)
                    ->sum('total_score');
                
                $student->total_score = $totalScore;
                $topStudents->push($student);
            }

            $topStudents = $topStudents->sortByDesc('total_score')->take(10);
        }

        return view('admin.reports.top_students', compact('classes', 'selectedClass', 'topStudents'));
    }

    public function subjectPerformance()
    {
        $schoolId = auth()->user()->school_id;
        // Get all subjects taught in the school
        $subjects = Subject::where(function($q) use ($schoolId) {
            $q->whereNull('school_id')->orWhere('school_id', $schoolId);
        })->get();

        $performance = [];
        foreach ($subjects as $sub) {
            $avg = DB::table('student_scores')
                ->join('users', 'student_scores.student_id', '=', 'users.id')
                ->where('users.school_id', $schoolId)
                ->where('student_scores.subject_id', $sub->id)
                ->avg('total_score');
            
            if ($avg > 0) {
                $performance[] = [
                    'name' => $sub->name,
                    'average' => round($avg, 2)
                ];
            }
        }

        // تقرير أداء الدروس (استيعاب الطلاب للدروس بناءً على الاختبارات القصيرة)
        $lessonPerformance = DB::table('quiz_attempts')
            ->join('student_profiles', 'quiz_attempts.student_id', '=', 'student_profiles.id')
            ->join('users', 'student_profiles.user_id', '=', 'users.id')
            ->join('lessons', 'quiz_attempts.lesson_id', '=', 'lessons.id')
            ->join('subjects', 'lessons.subject_id', '=', 'subjects.id')
            ->where('users.school_id', $schoolId)
            ->where('quiz_attempts.total', '>', 0)
            ->select(
                'subjects.name as subject_name',
                'lessons.title as lesson_name',
                DB::raw('SUM(quiz_attempts.score) as total_score'),
                DB::raw('SUM(quiz_attempts.total) as total_possible')
            )
            ->groupBy('subjects.id', 'subjects.name', 'lessons.id', 'lessons.title')
            ->get()
            ->map(function($item) {
                $item->percentage = $item->total_possible > 0 ? round(($item->total_score / $item->total_possible) * 100, 1) : 0;
                return $item;
            })
            ->sortByDesc('percentage');

        return view('admin.reports.subject_performance', compact('performance', 'lessonPerformance'));
    }

    public function teachersPerformance()
    {
        $schoolId = auth()->user()->school_id;
        $teachers = User::role('teacher')->where('school_id', $schoolId)->get();
        
        $teacherStats = [];
        foreach ($teachers as $teacher) {
            // Count sections taught
            $sectionsCount = DB::table('teacher_subject_section')
                ->where('teacher_id', $teacher->id)
                ->count();
                
            // Get average score of their students in their subjects
            $teacherSubjects = DB::table('teacher_subject_section')
                ->where('teacher_id', $teacher->id)
                ->pluck('subject_id');
                
            $avgScore = 0;
            if ($teacherSubjects->count() > 0) {
                $avgScore = DB::table('student_scores')
                    ->join('users', 'student_scores.student_id', '=', 'users.id')
                    ->where('users.school_id', $schoolId)
                    ->whereIn('student_scores.subject_id', $teacherSubjects)
                    ->avg('total_score');
            }

            $teacherStats[] = [
                'name' => $teacher->name,
                'sections_count' => $sectionsCount,
                'avg_student_score' => round($avgScore ?? 0, 2)
            ];
        }

        return view('admin.reports.teachers_performance', compact('teacherStats'));
    }

    public function attendanceAnalytics()
    {
        $schoolId = auth()->user()->school_id;
        
        // Count total attendance vs absence
        $presentCount = DB::table('attendances')
            ->join('users', 'attendances.student_id', '=', 'users.id')
            ->where('users.school_id', $schoolId)
            ->where('status', 'حاضر')
            ->count();
            
        $absentCount = DB::table('attendances')
            ->join('users', 'attendances.student_id', '=', 'users.id')
            ->where('users.school_id', $schoolId)
            ->where('status', 'غائب')
            ->count();

        return view('admin.reports.attendance_analytics', compact('presentCount', 'absentCount'));
    }

    public function financialReport()
    {
        $schoolId = auth()->user()->school_id;

        // Total fees collected
        $totalFees = DB::table('student_profiles')
            ->join('users', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.school_id', $schoolId)
            ->sum('paid_fees');

        // Total wallet recharges
        $totalWallet = DB::table('student_profiles')
            ->join('users', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.school_id', $schoolId)
            ->sum('wallet_balance');

        // Total expenses
        $totalExpenses = DB::table('expenses')
            ->where('school_id', $schoolId)
            ->sum('amount');

        return view('admin.reports.financial_report', compact('totalFees', 'totalWallet', 'totalExpenses'));
    }

    public function printCertificate($student_id)
    {
        return $this->printStudentCertificate($student_id);
    }

    public function printStudentCertificate($studentId)
    {
        $student = User::with('studentProfile.schoolClass.grade')->findOrFail($studentId);
        
        $classId = $student->studentProfile->class_id; 
        $school = DB::table('schools')->find($student->school_id);

        $subjects = DB::table('teacher_subject_section')
            ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
            ->where('teacher_subject_section.section_id', $classId)
            ->select('subjects.id', 'subjects.name')
            ->distinct() 
            ->get();

        $marks = [];
        $totalSum = 0;
        $maxPossibleSum = 0; 

        foreach ($subjects as $subject) {
            
            $scores = DB::table('student_scores')
                ->where('student_id', $studentId)
                ->where('subject_id', $subject->id)
                ->get();

            $scoreSem1 = $scores->firstWhere('semester', 1) ?? $scores->firstWhere('semester', 'first');
            $scoreSem2 = $scores->firstWhere('semester', 2) ?? $scores->firstWhere('semester', 'second');
            $singleRow = $scores->first();

            $works_sem1 = floatval($scoreSem1->works_score ?? ($singleRow->works_score_sem1 ?? 0));
            $final_sem1 = floatval($scoreSem1->final_score ?? ($singleRow->final_score_sem1 ?? 0));

            $works_sem2 = floatval($scoreSem2->works_score ?? ($singleRow->works_score_sem2 ?? 0));
            $final_sem2 = floatval($scoreSem2->final_score ?? ($singleRow->final_score_sem2 ?? 0));

            $subjectTotal = $works_sem1 + $final_sem1 + $works_sem2 + $final_sem2;

            $marks[] = (object) [
                'subject_name'     => $subject->name,
                'works_score_sem1' => $works_sem1 > 0 ? $works_sem1 : '-',
                'final_score_sem1' => $final_sem1 > 0 ? $final_sem1 : '-',
                'works_score_sem2' => $works_sem2 > 0 ? $works_sem2 : '-',
                'final_score_sem2' => $final_sem2 > 0 ? $final_sem2 : '-',
                'total_score'      => $subjectTotal > 0 ? $subjectTotal : '-',
            ];

            $totalSum += $subjectTotal;
            $maxPossibleSum += 100; 
        }

        $percentage = $maxPossibleSum > 0 ? ($totalSum / $maxPossibleSum) * 100 : 0;

        return view('teacher.certificates.print', compact('student', 'school', 'marks', 'totalSum', 'percentage'));
    }

    public function editMarks(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $activeGradeIds = DB::table('school_grade')
                            ->where('school_id', $schoolId)
                            ->pluck('grade_id');

        $classes = SchoolClass::where('school_id', $schoolId)
                            ->whereIn('grade_id', $activeGradeIds)
                            ->get();
        
        $subjects = collect();
        $assessments = collect();
        $students = collect();
        $existingMarks = collect();

        $selectedClass = $request->class_id;
        $selectedSubject = $request->subject_id;

        if ($selectedClass) {
            $class = SchoolClass::find($selectedClass);
            if ($class) {
                $subjects = Subject::where('grade_id', $class->grade_id)
                                ->where(function($q) use ($schoolId) {
                                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                                })
                                ->get();
            }
        }

        if ($selectedClass && $selectedSubject) {
            $selectedSemester = $request->semester; 

            $assessments = \App\Models\Assessment::where('subject_id', $selectedSubject)
                ->when($selectedSemester, function($query) use ($selectedSemester) {
                    return $query->where('semester', $selectedSemester);
                })->get();
            
            $students = StudentProfile::where('class_id', $selectedClass)
                            ->with('user')
                            ->get();
                            
            $assessmentIds = $assessments->pluck('id');
            $existingMarks = \App\Models\AssessmentMark::whereIn('assessment_id', $assessmentIds)
                                ->get()
                                ->groupBy('student_id')
                                ->map(function ($items) {
                                    return $items->keyBy('assessment_id');
                                });
        }

        return view('admin.marks.edit', compact(
            'classes', 'subjects', 'assessments', 'students', 'existingMarks',
            'selectedClass', 'selectedSubject'
        ));
    }

    public function updateMarks(Request $request)
    {
        $request->validate([
            'marks' => 'required|array'
        ]);

        foreach ($request->marks as $studentId => $assessments) {
            foreach ($assessments as $assessmentId => $markValue) {
                if ($markValue !== null && $markValue !== '') {
                    \App\Models\AssessmentMark::updateOrCreate(
                        [
                            'assessment_id' => $assessmentId,
                            'student_id' => $studentId
                        ],
                        [
                            'score' => $markValue
                        ]
                    );
                } 
            }
        }

        return back()->with('success', 'تم حفظ وتحديث درجات الطلاب بنجاح!');
    }
}
