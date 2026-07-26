<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// البحث عن طالب لديه درجات مرصودة في assessment_marks
$studentIdWithMarks = DB::table('assessment_marks')
    ->whereNotNull('score')
    ->pluck('student_id')
    ->first();

if (!$studentIdWithMarks) {
    echo "لم يتم العثور على أي طالب لديه درجات مرصودة في التقييمات.\n";
    // Check final_grades table just in case
    $studentIdWithMarks = DB::table('final_grades')
        ->whereNotNull('works_score')
        ->pluck('student_id')
        ->first();
    
    if (!$studentIdWithMarks) {
        echo "لم يتم العثور على طالب لديه درجات نهائية أيضاً.\n";
        exit;
    }
}

// العثور على ولي أمر هذا الطالب
$parentId = DB::table('parent_student')
    ->where('student_id', $studentIdWithMarks)
    ->value('parent_id');

if (!$parentId) {
    echo "الطالب الذي لديه درجات (ID: $studentIdWithMarks) ليس لديه ولي أمر مسجل.\n";
    exit;
}

$parent = User::find($parentId);
if ($parent) {
    echo "--- حساب ولي الأمر ---\n";
    echo "الاسم: " . $parent->name . "\n";
    echo "البريد الإلكتروني: " . $parent->email . "\n";
    echo "كلمة المرور (الافتراضية): password\n"; // Usually test users have password as default
    echo "معرف ولي الأمر: " . $parent->id . "\n";
} else {
    echo "لم يتم العثور على حساب ولي الأمر.\n";
}
