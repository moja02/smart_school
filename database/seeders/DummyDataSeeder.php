<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\Assessment;
use App\Models\AssessmentMark;
use App\Models\Attendance;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $this->command->info('بدأ توليد بيانات وهمية شاملة للمدرسة...');

        // 1. Roles
        Role::firstOrCreate(['name' => 'admin']);
        $roleTeacher = Role::firstOrCreate(['name' => 'teacher']);
        $roleStudent = Role::firstOrCreate(['name' => 'student']);
        $roleParent = Role::firstOrCreate(['name' => 'parent']);

        // 2. Parents
        $parents = [];
        for ($i = 1; $i <= 5; $i++) {
            $parent = User::firstOrCreate(
                ['email' => "parent{$i}@school.com"],
                [
                    'name' => 'ولي أمر ' . $i,
                    'password' => Hash::make('12345678'),
                    'role' => 'parent',
                    'school_id' => 1,
                ]
            );
            $parent->assignRole($roleParent);
            $parents[] = $parent;
        }

        // 3. Teachers
        $teachers = [];
        for ($i = 1; $i <= 3; $i++) {
            $teacher = User::firstOrCreate(
                ['email' => "teacher{$i}@school.com"],
                [
                    'name' => 'معلم ' . $faker->lastName,
                    'password' => Hash::make('12345678'),
                    'role' => 'teacher',
                    'school_id' => 1,
                ]
            );
            $teacher->assignRole($roleTeacher);
            $teachers[] = $teacher;
        }

        // 4. Grades and Subjects
        $gradeNames = ['الصف الأول الإعدادي', 'الصف الثاني الإعدادي'];
        $subjectNames = ['الرياضيات', 'العلوم', 'اللغة العربية', 'اللغة الإنجليزية'];
        
        foreach ($gradeNames as $gName) {
            $grade = Grade::firstOrCreate(['name' => $gName], ['school_id' => 1]);
            
            foreach ($subjectNames as $subName) {
                Subject::firstOrCreate([
                    'name' => $subName,
                    'grade_id' => $grade->id,
                ], ['school_id' => 1]);
            }

            // 5. Classes (Sections)
            foreach (['أ', 'ب'] as $sectionName) {
                $class = SchoolClass::firstOrCreate([
                    'name' => $grade->name . ' - ' . $sectionName,
                    'section' => $sectionName,
                    'grade_id' => $grade->id,
                ], ['school_id' => 1]);

                // 6. Students
                for ($k = 1; $k <= 3; $k++) {
                    $student = User::firstOrCreate(
                        ['email' => "student_{$class->id}_{$k}@school.com"],
                        [
                            'name' => 'طالب ' . $faker->firstName,
                            'password' => Hash::make('12345678'),
                            'role' => 'student',
                            'school_id' => 1,
                        ]
                    );
                    $student->assignRole($roleStudent);

                    StudentProfile::updateOrCreate(
                        ['user_id' => $student->id],
                        [
                            'class_id' => $class->id,
                            'wallet_balance' => rand(10, 50),
                        ]
                    );

                    // Link Parent
                    $randomParent = $parents[array_rand($parents)];
                    DB::table('parent_student')->updateOrInsert(
                        ['student_id' => $student->id],
                        ['parent_id' => $randomParent->id]
                    );

                    // 7. Attendance
                    for ($day = 0; $day < 5; $day++) {
                        Attendance::updateOrCreate([
                            'student_id' => $student->id,
                            'attendance_date' => now()->subDays($day)->format('Y-m-d'),
                        ], [
                            'school_id' => 1,
                            'section_id' => $class->id,
                            'status' => $faker->randomElement(['present', 'present', 'present', 'absent', 'late']),
                        ]);
                    }
                }

                // 8. Assessments & Marks
                $subjects = Subject::where('grade_id', $grade->id)->get();
                foreach ($subjects as $subject) {
                    $teacher = $teachers[array_rand($teachers)];
                    
                    // Create Assessment
                    $assessment = Assessment::firstOrCreate([
                        'name' => 'اختبار شهري - ' . $subject->name,
                        'subject_id' => $subject->id,
                        'section_id' => $class->id,
                    ], [
                        'school_id' => 1,
                        'semester' => 1,
                        'teacher_id' => $teacher->id,
                        'max_score' => 20,
                    ]);

                    // Add Marks
                    $studentsInClass = User::where('role', 'student')
                        ->whereHas('studentProfile', function ($query) use ($class) {
                            $query->where('class_id', $class->id);
                        })->get();

                    foreach ($studentsInClass as $st) {
                        AssessmentMark::updateOrCreate(
                            ['assessment_id' => $assessment->id, 'student_id' => $st->id],
                            ['score' => rand(10, 20)]
                        );
                    }
                }
            }
        }

        $this->command->info('✅ تمت إضافة البيانات الوهمية بنجاح! يمكنك الدخول بحساب:');
        $this->command->info('ولي أمر: parent1@school.com | كلمة المرور: 12345678');
        $this->command->info('طالب: student_1_1@school.com | كلمة المرور: 12345678');
        $this->command->info('معلم: teacher1@school.com | كلمة المرور: 12345678');
    }
}
