<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    // دالة مساعدة لتحديد "من يُسمح لي بمراسلته؟"
    private function getAllowedUsers()
{
    $user = Auth::user();
    $schoolId = $user->school_id;

    // 1. 🟢 إذا كان مديراً (Manager): يرى كل من في مدرسته
    if ($user->role == 'manager') {
        return User::where('school_id', $schoolId)
            ->where('id', '!=', $user->id)
            ->get();
    }

    // 2. 🔵 إذا كان أدمن (Admin): يرى المدير + المعلمين + الطلاب + أولياء الأمور
    if ($user->role == 'admin') {
        return User::where('school_id', $schoolId)
            ->where('id', '!=', $user->id)
            ->whereIn('role', ['manager', 'teacher', 'student', 'parent'])
            ->get();
    }

    // 3. 🟡 إذا كان ولي أمر: يرى المدير + الإدارة + المعلمين + أبناءه
    if ($user->role == 'parent') {
        $childrenUserIds = StudentProfile::where('parent_id', $user->id)->pluck('user_id');

        return User::where('school_id', $schoolId)
            ->where(function($q) use ($childrenUserIds) {
                $q->whereIn('role', ['manager', 'admin', 'teacher'])
                  ->orWhereIn('id', $childrenUserIds);
            })
            ->where('id', '!=', $user->id)
            ->get();
    }

    // 4. 🔴 إذا كان طالباً: يرى المدير + المعلمين + الإدارة + ولي أمره
    if ($user->role == 'student') {
        $parentId = $user->studentProfile->parent_id ?? null;
        $parentUserId = $parentId ? ParentProfile::find($parentId)->user_id : null;

        return User::where('school_id', $schoolId)
            ->where(function($q) use ($parentUserId) {
                $q->whereIn('role', ['manager', 'admin', 'teacher']);
                if ($parentUserId) {
                    $q->orWhere('id', $parentUserId);
                }
            })
            ->where('id', '!=', $user->id)
            ->get();
    }

    // 5. 🟣 إذا كان معلماً: يرى المدير + الإدارة + أولياء الأمور والطلاب (أو حسب رغبتك)
    if ($user->role == 'teacher') {
        return User::where('school_id', $schoolId)
            ->where('id', '!=', $user->id)
            ->whereIn('role', ['manager', 'admin', 'parent', 'student'])
            ->get();
    }

    return collect(); // إذا لم يكن له دور معروف
}


    // 1. عرض قائمة المستخدمين لبدء محادثة
    public function index()
    {
        // جلب كل المستخدمين ما عدا أنا
        $users = $this->getAllowedUsers();
        return view('messages.index', compact('users'));
    }

    // 2. عرض المحادثة مع شخص معين
    public function chat($userId)
    {
        $receiver = User::findOrFail($userId);
        $myId = Auth::id();

        Message::where('sender_id', $userId)   // الرسائل القادمة منه
               ->where('receiver_id', $myId)   // إليّ أنا
               ->where('is_read', false)       // التي لم تقرأ بعد
               ->update(['is_read' => true]);  // اجعلها مقروءة

        // جلب الرسائل بيني وبين هذا الشخص (مرتبة زمنياً)
        $messages = Message::where(function($q) use ($myId, $userId) {
                        $q->where('sender_id', $myId)->where('receiver_id', $userId);
                    })
                    ->orWhere(function($q) use ($myId, $userId) {
                        $q->where('sender_id', $userId)->where('receiver_id', $myId);
                    })
                    ->orderBy('created_at', 'asc')
                    ->get();


        $users = $this->getAllowedUsers();

        return view('messages.chat', compact('receiver', 'messages', 'users'));
    }

    // 3. إرسال رسالة
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return back(); // إعادة توجيه لنفس الصفحة لرؤية الرسالة
    }
}