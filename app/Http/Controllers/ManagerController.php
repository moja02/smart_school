<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash; // ✅ ضروري لتشفير كلمة المرور

class ManagerController extends Controller
{
    // 1. الصفحة الرئيسية للمدير (Dashboard)
    public function dashboard()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'students_count' => User::role('student')->where('school_id', $schoolId)->count(),
            'teachers_count' => User::role('teacher')->where('school_id', $schoolId)->count(),
            'classes_count'  => SchoolClass::where('school_id', $schoolId)->count(),
            'today_attendance' => Attendance::whereDate('attendance_date', now())
                                    ->where('status', 1)
                                    ->count()
        ];

        return view('manager.dashboard', compact('stats'));
    }

    // 2. عرض المعلمين
    public function listTeachers()
    {
        $teachers = User::role('teacher')->where('school_id', auth()->user()->school_id)->paginate(10);
        return view('manager.teachers.index', compact('teachers'));
    }

    // ==========================================
    // 🎓 إدارة حساب مسؤول الدراسة (Admin) - جديد
    // ==========================================

    // 3. عرض صفحة إنشاء الأدمن
    public function createStudyOfficer()
    {
        // التحقق من وجود مسؤول سابق
        $currentOfficer = User::where('school_id', auth()->user()->school_id)
                            ->where('role', 'admin') 
                            ->first();

        return view('manager.create_admin', compact('currentOfficer'));
    }

    // 4. حفظ الأدمن الجديد
    public function storeStudyOfficer(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        // إنشاء المستخدم
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'school_id' => auth()->user()->school_id,
            'role'      => 'admin', // دور الآدمن
        ]);

        // تعيين الصلاحية
        try {
            $user->assignRole('admin');
        } catch (\Exception $e) { }

        return redirect()->back()->with('success', 'تم تعيين مسؤول الدراسة والامتحانات (Admin) بنجاح ✅');
    }
}