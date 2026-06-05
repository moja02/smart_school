<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SystemManagerController extends Controller
{
    // 1. عرض لوحة التحكم الرئيسية لمدير النظام (قائمة المدارس)
    public function index()
    {
        // جلب جميع المدارس مع عدد المستخدمين في كل مدرسة
        $schools = School::withCount('users')->get();
        return view('system.schools.index', compact('schools'));
    }

    // 2. حفظ مدرسة جديدة
    public function storeSchool(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        School::create($request->all());

        return redirect()->route('system.schools.index')
            ->with('success', 'تم تسجيل المدرسة الجديدة بنجاح!');
    }

    // 3. عرض صفحة إنشاء مستخدم (مدير، معلم، الخ) لمدرسة معينة
    // 3. عرض صفحة إنشاء مستخدم لمدرسة معينة
    public function createUser($school_id)
    {
        $school = School::findOrFail($school_id);
        
        // ✅ التعديل هنا: جلب الرتب الأربعة المحددة فقط بدلاً من جلب كل شيء
        $allowedRoles = ['admin', 'teacher', 'student', 'parent'];
        $roles = Role::whereIn('name', $allowedRoles)->get();

        return view('system.users.create', compact('school', 'roles'));
    }

    // 4. حفظ المستخدم وربطه بالمدرسة
    public function storeUser(Request $request, $school_id)
    {
        // ✅ حماية إضافية: التأكد أن الرتبة المرسلة هي واحدة من الأربعة فقط
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,teacher,student,parent', 
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => $school_id, 
            'role' => $request->role, // ✅ حفظ الرتبة في عمود الـ ENUM الخاص بك
        ]);

        // ✅ إعطاء المستخدم الصلاحية عبر مكتبة Spatie
        $user->assignRole($request->role);

        // ترجمة الرتبة لطباعتها في رسالة النجاح
        $roleNameAr = match($request->role) {
            'admin' => 'مدير مدرسة',
            'teacher' => 'أستاذ',
            'student' => 'طالب',
            'parent' => 'ولي أمر',
            default => $request->role,
        };

        return redirect()->route('system.schools.index')
            ->with('success', "تم إنشاء حساب ($roleNameAr) بنجاح للمدرسة!");
    }
}