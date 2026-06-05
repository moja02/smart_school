<?php

// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'role'     => 'required|in:student,teacher,parent',
        ]);

        User::create($data);

        return to_route('login.form')->with('success', 'تم إنشاء الحساب بنجاح، تفضل بتسجيل الدخول.');
    }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($creds, $request->has('remember'))) {
            
            // 👇 إضافة فحص الحظر هنا (بعد التأكد من صحة البيانات) 👇
            if (Auth::user()->is_banned) {
                Auth::logout(); // تسجيل خروج فوري
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'عذراً، هذا الحساب محظور من قبل الإدارة. يرجى مراجعة المدرسة.']);
            }
            // 👆 نهاية كود الحظر 👆

            $request->session()->regenerate();
            
            // ✅ التعديل هنا: نمرر كائن المستخدم بالكامل للدالة للتحقق الدقيق
            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login.form');
    }

    // ✅ دالة التوجيه الشاملة (تدعم Spatie والعمود العادي)
    protected function redirectByRole($user)
    {
        if ($user->hasRole('super_admin') || $user->role === 'super_admin') {
            return redirect()->route('system.schools.index'); // 🌐 توجيه مدير النظام (الجديد)
        }
        if ($user->hasRole('manager') || $user->role === 'manager') {
            return redirect()->route('manager.dashboard'); // 👔 توجيه مدير المدرسة
        }
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return redirect()->route('admin.dashboard'); // 🎓 توجيه مسؤول الدراسة
        }
        if ($user->hasRole('teacher') || $user->role === 'teacher') {
            return redirect()->route('teacher.dashboard'); // 👨‍🏫 توجيه المعلم
        }
        if ($user->hasRole('parent') || $user->role === 'parent') {
            return redirect()->route('parent.dashboard'); // 👨‍👩‍👦 توجيه ولي الأمر
        }
        if ($user->hasRole('student') || $user->role === 'student') {
            return redirect()->route('student.dashboard'); // 🎒 توجيه الطالب
        }

        // صمام الأمان: في حال لم يمتلك أي صلاحية صالحة
        Auth::logout();
        return redirect()->route('login.form')->withErrors(['email' => 'حسابك لا يملك أي صلاحيات للدخول إلى النظام.']);
    }
}