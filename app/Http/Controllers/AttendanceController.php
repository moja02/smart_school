<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index($sectionId)
{
    $section = DB::table('classes')->find($sectionId);
    $grade = DB::table('grades')->find($section->grade_id);
    $students = \App\Models\User::role('student')
        ->whereHas('studentProfile', function($q) use ($sectionId) {
            $q->where('class_id', $sectionId);
        })->orderBy('name')->get();

    // جلب غياب اليوم إذا كان قد رُصد مسبقاً
    $todayAttendance = DB::table('attendances')
        ->where('section_id', $sectionId)
        ->where('attendance_date', date('Y-m-d'))
        ->pluck('status', 'student_id');

    return view('teacher.attendance.index', compact('section', 'grade', 'students', 'todayAttendance'));
}

public function store(Request $request)
{
    $sectionId = $request->section_id;
    $date = date('Y-m-d');

    foreach ($request->attendance as $studentId => $status) {
        DB::table('attendances')->updateOrInsert(
            [
                'student_id' => $studentId,
                'section_id' => $sectionId,
                'attendance_date' => $date
            ],
            [
                'school_id' => auth()->user()->school_id,
                'teacher_id' => auth()->user()->id,
                'status' => $status,
                'updated_at' => now()
            ]
        );
    }

    return back()->with('success', 'تم حفظ غياب اليوم بنجاح ✅');
}
}
