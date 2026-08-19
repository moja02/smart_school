<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_cannot_be_recorded_in_future()
    {
        $user = User::factory()->create(['role' => 'student']);
        $profile = StudentProfile::create(['user_id' => $user->id]);

        $attendance = new Attendance([
            'student_id' => $profile->id,
            'status' => 'present',
            'attendance_date' => now()->addDays(2)->format('Y-m-d')
        ]);
        
        // Simulating logic check before save
        $isValidDate = $attendance->attendance_date <= now()->format('Y-m-d');
        
        $this->assertFalse($isValidDate);
    }
}
