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

        // 1. إنشاء الأدوار (إذا لم تكن موجودة)
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleTeacher = Role::firstOrCreate(['name' => 'teacher']);
        $roleStudent = Role::firstOrCreate(['name' => 'student']);
        $roleParent = Role::firstOrCreate(['name' => 'parent']);

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
            $teacher = User::create([
                'name' => $faker->name,
                'email' => "teacher$i@school.com",
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'school_id' => 1,
            ]);
            $teacher->assignRole($roleTeacher);
            $teachers[] = $teacher;
        }
        $this->command->info('✅ تم إنشاء 10 معلمين.');

        // 4. إنشاء أولياء الأمور (20 ولي أمر)
        $parents = [];
        for ($i = 1; $i <= 20; $i++) {
            $parent = User::create([
                'name' => $faker->name,
                'email' => "parent$i@school.com",
                'password' => Hash::make('password'),
                'role' => 'parent',
                'school_id' => 1,
            ]);
            $parent->assignRole($roleParent);
            $parents[] = $parent;
        }
        $this->command->info('✅ تم إنشاء 20 ولي أمر.');

        // 5. جلب المراحل والمواد الموجودة (بدون إنشاء جديد)
        $grades = Grade::all();
        $allSubjects = Subject::all();

        if ($grades->count() == 0) {
            $this->command->error('❌ لا توجد مراحل دراسية! يرجى إضافتها أولاً.');
            return;
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
                    StudentProfile::create([
                        'user_id' => $student->id,
                        'class_id' => $class->id,
                        'phone' => $faker->phoneNumber,
                        'address' => $faker->address,
                        'birth_date' => $faker->date('Y-m-d', '2015-01-01'),
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
                            'student_id' => $student->id, // البعض يستخدم هذا العمود
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