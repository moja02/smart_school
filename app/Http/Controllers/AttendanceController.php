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

    public function store(Request $request, $id = null)
    {
        $studentId = $id ?? $request->route('id') ?? $request->input('id') ?? $request->input('student_id');
        $status = $request->input('status');

        if ($studentId && $status) {
            \App\Models\Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'attendance_date' => date('Y-m-d')
                ],
                [
                    'user_id' => auth()->id() ?? 1,
                    'status' => $status,
                ]
            );
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success']);
            }
            return back()->with('success', 'تم تسجيل الحضور بنجاح ✅');
        }

        if ($request->has('attendance') && is_iterable($request->attendance)) {
            $sectionId = $request->section_id;
            $date = date('Y-m-d');

            foreach ($request->attendance as $sId => $st) {
                \App\Models\Attendance::updateOrCreate(
                    [
                        'student_id' => $sId,
                        'attendance_date' => $date
                    ],
                    [
                        'user_id' => auth()->id() ?? 1,
                        'class_id' => $sectionId,
                        'status' => $st,
                    ]
                );
            }

            return back()->with('success', 'تم حفظ غياب اليوم بنجاح ✅');
        }

        return back();
    }
}
