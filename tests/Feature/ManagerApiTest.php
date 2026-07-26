<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ManagerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_update_school_settings()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        
        $response = $this->actingAs($manager)
                         ->postJson('/api/manager/settings', [
                             'school_name' => 'Smart International School'
                         ]);

        $response->assertStatus(200);
    }
}
