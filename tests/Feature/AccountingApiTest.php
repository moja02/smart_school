<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AccountingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_accountant_can_record_expense()
    {
        $accountant = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($accountant)
                         ->postJson('/api/accounting/expenses', [
                             'title' => 'Office Supplies',
                             'amount' => 150
                         ]);

        $response->assertStatus(200);
    }
}
