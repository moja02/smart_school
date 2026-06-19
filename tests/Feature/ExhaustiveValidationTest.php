<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;

class ExhaustiveValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $school = School::create(['name' => 'Test School']);
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school->id
        ]);
        $this->admin->assignRole('admin');
        
        // Add permissions required by tests
        $permissions = [
            'manage_accounting',
            'manage_wallet_sell',
            'manage_wallet',
            'manage_school',
            'manage_users'
        ];
        foreach ($permissions as $p) {
            if (!\Spatie\Permission\Models\Permission::where('name', $p)->exists()) {
                \Spatie\Permission\Models\Permission::create(['name' => $p, 'guard_name' => 'web']);
            }
            $this->admin->givePermissionTo($p);
        }
    }

    public function test_accounting_expense_validation()
    {
        $response = $this->actingAs($this->admin)->post('/accounting/expenses', [
            'title' => '', // required
            'category' => '', // required
            'amount' => -10, // min:0.1
            'expense_date' => 'not-a-date'
        ]);

        $response->assertSessionHasErrors(['title', 'category', 'amount', 'expense_date']);
    }

    public function test_wallet_topup_validation()
    {
        $response = $this->actingAs($this->admin)->post('/admin/wallet/topup', [
            'student_id' => 9999, // does not exist
            'amount' => -5 // min 0.5
        ]);

        $response->assertSessionHasErrors(['student_id', 'amount']);
    }
    
    public function test_store_class_validation()
    {
        $response = $this->actingAs($this->admin)->post('/admin/classes', [
            'grade_id' => 9999, // does not exist
            'sections' => 'not-an-array' // must be array
        ]);

        $response->assertSessionHasErrors(['grade_id', 'sections']);
    }

    public function test_store_grade_validation()
    {
        $response = $this->actingAs($this->admin)->post('/admin/grades', [
            'name' => '', // required
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_user_validation()
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => '', // required
            'email' => 'not-an-email', // must be email
            'password' => '123', // min 6
            'role' => 'invalid_role' // in:teacher,student,parent
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }
}
