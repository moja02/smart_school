<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    // عرض قائمة التقييمات لهذا الفصل
    public function index($subjectId, $sectionId)
    {
        $teacherId = auth()->user()->id;
        $schoolId = auth()->user()->school_id;
        // جلب البيانات الأساسية
        $subject = DB::table('subjects')->find($subjectId);
        $section = DB::table('classes')->find($sectionId);

        // إضافة حماية: إذا لم يجد الشعبة، لا يكمل الكود
        if (!$section) {
            abort(404, 'الشعبة غير موجودة، تأكد من صحة الرابط.');
        }
        $grade = DB::table('grades')->find($section->grade_id);

        // جلب التقييمات التي أنشأها المعلم لهذا الفصل
        $assessments = DB::table('assessments')
            ->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->get();

        // حساب مجموع درجات التقييمات الحالية
        $currentTotalMax = $assessments->sum('max_score');

        // جلب الحد الأقصى لأعمال السنة المحدد من الإدارة
        // نفترض أن جدول الإعدادات لديك اسمه school_subject_settings وفيه عمود works_max_score
        // إذا لم يوجد نضع قيمة افتراضية مثلاً 40
        $settings = DB::table('school_subject_settings')
            ->where('school_id', $schoolId)
            ->first(); // أو يمكنك التخصيص حسب الصف والمادة إذا كان تصميمك يدعم ذلك
            
        $allowedMaxWorks = $settings->works_score ?? 40; // الدرجة المسموحة لأعمال السنة

        // جلب حالة القفل
        $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');
        return view('teacher.assessments.index', compact('subject', 'section', 'assessments', 'currentTotalMax', 'allowedMaxWorks', 'isLocked'));
    }

    // إنشاء تقييم جديد
    public function store(Request $request)
    {
        $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');
        if ($isLocked) return back()->with('error', 'الرصد مغلق حالياً 🔒');

        $request->validate([
            'name' => 'required|string|max:191',
            'max_score' => 'required|numeric|min:1',
            'subject_id' => 'required',
            'section_id' => 'required',
        ]);

        $schoolId = auth()->user()->school_id;

        // 1. التحقق من الحد الأقصى
        $settings = DB::table('school_subject_settings')->where('school_id', $schoolId)->first();
        $allowedMaxWorks = $settings->works_score ?? 40;

        // مجموع التقييمات السابقة لهذا الفصل
        $currentSum = DB::table('assessments')
            ->where('subject_id', $request->subject_id)
            ->where('section_id', $request->section_id)
            ->sum('max_score');

        // هل الإضافة الجديدة ستتجاوز الحد المسموح؟
        if (($currentSum + $request->max_score) > $allowedMaxWorks) {
            return back()->with('error', "خطأ: لا يمكن إنشاء التقييم. المجموع سيصبح " . ($currentSum + $request->max_score) . " والحد الأقصى المسموح هو $allowedMaxWorks");
        }

        // 2. إنشاء التقييم
        DB::table('assessments')->insert([
            'school_id' => $schoolId,
            'teacher_id' => auth()->user()->id,
            'subject_id' => $request->subject_id,
            'section_id' => $request->section_id,
            'name' => $request->name,
            'max_score' => $request->max_score,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'تم إنشاء التقييم بنجاح');
    }

    // واجهة رصد الدرجات لتقييم معين
    public function editMarks($assessmentId)
    {
        $assessment = DB::table('assessments')->find($assessmentId);
        $subject = DB::table('subjects')->find($assessment->subject_id);
        $section = DB::table('classes')->find($assessment->section_id);

        // جلب الطلاب
        $students = User::role('student')
            ->whereHas('studentProfile', function($q) use ($assessment) {
                $q->where('class_id', $assessment->section_id);
            })
            ->orderBy('name')
            ->get();

        // جلب الدرجات المرصودة سابقاً لهذا التقييم
        $marks = DB::table('assessment_marks')
            ->where('assessment_id', $assessmentId)
            ->pluck('score', 'student_id');

        $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');

        return view('teacher.assessments.marks', compact('assessment', 'subject', 'section', 'students', 'marks', 'isLocked'));
    }

    // حفظ الدرجات وتحديث الجدول الرئيسي student_scores
    public function saveMarks(Request $request)
    {
        $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');
        if ($isLocked) return back()->with('error', 'الرصد مغلق حالياً 🔒');

        $assessmentId = $request->assessment_id;
        $assessment = DB::table('assessments')->find($assessmentId);
        $maxScore = $assessment->max_score;

        foreach ($request->marks as $studentId => $score) {
            // التحقق أن الدرجة لا تتجاوز درجة التقييم
            if ($score > $maxScore) continue; 
            
            // 1. حفظ الدرجة في جدول العلامات الفرعي
            DB::table('assessment_marks')->updateOrInsert(
                ['assessment_id' => $assessmentId, 'student_id' => $studentId],
                ['score' => $score ?? 0, 'updated_at' => now()]
            );

            // 2. تحديث مجموع أعمال السنة في الجدول الرئيسي (student_scores)
            $this->updateMainStudentScore($studentId, $assessment->subject_id, $assessment->section_id);
        }

        return back()->with('success', 'تم حفظ الدرجات وتحديث سجل الطالب.');
    }

    // دالة مساعدة لحساب المجموع الكلي وتحديثه
    private function updateMainStudentScore($studentId, $subjectId, $sectionId)
    {
        // نجمع كل درجات الطالب في كل التقييمات لهذه المادة
        $totalWorks = DB::table('assessment_marks')
            ->join('assessments', 'assessment_marks.assessment_id', '=', 'assessments.id')
            ->where('assessment_marks.student_id', $studentId)
            ->where('assessments.subject_id', $subjectId)
            ->where('assessments.section_id', $sectionId) // تأكدنا أنها لنفس الشعبة
            ->sum('assessment_marks.score');

        // تحديث works_score في الجدول الرئيسي
        DB::table('student_scores')->updateOrInsert(
            [
                'student_id' => $studentId, 
                'subject_id' => $subjectId,
                'class_id' => $sectionId
            ],
            [
                'school_id' => auth()->user()->school_id,
                'works_score' => $totalWorks,
                // نترك final_score كما هو ولا نعدله من هنا
                'updated_at' => now()
            ]
        );
        
    }
}