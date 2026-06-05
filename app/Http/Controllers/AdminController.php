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
use App\Models\TeacherPreference; // إضافة من الكود الثاني
use App\Models\Schedule;          // إضافة من الكود الثاني
use Illuminate\Support\Facades\Storage; // إضافة من الكود الثاني

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
            'role' => $request->role, // ✅ هذا هو السطر الذي كان مفقوداً وتمت إضافته
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
    // 1. عرض صفحة الربط (مع البحث وتصفية الطلاب)
    public function createParentLink(Request $request)
    {
        // 1. جلب جميع أولياء الأمور للقائمة
        $parents = User::role('parent')->get();
        
        // 2. 💡 (الجديد) جلب معرفات الطلاب المرتبطين بأي ولي أمر حالياً
        $linkedStudentIds = User::role('parent')
                            ->has('children')
                            ->with('children')
                            ->get()
                            ->pluck('children')
                            ->flatten()
                            ->pluck('id')
                            ->unique()
                            ->toArray();

        // 3. 💡 (الجديد) جلب الطلاب الذين *ليسوا* في مصفوفة المرتبطين
        $students = User::role('student')
                        ->whereNotIn('id', $linkedStudentIds)
                        ->get();

        // 4. جلب جدول العلاقات للعرض في الجهة اليسرى
        $query = User::role('parent')->has('children')->with('children');

        // تطبيق البحث إذا وجد
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
        $missingAssignments = collect(); // ✅ المتغير الجديد الخاص بالمواد بدون أساتذة
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
                        $total = \DB::table('student_scores')
                                    ->where('student_id', $student->id)
                                    ->sum('total_score'); 

                        $maxPossible = \App\Models\Assessment::whereIn('subject_id', function($q) use ($student) {
                                            $q->select('subject_id')
                                              ->from('teacher_subject_section')
                                              ->where('section_id', $student->studentProfile->class_id);
                                        })->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(max_score, full_mark, 0)'));

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

            // 3. ✅ التقرير الجديد: الفصول والمواد بدون أساتذة
            elseif ($type == 'missing_teachers') {
                // جلب جميع فصول هذا الصف
                $classes = \App\Models\SchoolClass::where('school_id', $schoolId)
                            ->where('grade_id', $gradeId)
                            ->get();

                // جلب جميع المواد المقررة على هذا الصف
                $gradeSubjects = \App\Models\Subject::where('grade_id', $gradeId)
                    ->where(function($q) use ($schoolId) {
                        $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                    })->get();

                foreach ($classes as $class) {
                    // جلب أرقام المواد التي تم إسنادها بالفعل لهذا الفصل
                    $assignedSubjectIds = \DB::table('teacher_subject_section')
                        ->where('section_id', $class->id)
                        ->pluck('subject_id')
                        ->toArray();

                    // تصفية المواد لاستخراج "الغير مسندة" فقط
                    $missing = $gradeSubjects->whereNotIn('id', $assignedSubjectIds);

                    if ($missing->isNotEmpty()) {
                        $missingAssignments->push((object)[
                            'class' => $class,
                            'missing_subjects' => $missing
                        ]);
                    }
                }
            }
        }

        // تمرير المتغير الجديد missingAssignments للواجهة
        return view('admin.reports.index', compact('grades', 'topStudents', 'studentsList', 'missingAssignments', 'selectedGrade', 'school'));
    }

// دالة طباعة الشهادة الفردية
// دالة طباعة الشهادة الفردية
    public function printCertificate($studentId)
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        
        // 1. جلب بيانات الطالب والفصل
        $student = \App\Models\User::with(['studentProfile.schoolClass.grade'])->findOrFail($studentId);
        $classId = $student->studentProfile->class_id;

        // 2. جلب "جميع المواد" المقررة على فصل هذا الطالب
        $subjects = \DB::table('teacher_subject_section')
            ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
            ->where('teacher_subject_section.section_id', $classId)
            ->select('subjects.id', 'subjects.name')
            ->distinct() 
            ->get();

        // 3. جلب درجات (النهائي) من جدول student_scores
        $finalScores = \DB::table('student_scores')
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('subject_id');

        // 4. 💡 جلب درجات (أعمال السنة) من جداول التقييم (جمع النقاط آلياً لكل سميستر)
        $assessmentMarks = \DB::table('assessment_marks')
            ->join('assessments', 'assessment_marks.assessment_id', '=', 'assessments.id')
            ->where('assessment_marks.student_id', $studentId)
            ->select(
                'assessments.subject_id', 
                'assessments.semester', 
                \DB::raw('SUM(assessment_marks.score) as total_works')
            )
            ->groupBy('assessments.subject_id', 'assessments.semester')
            ->get();

        // 5. جلب إعدادات المواد لحساب النسبة بدقة
        $settings = \DB::table('school_subject_settings')
            ->where('school_id', $schoolId)
            ->get()
            ->keyBy('subject_id');

        $marks = [];
        $totalSum = 0;
        $maxPossibleSum = 0;

        // 6. الدوران على المواد وتجميع البيانات للشهادة
        foreach ($subjects as $subject) {
            
            // --- أ: استخراج درجات الأعمال من التقييمات ---
            // نستخدم فلتر ذكي تحسباً لاختلاف طريقة التخزين (1 أو first)
            $worksSem1Record = $assessmentMarks->where('subject_id', $subject->id)
                ->filter(function($item) { return in_array($item->semester, [1, '1', 'first']); })->first();
                
            $worksSem2Record = $assessmentMarks->where('subject_id', $subject->id)
                ->filter(function($item) { return in_array($item->semester, [2, '2', 'second']); })->first();

            $works_sem1 = floatval($worksSem1Record->total_works ?? 0);
            $works_sem2 = floatval($worksSem2Record->total_works ?? 0);

            // --- ب: استخراج درجات النهائي من student_scores ---
            $finalRecord = $finalScores->get($subject->id);
            $final_sem1 = floatval($finalRecord->final_score_sem1 ?? 0);
            $final_sem2 = floatval($finalRecord->final_score_sem2 ?? 0);

            // المجموع الكلي لهذه المادة
            $subjectTotal = $works_sem1 + $works_sem2 + $final_sem1 + $final_sem2;

            // إضافتها لمصفوفة العرض (استبدال الصفر بشرطة "-")
            $marks[] = (object) [
                'subject_name'     => $subject->name,
                'works_score_sem1' => $works_sem1 > 0 ? $works_sem1 : '-',
                'final_score_sem1' => $final_sem1 > 0 ? $final_sem1 : '-',
                'works_score_sem2' => $works_sem2 > 0 ? $works_sem2 : '-',
                'final_score_sem2' => $final_sem2 > 0 ? $final_sem2 : '-',
                'total_score'      => $subjectTotal > 0 ? $subjectTotal : '-',
            ];

            // جمع درجات الطالب الكلية
            $totalSum += $subjectTotal;
            
            // استخراج الحد الأقصى للمادة من التقييمات الفعلية المنشأة
            $subjectMaxScore = \App\Models\Assessment::where('subject_id', $subject->id)
                                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(max_score, full_mark, 0)'));
            
            $maxPossibleSum += $subjectMaxScore > 0 ? $subjectMaxScore : 0; 
        }

        // حساب النسبة المئوية الكلية
        $percentage = $maxPossibleSum > 0 ? ($totalSum / $maxPossibleSum) * 100 : 0;

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

                $maxPossible = \App\Models\Assessment::whereIn('subject_id', function($q) use ($student) {
                                    $q->select('subject_id')
                                      ->from('teacher_subject_section')
                                      ->where('section_id', $student->studentProfile->class_id);
                                })->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(max_score, full_mark, 0)'));

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
        // 1. التحقق (grade_ids أصبحت مصفوفة بدلاً من grade_id)
        $request->validate([
            'name'           => 'required|string|max:255',
            'grade_ids'      => 'required|array', // التأكد أنه تم اختيار صف واحد على الأقل
            'grade_ids.*'    => 'exists:grades,id',
            'weekly_classes' => 'nullable|integer|min:1|max:20', 
        ]);

        $schoolId = auth()->user()->school_id;
        $weeklyClasses = $request->weekly_classes ?? 1;

        // 2. الحفظ لكل صف تم اختياره في الـ Checkboxes
        foreach ($request->grade_ids as $gradeId) {
            Subject::create([
                'name'           => $request->name,
                'grade_id'       => $gradeId,
                'school_id'      => $schoolId,
                'weekly_classes' => $weeklyClasses, 
            ]);
        }

        return redirect()->back()->with('success', 'تم إضافة المادة للصفوف المختارة بنجاح ✅');
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

    // =========================================================
    // 6. إدارة الجدول الدراسي والتفضيلات (مضافة من الكود الثاني)
    // =========================================================

    public function showSchedules()
    {
        $schoolId = auth()->user()->school_id;

        // 1. جلب المراحل (السنوات) المفعلة حالياً لهذه المدرسة فقط
        $activeGrades = \DB::table('school_grade')
                            ->where('school_id', $schoolId)
                            ->pluck('grade_id')
                            ->toArray();

        // 2. جلب الجداول مجمعة حسب الفصول (للمراحل المفعلة والمدرسة الحالية فقط)
        $classes = SchoolClass::where('school_id', $schoolId)
                            ->whereIn('grade_id', $activeGrades)
                            ->with(['schedules.subject', 'schedules.teacher'])
                            ->get();

        // 3. جلب الجداول مجمعة حسب المعلمين (لمعلمي هذه المدرسة فقط)
        $teachers = User::role('teacher')
                        ->where('school_id', $schoolId)
                        ->with(['schedules.subject', 'schedules.schoolClass'])
                        ->get();

        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        
        // 4. تم إضافة الحصة السابعة هنا لكي تظهر جميع الحصص التي يولدها الذكاء الاصطناعي
        $periods = [1, 2, 3, 4, 5, 6];

        return view('admin.schedules.index', compact('classes', 'teachers', 'days', 'periods'));
    }

    /**
     * 2. صفحة قائمة المعلمين لتعديل التفضيلات
     */
    public function preferencesList()
    {
        // جلب المعلمين مع تحميل التفضيلات مسبقاً لتوفير الاستعلامات (Eager Loading)
        $teachers = \App\Models\User::role('teacher')
                    ->with(['preferences']) // تأكد من تعريف العلاقة في موديل User
                    ->get();

        return view('admin.schedules.preferences', compact('teachers'));
    }

   
    

    /**
     * 4. حفظ التفضيلات في قاعدة البيانات
     */
    public function storePreference(Request $request, $id)
    {
        $data = $request->input('prefs', []);

        // الأيام المتاحة في النظام
        $allDays = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

        foreach ($allDays as $day) {
            $dayData = $data[$day] ?? null;

            $isDayOff = isset($dayData['off']) ? 1 : 0;
            // إذا كان اليوم "أوف"، نخزن الحصص كمصفوفة فارغة أو نلغيها، 
            // أما إذا لم يكن أوف، نأخذ أرقام الحصص التي تم اختيارها كـ "غير مرغوبة"
            $blockedPeriods = (isset($dayData['periods']) && !$isDayOff) 
                              ? array_keys($dayData['periods']) 
                              : [];

            TeacherPreference::updateOrCreate(
                ['teacher_id' => $id, 'day_name' => $day],
                [
                    'is_day_off' => $isDayOff,
                    'blocked_periods' => $blockedPeriods
                ]
            );
        }

        return redirect()->route('admin.schedules.preferences')->with('success', 'تم حفظ تفضيلات المعلم بنجاح.');
    }

    public function generateAutoSchedule()
    {
        try {
            $schoolId = auth()->user()->school_id;

            // 1. جلب المراحل (السنوات) المفعلة حالياً
            $activeGrades = \DB::table('school_grade')
                              ->where('school_id', $schoolId)
                              ->pluck('grade_id')
                              ->toArray();

            // 2. الفلترة الصارمة للمتطلبات (تجاهل أي فصل أو أستاذ محذوف)
            $assignments = \DB::table('teacher_subject_section')
                ->join('classes', 'teacher_subject_section.section_id', '=', 'classes.id')
                ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
                ->join('users', 'teacher_subject_section.teacher_id', '=', 'users.id')
                ->leftJoin('school_subject_settings', function($join) use ($schoolId) {
                    $join->on('teacher_subject_section.subject_id', '=', 'school_subject_settings.subject_id')
                         ->where('school_subject_settings.school_id', '=', $schoolId);
                })
                ->where('teacher_subject_section.school_id', $schoolId)
                ->whereIn('classes.grade_id', $activeGrades) 
                ->select(
                    'teacher_subject_section.section_id as class_id',
                    'teacher_subject_section.subject_id',
                    'teacher_subject_section.teacher_id',
                    \DB::raw('COALESCE(school_subject_settings.weekly_classes, subjects.weekly_classes, 1) as weekly_sessions')
                )
                ->get();

            if ($assignments->isEmpty()) {
                return redirect()->back()->with('error', 'لا توجد حصص مسندة للفصول المفعلة!');
            }

            // 3. استخراج بيانات الأساتذة
            $activeTeacherIds = $assignments->pluck('teacher_id')->unique()->toArray();
            $teachers = \App\Models\User::whereIn('id', $activeTeacherIds)->with('preferences')->get();
            
            $dayMapping = [
                'الأحد' => 'Sun', 'الاثنين' => 'Mon', 'الثلاثاء' => 'Tue', 
                'الأربعاء' => 'Wed', 'الخميس' => 'Thu'
            ];
            $revDayMapping = array_flip($dayMapping);

            $teachersData = [];
            foreach ($teachers as $teacher) {
                $unwanted = [];
                foreach ($teacher->preferences as $pref) {
                    $engDay = $dayMapping[$pref->day_name] ?? $pref->day_name;
                    if ($pref->is_day_off) {
                        $unwanted[$engDay] = [1, 2, 3, 4, 5, 6, 7];
                    } else {
                        $unwanted[$engDay] = $pref->blocked_periods ?? [];
                    }
                }
                $teachersData[] = [
                    'name' => (string)$teacher->id,
                    'unwanted_slots' => $unwanted
                ];
            }

            $requirements = [];
            foreach ($assignments as $assign) {
                $requirements[] = [
                    'class' => (string)$assign->class_id,
                    'subject' => (string)$assign->subject_id,
                    'teacher' => (string)$assign->teacher_id,
                    'sessions' => (int)$assign->weekly_sessions
                ];
            }

            $inputData = [
                'teachers' => $teachersData,
                'requirements' => $requirements
            ];

            // 🚨 الخطوة الأهم: تدمير الملف القديم لمنع قراءة "البيانات الشبحية" المعلقة
            $jsonPath = base_path('constraints.json');
            if (file_exists($jsonPath)) {
                @unlink($jsonPath); 
            }
            
            // كتابة بيانات جديدة ونظيفة
            file_put_contents($jsonPath, json_encode($inputData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            // 4. تشغيل الخوارزمية (البايثون)
            $pythonPath = base_path('scheduler.py');
            $output = shell_exec("python \"$pythonPath\" 2>&1");

            // 5. معالجة النتائج وحفظ الجدول بسلاسة
            if (file_exists($jsonPath)) {
                $resultData = json_decode(file_get_contents($jsonPath), true);
                
                if (isset($resultData['schedule']) && !empty($resultData['schedule'])) {
                    
                    // إيقاف مؤقت للقيود لضمان الحفظ المريح بدون أخطاء MySQL
                    \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
                    \App\Models\Schedule::truncate(); 

                    $successCount = 0;
                    foreach ($resultData['schedule'] as $item) {
                        try {
                            \App\Models\Schedule::create([
                                'class_id' => (int) $item['class'],
                                'subject_id' => (int) $item['subject'],
                                'teacher_id' => (int) $item['teacher'],
                                'day' => $revDayMapping[$item['day']] ?? $item['day'],
                                'period' => $item['slot']
                            ]);
                            $successCount++;
                        } catch (\Throwable $e) {
                            // تجاهل أي خطأ فردي لتمرير باقي الجدول
                            continue; 
                        }
                    }
                    
                    // إعادة تفعيل القيود
                    \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

                    if ($successCount > 0) {
                        return redirect()->back()->with('success', "تم التوليد بنجاح! 🚀 ($successCount حصة تم حفظها)");
                    } else {
                        return redirect()->back()->with('error', 'تم توليد الجدول لكن لم تحفظ الحصص.');
                    }
                } 
                elseif (isset($resultData['error'])) {
                    return redirect()->back()->with('error', 'فشل التوليد. السبب: ' . $resultData['error']);
                }
            }

            return redirect()->back()->with('error', 'حدث خطأ في النظام. التفاصيل: ' . ($output ?: 'لم يتم إرجاع أي بيانات.'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
            return redirect()->back()->with('error', 'خطأ فني: ' . $e->getMessage());
        }
    }
    // ==========================================
    // ⚙️ دوال إدارة تفضيلات الأساتذة
    // ==========================================

    // 1. عرض قائمة الأساتذة
    public function preferences()
    {
        $schoolId = auth()->user()->school_id;
        
        $teachers = \App\Models\User::role('teacher')
            ->where('school_id', $schoolId)
            ->get();

        return view('admin.schedules.preferences', compact('teachers'));
    }

    // 2. عرض صفحة تعديل تفضيلات أستاذ معين
    public function editPreference($id)
    {
        $schoolId = auth()->user()->school_id;
        
        $teacher = \App\Models\User::role('teacher')
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->with('preferences')
            ->firstOrFail();

        return view('admin.schedules.edit_preference', compact('teacher'));
    }

    // 3. حفظ التفضيلات في قاعدة البيانات
    public function updatePreference(\Illuminate\Http\Request $request, $id)
    {
        $schoolId = auth()->user()->school_id;
        
        $teacher = \App\Models\User::role('teacher')
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->firstOrFail();

        // مسح التفضيلات القديمة لهذا الأستاذ لإعادة كتابة الجديدة
        \App\Models\TeacherPreference::where('teacher_id', $teacher->id)->delete();

        // إذا تم إرسال تفضيلات جديدة، نقوم بحفظها
        if ($request->has('preferences')) {
            foreach ($request->preferences as $day => $data) {
                $isDayOff = isset($data['is_day_off']) ? true : false;
                $blockedPeriods = isset($data['blocked_periods']) ? $data['blocked_periods'] : [];

                // نحفظ فقط إذا كان اليوم عطلة أو فيه حصص محظورة
                if ($isDayOff || !empty($blockedPeriods)) {
                    \App\Models\TeacherPreference::create([
                        'teacher_id' => $teacher->id,
                        'day_name' => $day,
                        'is_day_off' => $isDayOff,
                        'blocked_periods' => $blockedPeriods,
                    ]);
                }
            }
        }

        return redirect()->route('admin.schedules.preferences')
            ->with('success', 'تم حفظ التفضيلات بنجاح للأستاذ: ' . $teacher->name);
    }

    // 1. عرض صفحة تعديل التقييمات التفاعلية
    // 1. عرض صفحة تعديل التقييمات التفاعلية
    public function editMarks(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        // 1. جلب السنوات الدراسية المفعلة فقط لهذه المدرسة
        $activeGradeIds = \DB::table('school_grade')
                            ->where('school_id', $schoolId)
                            ->pluck('grade_id');

        // 2. جلب الفصول التابعة للسنوات المفعلة فقط والمدرسة الحالية
        $classes = \App\Models\SchoolClass::where('school_id', $schoolId)
                            ->whereIn('grade_id', $activeGradeIds)
                            ->get();
        
        $subjects = collect();
        $assessments = collect();
        $students = collect();
        $existingMarks = collect();

        $selectedClass = $request->class_id;
        $selectedSubject = $request->subject_id;

        // إذا تم اختيار الفصل، نجلب المواد الخاصة بمرحلته
        if ($selectedClass) {
            $class = \App\Models\SchoolClass::find($selectedClass);
            if ($class) {
                // جلب المواد (العامة والخاصة بالمدرسة) المرتبطة بهذا الصف
                $subjects = \App\Models\Subject::where('grade_id', $class->grade_id)
                                ->where(function($q) use ($schoolId) {
                                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                                })
                                ->get();
            }
        }

        // إذا تم اختيار الفصل والمادة، نجلب الطلاب والتقييمات والدرجات
        if ($selectedClass && $selectedSubject) {
            // جلب التقييمات التابعة للمادة المحددة
            $selectedSemester = $request->semester; // استقبال الفلتر

$assessments = \App\Models\Assessment::where('subject_id', $selectedSubject)
                ->when($selectedSemester, function($query) use ($selectedSemester) {
                    return $query->where('semester', $selectedSemester);
                })->get();
            
            // جلب طلاب الفصل
            $students = StudentProfile::where('class_id', $selectedClass)
                            ->with('user')
                            ->get();
                            
            // جلب الدرجات الموجودة مسبقاً (مجمعة حسب الطالب ثم التقييم)
            $assessmentIds = $assessments->pluck('id');
            $existingMarks = \App\Models\AssessmentMark::whereIn('assessment_id', $assessmentIds)
                                ->get()
                                ->groupBy('student_id')
                                ->map(function ($items) {
                                    return $items->keyBy('assessment_id');
                                });
        }

        return view('admin.marks.edit', compact(
            'classes', 'subjects', 'assessments', 'students', 'existingMarks',
            'selectedClass', 'selectedSubject'
        ));
    }

    // 2. حفظ التعديلات في قاعدة البيانات
    public function updateMarks(Request $request)
    {
        $request->validate([
            'marks' => 'required|array'
        ]);

        // هيكل مصفوفة marks المتوقع:
        // marks[student_id][assessment_id] = score

        foreach ($request->marks as $studentId => $assessments) {
            foreach ($assessments as $assessmentId => $markValue) {
                // التحقق من أن الدرجة ليست فارغة
                if ($markValue !== null && $markValue !== '') {
                    \App\Models\AssessmentMark::updateOrCreate(
                        [
                            'assessment_id' => $assessmentId,
                            'student_id' => $studentId
                        ],
                        [
                            'score' => $markValue
                        ]
                    );
                } else {
                     // اختياري: إذا أردت حذف الدرجة عند إفراغ الحقل
                     // \App\Models\AssessmentMark::where('assessment_id', $assessmentId)->where('student_id', $studentId)->delete();
                }
            }
        }

        return back()->with('success', 'تم حفظ وتحديث درجات الطلاب بنجاح!');
    }
    // ==========================================
    // 🛡️ إدارة صلاحيات المشرفين (خاص بمدير المدرسة)
    // ==========================================

    public function manageAdminsPermissions()
    {
        $user = auth()->user();

        // حماية: التأكد أن من يفتح الصفحة هو مدير المدرسة فقط
        if ($user->role !== 'manager') {
            return redirect()->route('admin.dashboard')->with('error', 'هذه الصفحة مخصصة لمدير المدرسة فقط.');
        }

        $schoolId = $user->school_id;

        // جلب جميع الإداريين (Admins) التابعين لهذه المدرسة
        $admins = \App\Models\User::role('admin')->where('school_id', $schoolId)->get();

        // تعريف الصلاحيات المتاحة في النظام باللغة العربية
        $availablePermissions = [
            'manage_users'     => 'إدارة المستخدمين (طلاب ومعلمين)',
            'manage_classes'   => 'إدارة الفصول والشعب',
            'manage_subjects'  => 'إدارة المواد وتوزيع الدرجات',
            'assign_teachers'  => 'إسناد المواد للمعلمين',
            'manage_schedules' => 'إدارة الجدول الدراسي',
            'edit_marks'       => 'تعديل درجات الطلاب',
            'manage_reports'   => 'إصدار التقارير والشهادات',
            'toggle_grading'   => 'فتح وإغلاق الرصد', // الصلاحية التي طلبتها
        ];

        // التأكد من تسجيل هذه الصلاحيات في قاعدة بيانات Spatie لتجنب الأخطاء
        foreach (array_keys($availablePermissions) as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        return view('admin.managers.permissions', compact('admins', 'availablePermissions'));
    }

    public function updateAdminsPermissions(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'permissions' => 'nullable|array' // array of permission names
        ]);

        $admin = \App\Models\User::findOrFail($request->admin_id);

        // حماية أمنية
        if ($admin->school_id !== auth()->user()->school_id || $admin->role !== 'admin') {
            return back()->with('error', 'إجراء غير صالح.');
        }

        // تحديث الصلاحيات للأدمن (يقوم بحذف القديم وإضافة المختار فقط)
        $permissions = $request->permissions ?? [];
        $admin->syncPermissions($permissions);

        return back()->with('success', 'تم تحديث صلاحيات المشرف (' . $admin->name . ') بنجاح ✅');
    }

    // دالة حظر وإلغاء حظر المستخدم
    public function toggleBan($id)
    {
        $user = User::findOrFail($id);
        
        // حماية: منع الإدارة من حظر أنفسهم أو حظر المدير (Manager)
        if ($user->id == Auth::id() || $user->role == 'manager') {
            return back()->with('error', 'لا يمكنك حظر هذا الحساب!');
        }

        // عكس حالة الحظر الحالية
        $user->is_banned = !$user->is_banned;
        $user->save();

        $status = $user->is_banned ? 'محظور 🚫' : 'نشط ✅';
        return back()->with('success', "تم تغيير حالة حساب المستخدم ({$user->name}) ليصبح: $status");
    }

    public function printStudentCertificate($studentId)
    {
        // 1. جلب بيانات الطالب والفصل والمدرسة
        $student = \App\Models\User::with('studentProfile.schoolClass.grade')->findOrFail($studentId);
        
        // استخراج رقم شعبة الطالب
        $classId = $student->studentProfile->class_id; 
        $school = \DB::table('schools')->find($student->school_id);

        // 2. جلب "جميع المواد" المقررة على فصل هذا الطالب خلال السنة
        // نعتمد على جدول الإسناد لمعرفة المواد المربوطة بهذا الفصل تحديداً
        $subjects = \DB::table('teacher_subject_section')
            ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
            ->where('teacher_subject_section.section_id', $classId)
            ->select('subjects.id', 'subjects.name')
            ->distinct() // لمنع التكرار إذا كان يدرسها أكثر من أستاذ
            ->get();

        $marks = [];
        $totalSum = 0;
        $maxPossibleSum = 0; // لحساب النسبة المئوية

        // 3. الدوران على جميع المواد المكتشفة وتعبئة درجات الطالب
        foreach ($subjects as $subject) {
            
            // جلب سجلات الدرجات للطالب في هذه المادة تحديداً
            $scores = \DB::table('student_scores')
                ->where('student_id', $studentId)
                ->where('subject_id', $subject->id)
                ->get();

            /* 💡 كود ذكي يتوافق مع أي طريقة حفظ تستخدمها في الداتابيز:
             سواء كنت تحفظ الفصل الأول والثاني في (صفوف منفصلة) أو (أعمدة منفصلة).
            */
            $scoreSem1 = $scores->firstWhere('semester', 1) ?? $scores->firstWhere('semester', 'first');
            $scoreSem2 = $scores->firstWhere('semester', 2) ?? $scores->firstWhere('semester', 'second');
            $singleRow = $scores->first();

            // استخراج أعمال ونهائي الفصل الأول
            $works_sem1 = floatval($scoreSem1->works_score ?? ($singleRow->works_score_sem1 ?? 0));
            $final_sem1 = floatval($scoreSem1->final_score ?? ($singleRow->final_score_sem1 ?? 0));

            // استخراج أعمال ونهائي الفصل الثاني
            $works_sem2 = floatval($scoreSem2->works_score ?? ($singleRow->works_score_sem2 ?? 0));
            $final_sem2 = floatval($scoreSem2->final_score ?? ($singleRow->final_score_sem2 ?? 0));

            // حساب المجموع الكلي للمادة (للفصلين معاً)
            $subjectTotal = $works_sem1 + $final_sem1 + $works_sem2 + $final_sem2;

            // بناء هيكل المادة للواجهة (Blade)
            $marks[] = (object) [
                'subject_name'     => $subject->name,
                'works_score_sem1' => $works_sem1 > 0 ? $works_sem1 : '-',
                'final_score_sem1' => $final_sem1 > 0 ? $final_sem1 : '-',
                'works_score_sem2' => $works_sem2 > 0 ? $works_sem2 : '-',
                'final_score_sem2' => $final_sem2 > 0 ? $final_sem2 : '-',
                'total_score'      => $subjectTotal > 0 ? $subjectTotal : '-',
            ];

            // تجميع المجاميع الكلية للشهادة (لو أردت النسبة المئوية)
            $totalSum += $subjectTotal;
            
            // افترضنا أن الدرجة النهائية للمادة من 100 (يمكنك تعديلها لجلبها من الإعدادات)
            $maxPossibleSum += 100; 
        }

        // 4. حساب النسبة المئوية
        $percentage = $maxPossibleSum > 0 ? ($totalSum / $maxPossibleSum) * 100 : 0;

        // إرسال المتغيرات إلى صفحة Blade التي صنعناها في الرسالة السابقة
        return view('teacher.certificates.print', compact('student', 'school', 'marks', 'totalSum', 'percentage'));
    }

    // =========================================================
    // 🔄 تبديل الحصص بين الأساتذة في الجدول الدراسي
    // =========================================================

    /**
     * التحقق من إمكانية التبديل بين حصتين (AJAX)
     * يتأكد أن لا أحد من الأستاذين لديه حصة أخرى في الوقت الجديد
     */
    public function checkSwapAvailability(Request $request)
    {
        $request->validate([
            'schedule_a_id' => 'required|exists:schedules,id',
            'schedule_b_id' => 'required|exists:schedules,id',
        ]);

        $scheduleA = Schedule::with(['subject', 'teacher', 'schoolClass'])->findOrFail($request->schedule_a_id);
        $scheduleB = Schedule::with(['subject', 'teacher', 'schoolClass'])->findOrFail($request->schedule_b_id);

        // إذا كانت نفس الحصة
        if ($scheduleA->id === $scheduleB->id) {
            return response()->json(['valid' => false, 'message' => 'لا يمكنك تبديل حصة مع نفسها!']);
        }

        // إذا كانتا في نفس الوقت أصلاً (نفس اليوم ونفس الحصة)
        if ($scheduleA->day === $scheduleB->day && $scheduleA->period === $scheduleB->period) {
            return response()->json(['valid' => false, 'message' => 'الحصتان في نفس الوقت بالفعل، لا حاجة للتبديل.']);
        }

        $conflicts = [];

        // 1. هل أستاذ A لديه حصة أخرى في وقت B؟
        $teacherAConflict = Schedule::where('teacher_id', $scheduleA->teacher_id)
            ->where('day', $scheduleB->day)
            ->where('period', $scheduleB->period)
            ->where('id', '!=', $scheduleA->id)
            ->where('id', '!=', $scheduleB->id)
            ->with('schoolClass')
            ->first();

        if ($teacherAConflict) {
            $className = $teacherAConflict->schoolClass->name ?? '?';
            $conflicts[] = "الأستاذ ({$scheduleA->teacher->name}) لديه حصة في فصل ({$className}) يوم {$scheduleB->day} الحصة {$scheduleB->period}.";
        }

        // 2. هل أستاذ B لديه حصة أخرى في وقت A؟
        $teacherBConflict = Schedule::where('teacher_id', $scheduleB->teacher_id)
            ->where('day', $scheduleA->day)
            ->where('period', $scheduleA->period)
            ->where('id', '!=', $scheduleA->id)
            ->where('id', '!=', $scheduleB->id)
            ->with('schoolClass')
            ->first();

        if ($teacherBConflict) {
            $className = $teacherBConflict->schoolClass->name ?? '?';
            $conflicts[] = "الأستاذ ({$scheduleB->teacher->name}) لديه حصة في فصل ({$className}) يوم {$scheduleA->day} الحصة {$scheduleA->period}.";
        }

        // 3. هل فصل A لديه حصة أخرى في وقت B؟ (مهم عند التبديل بين فصلين مختلفين)
        if ($scheduleA->class_id !== $scheduleB->class_id) {
            $classAConflict = Schedule::where('class_id', $scheduleA->class_id)
                ->where('day', $scheduleB->day)
                ->where('period', $scheduleB->period)
                ->where('id', '!=', $scheduleA->id)
                ->where('id', '!=', $scheduleB->id)
                ->first();

            if ($classAConflict) {
                $className = $scheduleA->schoolClass->name ?? '?';
                $conflicts[] = "فصل ({$className}) لديه حصة أخرى يوم {$scheduleB->day} الحصة {$scheduleB->period}.";
            }

            // 4. هل فصل B لديه حصة أخرى في وقت A؟
            $classBConflict = Schedule::where('class_id', $scheduleB->class_id)
                ->where('day', $scheduleA->day)
                ->where('period', $scheduleA->period)
                ->where('id', '!=', $scheduleA->id)
                ->where('id', '!=', $scheduleB->id)
                ->first();

            if ($classBConflict) {
                $className = $scheduleB->schoolClass->name ?? '?';
                $conflicts[] = "فصل ({$className}) لديه حصة أخرى يوم {$scheduleA->day} الحصة {$scheduleA->period}.";
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'valid' => false,
                'message' => 'يوجد تعارض يمنع التبديل:',
                'conflicts' => $conflicts,
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'التبديل متاح ✅',
            'details' => [
                'a' => [
                    'subject' => $scheduleA->subject->name ?? 'مادة',
                    'teacher' => $scheduleA->teacher->name ?? 'أستاذ',
                    'class'   => $scheduleA->schoolClass->name ?? 'فصل',
                    'day'     => $scheduleA->day,
                    'period'  => $scheduleA->period,
                ],
                'b' => [
                    'subject' => $scheduleB->subject->name ?? 'مادة',
                    'teacher' => $scheduleB->teacher->name ?? 'أستاذ',
                    'class'   => $scheduleB->schoolClass->name ?? 'فصل',
                    'day'     => $scheduleB->day,
                    'period'  => $scheduleB->period,
                ],
            ],
        ]);
    }

    /**
     * تنفيذ التبديل بين حصتين في الجدول الدراسي
     */
    public function swapSchedules(Request $request)
    {
        $request->validate([
            'schedule_a_id' => 'required|exists:schedules,id',
            'schedule_b_id' => 'required|exists:schedules,id',
        ]);

        $scheduleA = Schedule::findOrFail($request->schedule_a_id);
        $scheduleB = Schedule::findOrFail($request->schedule_b_id);

        if ($scheduleA->id === $scheduleB->id) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك تبديل حصة مع نفسها!']);
        }

        // إعادة التحقق من التعارض (حماية مزدوجة)
        $teacherAConflict = Schedule::where('teacher_id', $scheduleA->teacher_id)
            ->where('day', $scheduleB->day)->where('period', $scheduleB->period)
            ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

        $teacherBConflict = Schedule::where('teacher_id', $scheduleB->teacher_id)
            ->where('day', $scheduleA->day)->where('period', $scheduleA->period)
            ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

        if ($teacherAConflict || $teacherBConflict) {
            return response()->json(['success' => false, 'message' => 'يوجد تعارض في جدول أحد الأساتذة! لم يتم التبديل.']);
        }

        if ($scheduleA->class_id !== $scheduleB->class_id) {
            $classAConflict = Schedule::where('class_id', $scheduleA->class_id)
                ->where('day', $scheduleB->day)->where('period', $scheduleB->period)
                ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

            $classBConflict = Schedule::where('class_id', $scheduleB->class_id)
                ->where('day', $scheduleA->day)->where('period', $scheduleA->period)
                ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

            if ($classAConflict || $classBConflict) {
                return response()->json(['success' => false, 'message' => 'يوجد تعارض في جدول أحد الفصول! لم يتم التبديل.']);
            }
        }

        // تنفيذ التبديل داخل Transaction
        \DB::transaction(function () use ($scheduleA, $scheduleB) {
            $tempDay = $scheduleA->day;
            $tempPeriod = $scheduleA->period;

            $scheduleA->update([
                'day' => $scheduleB->day,
                'period' => $scheduleB->period,
            ]);

            $scheduleB->update([
                'day' => $tempDay,
                'period' => $tempPeriod,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'تم تبديل الحصتين بنجاح! 🔄']);
    }
    // ==========================================
    // 🎓 الترحيل ونهاية السنة الدراسية
    // ==========================================

    /**
     * دالة مساعدة للتحقق من اكتمال التقييمات لصف معين
     * تتحقق أن مجموع درجات التقييمات (max_score) لكل مادة في كل شعبة
     * يساوي درجة أعمال السنة (works_score) المحددة للمادة
     */
    private function getIncompleteAssessments($schoolId, $gradeId)
    {
        // 1. جلب جميع الشعب في هذا الصف لهذه المدرسة
        $sections = \App\Models\SchoolClass::where('school_id', $schoolId)
            ->where('grade_id', $gradeId)
            ->get();

        $incomplete = [];

        foreach ($sections as $section) {
            // 2. جلب المواد المسندة لهذه الشعبة
            $assignments = \DB::table('teacher_subject_section')
                ->join('subjects', 'teacher_subject_section.subject_id', '=', 'subjects.id')
                ->where('teacher_subject_section.school_id', $schoolId)
                ->where('teacher_subject_section.section_id', $section->id)
                ->select('subjects.id as subject_id', 'subjects.name as subject_name')
                ->distinct()
                ->get();

            foreach ($assignments as $assignment) {
                // 3. جلب درجة أعمال السنة المحددة للمادة
                $settings = \DB::table('school_subject_settings')
                    ->where('school_id', $schoolId)
                    ->where('subject_id', $assignment->subject_id)
                    ->first();

                $totalWorksScore = ($settings && isset($settings->works_score) && $settings->works_score > 0)
                    ? $settings->works_score
                    : 40;

                // 4. حساب الحد الأقصى لكل سميستر
                $maxPerSemester = $totalWorksScore / 2;

                // 5. حساب مجموع التقييمات لكل سميستر في هذه الشعبة
                $sumSem1 = Assessment::where('subject_id', $assignment->subject_id)
                    ->where('section_id', $section->id)
                    ->where('semester', 1)
                    ->sum('max_score');

                $sumSem2 = Assessment::where('subject_id', $assignment->subject_id)
                    ->where('section_id', $section->id)
                    ->where('semester', 2)
                    ->sum('max_score');

                // 6. التحقق من اكتمال كل سميستر
                $sem1Complete = (abs($sumSem1 - $maxPerSemester) < 0.01);
                $sem2Complete = (abs($sumSem2 - $maxPerSemester) < 0.01);

                if (!$sem1Complete || !$sem2Complete) {
                    $details = [];
                    if (!$sem1Complete) {
                        $details[] = "الفصل الأول: تم رصد {$sumSem1} من {$maxPerSemester}";
                    }
                    if (!$sem2Complete) {
                        $details[] = "الفصل الثاني: تم رصد {$sumSem2} من {$maxPerSemester}";
                    }

                    $incomplete[] = [
                        'section_name' => $section->name . ' - ' . ($section->section ?? ''),
                        'subject_name' => $assignment->subject_name,
                        'total_works_score' => $totalWorksScore,
                        'sum_sem1' => $sumSem1,
                        'sum_sem2' => $sumSem2,
                        'max_per_semester' => $maxPerSemester,
                        'details' => implode(' | ', $details),
                    ];
                }
            }
        }

        return $incomplete;
    }

    /**
     * التحقق من اكتمال التقييمات لصف معين (AJAX)
     */
    public function checkAssessmentCompleteness(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $gradeId = $request->grade_id;

        if (!$gradeId) {
            return response()->json(['success' => false, 'message' => 'يجب تحديد الصف!']);
        }

        $incomplete = $this->getIncompleteAssessments($schoolId, $gradeId);

        if (empty($incomplete)) {
            return response()->json([
                'success' => true,
                'complete' => true,
                'message' => 'جميع التقييمات مكتملة لهذا الصف ✅'
            ]);
        }

        return response()->json([
            'success' => true,
            'complete' => false,
            'message' => 'يوجد مواد لم تكتمل تقييماتها بعد!',
            'incomplete' => $incomplete
        ]);
    }

    public function showPromotion()
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        
        // جلب الصفوف المتاحة في هذه المدرسة لغرض اختيار الصف للترحيل
        $grades = \App\Models\Grade::whereIn('id', function($q) use ($schoolId) {
            $q->select('grade_id')->from('school_grade')->where('school_id', $schoolId);
        })->orderBy('order')->get();

        return view('admin.promotion', compact('school', 'grades'));
    }

    public function updateAcademicYear(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string|max:20'
        ]);

        $school = \App\Models\School::find(auth()->user()->school_id);
        $school->academic_year = $request->academic_year;
        $school->save();

        return redirect()->back()->with('success', 'تم تحديث السنة الدراسية الحالية بنجاح.');
    }

    public function previewPromotion(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $gradeId = $request->grade_id;
        $passPercentage = $request->pass_percentage ?? 50;

        if (!$gradeId) {
            return response()->json(['success' => false, 'message' => 'يجب تحديد الصف!']);
        }

        // 1. جلب الصف الحالي والصف التالي
        $currentGrade = \App\Models\Grade::find($gradeId);
        $nextGrade = \App\Models\Grade::where('stage', $currentGrade->stage)
                                      ->where('order', '>', $currentGrade->order)
                                      ->orderBy('order')
                                      ->first();

        // 2. جلب جميع الطلاب في هذا الصف
        $students = \App\Models\User::role('student')
            ->where('school_id', $schoolId)
            ->whereHas('studentProfile.schoolClass', function($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })
            ->with(['studentProfile.schoolClass'])
            ->get();

        $previewData = [];

        foreach ($students as $student) {
            // حساب الدرجات كما فعلنا في كشف الدرجات
            $totalStudentScore = \DB::table('student_scores')
                                    ->where('student_id', $student->id)
                                    ->sum('total_score');

            // المجموع الفعلي للتقييمات
            $maxPossible = \App\Models\Assessment::whereIn('subject_id', function($q) use ($student) {
                                $q->select('subject_id')
                                  ->from('teacher_subject_section')
                                  ->where('section_id', $student->studentProfile->class_id);
                            })->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(max_score, full_mark, 0)'));

            $percentage = $maxPossible > 0 ? ($totalStudentScore / $maxPossible) * 100 : 0;
            $status = $percentage >= $passPercentage ? 'passed' : 'failed';
            $isGraduate = false;
            $newClassName = 'نفس الفصل';
            $newClassId = null;
            $newGradeName = null;

            if ($status === 'passed') {
                if ($nextGrade) {
                    // البحث عن فصل مقبل
                    $section = $student->studentProfile->schoolClass->section;
                    $nextClass = \App\Models\SchoolClass::where('school_id', $schoolId)
                                                        ->where('grade_id', $nextGrade->id)
                                                        ->where('section', $section)
                                                        ->first();
                    if ($nextClass) {
                        $newClassName = $nextClass->name . ' - ' . $nextClass->section;
                        $newClassId = $nextClass->id;
                        $newGradeName = $nextGrade->name;
                    } else {
                        $newClassName = 'يحتاج تعيين يدوي';
                    }
                } else {
                    $newClassName = 'متخرج من المرحلة';
                    $isGraduate = true;
                }
            }

            $previewData[] = [
                'id' => $student->id,
                'name' => $student->name,
                'current_class' => $student->studentProfile->schoolClass->name . ' - ' . $student->studentProfile->schoolClass->section,
                'total_score' => $totalStudentScore,
                'max_possible' => $maxPossible,
                'percentage' => round($percentage, 1),
                'status' => $status,
                'is_graduate' => $isGraduate,
                'new_class_name' => $newClassName,
                'new_class_id' => $newClassId,
                'new_grade_name' => $newGradeName,
                'old_class_id' => $student->studentProfile->class_id,
                'old_class_name' => $student->studentProfile->schoolClass->name,
                'old_grade_id' => $currentGrade->id,
                'old_grade_name' => $currentGrade->name
            ];
        }

        return response()->json([
            'success' => true,
            'students' => $previewData
        ]);
    }

    public function executePromotion(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $academicYear = $school->academic_year ?? 'غير محدد';
        
        $studentsData = $request->input('students', []);

        if (empty($studentsData)) {
            return response()->json(['success' => false, 'message' => 'لا يوجد طلاب محددين']);
        }

        // ✅ التحقق من اكتمال التقييمات قبل الترحيل
        // نستخرج grade_id من بيانات الطلاب
        $gradeId = $studentsData[0]['old_grade_id'] ?? null;
        if ($gradeId) {
            $incomplete = $this->getIncompleteAssessments($schoolId, $gradeId);
            if (!empty($incomplete)) {
                $subjectsList = collect($incomplete)->map(function($item) {
                    return $item['subject_name'] . ' (' . $item['section_name'] . ')';
                })->unique()->implode('، ');
                
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تنفيذ الترحيل! يوجد مواد لم تكتمل تقييماتها: ' . $subjectsList
                ]);
            }
        }

        \DB::transaction(function () use ($studentsData, $schoolId, $academicYear) {
            foreach ($studentsData as $data) {
                // 1. استخراج الدرجات التفصيلية الحالية لكل طالب من student_scores و assessment_marks كـ snapshot
                $scoresSnapshot = \DB::table('student_scores')->where('student_id', $data['id'])->get();

                // 2. تسجيل العملية في جدول الأرشيف
                \App\Models\PromotionArchive::create([
                    'school_id' => $schoolId,
                    'academic_year' => $academicYear,
                    'student_id' => $data['id'],
                    'student_name' => $data['name'],
                    'old_class_id' => $data['old_class_id'],
                    'old_class_name' => $data['old_class_name'],
                    'old_grade_id' => $data['old_grade_id'],
                    'old_grade_name' => $data['old_grade_name'],
                    'new_class_id' => $data['new_class_id'],
                    'new_grade_name' => $data['new_grade_name'],
                    'total_score' => $data['total_score'],
                    'max_possible_score' => $data['max_possible'],
                    'percentage' => $data['percentage'],
                    'status' => $data['status'],
                    'scores_snapshot' => $scoresSnapshot->toJson(),
                    'promoted_by' => auth()->id()
                ]);

                // 3. إذا ناجح وليس خريجاً، يتم نقله للفصل الجديد
                if ($data['status'] === 'passed' && !empty($data['new_class_id'])) {
                    \App\Models\StudentProfile::where('user_id', $data['id'])->update([
                        'class_id' => $data['new_class_id']
                    ]);
                }
                
                // (اختياري: يمكن تفريغ أو أرشفة الدرجات من student_scores و assessment_marks للطالب هنا للعام القادم)
            }
        });

        return response()->json(['success' => true, 'message' => 'تم تنفيذ الترحيل بنجاح وأرشفة النتائج.']);
    }

}