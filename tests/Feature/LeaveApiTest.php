<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\LeaveRequest;

class LeaveApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'parent']);
    }

    public function test_api_parent_can_request_leave()
    {
        $school = School::create(['name' => 'Leave School']);
        $parent = User::factory()->create(['role' => 'parent', 'school_id' => $school->id]);
        $parent->assignRole('parent');
        
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $profile = StudentProfile::create(['user_id' => $student->id]);
        
        // attach student to parent
        $parent->children()->attach($student->id);

        $response = $this->actingAs($parent)
                         ->withHeaders(['Accept' => 'application/json'])
                         ->postJson(route('parent.leaves.store'), [
                             'student_id' => $profile->id,
                             'reason' => 'مريض',
                             'leave_date' => now()->format('Y-m-d')
                         ]);

        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $profile->id,
            'reason' => 'مريض'
        ]);
    }
}
