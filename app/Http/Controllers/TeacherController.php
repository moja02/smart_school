<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
//use App\Models\Subject;
//use App\Models\SchoolClass;
use Illuminate\Http\Request;
//use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;
use App\Models\{Subject, Lesson, Question, Assessment, AssessmentMark, SchoolClass, StudentProfile};
use App\Models\QuizAttempt;
use App\Models\ExamSchedule;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacherId = \Illuminate\Support\Facades\Auth::id();

        // 1. جلب الفصول التي يدرسها المعلم
        $classes = \App\Models\SchoolClass::whereHas('subjects', function($query) use ($teacherId) {
            $query->where('teacher_subject.teacher_id', $teacherId);
        })->distinct()->get();

        // 2. حساب إجمالي عدد الطلاب في هذه الفصول
        $studentsCount = \App\Models\StudentProfile::whereIn('class_id', $classes->pluck('id'))->count();

        // 3. حساب عدد المواد التي يدرسها المعلم
        // نستخدم DB table مباشرة للأداء الأفضل أو عبر الموديل
        $subjectsCount = \Illuminate\Support\Facades\DB::table('teacher_subject')
                            ->where('teacher_id', $teacherId)
                            ->distinct('subject_id')
                            ->count();

        // 4. آخر الرسائل الواردة (تنبيهات سريعة)
        $recentMessages = \App\Models\Message::where('receiver_id', $teacherId)
                            ->where('is_read', 0)
                            ->latest()
                            ->take(5)
                            ->get();

        return view('teacher.dashboard', compact('classes', 'studentsCount', 'subjectsCount', 'recentMessages'));
    }

    public function students(Request $request)
    {
        $teacher = auth()->user();
        if ($teacher->role !== 'teacher') abort(403);

        $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'class_id'   => 'nullable|exists:classes,id',
        ]);

        $teaching = $teacher->teachingSubjects;

        // قوائم الفلاتر من مواد الأستاذ فقط
        $filterSubjects = $teaching->unique('id')->values();

        $filterClasses = SchoolClass::whereIn('id', $teaching->pluck('pivot.class_id')->unique())->get();

        $students = collect();

        if ($request->filled('subject_id') && $request->filled('class_id')) {
            // تأكد أن الأستاذ فعلاً يدرّس هذا (المادة + الصف)
            $allowed = $teaching->first(function ($s) use ($request) {
                return (int)$s->id === (int)$request->subject_id
                    && (int)$s->pivot->class_id === (int)$request->class_id;
            });

            if (!$allowed) abort(403, 'هذه المادة/الصف ليس ضمن موادك.');

            $students = User::where('role', 'student')
                ->whereIn('id', function ($q) use ($request) {
                    $q->select('student_id')
                    ->from('student_subject')
                    ->where('subject_id', $request->subject_id)
                    ->where('class_id', $request->class_id);
                })
                ->orderBy('name')
                ->get();
        }

        return view('teacher.students', compact(
            'filterSubjects', 'filterClasses', 'students'
        ));
    }

    
    // 1. دالة عرض نموذج إضافة الدرجة
    public function createGrade($student_id)
    {
    //$student = StudentProfile::with('user')->findOrFail($student_id);
    $student = \App\Models\StudentProfile::with('user')->findOrFail($student_id);
    // قائمة مواد مقترحة (يمكنك تغييرها)
    //$subjects = ['الرياضيات', 'اللغة العربية', 'العلوم', 'اللغة الإنجليزية', 'التربية الإسلامية', 'الحاسب الآلي'];
    $subjects = \Illuminate\Support\Facades\Auth::user()->teaching()->pluck('subjects.name');
    return view('teacher.create_grade', compact('student', 'subjects'));
    }
    // 2. دالة حفظ الدرجة
    public function storeGrade(Request $request, $student_id)
    {
    // التحقق من البيانات
    $request->validate([
        'subject' => 'required',
        'total_score' => 'required|numeric|min:0|max:100',
        ]);
            
    // حفظ أو تحديث الدرجة
    Grade::updateOrCreate(
    [
    'student_id' => $student_id,
    'subject' => $request->subject
    ],
    [
    'total_score' => $request->total_score,
    'max_score' => 100,
    'term' => 'الفصل الدراسي الأول'
    ]
    );
    
    return redirect()->route('teacher.class', StudentProfile::find($student_id)->class_id)
    ->with('success', 'تم رصد الدرجة بنجاح!');
    }

    // عرض قائمة الفصول الدراسية للمعلم
    public function myClasses()
    {
        $teacherId = Auth::id();
        
        // جلب الفصول التي يدرسها المعلم (عبر جدول التوزيع)
        // نستخدم distinct لمنع تكرار الفصل إذا كان المعلم يدرسه أكثر من مادة
        $classes = \App\Models\SchoolClass::whereHas('subjects', function($query) use ($teacherId) {
            $query->where('teacher_subject.teacher_id', $teacherId);
            })->distinct()->get();
            
            return view('teacher.classes.index', compact('classes'));
    }
    // عرض تفاصيل فصل معين (الطلاب + المواد)
    public function showClass($id)
    {
        $class = \App\Models\SchoolClass::with(['students.user', 'students.parent.user'])->findOrFail($id);
        
        // جلب المواد التي يدرسها هذا المعلم لهذا الفصل فقط
        $teacherSubjects = \App\Models\Subject::whereHas('teachers', function($query) use ($class) {
            $query->where('teacher_subject.teacher_id', Auth::id())
                    ->where('teacher_subject.class_id', $class->id);
        })->get();

        return view('teacher.classes.show', compact('class', 'teacherSubjects'));
    }

    // الصفحة الرئيسية للمادة
    public function showSubject($subject_id, $class_id)
    {
        $subject = Subject::findOrFail($subject_id);
        $class = SchoolClass::findOrFail($class_id);
        
        // ✅ هذا السطر ضروري جداً
        $lessons = Lesson::where('subject_id', $subject_id)->with('questions')->get();

        return view('teacher.subject.show', compact('subject', 'class', 'lessons'));
    }

    // 1. إضافة الأسئلة
    public function createQuestion($subject_id, $class_id) {
        $subject = Subject::findOrFail($subject_id);
        $class = SchoolClass::findOrFail($class_id);
        $lessons = Lesson::where('subject_id', $subject_id)->get();
        return view('teacher.questions.create', compact('subject', 'class', 'lessons'));
    }

    public function storeQuestion(Request $request, $subject_id, $class_id)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'content' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false',
            // تم حذف التحقق من score
            'correct_answer' => 'required',
        ]);

        // معالجة الخيارات (إذا كان اختيار من متعدد)
        $options = null;
        if ($request->type == 'multiple_choice') {
            // حذف الخيارات الفارغة
            $options = array_filter($request->options, function($value) {
                return !is_null($value) && $value !== '';
            });
            // إعادة ترتيب المصفوفة
            $options = array_values($options);
        }

        // إنشاء السؤال (بدون score)
        \App\Models\Question::create([
            'lesson_id' => $request->lesson_id,
            'content' => $request->content,
            'type' => $request->type,
            'options' => $options,
            'correct_answer' => $request->correct_answer,
            'feedback' => $request->feedback
        ]);

        return back()->with('success', 'تم إضافة السؤال بنجاح');
    }

    // 2. التقييمات
    public function createAssessment($subject_id, $class_id) {
        $subject = Subject::findOrFail($subject_id);
        $class = SchoolClass::findOrFail($class_id);
        $assessments = Assessment::where('subject_id', $subject_id)->where('teacher_id', Auth::id())->get();
        return view('teacher.assessments.index', compact('subject', 'class', 'assessments'));
    }

    public function storeAssessment(Request $request, $subject_id, $class_id) {
        $request->validate(['title' => 'required', 'max_score' => 'required|integer|min:1']);
        Assessment::create([
            'subject_id' => $subject_id, 'teacher_id' => Auth::id(),
            'title' => $request->title, 'max_score' => $request->max_score
        ]);
        return back()->with('success', 'تم إنشاء التقييم');
    }

    // 3. رصد الدرجات
    public function monitorGrades($subject_id, $class_id, $assessment_id) {
        $subject = Subject::findOrFail($subject_id);
        $class = SchoolClass::findOrFail($class_id);
        $assessment = Assessment::findOrFail($assessment_id);
        
        // جلب طلاب الفصل المحدد فقط
        $students = StudentProfile::where('class_id', $class_id)
            ->with(['user', 'assessmentMarks' => function($q) use($assessment_id){
                $q->where('assessment_id', $assessment_id);
            }])->get();

        return view('teacher.assessments.monitor', compact('subject', 'class', 'assessment', 'students'));
    }

    public function storeGrades(Request $request, $subject_id, $class_id, $assessment_id) {
        foreach($request->grades as $student_id => $score) {
            if($score !== null) {
                AssessmentMark::updateOrCreate(
                    ['assessment_id' => $assessment_id, 'student_id' => $student_id],
                    ['score' => $score]
                );
            }
        }
        return redirect()->route('teacher.assessments.index', ['subject_id' => $subject_id, 'class_id' => $class_id])
                         ->with('success', 'تم حفظ الدرجات');
    }
    
    // 4. التقارير
    public function subjectReport($subject_id, $class_id)
    {
        $subject = \App\Models\Subject::findOrFail($subject_id);
        $class = \App\Models\SchoolClass::with('students.user')->findOrFail($class_id);

        // 1. جلب التقييمات الرسمية
        // ✅ التعديل: حذفنا where('class_id') وأضفنا where('teacher_id')
        // لنجلب فقط التقييمات التي أنشأها هذا المعلم لهذه المادة
        $assessments = \App\Models\Assessment::where('subject_id', $subject_id)
                        ->where('teacher_id', \Illuminate\Support\Facades\Auth::id()) 
                        ->get();

        // 2. جلب جميع الدرجات المرصودة لهذه التقييمات
        $marks = \App\Models\AssessmentMark::whereIn('assessment_id', $assessments->pluck('id'))->get();

        // 3. جلب الدروس (لمعرفة الاختبارات الذاتية)
        $lessons = \App\Models\Lesson::where('subject_id', $subject_id)->get();

        // 4. جلب محاولات الطلاب في الاختبارات الذاتية
        $quizAttempts = \App\Models\QuizAttempt::whereIn('lesson_id', $lessons->pluck('id'))
                        ->get();

        return view('teacher.subject.report', compact(
            'subject', 
            'class', 
            'assessments', 
            'marks', 
            'lessons', 
            'quizAttempts'
        ));
    }

    // عرض صفحة الغياب لفصل معين
    public function attendance($id)
    {
        $class = \App\Models\SchoolClass::with('students.user')->findOrFail($id);
        $date = date('Y-m-d');

        // جلب الغياب المسجل لهذا اليوم (إن وجد) لتعديله
        $attendance = \App\Models\Attendance::where('class_id', $id)
                        ->where('attendance_date', $date)
                        ->pluck('status', 'student_id')
                        ->toArray();

        return view('teacher.attendance', compact('class', 'attendance', 'date'));
    }

    // حفظ الغياب
    public function storeAttendance(Request $request, $id)
    {
        $request->validate([
            'attendance' => 'required|array',
            'date' => 'required|date',
        ]);

        foreach ($request->attendance as $student_id => $status) {
            \App\Models\Attendance::updateOrCreate(
                [
                    'student_id' => $student_id,
                    'class_id' => $id, // تأكد أن جدول الحضور يحتوي على class_id
                    'attendance_date' => $request->date
                ],
                [
                    'status' => $status // 1 = حاضر، 0 = غائب
                ]
            );
        }

        return back()->with('success', 'تم حفظ سجل الحضور والغياب بنجاح لهذا اليوم.');
    }

    // --- تعديل الدروس ---
    public function editLesson($id)
    {
        $lesson = \App\Models\Lesson::findOrFail($id);
        return view('teacher.lessons.edit', compact('lesson'));
    }

    public function updateLesson(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            // يمكنك إضافة validtion للملفات إذا أردت السماح بتغيير الملف
        ]);

        $lesson = \App\Models\Lesson::findOrFail($id);
        $lesson->update([
            'title' => $request->title,
            // أضف الحقول الأخرى هنا إذا كنت تريد تحديثها
        ]);

        return redirect()->route('teacher.subject.show', ['subject_id' => $lesson->subject_id, 'class_id' => \App\Models\SchoolClass::first()->id]) // ملاحظة: قد تحتاج لتمرير class_id الصحيح
                         ->with('success', 'تم تحديث الدرس بنجاح');
    }

    // --- تعديل الأسئلة ---
    public function editQuestion($id)
    {
        $question = \App\Models\Question::findOrFail($id);
        return view('teacher.questions.edit', compact('question'));
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = \App\Models\Question::findOrFail($id);

        $request->validate([
            'content' => 'required|string',
            'correct_answer' => 'required',
        ]);

        $data = [
            'content' => $request->content,
            'correct_answer' => $request->correct_answer,
            'feedback' => $request->feedback
        ];

        // تحديث الخيارات فقط إذا كان السؤال "اختيار من متعدد"
        if ($question->type == 'multiple_choice' && $request->has('options')) {
            $options = array_filter($request->options, function($value) {
                return !is_null($value) && $value !== '';
            });
            $data['options'] = array_values($options);
        }

        $question->update($data);

        return redirect()->route('teacher.subject.show', ['subject_id' => $question->lesson->subject_id, 'class_id' => 1]) // تعديل الرابط حسب الحاجة
                         ->with('success', 'تم تعديل السؤال بنجاح');
    }
    public function storeLesson(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        \App\Models\Lesson::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            // الدرس يتبع المادة بشكل عام، ولا يرتبط بفصل محدد عادةً إلا إذا كان تصميمك مختلفاً
        ]);

        return back()->with('success', 'تم إضافة الدرس الجديد بنجاح');
    }

    public function printReport($subject_id, $class_id)
    {
        $subject = \App\Models\Subject::findOrFail($subject_id);
        $class = \App\Models\SchoolClass::with('students.user')->findOrFail($class_id);

        // جلب البيانات (نفس الكود السابق)
        $assessments = \App\Models\Assessment::where('subject_id', $subject_id)
                        ->where('teacher_id', \Illuminate\Support\Facades\Auth::id())
                        ->get();

        $marks = \App\Models\AssessmentMark::whereIn('assessment_id', $assessments->pluck('id'))->get();
        $lessons = \App\Models\Lesson::where('subject_id', $subject_id)->get();
        $quizAttempts = \App\Models\QuizAttempt::whereIn('lesson_id', $lessons->pluck('id'))->get();

        // لاحظ هنا: نرجع view عادية
        return view('teacher.subject.report_print', compact(
            'subject', 'class', 'assessments', 'marks', 'lessons', 'quizAttempts'
        ));
    }
    // 1. عرض الصفحة
public function showSchedule($subject_id, $class_id)
{
    $subject = \App\Models\Subject::findOrFail($subject_id);
    $class = \App\Models\SchoolClass::findOrFail($class_id);
    
    return view('teacher.schedule.index', compact('subject', 'class'));
}

public function getExamsEvents($subject_id, $class_id)
{
    // جلب كل امتحانات هذا الفصل (رياضيات، إحصاء، عربي... إلخ)
    $exams = \App\Models\ExamSchedule::with(['subject', 'teacher'])
                ->where('class_id', $class_id)
                ->get();
    
    $currentSubjectId = $subject_id; // آيدي المادة التي أفتح صفحتها الآن (مثلاً الإحصاء)

    $events = $exams->map(function($exam) use ($currentSubjectId) {
        
        // الشرط الجديد: هل هذا الامتحان يخص المادة التي أفتحها الآن؟
        // إذا كان امتحان "إحصاء" وأنا في صفحة الإحصاء -> هذا امتحاني الحالي (أزرق)
        // إذا كان امتحان "رياضيات" وأنا في صفحة الإحصاء -> هذا يعتبر امتحان "مادة أخرى" (حتى لو أنا المعلم)
        $isCurrentSubjectExam = ($exam->subject_id == $currentSubjectId);

        return [
            'id' => $exam->id,
            
            // العنوان:
            // إذا نفس المادة: اعرض "عنوان الامتحان" فقط
            // إذا مادة أخرى: اعرض "اسم المادة: عنوان الامتحان"
            'title' => $isCurrentSubjectExam ? $exam->title : ($exam->subject->name . ': ' . $exam->title),
            
            'start' => $exam->exam_date,
            
            // الألوان:
            // أزرق: للمادة الحالية (يمكن تعديله)
            // رمادي: لأي مادة أخرى (للعلم فقط ولا يمكن تعديله من هنا)
            'color' => $isCurrentSubjectExam ? '#0d6efd' : '#6c757d',
            
            'extendedProps' => [
                // نرسل هذا المتغير لنعرف هل نسمح بالتعديل أم لا
                // التعديل مسموح فقط إذا كنت أنا المعلم + أنا في صفحة نفس المادة
                'canEdit' => $isCurrentSubjectExam && ($exam->teacher_id == auth()->id()),
                'teacherName' => $exam->teacher->name,
                'subjectName' => $exam->subject->name
            ]
        ];
    });

    return response()->json($events);
}

// 3. حفظ الامتحان
public function storeExam(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:100',
        'exam_date' => 'required|date',
        'subject_id' => 'required',
        'class_id' => 'required',
    ]);

    ExamSchedule::create([
        'title' => $request->title,
        'exam_date' => $request->exam_date,
        'subject_id' => $request->subject_id,
        'class_id' => $request->class_id,
        'teacher_id' => auth()->id(),
    ]);

    return response()->json(['success' => 'تم تحديد موعد الامتحان بنجاح']);
}
// تحديث الامتحان
public function updateExam(Request $request)
{
    $request->validate(['exam_id' => 'required', 'title' => 'required']);
    
    $exam = \App\Models\ExamSchedule::where('id', $request->exam_id)
                ->where('teacher_id', auth()->id()) // أمان: التأكد أنه صاحب الامتحان
                ->firstOrFail();

    $exam->update(['title' => $request->title]);

    return response()->json(['success' => 'تم تعديل الامتحان بنجاح']);
}

// حذف الامتحان
public function deleteExam(Request $request)
{
    $exam = \App\Models\ExamSchedule::where('id', $request->exam_id)
                ->where('teacher_id', auth()->id())
                ->firstOrFail();

    $exam->delete();

    return response()->json(['success' => 'تم حذف الامتحان']);
}


}
