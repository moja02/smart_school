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
    Schema::create('student_scores', function (Blueprint $table) {
        $table->id();
        $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
        $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        $table->foreignId('class_id')->constrained('classes')->onDelete('cascade'); // الشعبة
        
        // تقسيم الدرجات
        $table->decimal('works_score', 5, 2)->default(0); // أعمال السنة
        $table->decimal('final_score', 5, 2)->default(0); // الامتحان النهائي
        $table->decimal('total_score', 5, 2)->virtualAs('works_score + final_score'); // المجموع تلقائي
        
        $table->string('semester')->default('first'); // الفصل الدراسي
        $table->year('academic_year'); // السنة الدراسية
        
        $table->unique(['student_id', 'subject_id', 'semester', 'academic_year'], 'unique_score_entry');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_scores');
    }
};
