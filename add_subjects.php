<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;

// 1. Delete corrupted subjects (id 10 to 15)
Subject::whereIn('id', [10, 11, 12, 13, 14, 15])->delete();

// Primary subjects
$primarySubjects = [
    'التربية الإسلامية',
    'اللغة العربية',
    'الرياضيات',
    'العلوم',
    'اللغة الإنجليزية',
    'التربية البدنية',
    'النشاط'
];

// Secondary Scientific subjects
$scientificSubjects = [
    'التربية الإسلامية',
    'اللغة العربية',
    'اللغة الإنجليزية',
    'تقنية المعلومات',
    'الرياضيات',
    'الفيزياء',
    'الكيمياء',
    'الأحياء',
    'الميكانيكا',
    'الإحصاء'
];

// Secondary Literary subjects
$literarySubjects = [
    'التربية الإسلامية',
    'اللغة العربية',
    'اللغة الإنجليزية',
    'تقنية المعلومات',
    'التاريخ',
    'الجغرافيا',
    'علم الاجتماع',
    'الفلسفة والمنطق',
    'الإحصاء'
];

function insertSubjectsForGrade($gradeId, $subjectsList, $schoolId = 1) {
    foreach ($subjectsList as $subjectName) {
        Subject::firstOrCreate([
            'name' => $subjectName,
            'grade_id' => $gradeId,
            'school_id' => $schoolId,
        ], [
            'works_score' => 40,
            'final_score' => 60,
            'total_score' => 100
        ]);
    }
}

// Grades 1, 2, 3 (Primary)
foreach ([1, 2, 3] as $gradeId) {
    insertSubjectsForGrade($gradeId, $primarySubjects);
}

// Grades 4, 6 (Secondary Scientific)
foreach ([4, 6] as $gradeId) {
    insertSubjectsForGrade($gradeId, $scientificSubjects);
}

// Grades 5, 7 (Secondary Literary)
foreach ([5, 7] as $gradeId) {
    insertSubjectsForGrade($gradeId, $literarySubjects);
}

echo "Subjects added successfully!\n";
