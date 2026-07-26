<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Admission;

class AdmissionController extends Controller
{
    public function index()
    {
        $admissions = Admission::with('grade')->orderBy('created_at', 'desc')->get();
        return view('admin.admissions.index', compact('admissions'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $admission = Admission::findOrFail($id);
        $admission->status = $request->status;
        $admission->save();

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب بنجاح.');
    }
}
