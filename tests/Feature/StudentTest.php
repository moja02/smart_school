<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $roles = ['super_admin', 'manager', 'admin', 'teacher', 'student', 'parent'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    private function createStudent()
    {
        $school = School::create(['name' => 'Test School']);
        $grade = \App\Models\Grade::create([
            'name' => 'Grade 1',
            'school_id' => $school->id
        ]);
        $class = SchoolClass::create([
            'name' => 'Class 1',
            'section' => 'A',
            'grade_id' => $grade->id,
            'school_id' => $school->id
        ]);
        
        $user = User::factory()->create([
            'role' => 'student',
            'school_id' => $school->id
        ]);
        $user->assignRole('student');
        
        StudentProfile::create([
            'user_id' => $user->id,
            'class_id' => $class->id
        ]);

        return $user;
    }

    public function test_student_can_view_dashboard(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertStatus(200);
    }

    public function test_student_can_view_my_subjects(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->get(route('student.subjects.index'));

        $response->assertStatus(200);
    }

    public function test_student_can_view_report_card(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->get(route('student.report_card'));

        $response->assertStatus(200);
    }
}
