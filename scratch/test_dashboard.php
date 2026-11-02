<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = \App\Models\User::where('email', 'teacher@school.com')->first();
auth()->login($teacher);

$ctl = new \App\Http\Controllers\TeacherController();
$view = $ctl->dashboard();
$data = $view->getData();

echo "Dashboard data:\n";
echo "- Classes count: " . $data['classesCount'] . "\n";
echo "- Students count: " . $data['studentsCount'] . "\n";
echo "- Subjects count: " . $data['subjectsCount'] . "\n";
echo "- Today: " . $data['todayArabic'] . "\n";
echo "- Today's scheduled periods: " . $data['todaySchedules']->count() . "\n";
foreach ($data['todaySchedules'] as $s) {
    echo "  * الحصة " . $s->period . ": " . ($s->subject->name ?? 'مادة') . " - " . ($s->schoolClass->name ?? 'فصل') . "\n";
}
