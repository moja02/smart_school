<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class userController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    // التحقق من صحة البيانات
    $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed', // تأكد من أن كلمة المرور تتطابق مع "تأكيد كلمة المرور"
        'name' => 'required|string|max:255',
    ]);

   User::create($request->all());

    // تخزين رسالة في الجلسة
    session()->flash('success', 'تم إنشاء الحساب بنجاح!');

    // إرجاع استجابة بعد العملية، يمكن أن تكون إعادة توجيه أو عرض رسالة
    return redirect()->route('users.index'); // إعادة التوجيه إلى قائمة المستخدمين مثلاً
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
