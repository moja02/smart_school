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
    Schema::create('exams', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // اسم الامتحان
        $table->date('exam_date'); // التاريخ
        $table->unsignedBigInteger('subject_id');
        $table->unsignedBigInteger('section_id'); // الرابط الأساسي بالشعبة
        $table->unsignedBigInteger('teacher_id'); // لضمان أن المعلم صاحب المادة هو من يعدل
        $table->timestamps();

        // علاقات
        $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        $table->foreign('section_id')->references('id')->on('classes')->onDelete('cascade'); // افترضنا أن جدول الشعب هو classes حسب نقاشنا السابق
        $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
