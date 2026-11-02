<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Schedule;
use App\Models\Assessment;
use App\Models\AssessmentMark;
use App\Models\Message;

echo "--- Setting Up Teacher Demo Data for أ. محمد المنصوري ---\n";

$teacher = User::where('email', 'teacher@school.com')->first();
if (!$teacher) {
    die("Teacher teacher@school.com not found!\n");
}

$schoolId = $teacher->school_id ?? 1;
echo "Found teacher ID: {$teacher->id} ({$teacher->name})\n";

// 1. Get or create Math subject
$mathSubject = Subject::where('name', 'like', '%رياضيات%')->first();
if (!$mathSubject) {
    $mathSubject = Subject::create([
        'name' => 'الرياضيات',
        'school_id' => $schoolId,
        'works_score' => 40,
        'final_score' => 60,
        'total_score' => 100,
    ]);
}
echo "Math Subject ID: {$mathSubject->id} ({$mathSubject->name})\n";

// 2. Get active classes (3 classes)
$classes = SchoolClass::take(3)->get();
if ($classes->isEmpty()) {
    die("No classes found!\n");
}

// 3. Assign to teacher_subject_section
foreach ($classes as $cls) {
    DB::table('teacher_subject_section')->updateOrInsert(
        [
            'teacher_id' => $teacher->id,
            'subject_id' => $mathSubject->id,
            'section_id' => $cls->id,
        ],
        [
            'school_id'    => $schoolId,
            'teacher_name' => $teacher->name,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]
    );

    // Also populate teacher_subject
    if (\Illuminate\Support\Facades\Schema::hasTable('teacher_subject')) {
        DB::table('teacher_subject')->updateOrInsert(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $mathSubject->id,
                'class_id'   => $cls->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    echo "Assigned class: {$cls->name} (ID: {$cls->id}) to teacher\n";
}

// Ensure students are assigned to these classes so studentsCount is not 0
$students = StudentProfile::all();
if ($students->count() > 0) {
    $i = 0;
    foreach ($students as $student) {
        $assignedClass = $classes[$i % $classes->count()];
        $student->class_id = $assignedClass->id;
        $student->save();
        $i++;
    }
    echo "Assigned {$students->count()} student profiles across the teacher's classes\n";
}

// 4. Create weekly schedules for the teacher (all days including today)
$days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
Schedule::where('teacher_id', $teacher->id)->delete();

foreach ($days as $day) {
    // 3 periods per day
    for ($p = 1; $p <= 3; $p++) {
        $assignedClass = $classes[($p - 1) % $classes->count()];
        Schedule::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $mathSubject->id,
            'class_id'   => $assignedClass->id,
            'day'        => $day,
            'period'     => $p,
            'is_backup'  => false,
        ]);
    }
}
echo "Weekly schedule generated (3 periods per day for all 7 days to guarantee today has classes)\n";

// 5. Add assessments for the first class
$firstClass = $classes->first();
if (\Illuminate\Support\Facades\Schema::hasTable('assessments')) {
    $a1 = Assessment::updateOrCreate(
        [
            'teacher_id' => $teacher->id,
            'subject_id' => $mathSubject->id,
            'section_id' => $firstClass->id,
            'name'       => 'اختبار الشهر الأول (الجبر)',
        ],
        [
            'max_score'  => 15,
            'semester'   => 1,
        ]
    );

    $a2 = Assessment::updateOrCreate(
        [
            'teacher_id' => $teacher->id,
            'subject_id' => $mathSubject->id,
            'section_id' => $firstClass->id,
            'name'       => 'الواجبات والأنشطة الأسبوعية',
        ],
        [
            'max_score'  => 10,
            'semester'   => 1,
        ]
    );
    echo "Sample assessments created for class {$firstClass->name}\n";
}

// 6. Create some sample messages for notification badge
$senderParent = User::role('parent')->first();
$senderStudent = User::role('student')->first();

if ($senderParent && \Illuminate\Support\Facades\Schema::hasTable('messages')) {
    Message::create([
        'sender_id'   => $senderParent->id,
        'receiver_id' => $teacher->id,
        'message'     => 'السلام عليكم يا أستاذ محمد، بخصوص واجب الرياضيات الأخير هل تم اعتماده؟',
        'is_read'     => false,
        'created_at'  => now()->subMinutes(15),
    ]);
}

if ($senderStudent && \Illuminate\Support\Facades\Schema::hasTable('messages')) {
    Message::create([
        'sender_id'   => $senderStudent->id,
        'receiver_id' => $teacher->id,
        'message'     => 'أستاذ محمد، هل يمكن إعادة شرح المسألة الأخيرة في حصة الغد؟',
        'is_read'     => false,
        'created_at'  => now()->subMinutes(5),
    ]);
}
echo "Sample unread messages created\n";

echo "=== All Teacher Demo Data Configured Successfully! ===\n";
