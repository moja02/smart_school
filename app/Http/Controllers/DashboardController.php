<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class DashboardController extends Controller
// {
//     public function index()
//     {
//         $role = Auth::user()->role;

//         if ($role == 'admin') {
//             return view('admin.dashboard'); // تأكد أن ملف العرض هذا موجود
//         } 
//         elseif ($role == 'student') {
//             return redirect()->route('student.dashboard');
//         }
//         elseif ($role == 'teacher') {
//             return redirect()->route('teacher.dashboard');
//         }
//         elseif ($role == 'parent') {
//             return redirect()->route('parent.dashboard');
//         }

//         return abort(403, 'User role not found');
//     }
// }
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. توجيه الطالب
        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        } 
        // 2. توجيه المعلم
        elseif ($user->role === 'teacher') {
            return redirect()->route('teacher.dashboard');
        }
        // 3. توجيه ولي الأمر (هذا هو الجزء الذي كان ناقصاً) 👇
        elseif ($user->role === 'parent') {
            return redirect()->route('parent.dashboard');
        }

        // 4. إذا لم يكن أياً مما سبق (يعني أدمن)، اعرض لوحة الأدمن
        $totalStudents = StudentProfile::count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $classes = SchoolClass::count();

        return view('admin.dashboard', compact('totalStudents', 'totalTeachers', 'classes'));
    }
}
