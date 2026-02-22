<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    // ضمان وجود دور المدير في جدول الصلاحيات
    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'school_manager']);

    // إنشاء المدير
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'manager@school.com'],
        [
            'name' => 'مدير المدرسة',
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'role' => 'manager', // ✅ الآن الداتا بيز تقبلها
            'school_id' => 1,
        ]
    );

    // إعطاء الصلاحية
    $user->assignRole($role);
    
    echo "✅ تم إنشاء المدير: manager@school.com / 12345678 \n";
}
}
