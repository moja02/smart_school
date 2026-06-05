<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// استدعاء كافة المودلز (الجداول) لكي لا يحدث خطأ "Class not found"
use App\Models\StudentProfile;
use App\Models\SchoolClass; 
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\AssessmentMark;
use App\Models\Message;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\QuizAttempt;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\User;

class StudentController extends Controller
{
    /**
     * 1. لوحة تحكم الطالب
     */
    /**
     * 1. لوحة تحكم الطالب
     */
    public function dashboard()
    {
        $user = auth()->user();

        // 1. جلب الفصل الدراسي للطالب
        $studentProfile = StudentProfile::where('user_id', $user->id)->first();
        $class_id = $studentProfile->class_id ?? $studentProfile->section_id ?? null;

        if (!$studentProfile || !$class_id) {
            return view('student.dashboard', [
                'user' => $user,
                'class' => null,
                'subjects' => collect([])
            ]);
        }

        $class = SchoolClass::find($class_id);

        // 2. جلب المواد الدراسية التابعة لمرحلة هذا الفصل
        $subjects = Subject::where('grade_id', $class->grade_id ?? null)->get();

        // 3. تجهيز بيانات كل مادة (الامتحانات، المعلم، الدرجات)
        foreach ($subjects as $subject) {
            
            // أ) جلب اسم المعلم من جدول التوزيع (الربط الجديد بالشعبة/الفصل)
            $teacherSubject = DB::table('teacher_subject_section')
                                ->where('section_id', $class->id) // البحث برقم الشعبة/الفصل الخاص بالطالب
                                ->where('subject_id', $subject->id)
                                ->first();
                                
            if ($teacherSubject) {
                $subject->teacher_name = $teacherSubject->teacher_name ?? 'غير محدد';
            } else {
                $subject->teacher_name = 'غير محدد';
            }

            // ب) جلب الامتحانات القادمة
            $subject->upcoming_exams = Exam::where('subject_id', $subject->id)
                                        ->whereDate('exam_date', '>=', now())
                                        ->orderBy('exam_date')
                                        ->get(); 

            // ج) جلب درجات الطالب (تم التحديث للجدول الجديد student_scores)
            $scoreRecord = DB::table('student_scores')
                                ->where('student_id', $studentProfile->id) // البحث برقم بروفايل الطالب
                                ->where('subject_id', $subject->id)
                                ->first(); 
                                    
            // إنشاء مصفوفة (Collection) للدرجات لكي تعمل مع الكود الموجود في صفحة Blade
            $myGrades = collect();
            
            if ($scoreRecord) {
                // إذا وجدت الدرجة، نضيفها للمصفوفة
                $myGrades->push((object)[
                    'score' => $scoreRecord->total_score ?? 0,
                    'max_score' => 100, // يمكنك تحديث هذه القيمة لتقرأ من إعدادات المادة إذا رغبت
                    'title' => 'المجموع النهائي'
                ]);
            }

            $subject->my_grades = $myGrades;
        }

        return view('student.dashboard', compact('user', 'class', 'subjects'));
    }

    /**
     * 2. عرض المواد الدراسية
     */
    /**
     * 2. عرض المواد الدراسية
     */
    public function mySubjects()
    {
        $user = auth()->user();

        $studentProfile = StudentProfile::where('user_id', $user->id)->first();
        $class_id = $studentProfile->class_id ?? $studentProfile->section_id ?? null;

        if (!$studentProfile || !$class_id) {
            return view('student.no-class'); 
        }

        $class = SchoolClass::find($class_id);

        // جلب المواد المرتبطة بمرحلة (Grade) هذا الطالب فقط
        $subjects = Subject::where('grade_id', $class->grade_id ?? null)->get();

        foreach ($subjects as $subject) {
            // ✅ تم التصحيح هنا: استخدام teacher_subject_section و section_id
            $teacherSubject = DB::table('teacher_subject_section')
                                ->where('section_id', $class->id)
                                ->where('subject_id', $subject->id)
                                ->first();
            
            // ✅ تم اختصار الكود لأن اسم المعلم مخزن بالفعل في جدول الإسناد
            if ($teacherSubject) {
                $subject->teacher_name = $teacherSubject->teacher_name ?? 'غير محدد';
            } else {
                $subject->teacher_name = 'غير محدد';
            }
        }

        return view('student.subjects.index', compact('subjects'));
    }

    /**
     * 3. عرض تفاصيل المادة: الدرجات، الدروس والأسئلة
     */
    /**
     * 3. عرض تفاصيل المادة: الدرجات، الدروس والأسئلة
     */
    public function showSubject($id)
    {
        $user = auth()->user();
        $student = $user->studentProfile;
        $subject = Subject::findOrFail($id);
        
        // 1. جلب الدروس
        $lessons = Lesson::where('subject_id', $id)->with('questions')->get();

        // 2. جلب توزيع الدرجات المعتمد
        $distribution = method_exists($subject, 'getGradeDistribution') 
                        ? $subject->getGradeDistribution() 
                        : ['works' => 40, 'final' => 60, 'total' => 100];

        // 3. جلب جميع التقييمات التي أضافها الأستاذ لهذه المادة
        $assessments = \App\Models\Assessment::where('subject_id', $id)->get();
        
        $worksTotal = 0;
        $finalTotal = 0;

        $maxPossibleTotal = 0;

        // دمج درجة الطالب مع كل تقييم
        foreach ($assessments as $assessment) {
            if ($student) {
                // البحث عن درجة الطالب في هذا التقييم تحديداً
                $markRecord = AssessmentMark::where('student_id', $student->id)
                                            ->where('assessment_id', $assessment->id)
                                            ->first();
                                            
                $assessment->student_mark = $markRecord ? ($markRecord->marks ?? $markRecord->score ?? $markRecord->mark ?? 0) : null;
                $assessment->student_notes = $markRecord ? ($markRecord->notes ?? $markRecord->remarks ?? '') : '';
            } else {
                $assessment->student_mark = null;
                $assessment->student_notes = '';
            }

            // تجميع الدرجات المرصودة فقط لحساب المجموع العام
            if ($assessment->student_mark !== null) {
                $title = strtolower($assessment->title ?? $assessment->name ?? $assessment->type ?? '');
                if (str_contains($title, 'final') || str_contains($title, 'نهائي')) {
                    $finalTotal += $assessment->student_mark;
                } else {
                    $worksTotal += $assessment->student_mark;
                }
            }
            
            // حساب إجمالي الدرجة المتاحة من التقييمات المُنشأة
            $maxPossibleTotal += ($assessment->max_score ?? $assessment->full_mark ?? 0);
        }

        $studentTotal = $worksTotal + $finalTotal;
        $percentage = $maxPossibleTotal > 0 ? round(($studentTotal / $maxPossibleTotal) * 100, 1) : 0;

        return view('student.subjects.show', compact(
            'subject', 'lessons', 'distribution', 'assessments', 
            'worksTotal', 'finalTotal', 'studentTotal', 'percentage'
        ));
    }

    /**
     * 4. كشف الدرجات الشامل
     */
    public function myGrades()
    {
        $student = Auth::user()->studentProfile;
        
        $marks = AssessmentMark::where('student_id', $student->id)
                    ->with(['assessment.subject'])
                    ->get()
                    ->groupBy(function($item) {
                        return $item->assessment->subject->name;
                    });

        return view('student.grades', compact('marks'));
    }

    /**
     * 4. كشف الدرجات الشامل (الشهادة الرسمية للطالب)
     */
    public function reportCard()
    {
        $user = auth()->user();
        $studentProfile = \App\Models\StudentProfile::where('user_id', $user->id)->first();
        
        if (!$studentProfile || !$studentProfile->class_id) {
            return redirect()->back()->with('error', 'عذراً، يجب أن تكون مسجلاً في فصل دراسي لعرض كشف الدرجات.');
        }

        $classId = $studentProfile->class_id;
        $class = \App\Models\SchoolClass::with('grade')->find($classId);
        $school = \DB::table('schools')->find($user->school_id);

        // 1. جلب جميع المواد المقررة على هذا الفصل
        $subjects = \DB::table('teacher_subject_section')
            ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
            ->where('teacher_subject_section.section_id', $classId)
            ->select('subjects.id', 'subjects.name')
            ->distinct() 
            ->get();

        // 2. جلب درجات (النهائي) من جدول student_scores
        $finalScores = \DB::table('student_scores')
            ->where('student_id', $user->id)
            ->get()
            ->keyBy('subject_id');

        // 3. جلب درجات (أعمال السنة) مقسمة ومجمعة من التقييمات
        $assessmentMarks = \DB::table('assessment_marks')
            ->join('assessments', 'assessment_marks.assessment_id', '=', 'assessments.id')
            ->where('assessment_marks.student_id', $user->id)
            ->select(
                'assessments.subject_id', 
                'assessments.semester', 
                \DB::raw('SUM(assessment_marks.score) as total_works')
            )
            ->groupBy('assessments.subject_id', 'assessments.semester')
            ->get();

        $marks = [];
        $totalSum = 0;
        $maxPossibleSum = 0;

        // 4. الدوران على المواد وتجميع البيانات للشهادة
        foreach ($subjects as $subject) {
            
            // استخراج أعمال السميستر الأول والثاني
            $worksSem1Record = $assessmentMarks->where('subject_id', $subject->id)
                ->filter(function($item) { return in_array($item->semester, [1, '1', 'first']); })->first();
            $worksSem2Record = $assessmentMarks->where('subject_id', $subject->id)
                ->filter(function($item) { return in_array($item->semester, [2, '2', 'second']); })->first();

            $works_sem1 = floatval($worksSem1Record->total_works ?? 0);
            $works_sem2 = floatval($worksSem2Record->total_works ?? 0);

            // استخراج نهائي السميستر الأول والثاني
            $finalRecord = $finalScores->get($subject->id);
            $final_sem1 = floatval($finalRecord->final_score_sem1 ?? 0);
            $final_sem2 = floatval($finalRecord->final_score_sem2 ?? 0);

            // المجموع الكلي للمادة
            $subjectTotal = $works_sem1 + $works_sem2 + $final_sem1 + $final_sem2;

            $marks[] = (object) [
                'subject_name'     => $subject->name,
                'works_score_sem1' => $works_sem1 > 0 ? $works_sem1 : '-',
                'final_score_sem1' => $final_sem1 > 0 ? $final_sem1 : '-',
                'works_score_sem2' => $works_sem2 > 0 ? $works_sem2 : '-',
                'final_score_sem2' => $final_sem2 > 0 ? $final_sem2 : '-',
                'total_score'      => $subjectTotal > 0 ? $subjectTotal : '-',
            ];

            $totalSum += $subjectTotal;
            // جمع الحد الأقصى من التقييمات لهذه المادة
            $subjectMaxTotal = \App\Models\Assessment::where('subject_id', $subject->id)
                                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(max_score, full_mark, 0)'));
            $maxPossibleSum += $subjectMaxTotal > 0 ? $subjectMaxTotal : 0;
        }

        $percentage = $maxPossibleSum > 0 ? ($totalSum / $maxPossibleSum) * 100 : 0;

        return view('student.report_card', compact('user', 'class', 'school', 'marks', 'totalSum', 'percentage'));
    }
    // --- الدوال الإضافية ---

    public function profile()
    {
        $studentProfile = Auth::user()->studentProfile;
        return view('student.profile', compact('studentProfile'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return redirect()->route('student.profile')->with('success', 'تم التحديث بنجاح');
    }

    public function schedule()
    {
        $studentProfile = Auth::user()->studentProfile;
        if (!$studentProfile || !$studentProfile->class_id) return back()->with('error', 'لا يوجد فصل');
        
        $schedules = Schedule::where('class_id', $studentProfile->class_id)
                        ->with('subject')->orderBy('period')->get()->groupBy('day');
        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $periods = [1, 2, 3, 4, 5, 6];
        return view('student.schedule', compact('schedules', 'days', 'periods'));
    }

    public function attendance()
    {
        $studentProfile = Auth::user()->studentProfile;
        $records = Attendance::where('student_id', $studentProfile->id)->orderBy('attendance_date', 'desc')->get();
        $absentCount = $records->where('status', 0)->count();
        $presentCount = $records->where('status', 1)->count();
        return view('student.attendance', compact('records', 'absentCount', 'presentCount'));
    }

    public function messages()
    {
        $messages = Message::where('receiver_id', Auth::id())->latest()->get();
        return view('student.messages', compact('messages'));
    }
    
    // الاختبارات
    public function startQuiz($id)
    {
        $student = Auth::user()->studentProfile;
        $lesson = Lesson::with('questions')->findOrFail($id);

        $previousAttempt = QuizAttempt::where('student_id', $student->id)
                                      ->where('lesson_id', $lesson->id)
                                      ->first();

        if ($previousAttempt) {
            return redirect()->route('student.subjects.show', $lesson->subject_id)
                   ->with('error', 'لقد قمت بأداء هذا الاختبار مسبقاً.');
        }

        if ($lesson->questions->isEmpty()) {
            return back()->with('error', 'لا توجد أسئلة مضافة لهذا الدرس.');
        }

        return view('student.quiz.start', compact('lesson'));
    }

    public function submitQuiz(Request $request, $id)
    {
        $student = Auth::user()->studentProfile;
        
        if (QuizAttempt::where('student_id', $student->id)->where('lesson_id', $id)->exists()) {
            return redirect()->back()->with('error', 'تم استلام إجابتك مسبقاً.');
        }

        $lesson = Lesson::with('questions')->findOrFail($id);
        $questions = $lesson->questions;
        
        $score = 0;
        $total = $questions->count(); 
        $results = [];

        foreach ($questions as $question) {
            $userAnswer = $request->input('q_' . $question->id);
            $isCorrect = false;

            if ($userAnswer && trim(strtolower($userAnswer)) == trim(strtolower($question->correct_answer))) {
                $score++; 
                $isCorrect = true;
            }

            $results[] = [
                'question' => $question->content,
                'user_answer' => $userAnswer ?? 'لم تتم الإجابة',
                'correct_answer' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'question_score' => 1,
                'score_earned' => $isCorrect ? 1 : 0,
                'explanation' => $question->feedback
            ];
        }

        QuizAttempt::create([
            'student_id' => $student->id,
            'lesson_id'  => $lesson->id,
            'score'      => $score,
            'total'      => $total
        ]);

        $percentage = ($total > 0) ? round(($score / $total) * 100, 1) : 0;

        return view('student.quiz.result', compact('lesson', 'score', 'total', 'percentage', 'results'));
    }
    /**
     * عرض جدول الامتحانات (الـ 30 يوماً القادمة)
     */
    public function examsCalendar()
    {
        $user = auth()->user();
        $studentProfile = \App\Models\StudentProfile::where('user_id', $user->id)->first();
        $class_id = $studentProfile->class_id ?? $studentProfile->section_id ?? null;

        if (!$studentProfile || !$class_id) {
            return view('student.no-class'); 
        }

        $class = \App\Models\SchoolClass::find($class_id);
        
        // جلب مواد الطالب
        $subjects = \App\Models\Subject::where('grade_id', $class->grade_id ?? null)->get();
        $subjectIds = $subjects->pluck('id');

        // جلب جميع الامتحانات القادمة لهذه المواد
        $exams = \App\Models\Exam::whereIn('subject_id', $subjectIds)
                                 ->whereDate('exam_date', '>=', now())
                                 ->with('subject')
                                 ->orderBy('exam_date')
                                 ->get();
                                 
        // تجميع الامتحانات بناءً على التاريخ لتسهيل وضعها في الجدول
        $examsByDate = $exams->groupBy(function($exam) {
            return \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d');
        });

        // إنشاء مصفوفة للأيام الثلاثين القادمة برمجياً
        $calendarDays = [];
        $today = \Carbon\Carbon::now();
        
        for ($i = 0; $i < 30; $i++) {
            $currentDate = $today->copy()->addDays($i);
            $dateStr = $currentDate->format('Y-m-d');
            
            $calendarDays[] = [
                'date' => $currentDate,
                'day_name' => $currentDate->locale('ar')->translatedFormat('l'), // اسم اليوم (الأحد، الإثنين..)
                'day_num' => $currentDate->format('d'), // رقم اليوم
                'month_name' => $currentDate->locale('ar')->translatedFormat('F'), // اسم الشهر
                'has_exam' => isset($examsByDate[$dateStr]), // هل يوجد امتحان في هذا اليوم؟
                'exams' => $examsByDate[$dateStr] ?? collect() // تفاصيل الامتحان
            ];
        }

        return view('student.exams', compact('calendarDays', 'exams'));
    }
}