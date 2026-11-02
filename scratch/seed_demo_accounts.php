<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use Spatie\Permission\Models\Role;

echo "--- Starting Demo Accounts Seeding ---\n";

// 1. School
$school = School::firstOrCreate(
    ['id' => 1],
    ['name' => 'المدرسة الذكية الحديثة', 'academic_year' => '2026/2027']
);

// 2. Roles
$roleAdmin = Role::firstOrCreate(['name' => 'admin']);
$roleTeacher = Role::firstOrCreate(['name' => 'teacher']);
$roleStudent = Role::firstOrCreate(['name' => 'student']);
$roleParent = Role::firstOrCreate(['name' => 'parent']);

// 3. Grade & Class
$grade = Grade::firstOrCreate(
    ['name' => 'الصف الأول الثانوي'],
    ['school_id' => $school->id, 'stage' => 'secondary', 'order' => 1]
);

$class = SchoolClass::firstOrCreate(
    ['name' => 'أولى ثانوي - أ'],
    ['section' => 'أ', 'grade_id' => $grade->id, 'school_id' => $school->id]
);

// 4. Admin
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
echo "✅ Admin: admin@school.com / password\n";

// 5. Teachers
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
    echo "✅ Teacher: {$tData['email']} / password\n";
}

// 6. Students
$studentsData = [
    ['email' => 'student@school.com', 'name' => 'طارق علي الترهوني', 'balance' => 150.00, 'fees' => 1200.00, 'paid' => 1200.00],
    ['email' => 'student1@school.com', 'name' => 'فاطمة حسن القماطي', 'balance' => 90.00, 'fees' => 1200.00, 'paid' => 800.00],
    ['email' => 'student2@school.com', 'name' => 'عبدالرحمن خالد الورفلي', 'balance' => 50.00, 'fees' => 1200.00, 'paid' => 600.00],
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

    StudentProfile::updateOrCreate(
        ['user_id' => $student->id],
        [
            'class_id' => $class->id,
            'wallet_balance' => $sData['balance'],
            'wallet_pin' => '1234',
            'total_fees' => $sData['fees'],
            'paid_fees' => $sData['paid'],
            'daily_limit' => 20.00,
        ]
    );
    echo "✅ Student: {$sData['email']} / password (PIN: 1234)\n";
}

// 7. Parents
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
    echo "✅ Parent: {$pData['email']} / password (Linked to student: {$targetStudent->name})\n";
}

echo "\n=============================================\n";
echo "🎉 ALL DEMO ACCOUNTS CREATED & UPDATED SUCCESSFULLY!\n";
echo "=============================================\n";
