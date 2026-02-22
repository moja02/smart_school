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
    Schema::create('marks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // الطالب
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade'); // المادة
        $table->decimal('score', 5, 2); // الدرجة (مثلاً 95.50)
        $table->string('term')->nullable(); // الفصل الدراسي (الأول/الثاني)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
