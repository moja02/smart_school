<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_top_up_increases_balance()
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = StudentProfile::create(['user_id' => $user->id, 'wallet_balance' => 10]);

        $student->wallet_balance += 50;
        $student->save();

        $this->assertEquals(60, $student->fresh()->wallet_balance);
    }

    public function test_wallet_cannot_be_negative_on_purchase()
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = StudentProfile::create(['user_id' => $user->id, 'wallet_balance' => 15]);

        $purchaseAmount = 20;
        
        $canPurchase = $student->wallet_balance >= $purchaseAmount;
        
        $this->assertFalse($canPurchase);
        $this->assertEquals(15, $student->wallet_balance);
    }

    public function test_wallet_deducts_correctly_on_valid_purchase()
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = StudentProfile::create(['user_id' => $user->id, 'wallet_balance' => 50]);

        $purchaseAmount = 20;
        
        if ($student->wallet_balance >= $purchaseAmount) {
            $student->wallet_balance -= $purchaseAmount;
            $student->save();
        }
        
        $this->assertEquals(30, $student->fresh()->wallet_balance);
    }
}
