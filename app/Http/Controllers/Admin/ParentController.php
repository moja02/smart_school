<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function listParents(Request $request)
    {
        $query = User::where('role', 'parent');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $parents = $query->latest()->paginate(10);

        return view('admin.parents.index', compact('parents'));
    }

    public function createParentLink(Request $request)
    {
        $parents = User::role('parent')->get();
        
        $linkedStudentIds = User::role('parent')
                            ->has('children')
                            ->with('children')
                            ->get()
                            ->pluck('children')
                            ->flatten()
                            ->pluck('id')
                            ->unique()
                            ->toArray();

        $students = User::role('student')
                        ->whereNotIn('id', $linkedStudentIds)
                        ->get();

        $query = User::role('parent')->has('children')->with('children');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $parentsWithChildren = $query->paginate(10);

        return view('admin.parents.link', compact('parents', 'students', 'parentsWithChildren'));
    }

    public function storeParentLink(Request $request)
    {
        $request->validate([
            'parent_id'   => 'required|exists:users,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $parent = User::findOrFail($request->parent_id);

        $parent->children()->syncWithoutDetaching($request->student_ids);

        return redirect()->back()->with('success', 'تم ربط الطلاب بولي الأمر بنجاح ✅');
    }

    public function deleteParentLink(Request $request, $id)
    {
        $parent = User::findOrFail($id);
        $studentId = $request->input('student_id');

        $parent->children()->detach($studentId);

        return redirect()->back()->with('success', 'تم إلغاء ربط الطالب بولي الأمر.');
    }
}
