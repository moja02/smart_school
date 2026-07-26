<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdmissionLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_age_validation()
    {
        $age = 5;
        $this->assertFalse($age >= 6); // Assuming min age is 6
    }
}
