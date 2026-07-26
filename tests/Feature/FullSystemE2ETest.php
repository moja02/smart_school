<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\School;

class FullSystemE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_full_student_lifecycle_scenario()
    {
        // 1. Admin logs in and creates a student with a parent
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $this->actingAs($admin);
        
        $grade = \App\Models\Grade::create(['name' => 'الصف الأول']);
        $class = SchoolClass::create(['name' => 'الصف الأول أ', 'grade_id' => $grade->id, 'section' => 'A']);
        
        // 1. Admin creates parent
        $this->post(route('admin.users.store'), [
            'name' => 'ولي أمر تجريبي',
            'email' => 'parent_e2e@school.com',
            'password' => 'password123',
            'role' => 'parent',
        ]);

        $parentUser = User::where('email', 'parent_e2e@school.com')->first();

        // 2. Admin creates student
        $this->post(route('admin.users.store'), [
            'name' => 'طالب تجريبي',
            'email' => 'student_e2e@school.com',
            'password' => 'password123',
            'role' => 'student',
        ]);
        
        $this->assertDatabaseHas('users', ['email' => 'student_e2e@school.com']);
        $this->assertDatabaseHas('users', ['email' => 'parent_e2e@school.com']);

        $studentUser = User::where('email', 'student_e2e@school.com')->first();
        
        // Link them
        $parentUser->children()->attach($studentUser->id);

        // Also create a student profile if not auto-created
        if (!$studentUser->studentProfile) {
            \App\Models\StudentProfile::create(['user_id' => $studentUser->id, 'class_id' => $class->id, 'total_fees' => 2000]);
        } else {
            $studentUser->studentProfile->update(['class_id' => $class->id, 'total_fees' => 2000]);
        }

        // 2. Parent logs in and pays fees (via wallet or topup)
        // Let's assume parent tops up their student's wallet
        // Wait, topup is done by admin in current system. Let's do it via admin.
        $this->post(route('admin.wallet.topup'), [
            'student_id' => $studentUser->studentProfile->id,
            'amount' => 500,
            'payment_method' => 'cash'
        ]);
        
        $this->assertEquals(500, $studentUser->studentProfile->fresh()->wallet_balance);

        // 3. Admin pays school fees using student's wallet or cash
        // fees.pay actually takes ID in URL. Let's check. 
        // wait, earlier FeeTest has $this->post(route('admin.fees.pay', $student->id), ['amount'=>...])
        // Let's assume fees.pay takes ID.
        $this->post(route('admin.fees.pay', $studentUser->studentProfile->id), [
            'amount' => 1000,
            'payment_date' => now()->format('Y-m-d')
        ]);

        $this->assertEquals(1000, $studentUser->studentProfile->fresh()->paid_fees);
        $this->assertEquals(1000, $studentUser->studentProfile->fresh()->remaining_fees);

        // 4. Student logs in and checks dashboard
        auth()->logout();
        $this->actingAs($studentUser);
        
        $response = $this->get(route('student.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('طالب تجريبي');
        $response->assertSee('1000'); // Sees remaining fees
    }
}
