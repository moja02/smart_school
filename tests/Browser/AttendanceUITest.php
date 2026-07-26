<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\School;

class AttendanceUITest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_teacher_can_take_attendance_ui()
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'password' => bcrypt('password123')
        ]);
        
        $this->browse(function (Browser $browser) use ($teacher) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/attendance')
                    ->assertSee('سجل الحضور والغياب')
                    ->select('class_id')
                    ->press('عرض الطلاب')
                    ->waitFor('.students-list', 3)
                    ->check('status[1]') // Mark absent for student ID 1
                    ->press('حفظ الغياب')
                    ->assertSee('تم حفظ الحضور بنجاح');
        });
    }
}
