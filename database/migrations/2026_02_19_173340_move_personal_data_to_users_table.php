<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. إضافة الحقول لجدول المستخدمين
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('address');
        });

        // 2. نقل البيانات الموجودة حالياً (لكي لا يضيع أي رقم هاتف أو عنوان للطالب)
        DB::table('student_profiles')->orderBy('id')->chunk(100, function ($profiles) {
            foreach ($profiles as $profile) {
                DB::table('users')
                    ->where('id', $profile->user_id)
                    ->update([
                        'phone' => $profile->phone,
                        'address' => $profile->address,
                        'birth_date' => $profile->birth_date,
                    ]);
            }
        });

        // 3. حذف الحقول من جدول بيانات الطلاب لتنظيف القاعدة
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'birth_date']);
        });
    }

    public function down()
    {
        // للتراجع في حال حدوث خطأ
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
        });

        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                DB::table('student_profiles')
                    ->where('user_id', $user->id)
                    ->update([
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'birth_date' => $user->birth_date,
                    ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'birth_date']);
        });
    }
};
