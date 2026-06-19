<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up(): void
    // {
    //     Schema::create('subjects', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('school_id');
    //         $table->string('name');

    //         $table->foreign('school_id')
    //               ->references('id')
    //               ->on('schools')
    //               ->cascadeOnDelete();

    //         $table->timestamps();
    //     });
    // }

    public function up(): void
{
    // جدول المواد (أسماء المواد فقط)
    Schema::create('subjects', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('works_score')->default(40);
        $table->integer('final_score')->default(60);
        $table->integer('total_score')->default(100);
        $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
        $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('cascade');
        $table->timestamps();
    });

    // جدول توزيع المواد (من يدرس ماذا ولمن؟)
    Schema::create('teacher_subject', function (Blueprint $table) {
        $table->id();
        $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade'); // المادة
        $table->foreignId('class_id')->constrained('classes')->onDelete('cascade'); // الفصل
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
