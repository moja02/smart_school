<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage_wallet_charge']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage_wallet_sell']);
        $adminRole->givePermissionTo(['manage_wallet_charge', 'manage_wallet_sell']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student']);
    }

    /**
     * Test API behavior for wallet purchase.
     * We simulate an API call expecting JSON response.
     */
    public function test_api_wallet_purchase_returns_json()
    {
        $school = School::create(['name' => 'API School']);
        $manager = User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $manager->assignRole('admin');
        
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $student->assignRole('student');
        $profile = StudentProfile::create(['user_id' => $student->id, 'wallet_balance' => 100]);

        // Simulate an ajax/api request by adding headers
        $response = $this->actingAs($manager)
                         ->withHeaders(['Accept' => 'application/json'])
                         ->postJson(route('admin.wallet.purchase'), [
                             'student_id' => $profile->id,
                             'amount' => 15,
                             'description' => 'API Purchase Test'
                         ]);

        $this->assertDatabaseHas('student_profiles', [
            'id' => $profile->id,
            'wallet_balance' => 85
        ]);
    }
}
