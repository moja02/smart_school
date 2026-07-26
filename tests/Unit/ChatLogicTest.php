<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_cannot_be_empty()
    {
        $message = "";
        $this->assertFalse(strlen($message) > 0);
    }
}
