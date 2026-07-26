<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OnlineExamLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_score_calculation()
    {
        $correctAnswers = ['q1' => 'A', 'q2' => 'B'];
        $studentAnswers = ['q1' => 'A', 'q2' => 'C'];
        
        $score = 0;
        foreach($correctAnswers as $q => $ans) {
            if(isset($studentAnswers[$q]) && $studentAnswers[$q] === $ans) $score++;
        }
        
        $this->assertEquals(1, $score);
    }
}
