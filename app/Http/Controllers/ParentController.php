<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StudentProfile;

class ParentController extends Controller
{
    // 1. الداشبورد: عرض الأبناء ودرجاتهم
    public function dashboard()
    {
        $parentId = Auth::id();

        // جلب بروفايلات الطلاب المرتبطين بولي الأمر هذا
        // مع جلب بيانات المستخدم (للاسم) والدرجات والمواد والفصل
        $children = StudentProfile::whereIn('id', function($q) use ($parentId) {
                        $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
                    })
                    ->with(['user', 'grades.subject', 'schoolClass'])
                    ->get();

        return view('parent.dashboard', compact('children'));
    }

    // 2. عرض صفحة تعديل الملف الشخصي
    public function editProfile()
    {
        return view('parent.profile');
    }

    // 3. حفظ التعديلات
    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6|confirmed', // كلمة المرور اختيارية
        ]);

        $user->name = $request->name;

        // تحديث كلمة المرور فقط إذا تم إدخالها
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'تم تحديث بياناتك بنجاح.');
    }

    public function attendance()
    {
        $parentId = \Illuminate\Support\Facades\Auth::id();

        // 1. جلب الأبناء
        $children = \App\Models\StudentProfile::whereIn('id', function($q) use ($parentId) {
                        $q->select('student_id')->from('parent_student')->where('parent_id', $parentId);
                    })->get();

        // 2. جلب سجلات الغياب لكل ابن
        $attendanceData = [];
        foreach($children as $child) {
            $attendanceData[$child->id] = \App\Models\Attendance::where('student_id', $child->id)
                                            ->orderBy('attendance_date', 'desc')
                                            ->get();
        }

        return view('parent.attendance', compact('children', 'attendanceData'));
    }
}