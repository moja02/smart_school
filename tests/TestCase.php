<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            return;
        }

        $roles = ['super_admin', 'manager', 'admin', 'teacher', 'student', 'parent'];
        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
        }
        
        $permissions = [
            'toggle_grading', 'manage_users', 'manage_subjects', 
            'manage_classes', 'assign_teachers', 'edit_marks', 
            'manage_schedules', 'manage_reports', 'manage_promotion',
            'manage_admissions', 'manage_fees', 'manage_leaves',
            'manage_wallet', 'manage_wallet_charge', 'manage_wallet_sell'
        ];
        
        $managerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager']);
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        foreach ($permissions as $perm) {
            $p = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm]);
            $managerRole->givePermissionTo($p);
            $adminRole->givePermissionTo($p);
        }
    }
}
