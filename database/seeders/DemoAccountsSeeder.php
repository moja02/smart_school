<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\ParentProfile;
use Spatie\Permission\Models\Role;

class DemoAccountsSeeder extends Seeder
{
    public function run()
    {
        // 1. التأكد من وجود المدرسة
        $school = School::firstOrCreate(
            ['id' => 1],
            ['name' => 'المدرسة الذكية الحديثة', 'academic_year' => '2026/2027']
        );

        // 2. التأكد من وجود الأدوار
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleTeacher = Role::firstOrCreate(['name' => 'teacher']);
        $roleStudent = Role::firstOrCreate(['name' => 'student']);
        $roleParent = Role::firstOrCreate(['name' => 'parent']);

        // 3. التأكد من وجود مرحلة وفصل
        $grade = Grade::firstOrCreate(
            ['name' => 'الصف الأول الثانوي'],
            ['school_id' => $school->id, 'stage' => 'secondary', 'order' => 1]
        );

        $class = SchoolClass::firstOrCreate(
            ['name' => 'أولى ثانوي - أ'],
            ['section' => 'أ', 'grade_id' => $grade->id, 'school_id' => $school->id]
        );

        // 4. إنشاء وتحديث حساب مدير النظام (Admin)
        $admin = User::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name' => 'أ. أحمد الفيتوري (مدير النظام)',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'school_id' => $school->id,
            ]
        );
        $admin->syncRoles([$roleAdmin]);

        // 5. إنشاء وتحديث حسابات المعلمين (Teachers)
        $teachersData = [
            ['email' => 'teacher@school.com', 'name' => 'أ. محمد المنصوري (معلم الرياضيات)'],
            ['email' => 'teacher1@school.com', 'name' => 'أ. سارة الورفلي (معلمة العلوم)'],
            ['email' => 'teacher2@school.com', 'name' => 'أ. علي الزوي (معلم اللغة العربية)'],
        ];

        foreach ($teachersData as $tData) {
            $teacher = User::updateOrCreate(
                ['email' => $tData['email']],
                [
                    'name' => $tData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'school_id' => $school->id,
                ]
            );
            $teacher->syncRoles([$roleTeacher]);
        }

        // 6. إنشاء وتحديث حسابات الطلاب (Students)
        $studentsData = [
            ['email' => 'student@school.com', 'name' => 'طارق علي الترهوني', 'number' => 'STU-2026-001', 'balance' => 150.00],
            ['email' => 'student1@school.com', 'name' => 'فاطمة حسن القماطي', 'number' => 'STU-2026-002', 'balance' => 90.00],
            ['email' => 'student2@school.com', 'name' => 'عبدالرحمن خالد الورفلي', 'number' => 'STU-2026-003', 'balance' => 50.00],
        ];

        $studentUsers = [];
        foreach ($studentsData as $sData) {
            $student = User::updateOrCreate(
                ['email' => $sData['email']],
                [
                    'name' => $sData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'school_id' => $school->id,
                ]
            );
            $student->syncRoles([$roleStudent]);
            $studentUsers[] = $student;

            // تحديث أو إنشاء ملف الطالب
            StudentProfile::updateOrCreate(
                ['user_id' => $student->id],
                [
                    'class_id' => $class->id,
                    'grade_id' => $grade->id ?? null,
                    'student_number' => $sData['number'],
                    'wallet_balance' => $sData['balance'],
                    'wallet_pin' => Hash::make('1234'),
                ]
            );
        }

        // 7. إنشاء وتحديث حسابات أولياء الأمور (Parents)
        $parentsData = [
            ['email' => 'parent@school.com', 'name' => 'علي مصطفى الترهوني (ولي أمر طارق)', 'student_index' => 0],
            ['email' => 'parent1@school.com', 'name' => 'حسن إبراهيم القماطي (ولي أمر فاطمة)', 'student_index' => 1],
        ];

        foreach ($parentsData as $pData) {
            $parent = User::updateOrCreate(
                ['email' => $pData['email']],
                [
                    'name' => $pData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'parent',
                    'school_id' => $school->id,
                ]
            );
            $parent->syncRoles([$roleParent]);

            // ربط ولي الأمر بالطالب المعني
            $targetStudent = $studentUsers[$pData['student_index']];
            DB::table('parent_student')->updateOrInsert(
                [
                    'parent_id' => $parent->id,
                    'student_id' => $targetStudent->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ تم إنشاء وتحديث جميع حسابات الدخول التجريبية بنجاح!');
    }
}
