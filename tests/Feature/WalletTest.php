<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage_wallet']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage_wallet_charge']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage_wallet_sell']);
        $adminRole->givePermissionTo(['manage_wallet', 'manage_wallet_charge', 'manage_wallet_sell']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student']);
    }

    public function test_admin_can_view_wallet_index(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get(route('admin.wallet'));
        $response->assertStatus(200);
    }

    public function test_admin_can_topup_wallet(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        $student = \App\Models\User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        $profile = \App\Models\StudentProfile::create(['user_id' => $student->id, 'wallet_balance' => 10]);

        $response = $this->actingAs($admin)->post(route('admin.wallet.topup'), [
            'student_id' => $profile->id,
            'amount' => 15
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_profiles', [
            'id' => $profile->id,
            'wallet_balance' => 25
        ]);
    }

    public function test_admin_can_deduct_purchase_from_wallet(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        $student = \App\Models\User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        $profile = \App\Models\StudentProfile::create(['user_id' => $student->id, 'wallet_balance' => 20]);

        $response = $this->actingAs($admin)->post(route('admin.wallet.purchase'), [
            'student_id' => $profile->id,
            'amount' => 5
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_profiles', [
            'id' => $profile->id,
            'wallet_balance' => 15
        ]);
    }
    
    public function test_purchase_fails_if_insufficient_funds(): void
    {
        $school = \App\Models\School::create(['name' => 'Test School']);
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        $student = \App\Models\User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        $profile = \App\Models\StudentProfile::create(['user_id' => $student->id, 'wallet_balance' => 5]);

        $response = $this->actingAs($admin)->post(route('admin.wallet.purchase'), [
            'student_id' => $profile->id,
            'amount' => 10
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('student_profiles', [
            'id' => $profile->id,
            'wallet_balance' => 5 // Balance remains the same
        ]);
    }
}
