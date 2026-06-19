<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admission;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use Spatie\Permission\Models\Role;

class AdmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $roles = ['super_admin', 'manager', 'admin', 'teacher', 'student', 'parent'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $permissions = [
            'toggle_grading', 'manage_users', 'manage_subjects', 'manage_classes',
            'assign_teachers', 'edit_marks', 'manage_schedules', 'manage_reports', 'manage_promotion'
        ];
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createAdmin()
    {
        $school = School::create(['name' => 'Test School']);
        $user = User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }

    public function test_public_can_view_admissions_form(): void
    {
        $response = $this->get('/admissions/apply');
        $response->assertStatus(200);
    }

    public function test_public_can_submit_admission_form(): void
    {
        $school = School::create(['name' => 'Test School']);
        $grade = Grade::create(['school_id' => $school->id, 'name' => 'Grade 1']);

        $response = $this->post('/admissions/apply', [
            'student_name' => 'طالب جديد',
            'parent_name' => 'ولي أمر',
            'parent_email' => 'parent@example.com',
            'parent_phone' => '0551234567',
            'student_dob' => '2010-01-01',
            'grade_id' => $grade->id,
            'school_id' => $school->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('admissions', [
            'student_name' => 'طالب جديد',
        ]);
    }

    public function test_admin_can_view_admissions_list(): void
    {
        $admin = $this->createAdmin();
        $grade = Grade::create(['school_id' => $admin->school_id, 'name' => 'Grade 1']);
        
        Admission::create([
            'school_id' => $admin->school_id,
            'student_name' => 'طالب تجريبي',
            'parent_name' => 'ولي أمر',
            'parent_email' => 'parent2@example.com',
            'parent_phone' => '0551234567',
            'student_dob' => '2010-01-01',
            'grade_id' => $grade->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.admissions.index'));
        $response->assertStatus(200);
        $response->assertSee('طالب تجريبي');
    }
}
