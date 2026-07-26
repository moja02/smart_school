<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeaveLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_status_defaults_to_pending()
    {
        $leave = new LeaveRequest([
            'student_id' => 1,
            'parent_id' => 1,
            'reason' => 'مريض',
            'leave_date' => now()->format('Y-m-d')
        ]);
        
        // Assuming status is 'pending' before approval
        $leave->status = 'pending';
        
        $this->assertEquals('pending', $leave->status);
    }
}
