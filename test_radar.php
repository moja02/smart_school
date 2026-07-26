<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$childId = 32;
$child = \App\Models\User::find($childId); 
$class_id = $child->studentProfile->class_id ?? $child->studentProfile->section_id ?? null; 
$class = $class_id ? \App\Models\SchoolClass::find($class_id) : null; 
$subjects = \App\Models\Subject::where('grade_id', $class->grade_id ?? null)->get(); 

$radarLabels = []; 
$radarData = []; 

foreach ($subjects as $subject) { 
    $assessments = \App\Models\Assessment::where('subject_id', $subject->id)->get(); 
    $subjectTotal = 0; 
    $subjectMaxTotal = 0; 

    foreach ($assessments as $assessment) { 
        $markRecord = \App\Models\AssessmentMark::where('student_id', $child->id)->where('assessment_id', $assessment->id)->first(); 
        $score = $markRecord ? ($markRecord->marks ?? $markRecord->score ?? $markRecord->mark ?? null) : null; 
        $assessmentMax = $assessment->max_score ?? $assessment->full_mark ?? 0; 
        
        if ($score !== null) { 
            $subjectTotal += $score; 
            $subjectMaxTotal += $assessmentMax; 
        } 
    } 

    $radarLabels[] = $subject->name; 
    $radarData[] = $subjectMaxTotal > 0 ? round(($subjectTotal / $subjectMaxTotal) * 100) : 0; 
} 

echo json_encode(['labels' => $radarLabels, 'data' => $radarData]);
