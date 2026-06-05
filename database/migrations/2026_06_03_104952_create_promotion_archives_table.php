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
        Schema::create('promotion_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('academic_year');
            $table->unsignedBigInteger('student_id');
            $table->string('student_name');
            $table->unsignedBigInteger('old_class_id')->nullable();
            $table->string('old_class_name')->nullable();
            $table->unsignedBigInteger('old_grade_id')->nullable();
            $table->string('old_grade_name')->nullable();
            $table->unsignedBigInteger('new_class_id')->nullable();
            $table->string('new_grade_name')->nullable();
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('max_possible_score', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->string('status'); // 'passed' or 'failed'
            $table->json('scores_snapshot')->nullable();
            $table->unsignedBigInteger('promoted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_archives');
    }
};
