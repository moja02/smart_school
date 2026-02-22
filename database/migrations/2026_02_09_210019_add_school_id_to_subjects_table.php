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
    Schema::table('subjects', function (Blueprint $table) {
        // إذا كان العمود غير موجود نضيفه
        if (!Schema::hasColumn('subjects', 'school_id')) {
            $table->foreignId('school_id')->nullable()->constrained('users')->onDelete('cascade'); 
            // ملاحظة: ربطناه بـ users لأننا اعتبرنا المدرسة هي حساب المستخدم حالياً، أو بجدول schools إذا كان منفصلاً
        }
    });
}

public function down()
{
    Schema::table('subjects', function (Blueprint $table) {
        $table->dropColumn('school_id');
    });
}
};
