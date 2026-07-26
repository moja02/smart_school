<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Grade;
use App\Models\SchoolClass; // تأكد أن اسم الموديل عندك SchoolClass أو Class
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\Mark;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA'); // بيانات عربية وهمية

        // 0. إنشاء مدرسة لربط المستخدمين بها
        $school = \App\Models\School::firstOrCreate(
            ['id' => 1],
            ['name' => 'المدرسة الذكية', 'academic_year' => '2026/2027']
        );

        // Permissions creation
        $permissions = [
            'toggle_grading',
            'manage_users',
            'manage_subjects',
            'manage_classes',
            'assign_teachers',
            'edit_marks',
            'manage_schedules',
            'manage_reports',
            'manage_promotion',
            'manage_admissions',
            'manage_fees',
            'manage_leaves',
            'manage_wallet',
            'manage_wallet_charge',
            'manage_wallet_sell',
            'manage_accounting',
            'manage_school'
        ];
        foreach ($permissions as $p) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 1. إنشاء الأدوار (إذا لم تكن موجودة)
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleTeacher = Role::firstOrCreate(['name' => 'teacher']);
        $roleStudent = Role::firstOrCreate(['name' => 'student']);
        $roleParent = Role::firstOrCreate(['name' => 'parent']);

        // Give permissions to Admin
        $roleAdmin->syncPermissions($permissions);

        // 2. إنشاء مدير النظام (Admin)
        if (!User::where('email', 'admin@school.com')->exists()) {
            $admin = User::create([
                'name' => 'مدير النظام',
                'email' => 'admin@school.com',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'school_id' => 1,
            ]);
            $admin->assignRole($roleAdmin);
            $this->command->info('✅ تم إنشاء الأدمن: admin@school.com / 12345678');
        }

        // 3. إنشاء المعلمين (10 معلمين)
        $teachers = [];
        for ($i = 1; $i <= 10; $i++) {
            $teacher = User::firstOrCreate(
                ['email' => "teacher$i@school.com"],
                [
                    'name' => $faker->name,
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'school_id' => 1,
                ]
            );
            $teacher->assignRole($roleTeacher);
            $teachers[] = $teacher;
        }
        $this->command->info('✅ تم إنشاء 10 معلمين.');

        // 4. إنشاء أولياء الأمور (20 ولي أمر)
        $parents = [];
        for ($i = 1; $i <= 20; $i++) {
            $parent = User::firstOrCreate(
                ['email' => "parent$i@school.com"],
                [
                    'name' => $faker->name,
                    'password' => Hash::make('password'),
                    'role' => 'parent',
                    'school_id' => 1,
                ]
            );
            $parent->assignRole($roleParent);
            $parents[] = $parent;
        }
        $this->command->info('✅ تم إنشاء 20 ولي أمر.');

        // 5. جلب المراحل والمواد الموجودة (بدون إنشاء جديد)
        $grades = Grade::all();
        $allSubjects = Subject::all();

        if ($grades->count() == 0) {
            $this->command->info('⏳ لا توجد مراحل دراسية، جاري إنشاء مراحل ومواد افتراضية...');
            $gradeNames = ['الصف الأول الابتدائي', 'الصف الثاني الابتدائي', 'الصف الثالث الابتدائي'];
            $subjectNames = ['اللغة العربية', 'الرياضيات', 'العلوم'];
            
            foreach ($gradeNames as $index => $gName) {
                $grade = Grade::create([
                    'name' => $gName,
                    'stage' => 'primary',
                    'order' => $index + 1
                ]);
                
                // Link grade to school
                DB::table('school_grade')->insert([
                    'school_id' => 1,
                    'grade_id' => $grade->id
                ]);

                foreach ($subjectNames as $sName) {
                    Subject::create([
                        'name' => $sName,
                        'grade_id' => $grade->id,
                        'school_id' => 1,
                        'works_score' => 40,
                        'final_score' => 60,
                        'total_score' => 100
                    ]);
                }
            }
            $grades = Grade::all();
            $allSubjects = Subject::all();
        }

        // 6. الحلقة الكبرى: إنشاء الفصول والطلاب والبيانات المرتبطة
        foreach ($grades as $grade) {
            // إنشاء فصلين لكل مرحلة (أ ، ب)
            $sections = ['أ', 'ب'];
            
            foreach ($sections as $section) {
                // إنشاء الفصل (Class)
                $class = SchoolClass::create([
                    'name' => $grade->name . ' - ' . $section,
                    'section' => $section,
                    'grade_id' => $grade->id,
                    'school_id' => 1,
                ]);

                // تعيين معلم عشوائي لهذا الفصل لمادة عشوائية (Teacher_Subject)
                // (يفترض وجود جدول teacher_subject، سنتجاوزه إذا لم يكن لديك موديل له، لكن سننشئ البيانات الأساسية)
                
                // إضافة 5 طلاب في هذا الفصل
                for ($k = 1; $k <= 5; $k++) {
                    // الطالب
                    $student = User::create([
                        'name' => $faker->firstName . ' ' . $faker->lastName,
                        'email' => $faker->unique()->userName . '@student.com',
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'school_id' => 1,
                    ]);
                    $student->assignRole($roleStudent);

                    // البروفايل
                    $profile = StudentProfile::create([
                        'user_id' => $student->id,
                        'class_id' => $class->id,
                    ]);

                    // ربط الطالب بولي أمر عشوائي
                    $randomParent = $parents[array_rand($parents)];
                    DB::table('parent_student')->insertOrIgnore([
                        'parent_id' => $randomParent->id,
                        'student_id' => $student->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // --- تعبئة الدرجات (Marks) ---
                    // نختار المواد المرتبطة بهذه المرحلة فقط
                    $gradeSubjects = $allSubjects->where('grade_id', $grade->id);
                    
                    if ($gradeSubjects->count() > 0) {
                        foreach ($gradeSubjects as $subject) {
                            Mark::create([
                                'user_id' => $student->id,
                                'subject_id' => $subject->id,
                                'score' => rand(40, 100), // درجة عشوائية
                                'term' => 'الفصل الأول',
                            ]);
                        }
                    }

                    // --- تعبئة الحضور والغياب (Attendance) ---
                    // نسجل حضور لآخر 5 أيام
                    for ($day = 0; $day < 5; $day++) {
                        Attendance::create([
                            'user_id' => $student->id,
                            'student_id' => $profile->id, // البعض يستخدم هذا العمود
                            'class_id' => $class->id,
                            'attendance_date' => now()->subDays($day)->format('Y-m-d'),
                            'status' => $faker->randomElement([1, 1, 1, 1, 0]), // 80% حضور (1)، 20% غياب (0)
                        ]);
                    }
                }
            }
        }

        $this->command->info('🚀 تمت تعبئة قاعدة البيانات بالكامل بنجاح!');
        $this->command->info('الطلاب، الفصول، الدرجات، الغياب، وأولياء الأمور جاهزون.');
    }
}