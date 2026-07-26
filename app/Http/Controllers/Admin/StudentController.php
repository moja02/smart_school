<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use App\Models\Grade;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function assignUnassignedStudents(Request $request)
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        StudentProfile::whereIn('user_id', $request->student_ids)
                    ->update(['class_id' => $request->class_id]);

        return redirect()->back()->with('success', 'تم تسكين الطلاب المختارين في الفصل بنجاح ✅');
    }

    public function transferStudent(Request $request)
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'student_ids'   => 'required|array',       
            'student_ids.*' => 'exists:users,id',
        ]);

        StudentProfile::whereIn('user_id', $request->student_ids)
                      ->update(['class_id' => $request->class_id]);

        return redirect()->back()->with('success', 'تم نقل الطلاب المختارين للفصل الجديد بنجاح ✅');
    }

    public function listStudents(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        $sections = collect(); 
        if ($request->filled('grade_id')) {
            $sections = \App\Models\SchoolClass::where('grade_id', $request->grade_id)
                        ->where('school_id', $schoolId)
                        ->get();
        }

        $query = \App\Models\User::role('student')->where('school_id', $schoolId);

        if ($request->filled('class_id')) {
            $query->whereHas('studentProfile', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        } elseif ($request->filled('grade_id')) {
            $query->whereHas('studentProfile.schoolClass', function($q) use ($request) {
                $q->where('grade_id', $request->grade_id);
            });
        }

        $students = $query->with(['studentProfile.schoolClass'])->get();

        return view('admin.students.index', compact('students', 'grades', 'sections'));
    }

    public function bulkTransfer(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array',          
            'student_ids.*' => 'exists:users,id',         
            'new_class_id'  => 'required|exists:classes,id', 
        ]);

        $newClass = \App\Models\SchoolClass::find($request->new_class_id);

        \App\Models\StudentProfile::whereIn('user_id', $request->student_ids)
            ->update(['class_id' => $request->new_class_id]);

        return redirect()->back()->with('success', "تم نقل " . count($request->student_ids) . " طالب إلى فصل ($newClass->name - $newClass->section) بنجاح ✅");
    }

    public function listUnassignedStudents()
    {
        $students = StudentProfile::whereNull('class_id')->with('user')->get();
        $classes = SchoolClass::all();
        return view('admin.students.unassigned', compact('students', 'classes'));
    }

    public function updateStudentClass(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'class_id'   => 'required|exists:classes,id',
        ]);

        $student = StudentProfile::find($request->student_id);
        $student->class_id = $request->class_id;
        $student->save();

        return back()->with('success', 'تم تسكين الطالب في الفصل بنجاح.');
    }

    public function deleteStudent($id)
    {
        $user = \App\Models\User::role('student')->findOrFail($id);
        $user->delete();
        return back()->with('success', 'تم حذف الطالب بنجاح.');
    }
}
