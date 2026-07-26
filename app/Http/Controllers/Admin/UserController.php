<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function listUsers(Request $request)
    {
        $query = User::where('school_id', auth()->user()->school_id)
                     ->whereNotIn('role', ['manager', 'admin']);

        // أ. فلتر البحث النصي (الاسم أو البريد)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // ب. فلتر حسب الصلاحية (Role)
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // تنفيذ الاستعلام
        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // منع حذف النفس
        if ($user->id == Auth::id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي!');
        }

        $user->delete();
        return back()->with('success', 'تم حذف المستخدم بنجاح.');
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        // إعادة تعيين كلمة المرور إلى قيمة افتراضية
        $defaultPassword = '12345678';
        
        $user->update([
            'password' => Hash::make($defaultPassword)
        ]);

        return back()->with('success', "تم إعادة تعيين كلمة مرور المستخدم {$user->name} إلى: $defaultPassword");
    }

    // 1. دالة عرض صفحة التعديل
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    // 2. دالة حفظ التعديلات
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required|in:teacher,student,parent',
            'password' => 'nullable|min:6',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users')->with('success', 'تم تحديث بيانات المستخدم بنجاح ✅');
    }

    public function createUser()
    {
        $students = User::role('student')->get(['id', 'name']);
        return view('admin.users.create', compact('students'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:teacher,student,parent',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/photos'), $filename);
            $photoPath = 'uploads/photos/' . $filename;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => auth()->user()->school_id,
            'role' => $request->role,
            'photo' => $photoPath,
        ]);

        $user->assignRole($request->role);

        if ($request->role == 'student') {
            StudentProfile::create(['user_id' => $user->id]);
        }

        if ($request->role === 'parent' && $request->has('student_ids')) {
            $user->children()->attach($request->student_ids);
        }

        return redirect()->route('admin.users')->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    // دالة حظر وإلغاء حظر المستخدم
    public function toggleBan($id)
    {
        $user = User::findOrFail($id);
        
        // حماية: منع الإدارة من حظر أنفسهم أو حظر المدير (Manager)
        if ($user->id == Auth::id() || $user->role == 'manager') {
            return back()->with('error', 'لا يمكنك حظر هذا الحساب!');
        }

        $user->is_banned = !$user->is_banned;
        $user->save();

        $status = $user->is_banned ? 'محظور 🚫' : 'نشط ✅';
        return back()->with('success', "تم تغيير حالة حساب المستخدم ({$user->name}) ليصبح: $status");
    }

    // البحث الشامل (Global Search)
    public function globalSearch(Request $request)
    {
        $query = $request->input('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $schoolId = auth()->user()->school_id;

        $users = User::where('school_id', $schoolId)
            ->where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'role')
            ->take(10)
            ->get();

        return response()->json($users);
    }

    public function storeStudyOfficer(\Illuminate\Http\Request $request)
    {
        // Implementation for storeStudyOfficer
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'school_id' => auth()->user()->school_id,
            'role' => 'admin',
        ]);
        $user->assignRole('admin');
        return redirect()->back()->with('success', 'تم إضافة مسؤول دراسة بنجاح.');
    }
}
