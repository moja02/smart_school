<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use Spatie\Permission\Models\Role;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $roles = ['super_admin', 'manager', 'admin', 'teacher', 'student', 'parent'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $permissions = [
            'toggle_grading', 'manage_users', 'manage_subjects', 'manage_classes',
            'assign_teachers', 'edit_marks', 'manage_schedules', 'manage_reports', 'manage_promotion'
        ];
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createAdmin()
    {
        $school = School::create(['name' => 'Test School']);
        $user = User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }

    public function test_admin_can_view_expenses_page(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('admin.accounting.expenses'));
        $response->assertStatus(200);
    }

    public function test_admin_can_store_expense(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.accounting.expenses.store'), [
            'title' => 'Electric Bill',
            'category' => 'utilities',
            'amount' => 500,
            'expense_date' => date('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'school_id' => $admin->school_id,
            'title' => 'Electric Bill',
            'amount' => 500,
        ]);
    }

    public function test_admin_can_view_profit_loss_report(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('admin.accounting.report'));
        $response->assertStatus(200);
        $response->assertSee('التقرير المالي');
    }
}
