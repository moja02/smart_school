<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class GradeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_publish_grades()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        
        $response = $this->actingAs($teacher)
                         ->postJson('/api/grades/publish', [
                             'student_id' => 1,
                             'subject_id' => 1,
                             'score' => 95
                         ]);

        $response->assertStatus(200);
    }
}
