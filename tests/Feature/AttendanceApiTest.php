<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\Attendance;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup basic roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'teacher']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student']);
    }

    public function test_teacher_can_record_attendance_via_api()
    {
        $school = School::create(['name' => 'Attendance School']);
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $teacher->assignRole('teacher');
        
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        $profile = StudentProfile::create(['user_id' => $student->id]);

        $response = $this->actingAs($teacher)
                         ->withHeaders(['Accept' => 'application/json'])
                         ->postJson(route('teacher.attendance.store', ['id' => $profile->id]), [
                             'status' => 'absent'
                         ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $profile->id,
            'status' => 'absent'
        ]);
    }
}
