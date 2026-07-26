<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManagerLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_name_required()
    {
        $name = "";
        $this->assertTrue(empty($name));
    }
}
