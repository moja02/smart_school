<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class GradeUITest extends DuskTestCase
{
    public function test_parent_can_view_grades_ui()
    {
        $parent = User::factory()->create(['role' => 'parent']);
        
        $this->browse(function (Browser $browser) use ($parent) {
            $browser->loginAs($parent)
                    ->visit('/parent/grades')
                    ->assertSee('كشف الدرجات');
        });
    }
}
