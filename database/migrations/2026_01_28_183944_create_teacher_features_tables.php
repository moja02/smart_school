<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الدروس (لتصنيف الأسئلة)
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->index(); // ربط بالمادة
            $table->string('title'); // عنوان الدرس
            $table->timestamps();

            // (اختياري) تفعيل القيد إذا كانت قاعدة البيانات سليمة
            // $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });

        // 2. جدول بنك الأسئلة
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->index(); // المادة
            $table->unsignedBigInteger('lesson_id')->nullable()->index(); // الدرس (يمكن أن يكون فارغاً)
            
            $table->enum('type', ['true_false', 'multiple_choice']); // نوع السؤال
            $table->text('content'); // نص السؤال
            $table->json('options')->nullable(); // الخيارات (للاختيار من متعدد) - مهم جداً يكون json
            $table->string('correct_answer'); // الإجابة الصحيحة
            $table->text('feedback')->nullable(); // ملاحظة للطالب
            $table->timestamps();
        });

        // 3. جدول تعريف التقييمات (الأوعية)
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->index(); // المادة
            $table->unsignedBigInteger('teacher_id')->index(); // المعلم الذي وضع التقييم
            
            $table->string('title'); // عنوان التقييم (اختبار شهر أول، واجب..)
            $table->integer('max_score'); // الدرجة العظمى
            $table->timestamps();
        });

        // 4. جدول رصد الدرجات
        Schema::create('assessment_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id')->index(); // التقييم
            $table->unsignedBigInteger('student_id')->index(); // الطالب
            
            $table->decimal('score', 5, 2); // الدرجة (تقبل كسور مثل 15.5)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_marks');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('lessons');
    }
};