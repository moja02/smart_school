<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdmissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_admission()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)
                         ->postJson('/api/admissions/1/approve');

        $response->assertStatus(200);
    }
}
