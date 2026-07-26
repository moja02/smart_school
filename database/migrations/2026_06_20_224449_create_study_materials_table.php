<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('classes')->onDelete('cascade');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name'); // الاسم الأصلي للملف
            $table->string('file_type')->nullable(); // pdf, docx, pptx...
            $table->unsignedBigInteger('file_size')->default(0); // الحجم بالبايت
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_materials');
    }
};
