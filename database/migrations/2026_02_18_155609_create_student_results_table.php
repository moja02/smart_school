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
        Schema::create('student_results', function (Blueprint $table) {
            $table->id();
            
            // ربط النتيجة بالطالب
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            
            // ربط النتيجة بالاختبار (التعديل الذي طلبته)
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            
            // ربط اختياري بالدرس (إذا كنت تريد معرفة أي درس غطاه الاختبار)
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('set null');

            $table->integer('score'); // درجة الطالب
            $table->integer('total'); // الدرجة الكلية للاختبار
            $table->integer('time_spent')->nullable(); // سنخزنه بالدقائق مثلاً
            
            $table->timestamps(); // لتخزين وقت وتاريخ أداء الاختبار
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};
