<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GradeLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_is_valid_within_range()
    {
        $score = 105;
        $maxScore = 100;
        
        $isValid = $score >= 0 && $score <= $maxScore;
        $this->assertFalse($isValid);
    }
}
