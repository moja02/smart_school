<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class OnlineExamUITest extends DuskTestCase
{
    public function test_student_can_take_exam_ui()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/student/exams')
                    ->assertSee('الامتحانات الإلكترونية')
                    ->clickLink('بدء الامتحان')
                    ->radio('q1', 'A')
                    ->press('تسليم')
                    ->assertSee('تم التسليم بنجاح');
        });
    }
}
