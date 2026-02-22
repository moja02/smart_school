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
    Schema::create('student_parent', function (Blueprint $table) {
        $table->id();
        // ربط معرف الطالب
        $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
        // ربط معرف ولي الأمر
        $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
        
        $table->timestamps();
        
        // منع التكرار (نفس الطالب لنفس الأب مرتين)
        $table->unique(['student_id', 'parent_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_parent');
    }
};
