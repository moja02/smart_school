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
        Schema::create('online_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('online_exam_id');
            $table->text('question_text');
            $table->string('question_type')->default('multiple_choice');
            $table->json('options')->nullable();
            $table->string('correct_answer');
            $table->integer('score')->default(1);
            $table->timestamps();

            $table->foreign('online_exam_id')->references('id')->on('online_exams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_questions');
    }
};
