<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_student_profile_relationship()
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = StudentProfile::create([
            'user_id' => $user->id,
            'total_fees' => 1000,
            'paid_fees' => 500,
        ]);

        $this->assertEquals($student->id, $user->studentProfile->id);
        $this->assertEquals(500, $student->remaining_fees);
    }

    public function test_student_belongs_to_parent()
    {
        $parentUser = User::factory()->create(['role' => 'parent']);
        $studentUser = User::factory()->create(['role' => 'student']);
        
        $studentUser->parents()->attach($parentUser->id);

        $this->assertTrue($studentUser->parents->contains($parentUser));
        $this->assertTrue($parentUser->children->contains($studentUser));
    }
}
