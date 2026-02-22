<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Grade;       // موديل السنوات الدراسية الجديد
use App\Models\SubjectGrade; // موديل ربط المواد بالسنوات
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    // ==========================================
    // ⚙️ إعدادات هيكلية المدرسة (تفعيل المراحل)
    // ==========================================
    /**
     * عرض الصفحة الرئيسية للوحة تحكم الأدمن
     */
    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $totalStudents = User::role('student')->where('school_id', $schoolId)->count();
        $totalTeachers = User::role('teacher')->where('school_id', $schoolId)->count();
        $classes = SchoolClass::where('school_id', $schoolId)->count();
        
        // ✅ التصحيح هنا: أضفنا admin. قبل اسم الملف
        return view('admin.dashboard', compact('totalStudents', 'totalTeachers', 'classes'));
    }

    // 1. عرض صفحة اختيار المراحل
    public function editSchoolStructure()
    {
        // جلب كل المراحل المتوفرة في النظام (الماستر)
        $allGrades = Grade::whereNull('school_id')->get(); 
        
        // جلب المراحل المفعلة حالياً لهذه المدرسة
        $schoolId = auth()->user()->school_id;
        $activeGradeIds = \DB::table('school_grade')
                            ->where('school_id', $schoolId)
                            ->pluck('grade_id')
                            ->toArray();

        return view('admin.settings.structure', compact('allGrades', 'activeGradeIds'));
    }

    // 2. حفظ المراحل المختارة
    public function updateSchoolStructure(Request $request)
    {
        $request->validate([
            'grades' => 'array', // مصفوفة الـ IDs المختارة
            'grades.*' => 'exists:grades,id',
        ]);

        $schoolId = auth()->user()->school_id;
        
        // استخدام sync لتحديث القائمة (يحذف القديم ويضيف الجديد)
        // إذا لم يكن لديك موديل School، نستخدم DB مباشرة
        $grades = $request->input('grades', []);
        
        // تجهيز البيانات للإدخال
        $data = [];
        foreach ($grades as $gradeId) {
            $data[] = [
                'school_id' => $schoolId, 
                'grade_id' => $gradeId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // حذف القديم وإدخال الجديد (Manual Sync)
        \DB::transaction(function () use ($schoolId, $data) {
            \DB::table('school_grade')->where('school_id', $schoolId)->delete();
            \DB::table('school_grade')->insert($data);
        });

        return redirect()->route('admin.subjects')->with('success', 'تم تحديث هيكلية المدرسة والمراحل الدراسية بنجاح ✅');
    }
    // =========================================================
    // 1. إدارة المستخدمين (مع الفلترة والبحث)
    // =========================================================
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
    $defaultPassword = '12345678'; // أو 12345678
    
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
        'email' => 'required|email|unique:users,email,' . $id, // استثناء الإيميل الحالي من فحص التكرار
        'role'  => 'required|in:teacher,student,parent',
        'password' => 'nullable|min:6', // كلمة المرور اختيارية عند التعديل
    ]);

    // تحديث البيانات الأساسية
    $user->name  = $request->name;
    $user->email = $request->email;
    $user->role  = $request->role; // تحديث العمود النصي

    // تحديث كلمة المرور فقط إذا تم إدخالها
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    // تحديث الصلاحيات (Spatie)
    $user->syncRoles([$request->role]);

    return redirect()->route('admin.users')->with('success', 'تم تحديث بيانات المستخدم بنجاح ✅');
    }

    public function createUser()
    {
        // جلب جميع الطلاب فقط (id والاسم) لتخفيف الحمل
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => auth()->user()->school_id,
        ]);

        $user->assignRole($request->role);

        // إنشاء بروفايل للطالب تلقائياً
        if ($request->role == 'student') {
            StudentProfile::create(['user_id' => $user->id]);
        }

        //✅ ربط الأبناء (إذا كان ولي أمر وتم اختيار طلاب)
        if ($request->role === 'parent' && $request->has('student_ids')) {
            // نستخدم attach لإضافة العلاقات
            $user->children()->attach($request->student_ids);
        }

        return redirect()->route('admin.users')->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    // ==========================================
    // إدارة أولياء الأمور وربط الطلاب
    // ==========================================

    // 1. عرض صفحة الربط
    // 1. عرض صفحة الربط (مع البحث)
    public function createParentLink(Request $request)
    {
        // 1. جلب جميع أولياء الأمور (للقائمة المنسدلة - لا تتأثر بالبحث)
        $parents = User::role('parent')->get();
        
        // 2. جلب جميع الطلاب (للقائمة المنسدلة - لا تتأثر بالبحث)
        $students = User::role('student')->get();

        // 3. جلب جدول العلاقات (الآباء مع أبنائهم) - ✅ هنا نضيف البحث
        $query = User::role('parent')->has('children')->with('children');

        // تطبيق البحث إذا وجد
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // تنفيذ الاستعلام مع التقسيم للصفحات (Pagination)
        $parentsWithChildren = $query->paginate(10);

        return view('admin.parents.link', compact('parents', 'students', 'parentsWithChildren'));
    }
    // 2. حفظ الربط (تخزين البيانات)
    public function storeParentLink(Request $request)
    {
        $request->validate([
            'parent_id'   => 'required|exists:users,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $parent = User::findOrFail($request->parent_id);

        // استخدام syncWithoutDetaching لمنع حذف الأبناء القدامى وإضافة الجدد فقط
        $parent->children()->syncWithoutDetaching($request->student_ids);

        return redirect()->back()->with('success', 'تم ربط الطلاب بولي الأمر بنجاح ✅');
    }

    // 3. حذف ربط طالب معين بولي أمر
    public function deleteParentLink(Request $request, $id)
    {
        // الـ $id هنا هو معرف ولي الأمر (Parent ID)
        // سنحتاج لمعرفة معرف الطالب من الطلب (Request)
        
        $parent = User::findOrFail($id);
        $studentId = $request->input('student_id');

        // فك الارتباط
        $parent->children()->detach($studentId);

        return redirect()->back()->with('success', 'تم إلغاء ربط الطالب بولي الأمر.');
    }

    public function listParents(Request $request)
    {
        $query = User::where('role', 'parent');

        // إذا تم إرسال كلمة بحث
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

    // =========================================================
    // 2. إدارة الهيكل الدراسي (السنوات والفصول) - ديناميكي
    // =========================================================
    
    //  دالة حفظ المرحلة الدراسية (Grades)
    // ==========================================
    public function storeGrade(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:grades,name',
            'notes' => 'nullable|string',
        ]);

        Grade::create([
            'name' => $request->name,
            'notes' => $request->notes,
            // يمكن إضافة school_id هنا إذا كانت المراحل تختلف من مدرسة لأخرى
            'school_id' => auth()->user()->school_id, 
        ]);

        return redirect()->back()->with('success', 'تم إضافة المرحلة الدراسية بنجاح ✅');
    }

    public function listClasses()
    {
        $user = auth()->user();

        if (!$user->school_id) {
            return redirect()->route('admin.dashboard')->with('error', 'حسابك غير مرتبط بمدرسة.');
        }

        // جلب الصفوف (Grades) التي تحتوي على فصول (Classes) في هذه المدرسة
        // مع جلب عدد الطلاب في كل شعبة (للعرض الإحصائي)
        $grades = \App\Models\Grade::whereHas('classes', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->with(['classes' => function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->withCount('students'); // تأكد أن علاقة students موجودة في موديل SchoolClass
            }])
            ->get();

        return view('admin.classes.index', compact('grades'));
    }
    
    public function assignUnassignedStudents(Request $request)
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // تحديث جميع الطلاب المختارين دفعة واحدة
        StudentProfile::whereIn('user_id', $request->student_ids)
                    ->update(['class_id' => $request->class_id]);

        return redirect()->back()->with('success', 'تم تسكين الطلاب المختارين في الفصل بنجاح ✅');
    }

    
    
    // 🆕 صفحة إنشاء فصل جديد (هذه الدالة الجديدة)
    public function createClass()
    {
        $user = auth()->user();
        
        // جلب الصفوف مع فصولها الحالية
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($user){
                $q->select('grade_id')->from('school_grade')->where('school_id', $user->school_id);
            })
            ->with(['classes' => function($query) use ($user) {
                $query->where('school_id', $user->school_id);
            }])
            ->get();

        return view('admin.classes.create', compact('grades'));
    }

    // دالة الحفظ الجديدة (Bulk Create)
    public function storeClass(Request $request)
    {
        $request->validate([
            'grade_id'   => 'required|exists:grades,id',
            'sections'   => 'required|array',       // مصفوفة
            'sections.*' => 'required|string|distinct', // عناصر المصفوفة
        ]);

        $user = auth()->user();
        $grade = \App\Models\Grade::find($request->grade_id);
        $count = 0;

        foreach ($request->sections as $sectionName) {
            if (!empty($sectionName)) {
                // التحقق من التكرار
                $exists = \App\Models\SchoolClass::where('school_id', $user->school_id)
                            ->where('grade_id', $request->grade_id)
                            ->where('section', $sectionName)
                            ->exists();

                if (!$exists) {
                    \App\Models\SchoolClass::create([
                        'name'      => $grade->name. ' - ' . $sectionName,
                        'section'   => $sectionName,
                        'grade_id'  => $request->grade_id,
                        'school_id' => $user->school_id,
                    ]);
                    $count++;
                }
            }
        }

        if ($count > 0) {
            return redirect()->back()->with('success', "تم إضافة $count شعبة بنجاح ✅");
        } else {
            return redirect()->back()->with('warning', 'لم يتم إضافة أي شعبة (قد تكون مكررة أو فارغة).');
        }
    }

    // 2. حذف الفصل
    public function deleteClass($id)
    {
        \App\Models\SchoolClass::where('school_id', auth()->user()->school_id)->findOrFail($id)->delete();
        return redirect()->back()->with('success', 'تم حذف الفصل بنجاح 🗑️');
    }
    // 3. نقل طالب من فصل لآخر
    public function transferStudent(Request $request)
{
    $request->validate([
        'class_id'      => 'required|exists:classes,id',
        'student_ids'   => 'required|array',       // ✅ أصبحت مصفوفة
        'student_ids.*' => 'exists:users,id',
    ]);

    // تحديث جميع الطلاب المختارين
    StudentProfile::whereIn('user_id', $request->student_ids)
                  ->update(['class_id' => $request->class_id]);

    return redirect()->back()->with('success', 'تم نقل الطلاب المختارين للفصل الجديد بنجاح ✅');
}
// دالة AJAX لجلب الشعب
    public function getGradeSections($grade_id)
    {
        $user = auth()->user();

        // جلب الشعب (sections) فقط
        $sections = \App\Models\SchoolClass::where('grade_id', $grade_id)
                    ->where('school_id', $user->school_id) // الشعب الخاصة بهذه المدرسة
                    ->pluck('section')
                    ->toArray();

        // إرجاع النتيجة كـ JSON لكي يفهمها الجافاسكربت
        return response()->json($sections);
    }
    // ==========================================
    // 🚌 نقل الطلاب الجماعي (Bulk Transfer)
    // ==========================================
    public function listStudents(Request $request)
{
    $user = auth()->user();
    $schoolId = $user->school_id;

    // 1. جلب كل الصفوف المتاحة للمدرسة (للقائمة الأولى)
    $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
        $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
    })->get();

    // 2. جلب الشعب بناءً على الصف المختار (إذا اختار المستخدم صفاً)
    $sections = collect(); // مجموعة فارغة افتراضياً
    if ($request->filled('grade_id')) {
        $sections = \App\Models\SchoolClass::where('grade_id', $request->grade_id)
                    ->where('school_id', $schoolId)
                    ->get();
    }

    // 3. فلترة الطلاب بناءً على الاختيارات
    $query = \App\Models\User::role('student')->where('school_id', $schoolId);

    if ($request->filled('class_id')) {
        $query->whereHas('studentProfile', function($q) use ($request) {
            $q->where('class_id', $request->class_id);
        });
    } elseif ($request->filled('grade_id')) {
        $query->whereHas('studentProfile.schoolClass', function($q) use ($request) {
            $q->where('grade_id', $request->grade_id);
        });
    }

    $students = $query->with(['studentProfile.schoolClass'])->get();

    return view('admin.students.index', compact('students', 'grades', 'sections'));
}
    public function bulkTransfer(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array',          // مصفوفة معرفات الطلاب
            'student_ids.*' => 'exists:users,id',         // التأكد من وجودهم
            'new_class_id'  => 'required|exists:classes,id', // الفصل الجديد
        ]);

        // جلب الفصل الجديد للتأكد من وجوده ومعرفة اسمه (لرسالة النجاح)
        $newClass = \App\Models\SchoolClass::find($request->new_class_id);

        // تحديث جميع الطلاب المحددين دفعة واحدة
        // نفترض أن class_id موجود في جدول student_profiles
        // إذا كان في جدول users، غير student_profiles إلى users
        \App\Models\StudentProfile::whereIn('user_id', $request->student_ids)
            ->update(['class_id' => $request->new_class_id]);

        return redirect()->back()->with('success', "تم نقل " . count($request->student_ids) . " طالب إلى فصل ($newClass->name - $newClass->section) بنجاح ✅");
    }

    // ==========================================
    // تعديل الفصول الدراسية
    // ==========================================

    // 1. عرض صفحة التعديل
    public function editClass($id)
    {
        $class = SchoolClass::findOrFail($id);
        // جلب المراحل الخاصة بالمدرسة لتعديل المرحلة إذا لزم الأمر
        $grades = Grade::where('school_id', auth()->user()->school_id)->get();
        
        return view('admin.classes.edit', compact('class', 'grades'));
    }

    // 2. حفظ التعديلات
    public function updateClass(Request $request, $id)
    {
        $request->validate([
            'section' => 'required|string|max:255',
        ]);

        $class = \App\Models\SchoolClass::findOrFail($id);
        
        // التأكد أن المستخدم يملك صلاحية تعديل فصول مدرسته فقط
        if ($class->school_id !== auth()->user()->school_id) {
            return back()->with('error', 'غير مصرح لك بهذا الإجراء');
        }

        $class->update([
            'section' => $request->section,
            // تحديث الاسم الكامل للفصل إذا كنت تستخدم صيغة (الصف - الشعبة)
            'name' => $class->grade->name . ' - ' . $request->section,
        ]);

        return back()->with('success', 'تم تحديث اسم الشعبة بنجاح');
    }

    // دالة عرض طلاب الفصل (تقرير الفصل)
    public function showClassStudents($id)
    {
        // جلب الفصل مع المرحلة
        $class = SchoolClass::with('grade')->findOrFail($id);

        // جلب الطلاب المسكنين في هذا الفصل
        // نفترض أن العلاقة عبر StudentProfile
        $students = User::role('student')
            ->whereHas('studentProfile', function($q) use ($id) {
                $q->where('class_id', $id);
            })
            ->with('studentProfile') // لجلب بيانات إضافية لو احتجت
            ->orderBy('name')
            ->get();

        return view('admin.classes.students', compact('class', 'students'));
    }

    // دالة تقرير المرحلة (كشف الدرجات الشامل)
    public function showGradeReport(Request $request)
{
    $schoolId = auth()->user()->school_id;
    $gradeId = $request->grade_id;

    // 1. جلب قائمة الصفوف للفلتر
    $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
        $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
    })->get();

    $topStudents = collect();
    $selectedGrade = null;

    if ($gradeId) {
        $selectedGrade = \App\Models\Grade::find($gradeId);

        // 2. جلب الطلاب وحساب مجموعهم ونسبتهم
        // ملاحظة: نفترض وجود جدول 'grades' يحتوي على درجات الطلاب
        $topStudents = \App\Models\User::role('student')
            ->where('school_id', $schoolId)
            ->whereHas('schoolClass', function($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })
            ->with(['schoolClass'])
            ->get()
            ->map(function($student) {
                // هنا نقوم بحساب المجموع (مثال برمجياً)
                $totalScore = \DB::table('student_scores')->where('student_id', $student->id)->sum('score');
                $maxPossible = \DB::table('school_subject_settings') // نستخدم إعداداتنا التي برمجناها سابقاً
                                ->where('school_id', $student->school_id)
                                ->sum('total_score');

                $student->total_final_score = $totalScore;
                $student->percentage = $maxPossible > 0 ? ($totalScore / $maxPossible) * 100 : 0;
                
                return $student;
            })
            ->sortByDesc('total_final_score')
            ->take(10); // أفضل 10 طلاب فقط
    }

    return view('admin.reports.index', compact('grades', 'topStudents', 'selectedGrade'));
}

// دالة عرض التقارير الرئيسية
public function showReports(Request $request)
{
    $schoolId = auth()->user()->school_id;
    $type = $request->type;
    $gradeId = $request->grade_id;

    $school = \App\Models\School::find($schoolId);

    $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
        $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
    })->get();

    $topStudents = collect();
    $studentsList = collect();
    $selectedGrade = null;

    if ($gradeId) {
        $selectedGrade = \App\Models\Grade::find($gradeId);

        // 1. منطق تقرير الأوائل
        if ($type == 'top_students') {
            $topStudents = \App\Models\User::role('student')
                ->where('school_id', $schoolId)
                ->whereHas('studentProfile.schoolClass', function($q) use ($gradeId) {
                    $q->where('grade_id', $gradeId);
                })
                ->with(['studentProfile.schoolClass'])
                ->get()
                ->map(function($student) use ($schoolId) {
                    // ✅ القراءة الآن من الجدول الصحيح student_scores
                    $total = \DB::table('student_scores')
                                ->where('student_id', $student->id)
                                ->sum('total_score'); 

                    // المجموع الكلي الممكن من إعدادات المواد
                    $maxPossible = \DB::table('school_subject_settings')
                                    ->where('school_id', $schoolId)
                                    ->sum('total_score');

                    $student->total_final_score = $total;
                    $student->percentage = $maxPossible > 0 ? ($total / $maxPossible) * 100 : 0;
                    
                    return $student;
                })
                ->sortByDesc('total_final_score')
                ->take(10);
        }
        
        // 2. منطق قائمة الشهادات
        elseif ($type == 'certificates') {
            $studentsList = \App\Models\User::role('student')
                ->where('school_id', $schoolId)
                ->whereHas('studentProfile.schoolClass', function($q) use ($gradeId) {
                    $q->where('grade_id', $gradeId);
                })
                ->with('studentProfile.schoolClass')
                ->orderBy('name')
                ->get();
        }
    }

    return view('admin.reports.index', compact('grades', 'topStudents', 'studentsList', 'selectedGrade', 'school'));
}

// دالة طباعة الشهادة الفردية
public function printCertificate($studentId)
{
    $schoolId = auth()->user()->school_id;
    $school = \App\Models\School::find($schoolId);
    
    // جلب بيانات الطالب
    $student = \App\Models\User::with(['studentProfile.schoolClass.grade'])->findOrFail($studentId);

    // ✅ جلب الدرجات من الجدول الجديد student_scores مع ربطه بالمواد
    $marks = \DB::table('student_scores')
        ->join('subjects', 'student_scores.subject_id', '=', 'subjects.id')
        ->where('student_scores.student_id', $studentId)
        ->select(
            'subjects.name as subject_name',
            'student_scores.works_score',
            'student_scores.final_score',
            'student_scores.total_score'
        )
        ->get();

    // حساب المجموع والنسبة
    $totalSum = $marks->sum('total_score');
    $maxPossible = \DB::table('school_subject_settings')->where('school_id', $schoolId)->sum('total_score');
    $percentage = $maxPossible > 0 ? ($totalSum / $maxPossible) * 100 : 0;

    return view('admin.reports.certificate', compact('student', 'school', 'marks', 'totalSum', 'percentage'));
}

    public function printReport(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        //جلب بيانات المدرسة من الجدول
        $school = \App\Models\School::find($schoolId);
        $gradeId = $request->grade_id;

        if (!$gradeId) {
            return redirect()->back()->with('error', 'يرجى اختيار الصف أولاً');
        }

        $selectedGrade = \App\Models\Grade::findOrFail($gradeId);

        // نفس منطق الحساب السابق
        $topStudents = \App\Models\User::role('student')
            ->where('school_id', $schoolId)
            ->whereHas('studentProfile.schoolClass', function($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })
            ->with(['studentProfile.schoolClass'])
            ->get()
            ->map(function($student) use ($schoolId) {
                // (تأكد من استخدام الجدول الصحيح حسب آخر تعديل عندك سواء grades أو student_scores)
                $total = \DB::table('student_scores') 
                            ->where('student_id', $student->id)
                            ->sum('total_score');

                $maxPossible = \DB::table('school_subject_settings')
                                ->where('school_id', $schoolId)
                                ->sum('total_score');

                $student->total_final_score = $total;
                $student->percentage = $maxPossible > 0 ? ($total / $maxPossible) * 100 : 0;
                return $student;
            })
            ->sortByDesc('total_final_score')
            ->take(10);

        // توجيه لصفحة الطباعة الجديدة
        return view('admin.reports.print', compact('selectedGrade', 'topStudents', 'school'));
    }
    // =========================================================
    // 3. إدارة المواد (Subjects) - ديناميكي
    // =========================================================

    // عرض صفحة توزيع الدرجات
    public function gradeSettings(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        
        // 1. جلب الصفوف المفعّلة للمدرسة حالياً
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        // 2. استعلام جلب المواد
        // سنبحث عن المواد التي تتبع مدرستك "أو" المواد العامة (التي ليس لها مدرسة محددة)
        $query = \App\Models\Subject::where(function($q) use ($schoolId) {
            $q->where('school_id', $schoolId)
            ->orWhereNull('school_id'); 
        });

        // 3. الفلترة حسب الصف الدراسي المختار
        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        } else {
            // إذا لم يتم اختيار صف، نعرض فقط المواد المربوطة بالصفوف المتاحة لهذه المدرسة
            $query->whereIn('grade_id', $grades->pluck('id'));
        }

        $subjects = $query->with('grade')->get();

        return view('admin.subjects.grade_settings', compact('subjects', 'grades'));
    }

    // حفظ التوزيع وإرساله (تحديث الدرجات)
    public function storeGradeSettings(Request $request)
    {
        // حماية لو تم الضغط على حفظ بدون مواد
        if (!$request->has('subject_id')) {
            return redirect()->back()->with('error', 'لا توجد مواد لحفظها');
        }

        $schoolId = auth()->user()->school_id;

        foreach ($request->subject_id as $index => $subjectId) {
            $works = $request->works_score[$index] ?? 40;
            $final = $request->final_score[$index] ?? 60;
            $total = $works + $final; // نحسبوا المجموع برمجياً للضمان
            $classes = $request->weekly_classes[$index] ?? 1;

            // نحفظوا أو نحدثوا البيانات في جدول الإعدادات
            \DB::table('school_subject_settings')->updateOrInsert(
                [
                    'school_id'  => $schoolId, 
                    'subject_id' => $subjectId
                ],
                [
                    'weekly_classes' => $classes,
                    'works_score'    => $works,
                    'final_score'    => $final,
                    'total_score'    => $total,
                    'created_at'     => now(),
                    'updated_at'     => now()
                ]
            );
        }

        return redirect()->back()->with('success', 'تم حفظ إعدادات المواد بنجاح ✅');
    }
    public function listSubjects()
    {
        $schoolId = auth()->user()->school_id;

        // ✅ التعديل الجوهري: نجلب فقط المراحل المربوطة بالمدرسة
        $grades = Grade::whereIn('id', function($query) use ($schoolId) {
                        $query->select('grade_id')
                            ->from('school_grade')
                            ->where('school_id', $schoolId);
                    })
                    ->with(['subjects' => function($query) use ($schoolId) {
                        $query->whereNull('school_id')        // المواد العامة
                            ->orWhere('school_id', $schoolId); // المواد الخاصة
                    }])
                    ->get();

        // إذا كانت القائمة فارغة (مدرسة جديدة)، نوجههم لصفحة الإعدادات
        if ($grades->isEmpty()) {
            return redirect()->route('admin.settings.structure')->with('warning', 'يرجى تحديد المراحل الدراسية الخاصة بالمدرسة أولاً.');
        }

        return view('admin.subjects.index', compact('grades'));
    }

    public function storeSubject(Request $request)
{
    // 1. التحقق (جعلنا عدد الحصص اختياري nullable)
    $request->validate([
        'name'           => 'required|string|max:255',
        'grade_id'       => 'required|exists:grades,id',
        'weekly_classes' => 'nullable|integer|min:1|max:20', 
    ]);

    // 2. الحفظ مع قيمة افتراضية
    \App\Models\Subject::create([
        'name'           => $request->name,
        'grade_id'       => $request->grade_id,
        'school_id'      => auth()->user()->school_id,
        // إذا لم يتم إرسال عدد الحصص، سيتم وضع 1 تلقائياً
        'weekly_classes' => $request->weekly_classes ?? 1, 
    ]);

    return redirect()->back()->with('success', 'تم إضافة المادة بنجاح ✅');
}

public function updateSubject(Request $request)
{
    // التحقق من البيانات (بدون عدد الحصص)
    $request->validate([
        'subject_id' => 'required|exists:subjects,id',
        'name'       => 'required|string|max:255',
        'grade_id'   => 'required|exists:grades,id',
    ]);

    $subject = \App\Models\Subject::findOrFail($request->subject_id);

    // حماية: التأكد أن المادة خاصة بالمدرسة وليست عامة
    if ($subject->school_id != auth()->user()->school_id) {
        return redirect()->back()->with('error', 'عذراً، لا يمكنك تعديل المواد العامة أو مواد مدارس أخرى.');
    }

    // التحديث
    $subject->update([
        'name'       => $request->name,
        'grade_id'   => $request->grade_id,
    ]);

    return redirect()->back()->with('success', 'تم تعديل بيانات المادة بنجاح ✅');
}

    // 2. دالة حذف مادة
    public function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);

        // حماية: منع حذف المواد العامة
        if ($subject->school_id == null) {
            return redirect()->back()->with('error', 'تنبيه: لا يمكن حذف المواد الأساسية العامة (مثل الرياضيات والعربي).');
        }

        // حماية: التأكد أن المادة تابعة لنفس المدرسة
        if ($subject->school_id != auth()->user()->school_id) {
            return redirect()->back()->with('error', 'ليس لديك صلاحية لحذف هذه المادة.');
        }

        // التحقق هل المادة مرتبطة بدرجات أو جداول (اختياري - لحماية البيانات)
        // if ($subject->marks()->count() > 0) { ... }

        $subject->delete();
        return redirect()->back()->with('success', 'تم حذف المادة الخاصة بنجاح 🗑️');
    }

    // =========================================================
    // 4. إدارة الطلاب والتوزيع (Assign)
    // =========================================================

    public function assign(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $selectedGradeId = $request->grade_id;
        $selectedSubjectId = $request->subject_id;

        // 1. جلب الصفوف المتاحة للمدرسة
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        // 2. جلب المواد بناءً على الصف المختار
        $subjects = collect();
        if ($selectedGradeId) {
            $subjects = \App\Models\Subject::where('grade_id', $selectedGradeId)
                ->where(function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->orWhereNull('school_id');
                })->get();
        }

        // 3. جلب الشعب وحالة الإسناد إذا تم اختيار المادة
        $sections = collect();
        $assignedSections = [];
        if ($selectedSubjectId) {
            $sections = \App\Models\Section::where('grade_id', $selectedGradeId)
                        ->where('school_id', $schoolId)->get();

            // جلب المعلمين المرتبطين بهذه المادة في هذه الشعب
            $assignedSections = \DB::table('teacher_subject_section')
                                ->where('subject_id', $selectedSubjectId)
                                ->pluck('teacher_name', 'section_id')->toArray();
        }

        $teachers = \App\Models\User::where('school_id', $schoolId)->where('role', 'teacher')->get();

        return view('admin.subjects.assign', compact('grades', 'subjects', 'sections', 'assignedSections', 'teachers'));
    }
    public function createAssignment(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $selectedGradeId = $request->grade_id;
        $selectedSubjectId = $request->subject_id;

        // 1. جلب الصفوف
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->get();

        // 2. جلب المواد
        $subjects = collect();
        if ($selectedGradeId) {
            $subjects = \App\Models\Subject::where('grade_id', $selectedGradeId)
                ->where(function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->orWhereNull('school_id');
                })->get();
        }

        // 3. جلب الشعب (المودل الصحيح هو SchoolClass)
        $sections = collect();
        $assignedSections = [];
        if ($selectedSubjectId) {
            $sections = \App\Models\SchoolClass::where('grade_id', $selectedGradeId)
                        ->where('school_id', $schoolId)->get();

            // تعديل هنا: جلب المعلم (الاسم والمعرف) لكل شعبة
            $assignedSections = \DB::table('teacher_subject_section')
                                ->where('subject_id', $selectedSubjectId)
                                ->where('school_id', $schoolId)
                                ->get()
                                ->keyBy('section_id'); // ترتيبهم برقم الشعبة ليسهل الوصول إليهم
        }

        $teachers = \App\Models\User::role('teacher')->where('school_id', $schoolId)->get();

        return view('admin.assign', compact('grades', 'subjects', 'sections', 'assignedSections', 'teachers'));
    }

    // دالة البحث عن المواد المتاحة لفصل معين (AJAX)
    // ملاحظة: تحتاج لإضافتها كـ Route إذا لم تكن موجودة
    public function getAvailableSubjects($class_id)
    {
        $class = SchoolClass::findOrFail($class_id);

        if (!$class->grade_id) {
            return response()->json([]);
        }

        // جلب المواد المرتبطة بسنة هذا الفصل
        $allSubjects = Subject::whereHas('grades', function($q) use ($class) {
            $q->where('grade_id', $class->grade_id);
        })->get();

        // استبعاد المواد المحجوزة مسبقاً لهذا الفصل
        $availableSubjects = $allSubjects->filter(function($subject) use ($class_id) {
            $isAssigned = DB::table('teacher_subject')
                        ->where('class_id', $class_id)
                        ->where('subject_id', $subject->id)
                        ->exists();
            return !$isAssigned;
        });

        return response()->json($availableSubjects->values());
    }

    public function storeAssignment(Request $request)
    {
        // التحقق من البيانات
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'section_ids' => 'required|array',
        ]);

        $schoolId = auth()->user()->school_id;
        $teacher = \App\Models\User::findOrFail($request->teacher_id);

        foreach ($request->section_ids as $sectionId) {
            // إضافة أو تحديث الإسناد (تجنب التكرار لنفس الشعبة والمادة)
            \DB::table('teacher_subject_section')->updateOrInsert(
                [
                    'school_id' => $schoolId,
                    'subject_id' => $request->subject_id,
                    'section_id' => $sectionId,
                ],
                [
                    'teacher_id' => $request->teacher_id,
                    'teacher_name' => $teacher->name, // حفظ الاسم لسرعة العرض كما طلبنا
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return back()->with('success', 'تم إسناد المادة للشعب المختارة بنجاح ✅');
    }

    public function removeAssignment(Request $request, $section_id)
{
    // أضف هذا السطر مؤقتاً للتأكد من وصول الطلب (إذا اشتغل سيظهر لك شاشة سوداء فيها رقم)
    // dd($section_id, $request->subject_id); 

    $subjectId = $request->subject_id;
    $schoolId = auth()->user()->school_id;

    \DB::table('teacher_subject_section')
        ->where('school_id', $schoolId)
        ->where('section_id', $section_id)
        ->where('subject_id', $subjectId)
        ->delete();

    return back()->with('success', 'تم إلغاء الربط بنجاح');
}
    public function updateAssignment(Request $request)
{
    $request->validate([
        'teacher_id' => 'required|exists:users,id',
        'subject_id' => 'required|exists:subjects,id',
        'section_id' => 'required|exists:classes,id',
    ]);

    $schoolId = auth()->user()->school_id;
    $teacher = \App\Models\User::findOrFail($request->teacher_id);

    // تحديث الأستاذ في جدول الربط
    \DB::table('teacher_subject_section')
        ->where('school_id', $schoolId)
        ->where('subject_id', $request->subject_id)
        ->where('section_id', $request->section_id)
        ->update([
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'updated_at' => now(),
        ]);

    return back()->with('success', 'تم تغيير الأستاذ بنجاح ✅');
}

    // تحديث عدد الحصص الأسبوعية (Ajax أو Form عادي)
    public function updateSubjectClasses(Request $request)
{
    $request->validate([
        'subject_id'     => 'required|exists:subjects,id',
        'weekly_classes' => 'required|integer|min:1|max:20',
    ]);

    $subject = Subject::findOrFail($request->subject_id);
    $userSchoolId = auth()->user()->school_id;

    // الحالة 1: المادة خاصة بالمدرسة (Private) -> نعدلها مباشرة
    if ($subject->school_id == $userSchoolId) {
        $subject->update(['weekly_classes' => $request->weekly_classes]);
    } 
    // الحالة 2: المادة عامة (Global) -> لا نعدلها، بل نضيف/نعدل التخصيص في الجدول الجديد
    else {
        \DB::table('school_subject_settings')->updateOrInsert(
            [
                'school_id'  => $userSchoolId,
                'subject_id' => $subject->id
            ],
            [
                'weekly_classes' => $request->weekly_classes,
                'updated_at'     => now()
            ]
        );
    }

    return redirect()->back()->with('success', 'تم تحديث نصاب الحصص لهذه المادة بنجاح ✅');
}

    // =========================================================
    // 5. إدارة بيانات الطلاب وتوزيعهم على الفصول
    // =========================================================
    public function listUnassignedStudents()
    {
        $students = StudentProfile::whereNull('class_id')->with('user')->get();
        $classes = SchoolClass::all();
        return view('admin.students.unassigned', compact('students', 'classes'));
    }

    public function updateStudentClass(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'class_id'   => 'required|exists:classes,id',
        ]);

        $student = StudentProfile::find($request->student_id);
        $student->class_id = $request->class_id;
        $student->save();

        return back()->with('success', 'تم تسكين الطالب في الفصل بنجاح.');
    }

    public function toggleGrading()
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        
        // عكس الحالة الحالية
        $school->grading_locked = !$school->grading_locked;
        $school->save();

        $status = $school->grading_locked ? 'تم إغلاق الرصد 🔒' : 'تم فتح الرصد 🔓';
        return back()->with('success', $status);
    }
    
}