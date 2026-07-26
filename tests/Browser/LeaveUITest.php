<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;

class LeaveUITest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_parent_can_submit_leave_request_ui()
    {
        $school = School::factory()->create();
        $parent = User::factory()->create([
            'role' => 'parent',
            'school_id' => $school->id,
            'password' => bcrypt('password123')
        ]);
        
        $this->browse(function (Browser $browser) use ($parent) {
            $browser->loginAs($parent)
                    ->visit('/parent/leaves')
                    ->assertSee('طلبات الاستئذان')
                    ->clickLink('طلب جديد')
                    ->waitFor('#leaveModal', 3)
                    ->select('student_id')
                    ->type('reason', 'ظرف عائلي طارئ')
                    ->press('إرسال الطلب')
                    ->assertSee('تم إرسال الطلب بنجاح');
        });
    }
}
