<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\School;

class WalletUITest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A basic browser test example for Wallet UI.
     * This test checks if the wallet page loads and modal opens.
     */
    public function test_manager_can_see_wallet_page_and_purchase_modal()
    {
        $school = School::factory()->create();
        $manager = User::factory()->create([
            'role' => 'manager',
            'school_id' => $school->id,
            'password' => bcrypt('password123')
        ]);
        
        // This is a UI level test simulating real browser clicks
        $this->browse(function (Browser $browser) use ($manager) {
            $browser->loginAs($manager)
                    ->visit('/admin/wallet')
                    ->assertSee('إدارة المحفظة والمقصف المدرسي')
                    // Click on the purchase button which opens the modal
                    ->click('button[data-bs-target="#purchaseModal"]')
                    // Wait for the modal to be visible
                    ->waitFor('#purchaseModal', 3)
                    ->assertSee('تسجيل مشتريات مقصف (خصم رصيد)')
                    // Ensure the student selection exists
                    ->assertPresent('select[name="student_id"]');
        });
    }
}
