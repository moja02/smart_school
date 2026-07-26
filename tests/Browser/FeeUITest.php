<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\School;

class FeeUITest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_can_create_fee_ui()
    {
        $school = School::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school->id,
            'password' => bcrypt('password123')
        ]);
        
        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/fees')
                    ->assertSee('إدارة الرسوم الدراسية')
                    ->clickLink('إضافة قسط')
                    ->waitFor('#feeModal', 3)
                    ->type('title', 'قسط الفصل الدراسي الأول')
                    ->type('amount', '500')
                    ->keys('#due_date', '2026-09-01')
                    ->press('حفظ الرسوم')
                    ->assertSee('تم إضافة القسط بنجاح');
        });
    }
}
