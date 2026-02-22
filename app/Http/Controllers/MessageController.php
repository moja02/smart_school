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

        // 🟢 1. إذا كان ولي أمر: يرى الإدارة + المعلمين + أبناءه فقط
        if ($user->role == 'parent') {
            
            $childrenUserIds = StudentProfile::whereIn('id', function($q) use ($user) {
                $q->select('student_id')->from('parent_student')->where('parent_id', $user->id);
            })->pluck('user_id');

            return User::where('role', 'admin')
                ->orWhere('role', 'teacher')
                ->orWhereIn('id', $childrenUserIds)
                ->get();
        }

        // 🔵 2. إذا كان طالباً: يرى المعلمين + الإدارة + (ولي أمره فقط)
        if ($user->role == 'student') {
            
            // جلب رقم حساب ولي الأمر المرتبط بهذا الطالب
            // نفترض أن الطالب له ملف شخصي واحد
            $parentId = DB::table('parent_student')
                        ->where('student_id', $user->studentProfile->id ?? 0)
                        ->value('parent_id');

            return User::whereIn('role', ['admin', 'teacher'])
                ->when($parentId, function($query, $parentId) {
                    return $query->orWhere('id', $parentId);
                })
                ->get();
        }

        // 🔴 3. الافتراضي (للأدمن والمعلم): يرى الجميع ما عدا نفسه
        return User::where('id', '!=', $user->id)->get();
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