<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_chat_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('chat.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.chat.index');
    }

    public function test_user_can_send_message_via_api()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $response = $this->actingAs($sender)->postJson(route('chat.send'), [
            'receiver_id' => $receiver->id,
            'message' => 'Hello there!'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => 'Hello there!'
        ]);
    }

    public function test_user_can_fetch_messages()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => 'Test msg'
        ]);

        $response = $this->actingAs($sender)->getJson(route('chat.messages', $receiver->id));

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Test msg']);
    }
}
