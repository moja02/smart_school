<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Message;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * عرض صفحة الداشبورد للطالب.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // الحصول على بيانات المستخدم الحالي
        $user = Auth::user();
        
        // الحصول على بيانات الطالب من العلاقة بين المستخدم وStudentProfile
        $studentProfile = $user->studentProfile;

        // الحصول على الدرجات الخاصة بالطالب
        $grades = Grade::where('student_id', $studentProfile->id)->get();

        // الحصول على آخر 5 رسائل للطالب
        $messages = Message::where('student_id', $studentProfile->id)->latest()->take(5)->get();

        // إرسال البيانات إلى الـ view
        return view('student.dashboard', compact('studentProfile', 'grades', 'messages'));
    }

    /**
     * عرض صفحة الملف الشخصي للطالب.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        // الحصول على بيانات المستخدم الحالي
        $user = Auth::user();

        // الحصول على بيانات الطالب
        $studentProfile = $user->studentProfile;

        // عرض الصفحة مع تمرير بيانات الملف الشخصي
        return view('student.profile', compact('studentProfile'));
    }

    /**
     * تحديث الملف الشخصي للطالب.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        // التحقق من البيانات المدخلة
        $request->validate([
            'name' => 'required|string|max:255', // اسم الطالب
            'class_name' => 'required|string|max:255', // الصف الدراسي
        ]);

        // الحصول على المستخدم الحالي
        $user = Auth::user();

        // تحديث اسم المستخدم
        $user->update([
            'name' => $request->name
        ]);

        // تحديث معلومات الطالب مثل الصف الدراسي
        $user->studentProfile->update([
            'class_name' => $request->class_name
        ]);

        // إعادة التوجيه إلى صفحة الملف الشخصي مع رسالة نجاح
        return redirect()->route('student.profile')->with('status', 'تم تحديث الملف الشخصي بنجاح!');
    }

    /**
     * عرض درجات الطالب.
     *
     * @return \Illuminate\View\View
     */
    public function grades()
    {
        // الحصول على بيانات المستخدم الحالي
        $user = Auth::user();

        // الحصول على درجات الطالب
        $grades = Grade::where('student_id', $user->studentProfile->id)->get();

        // عرض درجات الطالب
        return view('student.grades', compact('grades'));
    }

    /**
     * عرض الرسائل الخاصة بالطالب.
     *
     * @return \Illuminate\View\View
     */
    public function messages()
    {
        // الحصول على بيانات المستخدم الحالي
        $user = Auth::user();

        // الحصول على آخر الرسائل للطالب
        $messages = Message::where('student_id', $user->studentProfile->id)->latest()->get();

        // عرض الرسائل
        return view('student.messages', compact('messages'));
    }
}
