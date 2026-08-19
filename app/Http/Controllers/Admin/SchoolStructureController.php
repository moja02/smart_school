<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class SchoolStructureController extends Controller
{
    public function editSchoolStructure()
    {
        $schoolId = auth()->user()->school_id;
        // جلب السنوات الدراسية العامة (بدون مدرسة) والسنوات الخاصة بالمدرسة الحالية
        $allGrades = Grade::whereNull('school_id')
                          ->orWhere('school_id', $schoolId)
                          ->get(); 
        $activeGradeIds = \DB::table('school_grade')
                            ->where('school_id', $schoolId)
                            ->pluck('grade_id')
                            ->toArray();
        return view('admin.settings.structure', compact('allGrades', 'activeGradeIds'));
    }

    public function updateSchoolStructure(Request $request)
    {
        $request->validate([
            'grades' => 'array',
            'grades.*' => 'exists:grades,id',
        ]);
        $schoolId = auth()->user()->school_id;
        $grades = $request->input('grades', []);
        $data = [];
        foreach ($grades as $gradeId) {
            $data[] = [
                'school_id' => $schoolId, 
                'grade_id' => $gradeId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        \DB::transaction(function () use ($schoolId, $data) {
            \DB::table('school_grade')->where('school_id', $schoolId)->delete();
            \DB::table('school_grade')->insert($data);
        });
        return redirect()->route('admin.subjects')->with('success', 'تم تحديث هيكلية المدرسة والمراحل الدراسية بنجاح ✅');
    }

    public function storeGrade(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'stage' => 'nullable|string|in:primary,middle,secondary',
            'notes' => 'nullable|string',
        ]);
        Grade::create([
            'name' => $request->name,
            'stage' => $request->stage ?? 'primary',
            'school_id' => auth()->user()->school_id ?? null, 
        ]);
        return redirect()->back()->with('success', 'تم إضافة السنة الدراسية بنجاح');
    }

    public function listClasses()
    {
        $user = auth()->user();
        if (!$user->school_id) {
            return redirect()->route('admin.dashboard')->with('error', 'حسابك غير مرتبط بمدرسة.');
        }
        $grades = \App\Models\Grade::whereHas('classes', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->with(['classes' => function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->withCount('students'); 
            }])
            ->get();
        return view('admin.classes.index', compact('grades'));
    }

    public function createClass()
    {
        $user = auth()->user();
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($user){
                $q->select('grade_id')->from('school_grade')->where('school_id', $user->school_id);
            })
            ->with(['classes' => function($query) use ($user) {
                $query->where('school_id', $user->school_id);
            }])
            ->get();
        return view('admin.classes.create', compact('grades'));
    }

    public function storeClass(Request $request)
    {
        $request->validate([
            'grade_id'   => 'required|exists:grades,id',
            'sections'   => 'required|array',       
            'sections.*' => 'required|string|distinct', 
        ]);
        $user = auth()->user();
        $grade = \App\Models\Grade::find($request->grade_id);
        $count = 0;
        foreach ($request->sections as $sectionName) {
            if (!empty($sectionName)) {
                $exists = \App\Models\SchoolClass::where('school_id', $user->school_id)
                            ->where('grade_id', $request->grade_id)
                            ->where('section', $sectionName)
                            ->exists();
                if (!$exists) {
                    \App\Models\SchoolClass::create([
                        'name'      => $grade->name. ' - ' . $sectionName,
                        'section'   => $sectionName,
                        'grade_id'  => $request->grade_id,
                        'school_id' => $user->school_id,
                    ]);
                    $count++;
                }
            }
        }
        if ($count > 0) {
            return redirect()->back()->with('success', "تم إضافة $count شعبة بنجاح ✅");
        } else {
            return redirect()->back()->with('warning', 'لم يتم إضافة أي شعبة (قد تكون مكررة أو فارغة).');
        }
    }

    public function deleteClass($id)
    {
        \App\Models\SchoolClass::where('school_id', auth()->user()->school_id)->findOrFail($id)->delete();
        return redirect()->back()->with('success', 'تم حذف الفصل بنجاح 🗑️');
    }

    public function getGradeSections($grade_id)
    {
        $user = auth()->user();
        $sections = \App\Models\SchoolClass::where('grade_id', $grade_id)
                    ->where('school_id', $user->school_id) 
                    ->pluck('section')
                    ->toArray();
        return response()->json($sections);
    }

    public function editClass($id)
    {
        $class = SchoolClass::findOrFail($id);
        $grades = Grade::where('school_id', auth()->user()->school_id)->get();
        return view('admin.classes.edit', compact('class', 'grades'));
    }

    public function updateClass(Request $request, $id)
    {
        $request->validate([
            'section' => 'required|string|max:255',
        ]);
        $class = \App\Models\SchoolClass::findOrFail($id);
        if ($class->school_id !== auth()->user()->school_id) {
            return back()->with('error', 'غير مصرح لك بهذا الإجراء');
        }
        $class->update([
            'section' => $request->section,
            'name' => $class->grade->name . ' - ' . $request->section,
        ]);
        return back()->with('success', 'تم تحديث اسم الشعبة بنجاح');
    }

    public function showClassStudents($id)
    {
        $class = SchoolClass::with('grade')->findOrFail($id);
        $students = User::role('student')
            ->whereHas('studentProfile', function($q) use ($id) {
                $q->where('class_id', $id);
            })
            ->with('studentProfile') 
            ->orderBy('name')
            ->get();
        return view('admin.classes.students', compact('class', 'students'));
    }
}
