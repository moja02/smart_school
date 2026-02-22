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

        // 1. جلب الفصول (الشعب) التي يدرسها المعلم
        $classes = \Illuminate\Support\Facades\DB::table('teacher_subject_section')
            ->join('classes', 'teacher_subject_section.section_id', '=', 'classes.id')
            ->where('teacher_subject_section.teacher_id', $teacherId)
            ->select('classes.*')
            ->distinct()
            ->get();

        // 2. حساب إجمالي عدد الطلاب في هذه الفصول
        // ✅ التعديل: استخدام جدول student_profiles بدلاً من users
        // تأكد أن اسم الجدول لديك هو 'student_profiles' واسم العمود 'class_id' أو 'section_id'
        $studentsCount = \Illuminate\Support\Facades\DB::table('student_profiles')
            ->whereIn('class_id', $classes->pluck('id')) // إذا كان العمود اسمه section_id غيره هنا
            ->count();

        /* ملاحظة: إذا لم يكن لديك جدول student_profiles، 
        وكان الطلاب مرتبطين بالفصل في جدول users مباشرة، 
        تأكد من أن العمود في قاعدة البيانات اسمه class_id أو section_id وعدل الكود أعلاه.
        */

        // 3. حساب عدد المواد التي يدرسها المعلم
        $subjectsCount = \Illuminate\Support\Facades\DB::table('teacher_subject_section')
            ->where('teacher_id', $teacherId)
            ->distinct('subject_id')
            ->count('subject_id');

        // 4. عدد الفصول للعرض
        $classesCount = $classes->count();

        // 5. آخر الرسائل
        $recentMessages = \App\Models\Message::where('receiver_id', $teacherId)
            ->where('is_read', 0)
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.dashboard', compact('classes', 'classesCount', 'studentsCount', 'subjectsCount', 'recentMessages'));
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
    public function createGrades($subjectId, $sectionId)
    {
        $teacherId = auth()->user()->id;

        // 1. التحقق من أن المعلم يدرس هذه المادة وهذه الشعبة
        $hasAccess = \DB::table('teacher_subject_section')
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'غير مصرح لك برصد درجات هذا الفصل.');
        }

        // 2. جلب بيانات المادة والشعبة
        $subject = \DB::table('subjects')->find($subjectId);
        $section = \DB::table('classes')->find($sectionId); // الجدول اسمه classes في قاعدتك
        $grade = \DB::table('grades')->find($section->grade_id);

        // 3. جلب الطلاب المرتبطين بهذه الشعبة
        // نعتمد على جدول student_profiles للربط بين الطالب والشعبة
        $students = \App\Models\User::whereHas('studentProfile', function($q) use ($sectionId) {
            $q->where('class_id', $sectionId);
        })->orderBy('name')->get();

        // 4. جلب الدرجات السابقة (إن وجدت) لعرضها في الخانات
        // ملاحظة: نفترض أنك أنشأت جدول student_scores كما اتفقنا سابقاً
        $currentScores = \DB::table('student_scores')
            ->where('subject_id', $subjectId)
            ->where('class_id', $sectionId)
            ->get()
            ->keyBy('student_id');

        return view('teacher.grades.create', compact('subject', 'section', 'grade', 'students', 'currentScores'));
    }

    // 2. دالة حفظ الدرجة
    public function storeGrades(Request $request)
{
    $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');
    
    if ($isLocked) {
        return back()->with('error', 'عذراً، تم إغلاق باب رصد الدرجات من قبل الإدارة. لا يمكن التعديل حالياً.');
    }
    // التحقق من صحة البيانات
    $request->validate([
        'grades' => 'array',
        'subject_id' => 'required',
        'section_id' => 'required'
    ]);

    foreach ($request->grades as $studentId => $scores) {
        // إذا الحقل فارغ نعتبره 0
        $works = $scores['works'] ?? 0;
        $final = $scores['final'] ?? 0;
        $total = $works + $final;

        // الحفظ أو التحديث في جدول الدرجات
        \DB::table('student_scores')->updateOrInsert(
            [
                'student_id' => $studentId,
                'subject_id' => $request->subject_id,
                'class_id'   => $request->section_id, // انتبه: في جدول الدرجات سميناه class_id
            ],
            [
                'school_id' => auth()->user()->school_id,
                'works_score' => $works,
                'final_score' => $final,
                'total_score' => $total,
                'academic_year' => date('Y'),
                'semester' => 'first', // يمكنك جعلها ديناميكية لاحقاً
                'updated_at' => now(),
            ]
        );
    }

    return back()->with('success', 'تم حفظ الدرجات بنجاح ✅');
}

    public function editFinalGrades($subjectId, $sectionId)
    {
        $teacherId = auth()->user()->id;
        $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');

        // جلب البيانات الأساسية
        $subject = \DB::table('subjects')->find($subjectId);
        $section = \DB::table('classes')->find($sectionId);
        $grade = \DB::table('grades')->find($section->grade_id);

        // جلب الطلاب ودرجاتهم من الجدول الرئيسي
        $students = \App\Models\User::role('student')
            ->whereHas('studentProfile', function($q) use ($sectionId) {
                $q->where('class_id', $sectionId);
            })
            ->orderBy('name')
            ->get();

        $scores = \DB::table('student_scores')
            ->where('subject_id', $subjectId)
            ->where('class_id', $sectionId)
            ->get()
            ->keyBy('student_id');

        // جلب الدرجة العظمى للنهائي من إعدادات المادة (مثلاً 60)
        $maxFinal = \DB::table('school_subject_settings')
            ->where('school_id', auth()->user()->school_id)
            ->value('final_score') ?? 60;

        return view('teacher.assessments.final_edit', compact('subject', 'section', 'grade', 'students', 'scores', 'isLocked', 'maxFinal'));
    }

    public function storeFinalGrades(Request $request)
    {
        $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');
        if ($isLocked) return back()->with('error', 'الرصد مغلق حالياً 🔒');

        foreach ($request->final_marks as $studentId => $mark) {
            \DB::table('student_scores')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'subject_id' => $request->subject_id,
                    'class_id'   => $request->section_id,
                ],
                [
                    'school_id' => auth()->user()->school_id,
                    'final_score' => $mark ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

        }

        return back()->with('success', 'تم رصد درجات الامتحان النهائي بنجاح ✅');
    }

    // عرض قائمة الفصول الدراسية للمعلم
    public function myClasses()
{
    $teacherId = auth()->user()->id;

    // جلب المواد والفصول من الجدول الوسيط teacher_subject_section
    $subjects = \DB::table('teacher_subject_section')
        ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
        ->join('classes', 'teacher_subject_section.section_id', '=', 'classes.id') // استخدام section_id حسب الجدول لديك
        ->join('grades', 'classes.grade_id', '=', 'grades.id')
        ->where('teacher_subject_section.teacher_id', $teacherId)
        ->select(
            'subjects.id as subject_id',
            'subjects.name as subject_name',
            // تم حذف subjects.code لأنه غير موجود
            'grades.name as grade_name',       // اسم الصف
            'classes.section as class_section', // اسم الشعبة
            'classes.id as class_id'            // رقم الشعبة (مهم للرابط)
        )
        ->get();

    return view('teacher.classes.index', compact('subjects'));
}
    // عرض تفاصيل فصل معين (الطلاب + المواد)
    public function showClass($subjectId, $classId)
    {
        $teacherId = auth()->user()->id;

        // التحقق من الصلاحية
        $hasAccess = \DB::table('teacher_subject_section')
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->where('section_id', $classId)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا الفصل.');
        }

        $subject = \DB::table('subjects')->find($subjectId);
        $class = \DB::table('classes')->find($classId);
        $grade = \DB::table('grades')->find($class->grade_id);

        // جلب الطلاب
        $students = \App\Models\User::role('student')
            ->whereHas('studentProfile', function($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->with('studentProfile')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.show', compact('subject', 'class', 'grade', 'students'));
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
    public function createQuestion($subject_id, $class_id)
    {
        $subject = \DB::table('subjects')->where('id', $subject_id)->first();
        $class = \DB::table('classes')->where('id', $class_id)->first();

        // ✅ التعديل هنا: استخدام section_id بدلاً من class_id
        $lessons = \DB::table('lessons')
                    ->where('subject_id', $subject_id)
                    ->where('section_id', $class_id) // استخدام اسم العمود الصحيح في قاعدة البيانات
                    ->get();

        return view('teacher.questions.create', compact('subject', 'class', 'lessons'));
    }

    public function storeQuestion(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:true_false,multiple_choice',
            'correct_answer' => 'required|string',
            'lesson_id' => 'nullable|exists:lessons,id',
            'lesson_name' => 'nullable|string|max:255',
        ]);

        // 1. 🛑 منع التكرار: فحص هل السؤال موجود مسبقاً لنفس الفصل؟
        $exists = \DB::table('questions')
                    ->where('section_id', $request->class_id) // أو section_id حسب اسم العمود عندك
                    ->where('content', $request->content)
                    ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'هذا السؤال موجود بالفعل في بنك الأسئلة لهذا الفصل! لا داعي لإضافته مرة أخرى.');
        }

        // 2. معالجة الدرس
        $lessonId = $request->lesson_id;
        if (empty($lessonId)) {
            if ($request->filled('lesson_name')) {
                // التأكد من عدم تكرار اسم الدرس أيضاً
                $existingLesson = \DB::table('lessons')
                                    ->where('section_id', $request->class_id)
                                    ->where('title', $request->lesson_name)
                                    ->first();
                
                if ($existingLesson) {
                    $lessonId = $existingLesson->id;
                } else {
                    $lessonId = \DB::table('lessons')->insertGetId([
                        'subject_id' => $request->subject_id,
                        'section_id' => $request->class_id,
                        'title' => $request->lesson_name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                return back()->withErrors(['lesson_id' => 'يرجى اختيار درس أو إنشاء درس جديد.'])->withInput();
            }
        }

        // 3. حفظ السؤال (بعد التأكد من عدم تكراره)
        \DB::table('questions')->insert([
            'lesson_id' => $lessonId,
            'subject_id' => $request->subject_id,
            'section_id' => $request->class_id,
            'content' => $request->content,
            'type' => $request->type,
            'correct_answer' => $request->correct_answer,
            'options' => $request->type == 'multiple_choice' ? json_encode($request->options) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('teacher.questions.create', [
            'subject_id' => $request->subject_id, 
            'class_id' => $request->class_id
        ])->with('success', 'تم حفظ السؤال وإضافته للبنك بنجاح.');
    }
    public function destroyQuestion($id)
    {
        // حذف السؤال من قاعدة البيانات
        \DB::table('questions')->where('id', $id)->delete();

        return back()->with('success', 'تم حذف السؤال من بنك الأسئلة بنجاح.');
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

    public function updateQuestion(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:true_false,multiple_choice',
            'correct_answer' => 'required|string',
        ]);

        // تحديث البيانات
        \DB::table('questions')
            ->where('id', $id)
            ->update([
                'content' => $request->content,
                'type' => $request->type,
                'correct_answer' => $request->correct_answer,
                // إذا كان "اختيارات" نأخذ المصفوفة ونحولها لنص، وإلا نضع null
                'options' => $request->type == 'multiple_choice' ? json_encode($request->options) : null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'تم تعديل السؤال بنجاح.');
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

    // ==========================================
    //  إدارة الاختبارات التجريبية (Quizzes)
    // ==========================================

    public function indexQuizzes($subject_id, $section_id)
    {
        // 1. جلب بيانات المادة والشعبة
        $subject = \DB::table('subjects')->where('id', $subject_id)->first();
        $section = \DB::table('classes')->where('id', $section_id)->first();

        // 2. جلب الاختبارات المرتبطة بهذه المادة والشعبة
        // نفترض وجود جدول quizzes
        $quizzes = \DB::table('quizzes')
                    ->where('subject_id', $subject_id)
                    ->where('section_id', $section_id)
                    ->orderByDesc('created_at')
                    ->get();

        // إضافة عدد الأسئلة لكل اختبار (اختياري)
        foreach ($quizzes as $quiz) {
            $quiz->questions_count = \DB::table('questions')
                                    ->where('quiz_id', $quiz->id)
                                    ->count();
        }

        return view('teacher.quizzes.index', compact('subject', 'section', 'quizzes'));
    }

    public function createQuiz($subject_id, $section_id)
    {
        $subject = \DB::table('subjects')->where('id', $subject_id)->first();
        $section = \DB::table('classes')->where('id', $section_id)->first();

        $lessons = \DB::table('lessons')
                    ->where('subject_id', $subject_id)
                    ->where('section_id', $section_id)
                    ->get();

        // ✅ التعديل هنا: نحسب فقط الأسئلة التي quiz_id تبعها NULL (المتاحة في البنك)
        foreach ($lessons as $lesson) {
            $lesson->questions_count = \DB::table('questions')
                                        ->where('lesson_id', $lesson->id)
                                        ->whereNull('quiz_id') // <--- الشرط المهم
                                        ->count();
        }

        // حساب الأسئلة العامة (المتاحة)
        $generalQuestionsCount = \DB::table('questions')
                                ->where('subject_id', $subject_id)
                                ->where('section_id', $section_id)
                                ->whereNull('lesson_id')
                                ->whereNull('quiz_id') // <--- شرط مهم أيضاً
                                ->count();

        return view('teacher.quizzes.create', compact('subject', 'section', 'lessons', 'generalQuestionsCount'));
    }

    public function storeQuiz(\Illuminate\Http\Request $request)
    {
        // 1. التحقق من المدخلات
        $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'questions_count' => 'nullable|integer|min:1', // عدد الأسئلة المطلوب
        ]);

        // 2. إنشاء الاختبار (Quiz)
        $quizId = \DB::table('quizzes')->insertGetId([
            'title' => $request->title,
            'description' => $request->description,
            'subject_id' => $request->subject_id,
            'section_id' => $request->section_id,
            'duration' => $request->duration,
            'is_active' => 1, // ✅ تفعيل مباشر (إرسال فوري للطلاب)
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. ⚡ السحر: التوليد التلقائي للأسئلة
        // إذا اختار المعلم التوليد التلقائي وحدد عدداً للأسئلة
        if ($request->has('auto_generate') && $request->filled('questions_count')) {
            
            // نبدأ الاستعلام
            $query = \DB::table('questions')
                ->where('subject_id', $request->subject_id)
                ->where('section_id', $request->section_id) // تأكيد إضافي على الشعبة
                ->whereNull('quiz_id'); //  هذا الشرط يضمن أخذ أسئلة البنك فقط

            // ✅ التعديل هنا: إذا اختار المعلم درساً محدداً، نفلتر به
            if ($request->filled('lesson_id')) {
                if ($request->lesson_id == 'general') {
                    // إذا اختار "أسئلة عامة" (نبحث عن lesson_id = NULL)
                    $query->whereNull('lesson_id');
                } else {
                    // إذا اختار درساً محدداً
                    $query->where('lesson_id', $request->lesson_id);
                }
            }

            // إكمال الاستعلام (عشوائي + العدد المطلوب)
            $randomQuestions = $query->inRandomOrder()
                ->limit($request->questions_count)
                ->get();

            // التحقق: هل يوجد أسئلة كافية؟
            if ($randomQuestions->count() < $request->questions_count) {
                // حذف الاختبار الفارغ
                \DB::table('quizzes')->where('id', $quizId)->delete();
                
                // رسالة خطأ ذكية
                $msg = $request->filled('lesson_id') 
                    ? 'لا يوجد عدد كافٍ من الأسئلة في هذا الدرس تحديداً!' 
                    : 'بنك الأسئلة لا يحتوي على عدد كافٍ!';
                    
                return back()->with('error', $msg . ' (المتوفر: ' . $randomQuestions->count() . ')');
            }

            // نسخ الأسئلة (نفس الكود السابق)...
            foreach ($randomQuestions as $q) {
                \DB::table('questions')->insert([
                    'quiz_id' => $quizId,
                    'subject_id' => $q->subject_id,
                    'section_id' => $q->section_id,
                    'lesson_id' => $q->lesson_id,
                    'content' => $q->content,
                    'type' => $q->type,
                    'options' => $q->options,
                    'correct_answer' => $q->correct_answer,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('teacher.quizzes.index', [
            'subject_id' => $request->subject_id, 
            'section_id' => $request->section_id
        ])->with('success', 'تم إنشاء الاختبار وإرساله للطلاب بنجاح! 🚀');
    }

    public function deleteQuiz($id)
    {
        // حذف الأسئلة المرتبطة أولاً
        \DB::table('questions')->where('quiz_id', $id)->delete();
        
        // حذف الاختبار
        \DB::table('quizzes')->where('id', $id)->delete();

        return back()->with('success', 'تم حذف الاختبار بنجاح.');
    }

    public function quizResults($id)
    {
        // هذه الدالة لعرض النتائج (Placeholder)
        return back()->with('error', 'صفحة النتائج قيد التطوير حالياً.');
    }
    public function showQuiz($id)
    {
        // جلب الاختبار مع المادة والشعبة
        $quiz = \DB::table('quizzes')
                ->join('subjects', 'quizzes.subject_id', '=', 'subjects.id')
                ->join('classes', 'quizzes.section_id', '=', 'classes.id') // أو section_id حسب الجدول
                ->where('quizzes.id', $id)
                ->select('quizzes.*', 'subjects.name as subject_name', 'classes.section as section_name')
                ->first();

        if (!$quiz) {
            return back()->with('error', 'الاختبار غير موجود.');
        }

        // جلب الأسئلة المرتبطة بهذا الاختبار
        $questions = \DB::table('questions')
                    ->where('quiz_id', $id)
                    ->get();

        return view('teacher.quizzes.show', compact('quiz', 'questions'));
    }
    public function quizReport($id)
    {
        $quiz = \DB::table('quizzes')
                ->join('subjects', 'quizzes.subject_id', '=', 'subjects.id')
                ->join('classes', 'quizzes.section_id', '=', 'classes.id')
                ->where('quizzes.id', $id)
                ->select('quizzes.*', 'subjects.name as subject_name', 'classes.section as section_name')
                ->first();

        if (!$quiz) { return abort(404); }

        $questions = \DB::table('questions')->where('quiz_id', $id)->get();

        // نستخدم view مستقلة تماماً بدون Layout المعلم المعتاد
        return view('teacher.quizzes.report', compact('quiz', 'questions'));
    }
    public function showQuizResults($quiz_id)
    {
        $quiz = DB::table('quizzes')->where('id', $quiz_id)->first();
        
        if (!$quiz) {
            return redirect()->back()->with('error', 'الاختبار غير موجود');
        }

        $results = DB::table('student_results')
            ->leftJoin('users', 'student_results.student_id', '=', 'users.id')
            ->where('student_results.quiz_id', $quiz_id)
            ->select('student_results.*', 'users.name as student_name')
            ->get();

        return view('teacher.quizzes.results', compact('quiz', 'results'));
    }
    public function printQuizResults($id)
    {
        $quiz = DB::table('quizzes')->where('id', $id)->first();
        
        if (!$quiz) { return abort(404); }

        $results = DB::table('student_results')
            ->leftJoin('users', 'student_results.student_id', '=', 'users.id')
            ->where('student_results.quiz_id', $id)
            ->select('student_results.*', 'users.name as student_name')
            ->orderBy('score', 'desc')
            ->get();

        return view('teacher.quizzes.print_results', compact('quiz', 'results'));
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
