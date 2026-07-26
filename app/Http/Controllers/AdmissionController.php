<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Admission;
use App\Models\Grade;

class AdmissionController extends Controller
{
    public function create()
    {
        $grades = Grade::all();
        return view('admissions.create', compact('grades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_name' => 'required|string|max:255',
            'parent_email' => 'required|email|max:255',
            'parent_phone' => 'required|string|max:20',
            'student_name' => 'required|string|max:255',
            'student_dob' => 'required|date',
            'grade_id' => 'required|exists:grades,id',
            'notes' => 'nullable|string',
        ]);

        Admission::create($request->all());

        return redirect()->back()->with('success', 'تم استلام طلب التسجيل بنجاح. سنتواصل معك قريباً.');
    }
}
