<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function gradeSettings(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        $query = \App\Models\Subject::where(function($q) use ($schoolId) {
            $q->where('school_id', $schoolId)
            ->orWhereNull('school_id'); 
        });

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        } else {
            $query->whereIn('grade_id', $grades->pluck('id'));
        }

        $subjects = $query->with('grade')->get();

        return view('admin.subjects.grade_settings', compact('subjects', 'grades'));
    }

    public function storeGradeSettings(Request $request)
    {
        if (!$request->has('subject_id')) {
            return redirect()->back()->with('error', 'لا توجد مواد لحفظها');
        }

        $schoolId = auth()->user()->school_id;

        foreach ($request->subject_id as $index => $subjectId) {
            $works = $request->works_score[$index] ?? 40;
            $final = $request->final_score[$index] ?? 60;
            $total = $works + $final; 
            $classes = $request->weekly_classes[$index] ?? 1;

            \DB::table('school_subject_settings')->updateOrInsert(
                [
                    'school_id'  => $schoolId, 
                    'subject_id' => $subjectId
                ],
                [
                    'weekly_classes' => $classes,
                    'works_score'    => $works,
                    'final_score'    => $final,
                    'total_score'    => $total,
                    'created_at'     => now(),
                    'updated_at'     => now()
                ]
            );
        }

        return redirect()->back()->with('success', 'تم حفظ إعدادات المواد بنجاح ✅');
    }

    public function listSubjects()
    {
        $schoolId = auth()->user()->school_id;

        $grades = Grade::whereIn('id', function($query) use ($schoolId) {
                        $query->select('grade_id')
                            ->from('school_grade')
                            ->where('school_id', $schoolId);
                    })
                    ->with(['subjects' => function($query) use ($schoolId) {
                        $query->whereNull('school_id')        
                            ->orWhere('school_id', $schoolId); 
                    }])
                    ->get();

        if ($grades->isEmpty()) {
            return redirect()->route('admin.settings.structure')->with('warning', 'يرجى تحديد المراحل الدراسية الخاصة بالمدرسة أولاً.');
        }

        return view('admin.subjects.index', compact('grades'));
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'grade_ids'      => 'required|array', 
            'grade_ids.*'    => 'exists:grades,id',
            'weekly_classes' => 'nullable|integer|min:1|max:20', 
        ]);

        $schoolId = auth()->user()->school_id;
        $weeklyClasses = $request->weekly_classes ?? 1;

        foreach ($request->grade_ids as $gradeId) {
            $subject = Subject::create([
                'name'           => $request->name,
                'grade_id'       => $gradeId,
                'school_id'      => $schoolId,
            ]);

            \DB::table('school_subject_settings')->insert([
                'school_id'      => $schoolId,
                'subject_id'     => $subject->id,
                'weekly_classes' => $weeklyClasses,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        return redirect()->back()->with('success', 'تم إضافة المادة للصفوف المختارة بنجاح ✅');
    }

    public function updateSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name'       => 'required|string|max:255',
            'grade_id'   => 'required|exists:grades,id',
        ]);

        $subject = \App\Models\Subject::findOrFail($request->subject_id);

        if ($subject->school_id != auth()->user()->school_id) {
            return redirect()->back()->with('error', 'عذراً، لا يمكنك تعديل المواد العامة أو مواد مدارس أخرى.');
        }

        $subject->update([
            'name'       => $request->name,
            'grade_id'   => $request->grade_id,
        ]);

        return redirect()->back()->with('success', 'تم تعديل بيانات المادة بنجاح ✅');
    }

    public function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);

        if ($subject->school_id == null) {
            return redirect()->back()->with('error', 'تنبيه: لا يمكن حذف المواد الأساسية العامة (مثل الرياضيات والعربي).');
        }

        if ($subject->school_id != auth()->user()->school_id) {
            return redirect()->back()->with('error', 'ليس لديك صلاحية لحذف هذه المادة.');
        }

        $subject->delete();
        return redirect()->back()->with('success', 'تم حذف المادة الخاصة بنجاح 🗑️');
    }

    public function assign(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $selectedGradeId = $request->grade_id;
        $selectedSubjectId = $request->subject_id;

        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        $subjects = collect();
        if ($selectedGradeId) {
            $subjects = \App\Models\Subject::where('grade_id', $selectedGradeId)
                ->where(function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->orWhereNull('school_id');
                })->get();
        }

        $sections = collect();
        $assignedSections = [];
        if ($selectedSubjectId) {
            $sections = \App\Models\SchoolClass::where('grade_id', $selectedGradeId)
                        ->where('school_id', $schoolId)->get();

            $assignedSections = \DB::table('teacher_subject_section')
                                ->where('subject_id', $selectedSubjectId)
                                ->pluck('teacher_name', 'section_id')->toArray();
        }

        $teachers = \App\Models\User::where('school_id', $schoolId)->where('role', 'teacher')->get();

        return view('admin.subjects.assign', compact('grades', 'subjects', 'sections', 'assignedSections', 'teachers'));
    }

    public function createAssignment(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $selectedSubjectName = $request->subject_name;

        // جلب أسماء المواد المتاحة في المدرسة بدون تكرار
        $subjectNames = \App\Models\Subject::where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->select('name')
            ->distinct()
            ->pluck('name');

        $sectionsByGrade = collect();
        $assignedSections = [];

        if ($selectedSubjectName) {
            // جلب معرّفات الصفوف التي تدرس هذه المادة
            $gradeIds = \App\Models\Subject::where('name', $selectedSubjectName)
                ->where(function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->orWhereNull('school_id');
                })
                ->pluck('grade_id')
                ->toArray();

            if (!empty($gradeIds)) {
                // جلب كافة الفصول التي تنتمي لهذه الصفوف
                $sections = \App\Models\SchoolClass::whereIn('grade_id', $gradeIds)
                            ->where('school_id', $schoolId)
                            ->with('grade')
                            ->get();

                // تجميع الفصول حسب اسم الصف
                $sectionsByGrade = $sections->groupBy(function($section) {
                    return $section->grade ? $section->grade->name : 'أخرى';
                });

                // جلب الإسنادات الحالية للمادة المحددة في هذه الفصول
                $subjectIds = \App\Models\Subject::where('name', $selectedSubjectName)
                    ->where(function($q) use ($schoolId) {
                        $q->where('school_id', $schoolId)->orWhereNull('school_id');
                    })
                    ->pluck('id')
                    ->toArray();

                $assignedSections = \DB::table('teacher_subject_section')
                                    ->whereIn('subject_id', $subjectIds)
                                    ->where('school_id', $schoolId)
                                    ->get()
                                    ->keyBy('section_id'); 
            }
        }

        $teachers = \App\Models\User::role('teacher')->where('school_id', $schoolId)->get();

        return view('admin.assign', compact('subjectNames', 'sectionsByGrade', 'assignedSections', 'teachers', 'selectedSubjectName'));
    }

    public function getAvailableSubjects($class_id)
    {
        $class = SchoolClass::findOrFail($class_id);

        if (!$class->grade_id) {
            return response()->json([]);
        }

        $allSubjects = Subject::whereHas('grades', function($q) use ($class) {
            $q->where('grade_id', $class->grade_id);
        })->get();

        $availableSubjects = $allSubjects->filter(function($subject) use ($class_id) {
            $isAssigned = DB::table('teacher_subject')
                        ->where('class_id', $class_id)
                        ->where('subject_id', $subject->id)
                        ->exists();
            return !$isAssigned;
        });

        return response()->json($availableSubjects->values());
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_name' => 'required|string',
            'section_ids' => 'required|array',
        ]);

        $schoolId = auth()->user()->school_id;
        $teacher = \App\Models\User::findOrFail($request->teacher_id);

        foreach ($request->section_ids as $sectionId) {
            $section = \App\Models\SchoolClass::findOrFail($sectionId);
            
            // البحث عن المادة الصحيحة التابعة لصف هذه الشعبة
            $subject = \App\Models\Subject::where('name', $request->subject_name)
                ->where('grade_id', $section->grade_id)
                ->where(function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->orWhereNull('school_id');
                })->first();

            if ($subject) {
                \DB::table('teacher_subject_section')->updateOrInsert(
                    [
                        'school_id' => $schoolId,
                        'subject_id' => $subject->id,
                        'section_id' => $sectionId,
                    ],
                    [
                        'teacher_id' => $request->teacher_id,
                        'teacher_name' => $teacher->name, 
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        return back()->with('success', 'تم إسناد المادة للفصول المختارة بنجاح ✅');
    }

    public function removeAssignment(Request $request, $section_id)
    {
        $subjectName = $request->subject_name;
        $schoolId = auth()->user()->school_id;

        $subjectIds = \App\Models\Subject::where('name', $subjectName)
            ->where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })->pluck('id')->toArray();

        if (!empty($subjectIds)) {
            $deleted = \DB::table('teacher_subject_section')
                ->where('school_id', $schoolId)
                ->where('section_id', $section_id)
                ->whereIn('subject_id', $subjectIds)
                ->delete();
                
            if ($deleted == 0) {
                return back()->with('error', 'لم يتم العثور على الإسناد لإلغائه.');
            }
        } else {
            return back()->with('error', 'المادة غير موجودة.');
        }

        return back()->with('success', 'تم إلغاء الربط بنجاح');
    }

    public function updateAssignment(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_name' => 'required|string',
            'section_id' => 'required',
        ]);

        $schoolId = auth()->user()->school_id;
        $teacher = \App\Models\User::findOrFail($request->teacher_id);

        $subjectIds = \App\Models\Subject::where('name', $request->subject_name)
            ->where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })->pluck('id')->toArray();

        if (!empty($subjectIds)) {
            $updated = \DB::table('teacher_subject_section')
                ->where('school_id', $schoolId)
                ->whereIn('subject_id', $subjectIds)
                ->where('section_id', $request->section_id)
                ->update([
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name,
                    'updated_at' => now(),
                ]);
                
            if ($updated == 0) {
                return back()->with('error', 'لم يتم العثور على الإسناد لتعديله.');
            }
        } else {
            return back()->with('error', 'المادة غير موجودة.');
        }

        return back()->with('success', 'تم تغيير الأستاذ بنجاح ✅');
    }

    public function updateSubjectClasses(Request $request)
    {
        $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'weekly_classes' => 'required|integer|min:1|max:20',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $userSchoolId = auth()->user()->school_id;

        \DB::table('school_subject_settings')->updateOrInsert(
            [
                'school_id'  => $userSchoolId,
                'subject_id' => $subject->id
            ],
            [
                'weekly_classes' => $request->weekly_classes,
                'updated_at'     => now()
            ]
        );

        return redirect()->back()->with('success', 'تم تحديث نصاب الحصص لهذه المادة بنجاح ✅');
    }

    public function editSubject($id)
    {
        $subject = \App\Models\Subject::findOrFail($id);
        $grades = \App\Models\Grade::where('school_id', auth()->user()->school_id)->get();
        return view('admin.subjects.edit', compact('subject', 'grades'));
    }
}
