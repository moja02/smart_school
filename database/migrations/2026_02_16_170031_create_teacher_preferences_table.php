<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_preferences', function (Blueprint $table) {
            $table->id();
            
            // ربط المعلم بجدول المستخدمين
            $table->foreignId('teacher_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // حذف التفضيلات إذا حُذف المعلم

            // اسم اليوم (الأحد، الاثنين...)
            $table->string('day_name');

            // هل اليوم إجازة كاملة؟ (نعم/لا)
            $table->boolean('is_day_off')->default(false);

            // الحصص المرفوضة (تخزن كمصفوفة JSON)
            // مثال: [1, 4] يعني الحصة الأولى والرابعة مرفوضة
            $table->json('blocked_periods')->nullable();

            $table->timestamps();

            // منع تكرار نفس اليوم لنفس المعلم
            $table->unique(['teacher_id', 'day_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_preferences');
    }
};