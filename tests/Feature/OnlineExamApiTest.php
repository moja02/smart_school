<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;

class OnlineExamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_exam()
    {
        $school = School::create(['name' => 'Exam School']);
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        
        $response = $this->actingAs($student)
                         ->postJson('/api/exams/1/submit', [
                             'answers' => ['q1' => 'A', 'q2' => 'B']
                         ]);

        $response->assertStatus(200); // Assuming standard response
    }
}
