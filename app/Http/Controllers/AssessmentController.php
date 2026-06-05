<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Assessment;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    // ==========================================
    // 1. عرض قائمة التقييمات
    // ==========================================
    public function index($subjectId, $sectionId)
    {
        $teacherId = auth()->user()->id;
        $schoolId = auth()->user()->school_id;

        $subject = Subject::findOrFail($subjectId);
        $section = SchoolClass::findOrFail($sectionId); 

        // جلب التقييمات التي أنشأها المعلم لهذه الشعبة وهذه المادة
        $assessments = Assessment::where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->where('teacher_id', $teacherId)
            ->get();

        // 💡 جلب الدرجة الكلية لأعمال السنة
        $settings = DB::table('school_subject_settings')
            ->where('school_id', $schoolId)
            ->where('subject_id', $subjectId)
            ->first();
            
        $totalWorksScore = ($settings && isset($settings->works_score) && $settings->works_score > 0) ? $settings->works_score : 40;

        // 💡 تقسيم الدرجة على 2 (الحد الأقصى لكل سميستر)
        $maxPerSemester = $totalWorksScore / 2;

        // 💡 حساب مجموع ما تم تسجيله في كل سميستر على حدة
        $sumSem1 = $assessments->where('semester', 1)->sum('max_score');
        $sumSem2 = $assessments->where('semester', 2)->sum('max_score');

        // 💡 حساب الرصيد المتبقي لكل سميستر
        $remSem1 = max(0, $maxPerSemester - $sumSem1);
        $remSem2 = max(0, $maxPerSemester - $sumSem2);

        $isLocked = DB::table('schools')->where('id', $schoolId)->value('grading_locked');

        return view('teacher.assessments.index', compact(
            'subject', 'section', 'assessments', 'isLocked',
            'maxPerSemester', 'remSem1', 'remSem2'
        ));
    }

    // ==========================================
    // 2. إنشاء تقييم جديد
    // ==========================================
    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $isLocked = DB::table('schools')->where('id', $schoolId)->value('grading_locked');
        if ($isLocked) return back()->with('error', 'الرصد مغلق حالياً 🔒');

        $request->validate([
            'name'       => 'required|string|max:191',
            'max_score'  => 'required|numeric|min:0.5',
            'subject_id' => 'required',
            'section_id' => 'required',
            'semester'   => 'required|in:1,2'
        ]);

        $teacherId = auth()->id();
        $newScore = $request->max_score;
        $requestedSemester = $request->semester;

        // جلب الدرجة الكلية وحساب حد السميستر
        $settings = DB::table('school_subject_settings')
            ->where('school_id', $schoolId)
            ->where('subject_id', $request->subject_id)
            ->first();
        $totalWorksScore = ($settings && isset($settings->works_score) && $settings->works_score > 0) ? $settings->works_score : 40;
        $maxPerSemester = $totalWorksScore / 2;

        // مجموع التقييمات السابقة *لهذا السميستر تحديداً*
        $currentSemesterSum = Assessment::where('subject_id', $request->subject_id)
            ->where('section_id', $request->section_id)
            ->where('teacher_id', $teacherId)
            ->where('semester', $requestedSemester)
            ->sum('max_score');

        // التحقق
        if (($currentSemesterSum + $newScore) > $maxPerSemester) {
            $remaining = max(0, $maxPerSemester - $currentSemesterSum);
            $semName = $requestedSemester == 1 ? 'الأول' : 'الثاني';
            return back()->with('error', "خطأ: لا يمكن إنشاء التقييم. أقصى درجة متبقية للفصل الدراسي $semName هي ($remaining درجة).");
        }

        // إنشاء التقييم
        Assessment::create([
            'teacher_id' => $teacherId,
            'subject_id' => $request->subject_id,
            'section_id' => $request->section_id,
            'name'       => $request->name,
            'max_score'  => $newScore,
            'semester'   => $requestedSemester
        ]);

        return back()->with('success', 'تم إنشاء التقييم بنجاح');
    }

    // ==========================================
    // 3. تعديل التقييم
    // ==========================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'max_score' => 'required|numeric|min:0.5',
        ]);

        $assessment = Assessment::findOrFail($id);
        $schoolId = auth()->user()->school_id;

        $settings = DB::table('school_subject_settings')
            ->where('school_id', $schoolId)
            ->where('subject_id', $assessment->subject_id)
            ->first();
        $totalWorksScore = ($settings && isset($settings->works_score) && $settings->works_score > 0) ? $settings->works_score : 40;
        $maxPerSemester = $totalWorksScore / 2;
        
        // الفحص يشمل نفس الشعبة ونفس السميستر مع استثناء التقييم الحالي
        $currentSemesterSum = Assessment::where('subject_id', $assessment->subject_id)
            ->where('section_id', $assessment->section_id) 
            ->where('teacher_id', auth()->id())
            ->where('semester', $assessment->semester)
            ->where('id', '!=', $id) 
            ->sum('max_score');

        if (($currentSemesterSum + $request->max_score) > $maxPerSemester) {
            $remaining = max(0, $maxPerSemester - $currentSemesterSum);
            return back()->with('error', "لا يمكن الحفظ! أقصى درجة متبقية يمكنك وضعها في هذا السميستر هي ($remaining درجة).");
        }

        $assessment->update([
            'name'      => $request->name,
            'max_score' => $request->max_score
        ]);

        return back()->with('success', 'تم تعديل التقييم بنجاح ✅');
    }

    // ==========================================
    // 4. حذف التقييم
    // ==========================================
    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        DB::table('assessment_marks')->where('assessment_id', $id)->delete();
        $assessment->delete();

        return back()->with('success', 'تم حذف التقييم ودرجاته بنجاح 🗑️');
    }

    // ==========================================
    // 5. واجهة رصد الدرجات لتقييم معين
    // ==========================================
    public function editMarks($assessmentId)
    {
        $assessment = DB::table('assessments')->find($assessmentId);
        $subject = DB::table('subjects')->find($assessment->subject_id);
        $section = DB::table('classes')->find($assessment->section_id);

        $students = User::role('student')
            ->whereHas('studentProfile', function($q) use ($assessment) {
                $q->where('class_id', $assessment->section_id);
            })
            ->orderBy('name')
            ->get();

        $marks = DB::table('assessment_marks')
            ->where('assessment_id', $assessmentId)
            ->pluck('score', 'student_id');

        $isLocked = DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');

        return view('teacher.assessments.marks', compact('assessment', 'subject', 'section', 'students', 'marks', 'isLocked'));
    }

    // ==========================================
    // 6. حفظ درجات التقييم للطلاب
    // ==========================================
    public function saveMarks(Request $request)
    {
        $isLocked = DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');
        if ($isLocked) return back()->with('error', 'الرصد مغلق حالياً 🔒');

        $assessmentId = $request->assessment_id;
        $assessment = DB::table('assessments')->find($assessmentId);
        $maxScore = $assessment->max_score;

        foreach ($request->marks as $studentId => $score) {
            if ($score > $maxScore) continue; 
            
            DB::table('assessment_marks')->updateOrInsert(
                ['assessment_id' => $assessmentId, 'student_id' => $studentId],
                ['score' => $score ?? 0, 'updated_at' => now()]
            );

            $this->updateMainStudentScore($studentId, $assessment->subject_id, $assessment->section_id);
        }

        return back()->with('success', 'تم حفظ الدرجات وتحديث سجل الطالب.');
    }

    // دالة مساعدة لحساب المجموع الكلي وتحديثه
    private function updateMainStudentScore($studentId, $subjectId, $sectionId)
    {
        $totalWorks = DB::table('assessment_marks')
            ->join('assessments', 'assessment_marks.assessment_id', '=', 'assessments.id')
            ->where('assessment_marks.student_id', $studentId)
            ->where('assessments.subject_id', $subjectId)
            ->where('assessments.section_id', $sectionId) 
            ->sum('assessment_marks.score');

        DB::table('student_scores')->updateOrInsert(
            [
                'student_id' => $studentId, 
                'subject_id' => $subjectId,
                'class_id' => $sectionId
            ],
            [
                'school_id'   => auth()->user()->school_id,
                'works_score' => $totalWorks,
                'updated_at'  => now()
            ]
        );
    }
}