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
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
        $table->foreignId('class_id')->constrained('classes')->onDelete('cascade'); // للفصل
        $table->date('attendance_date'); // تاريخ اليوم
        $table->boolean('status')->default(0); // 1 = حاضر، 0 = غائب
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
