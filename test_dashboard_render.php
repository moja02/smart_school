<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parentId = 27; // The parent ID
$children = \App\Models\StudentProfile::whereIn('user_id', function($query) use ($parentId) {
    $query->select('student_id')
            ->from('parent_student')
            ->where('parent_id', $parentId);
})->with(['user', 'schoolClass'])->get();

$child = $children->first();
$childId = $child->id;
$userId = $child->user_id;

echo "Child ID: $childId\n";
echo "User ID: $userId\n";

$class_id = $child->class_id ?? $child->section_id ?? null;
$class = $class_id ? \App\Models\SchoolClass::find($class_id) : null;
$subjects = \App\Models\Subject::where('grade_id', $class->grade_id ?? null)->get();

$subjectsData = collect();
foreach ($subjects as $subject) {
    $assessments = \App\Models\Assessment::where('subject_id', $subject->id)->get();
    $subjectTotal = 0;
    $subjectMaxTotal = 0;
    
    foreach ($assessments as $assessment) {
        $markRecord = \App\Models\AssessmentMark::where('student_id', $userId)
                                    ->where('assessment_id', $assessment->id)
                                    ->first();
        
        if ($markRecord && $markRecord->score !== null) {
            $score = $markRecord->score ?? $markRecord->marks ?? $markRecord->mark ?? 0;
            $assessmentMax = $assessment->max_score ?? $assessment->full_mark ?? 0;
            
            $subjectTotal += $score;
            $subjectMaxTotal += $assessmentMax;
        }
    }
    
    $subjectsData->push((object)[
        'subject_name'   => $subject->name,
        'total_score'    => $subjectTotal,
        'max_total'      => $subjectMaxTotal
    ]);
}

$radarLabels = [];
$radarData = [];
foreach ($subjectsData as $sd) {
    if ($sd->max_total > 0) {
        $radarLabels[] = $sd->subject_name;
        $radarData[] = round(($sd->total_score / $sd->max_total) * 100);
    }
}

$chartLabels = json_encode($radarLabels);
$chartData = json_encode($radarData);

echo "Labels JSON: " . $chartLabels . "\n";
echo "Data JSON: " . $chartData . "\n";
echo "HTML Element:\n";
echo "<div id=\"chartData-{$childId}\" data-labels=\"" . htmlspecialchars($chartLabels) . "\" data-data=\"" . htmlspecialchars($chartData) . "\"></div>\n";
