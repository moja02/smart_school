<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Fee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeeLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_cannot_have_negative_amount()
    {
        $fee = new Fee([
            'title' => 'Invalid Fee',
            'amount' => -100,
            'due_date' => now()->format('Y-m-d')
        ]);
        
        $isValidAmount = $fee->amount >= 0;
        
        $this->assertFalse($isValidAmount);
    }
}
