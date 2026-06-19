<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParentTest extends TestCase
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

    private function createParent()
    {
        $school = School::create(['name' => 'Test School']);
        $user = User::factory()->create([
            'role' => 'parent',
            'school_id' => $school->id
        ]);
        $user->assignRole('parent');

        return $user;
    }

    public function test_parent_can_view_dashboard(): void
    {
        $parent = $this->createParent();

        $response = $this->actingAs($parent)->get(route('parent.dashboard'));

        $response->assertStatus(200);
    }

    public function test_parent_can_view_attendance(): void
    {
        $parent = $this->createParent();

        $response = $this->actingAs($parent)->get(route('parent.attendance'));

        $response->assertStatus(200);
    }

    public function test_parent_can_view_exams(): void
    {
        $parent = $this->createParent();

        $response = $this->actingAs($parent)->get(route('parent.exams'));

        $response->assertRedirect(route('parent.dashboard'));
    }
}
