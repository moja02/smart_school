<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\Assessment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherTest extends TestCase
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

    private function createTeacher()
    {
        $school = School::create([
            'name' => 'Test School',
            'grading_locked' => false
        ]);
        $user = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id
        ]);
        $user->assignRole('teacher');
        return $user;
    }

    public function test_teacher_can_view_dashboard(): void
    {
        $teacher = $this->createTeacher();

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

        $response->assertStatus(200);
    }

    public function test_teacher_can_view_my_classes(): void
    {
        $teacher = $this->createTeacher();

        $response = $this->actingAs($teacher)->get(route('teacher.classes'));

        $response->assertStatus(200);
    }

    public function test_teacher_can_store_assessment(): void
    {
        $teacher = $this->createTeacher();
        $grade = \App\Models\Grade::create([
            'name' => 'Grade 1',
            'school_id' => $teacher->school_id
        ]);
        $class = SchoolClass::create([
            'name' => 'Class 1',
            'section' => 'A',
            'grade_id' => $grade->id,
            'school_id' => $teacher->school_id
        ]);
        $subject = Subject::create([
            'name' => 'Math',
            'school_id' => $teacher->school_id,
            'grade_id' => $grade->id
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.assessments.store'), [
            'name' => 'Test Assessment',
            'max_score' => 10,
            'subject_id' => $subject->id,
            'section_id' => $class->id,
            'semester' => 1,
        ]);

        if (session('error')) {
            dump(session('error'));
        }
        $response->assertSessionHasNoErrors();

        $response->assertRedirect();
        $this->assertDatabaseHas('assessments', [
            'name' => 'Test Assessment',
            'max_score' => 10,
            'subject_id' => $subject->id,
            'section_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);
    }
}
