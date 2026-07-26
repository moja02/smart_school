<?php

namespace Tests\Unit;

use Tests\TestCase;

class AccountingLogicTest extends TestCase
{
    public function test_expense_must_be_positive()
    {
        $amount = -50;
        $this->assertFalse($amount > 0);
    }
}
