<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\OnlineExam;
use App\Models\SchoolClass;
use App\Models\Subject;
use Spatie\Permission\Models\Role;

class OnlineExamTest extends TestCase
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
        $school = School::create(['name' => 'Test School']);
        $user = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $user->assignRole('teacher');
        return $user;
    }

    public function test_teacher_can_create_online_exam(): void
    {
        $teacher = $this->createTeacher();
        $grade = \App\Models\Grade::create(['school_id' => $teacher->school_id, 'name' => 'Grade 1']);
        $class = SchoolClass::create(['school_id' => $teacher->school_id, 'name' => 'Class 1', 'level' => 1, 'section' => 'A', 'grade_id' => $grade->id]);
        $subject = Subject::create(['school_id' => $teacher->school_id, 'name' => 'Math']);

        $response = $this->actingAs($teacher)->post(route('teacher.online_exams.store'), [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Midterm Exam',
            'duration_minutes' => 60,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('online_exams', [
            'title' => 'Midterm Exam',
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_teacher_can_add_question_to_online_exam(): void
    {
        $teacher = $this->createTeacher();
        $grade = \App\Models\Grade::create(['school_id' => $teacher->school_id, 'name' => 'Grade 1']);
        $class = SchoolClass::create(['school_id' => $teacher->school_id, 'name' => 'Class 1', 'level' => 1, 'section' => 'A', 'grade_id' => $grade->id]);
        $subject = Subject::create(['school_id' => $teacher->school_id, 'name' => 'Math']);

        $exam = OnlineExam::create([
            'school_id' => $teacher->school_id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Test Exam',
            'duration_minutes' => 30,
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.online_exams.questions.store', $exam->id), [
            'question_text' => 'What is 2+2?',
            'option_1' => '3',
            'option_2' => '4',
            'correct_answer' => '4',
            'score' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('online_exam_questions', [
            'online_exam_id' => $exam->id,
            'correct_answer' => '4',
        ]);
    }
}
