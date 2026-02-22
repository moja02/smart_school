<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\AssessmentMark;
use App\Models\Message;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\QuizAttempt;

class StudentController extends Controller
{
    /**
     * 1. لوحة تحكم الطالب
     */
    public function dashboard()
    {
        $user = auth()->user();

        // 1. جلب الفصل الدراسي للطالب
        // نستخدم العلاقة عبر البروفايل أو نبحث يدوياً في student_profiles
        $studentProfile = \App\Models\StudentProfile::where('user_id', $user->id)->first();
        
        if (!$studentProfile || !$studentProfile->class_id) {
            // حالة الطالب غير مسكن في فصل
            return view('student.dashboard', [
                'user' => $user,
                'class' => null,
                'subjects' => collect([])
            ]);
        }

        $class = \App\Models\SchoolClass::find($studentProfile->class_id);

        // 2. جلب المواد الدراسية الخاصة بمرحلة هذا الفصل
        $subjects = \App\Models\Subject::where('grade_id', $class->grade_id)->get();

        // 3. تجهيز بيانات كل مادة (الامتحانات، المعلم، الدرجات)
        foreach ($subjects as $subject) {
            
            // أ) جلب اسم المعلم من جدول التوزيع (teacher_subject)
            $teacherSubject = \DB::table('teacher_subject')
                                ->where('class_id', $class->id)
                                ->where('subject_id', $subject->id)
                                ->first();
                                
            if ($teacherSubject) {
                $teacher = \App\Models\User::find($teacherSubject->teacher_id);
                $subject->teacher_name = $teacher ? $teacher->name : 'غير محدد';
            } else {
                $subject->teacher_name = 'غير محدد';
            }

            // ب) جلب الامتحانات القادمة (من جدول exams)
            // نتأكد أنها لنفس المادة ولنفس الفصل
            $subject->upcoming_exams = \App\Models\Exam::where('subject_id', $subject->id)
                                        ->where('class_id', $class->id)
                                        ->whereDate('exam_date', '>=', now())
                                        ->orderBy('exam_date')
                                        ->get(); // get() تعيد Collection دائماً ولا تعيد null

            // ج) جلب درجات الطالب (من جدول marks)
            $subject->my_grades = \App\Models\Mark::where('user_id', $user->id)
                                    ->where('subject_id', $subject->id)
                                    ->get(); // get() تعيد Collection دائماً
                                    
            // إضافة حقول إضافية للعرض (لتجنب الأخطاء في الفيو)
            foreach($subject->my_grades as $grade) {
                 // نفترض أن الحد الأقصى افتراضياً 100 إذا لم يحدد
                 $grade->max_score = 100; 
                 $grade->title = $grade->term ?? 'اختبار';
            }
        }

        return view('student.dashboard', compact('user', 'class', 'subjects'));
    }

    /**
     * 2. عرض المواد الدراسية
     */
    public function mySubjects()
    {
        $student = Auth::user()->studentProfile;
        
        if (!$student || !$student->schoolClass) {
            return back()->with('error', 'لم يتم تحديد فصل دراسي لك.');
        }

        // جلب المواد المرتبطة بفصل الطالب
        $subjects = $student->schoolClass->subjects;
        
        return view('student.subjects.index', compact('subjects'));
    }

    /**
     * 3. عرض تفاصيل المادة: الدروس والأسئلة
     */
    public function showSubject($id)
    {
        $subject = Subject::findOrFail($id);
        
        // جلب الدروس مع الأسئلة التابعة لها
        $lessons = Lesson::where('subject_id', $id)
                    ->with('questions')
                    ->get();

        return view('student.subjects.show', compact('subject', 'lessons'));
    }

    /**
     * 4. كشف الدرجات الشامل
     */
    public function myGrades()
    {
        $student = Auth::user()->studentProfile;
        
        // جلب كل الدرجات مرتبة حسب المادة
        $marks = AssessmentMark::where('student_id', $student->id)
                    ->with(['assessment.subject'])
                    ->get()
                    ->groupBy(function($item) {
                        return $item->assessment->subject->name;
                    });

        return view('student.grades', compact('marks'));
    }

    // --- الدوال القديمة للحفاظ على عمل باقي النظام ---

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
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
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
    // 1. بدء الاختبار
    public function startQuiz($id)
    {
        $student = Auth::user()->studentProfile;
        $lesson = Lesson::with('questions')->findOrFail($id);

        // التحقق من المحاولة السابقة
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

    // 2. تصحيح وحفظ النتيجة (بسيط: كل سؤال = درجة)
    public function submitQuiz(Request $request, $id)
    {
        $student = Auth::user()->studentProfile;
        
        // منع التكرار
        if (QuizAttempt::where('student_id', $student->id)->where('lesson_id', $id)->exists()) {
            return redirect()->back()->with('error', 'تم استلام إجابتك مسبقاً.');
        }

        $lesson = Lesson::with('questions')->findOrFail($id);
        $questions = $lesson->questions;
        
        $score = 0;
        $total = $questions->count(); // المجموع الكلي = عدد الأسئلة
        $results = [];

        foreach ($questions as $question) {
            $userAnswer = $request->input('q_' . $question->id);
            $isCorrect = false;

            // مقارنة الإجابة
            if ($userAnswer && trim(strtolower($userAnswer)) == trim(strtolower($question->correct_answer))) {
                $score++; // زيادة 1 لكل إجابة صحيحة
                $isCorrect = true;
            }

            $results[] = [
                'question' => $question->content,
                'user_answer' => $userAnswer ?? 'لم تتم الإجابة',
                'correct_answer' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'question_score' => 1, // ثابت 1 للعرض فقط
                'score_earned' => $isCorrect ? 1 : 0, // للعرض
                'explanation' => $question->feedback
            ];
        }

        // حفظ النتيجة
        QuizAttempt::create([
            'student_id' => $student->id,
            'lesson_id'  => $lesson->id,
            'score'      => $score,
            'total'      => $total
        ]);

        $percentage = ($total > 0) ? round(($score / $total) * 100, 1) : 0;

        return view('student.quiz.result', compact('lesson', 'score', 'total', 'percentage', 'results'));
    }
}