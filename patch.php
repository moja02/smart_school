<?php

// 1. Add missing deleteStudent to AdminStudentController
$studentControllerPath = __DIR__ . '/app/Http/Controllers/Admin/StudentController.php';
$studentContent = file_get_contents($studentControllerPath);
$deleteStudentMethod = "
    public function deleteStudent(\$id)
    {
        \$user = \App\Models\User::role('student')->findOrFail(\$id);
        \$user->delete();
        return back()->with('success', 'تم حذف الطالب بنجاح.');
    }
}
";
$studentContent = preg_replace('/}\s*$/', $deleteStudentMethod, $studentContent);
file_put_contents($studentControllerPath, $studentContent);

// 2. Add missing storeStudyOfficer to AdminUserController
$userControllerPath = __DIR__ . '/app/Http/Controllers/Admin/UserController.php';
$userContent = file_get_contents($userControllerPath);
$storeStudyOfficerMethod = "
    public function storeStudyOfficer(\Illuminate\Http\Request \$request)
    {
        // Implementation for storeStudyOfficer
        \$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        \$user = \App\Models\User::create([
            'name' => \$request->name,
            'email' => \$request->email,
            'password' => \Illuminate\Support\Facades\Hash::make(\$request->password),
            'school_id' => auth()->user()->school_id,
            'role' => 'admin',
        ]);
        \$user->assignRole('admin');
        return redirect()->back()->with('success', 'تم إضافة مسؤول دراسة بنجاح.');
    }
}
";
$userContent = preg_replace('/}\s*$/', $storeStudyOfficerMethod, $userContent);
file_put_contents($userControllerPath, $userContent);

// 3. Add missing toggleGrading to AdminOperationController
$operationControllerPath = __DIR__ . '/app/Http/Controllers/Admin/OperationController.php';
$opContent = file_get_contents($operationControllerPath);
$toggleGradingMethod = "
    public function toggleGrading(\$semester)
    {
        \$school = \App\Models\School::find(auth()->user()->school_id);
        if (\$semester == 1) {
            \$school->grading_locked_sem1 = !\$school->grading_locked_sem1;
        } else {
            \$school->grading_locked_sem2 = !\$school->grading_locked_sem2;
        }
        \$school->save();
        return back()->with('success', 'تم تحديث حالة الرصد بنجاح.');
    }
}
";
$opContent = preg_replace('/}\s*$/', $toggleGradingMethod, $opContent);
file_put_contents($operationControllerPath, $opContent);

// 4. Add missing editSubject to AdminSubjectController
$subjectControllerPath = __DIR__ . '/app/Http/Controllers/Admin/SubjectController.php';
$subjectContent = file_get_contents($subjectControllerPath);
$editSubjectMethod = "
    public function editSubject(\$id)
    {
        \$subject = \App\Models\Subject::findOrFail(\$id);
        \$grades = \App\Models\Grade::where('school_id', auth()->user()->school_id)->get();
        return view('admin.subjects.edit', compact('subject', 'grades'));
    }
}
";
$subjectContent = preg_replace('/}\s*$/', $editSubjectMethod, $subjectContent);
file_put_contents($subjectControllerPath, $subjectContent);

echo "Missing methods added!\n";
