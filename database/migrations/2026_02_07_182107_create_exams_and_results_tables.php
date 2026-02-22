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
    // 1. جدول الامتحانات (يحدده المعلم)
    Schema::create('exams', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // مثلاً: امتحان نصفي، اختبار قصير
        $table->date('exam_date'); // موعد الامتحان
        $table->integer('max_score')->default(100); // الدرجة العظمى
        $table->foreignId('subject_id')->constrained()->onDelete('cascade');
        $table->foreignId('class_id')->constrained('classes')->onDelete('cascade'); // خاص بفصل معين
        $table->timestamps();
    });

    // 2. جدول نتائج الطلاب
    Schema::create('exam_results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_id')->constrained()->onDelete('cascade');
        $table->foreignId('student_id')->constrained('users')->onDelete('cascade'); // نربطه باليوزر الطالب
        $table->float('score'); // درجة الطالب
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('exam_results');
    Schema::dropIfExists('exams');
}
};
