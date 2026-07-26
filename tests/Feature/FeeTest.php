<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student']);
    }

    public function test_admin_can_view_fees_index(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get(route('admin.fees.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_update_total_fees(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        $student = \App\Models\User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        $profile = \App\Models\StudentProfile::create(['user_id' => $student->id, 'total_fees' => 100]);

        $response = $this->actingAs($admin)->post(route('admin.fees.update_total', $profile->id), [
            'total_fees' => 500
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $student->id,
            'total_fees' => 500
        ]);
    }

    public function test_admin_can_pay_fees(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        $student = \App\Models\User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        $profile = \App\Models\StudentProfile::create(['user_id' => $student->id, 'total_fees' => 1000]);

        $response = $this->actingAs($admin)->post(route('admin.fees.pay', $profile->id), [
            'amount' => 300,
            'payment_date' => date('Y-m-d')
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_payments', [
            'student_id' => $profile->id,
            'amount' => 300
        ]);
        $this->assertDatabaseHas('student_profiles', [
            'id' => $profile->id,
            'paid_fees' => 300
        ]);
    }
}
