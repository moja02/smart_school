<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student']);
    }

    public function test_admin_can_view_leaves_index(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get(route('admin.leaves'));
        $response->assertStatus(200);
    }

    public function test_admin_can_approve_leave(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        $student = \App\Models\User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        
        $parent = \App\Models\User::factory()->create(['role' => 'parent', 'school_id' => $school->id]);
        $parent->assignRole('parent');

        $leave = \App\Models\LeaveRequest::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'type' => 'absence',
            'leave_date' => date('Y-m-d'),
            'reason' => 'مريض',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.leaves.status', $leave->id), [
            'status' => 'approved'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved'
        ]);
    }
}
