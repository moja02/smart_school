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

        if (Auth::attempt($creds, remember: true)) {
            $request->session()->regenerate();
            
            // ✅ التعديل هنا: استدعاء دالة التوجيه الذكي
            return $this->redirectByRole(Auth::user()->role);
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

    // ✅ دالة التوجيه بناءً على الصلاحية
    protected function redirectByRole(string $role)
    {
        return match ($role) {
            'manager' => redirect()->route('manager.dashboard'), // 👔 توجيه المدير للوحته الخاصة
            'admin'   => redirect()->route('admin.dashboard'),   // 🎓 توجيه الأدمن (مسؤول الدراسة)
            'student' => redirect()->route('student.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent'  => redirect()->route('parent.dashboard'),
            default   => redirect()->intended('dashboard'),      // الاحتياطي
        };
    }
}