<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_message()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->postJson('/api/chat/send', [
                             'receiver_id' => 2,
                             'message' => 'Hello there!'
                         ]);

        $response->assertStatus(200);
    }
}
