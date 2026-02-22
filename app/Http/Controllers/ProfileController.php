<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // عرض صفحة الملف الشخصي
    public function edit()
    {
        $user = Auth::user();

        // تحديد القالب (Layout) المناسب بناءً على الصلاحيات الخمسة
        $layout = 'layouts.app'; // قالب افتراضي احتياطي
        
        if ($user->hasRole('manager')) {
            $layout = 'layouts.manager'; // قالب المدير العام (المالك)
        } elseif ($user->hasRole('admin')) {
            $layout = 'layouts.admin';   // قالب الإدارة المدرسية
        } elseif ($user->hasRole('teacher')) {
            $layout = 'layouts.teacher'; // قالب المعلم
        } elseif ($user->hasRole('student')) {
            $layout = 'layouts.student'; // قالب الطالب
        } elseif ($user->hasRole('parent')) {
            $layout = 'layouts.parent';  // قالب ولي الأمر
        }

        return view('profile.edit', compact('user', 'layout'));
    }
    // تحديث البيانات
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. التحقق من صحة المدخلات 
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'password' => 'nullable|string|min:8|confirmed', // كلمة المرور اختيارية
        ]);

        // 2. تحديث البيانات (لا نحدث الإيميل أبداً)
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->birth_date = $request->birth_date;

        // 3. تحديث كلمة المرور فقط إذا قام بإدخال واحدة جديدة
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'تم تحديث بيانات ملفك الشخصي بنجاح!');
    }
}