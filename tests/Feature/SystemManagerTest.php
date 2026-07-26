<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemManagerTest extends TestCase
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

    private function createSuperAdmin()
    {
        // Using valid enum role for DB column, assigning super_admin via Spatie
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('super_admin');
        return $user;
    }

    private function createStudent()
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');
        return $user;
    }

    public function test_super_admin_can_view_schools_list(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $school = School::create(['name' => 'مدرسة تجريبية']);

        $response = $this->actingAs($superAdmin)->get(route('system.schools.index'));

        $response->assertStatus(200);
        $response->assertSee('مدرسة تجريبية');
    }

    public function test_non_super_admin_cannot_view_schools_list(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->get(route('system.schools.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_school(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post(route('system.schools.store'), [
            'name' => 'مدرسة التميز',
        ]);

        $response->assertRedirect(route('system.schools.index'));
        $this->assertDatabaseHas('schools', [
            'name' => 'مدرسة التميز',
        ]);
    }

    public function test_super_admin_can_view_create_user_form_for_school(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $school = School::create(['name' => 'مدرسة تجريبية']);

        $response = $this->actingAs($superAdmin)->get(route('system.users.create', ['school' => $school->id]));

        $response->assertStatus(200);
        $response->assertSee('مدرسة تجريبية');
    }

    public function test_super_admin_can_store_user_for_school(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $school = School::create(['name' => 'مدرسة تجريبية']);

        $response = $this->actingAs($superAdmin)->post(route('system.users.store', ['school' => $school->id]), [
            'name' => 'مدير تجريبي',
            'email' => 'admin_test@school.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('system.schools.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin_test@school.com',
            'school_id' => $school->id,
            'role' => 'admin',
        ]);
        
        $user = User::where('email', 'admin_test@school.com')->first();
        $this->assertTrue($user->hasRole('admin'));
    }
}
