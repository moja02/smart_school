<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
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

    private function createAdmin()
    {
        $school = School::create(['name' => 'Test School']);
        // Assign db role enum correctly and Spatie role
        $user = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school->id
        ]);
        $user->assignRole('admin');
        return $user;
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Teacher',
            'email' => 'teacher@school.com',
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $response->assertRedirect(route('admin.users'));
        $this->assertDatabaseHas('users', [
            'email' => 'teacher@school.com',
            'school_id' => $admin->school_id,
            'role' => 'teacher'
        ]);
    }

    public function test_admin_can_create_grade(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.grades.store'), [
            'name' => 'الصف الأول',
            'notes' => 'مرحلة ابتدائية',
        ]);

        $response->assertRedirect(); // Usually redirects back
        $this->assertDatabaseHas('grades', [
            'name' => 'الصف الأول',
            'school_id' => $admin->school_id,
        ]);
    }

    public function test_admin_can_create_classes(): void
    {
        $admin = $this->createAdmin();
        $grade = Grade::create([
            'name' => 'الصف الأول',
            'school_id' => $admin->school_id
        ]);

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'grade_id' => $grade->id,
            'sections' => ['أ', 'ب'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', [
            'name' => 'الصف الأول - أ',
            'section' => 'أ',
            'grade_id' => $grade->id,
            'school_id' => $admin->school_id,
        ]);
        
        $this->assertDatabaseHas('classes', [
            'name' => 'الصف الأول - ب',
            'section' => 'ب',
            'grade_id' => $grade->id,
            'school_id' => $admin->school_id,
        ]);
    }
}
