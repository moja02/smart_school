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
    Schema::create('school_subject_settings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        $table->integer('weekly_classes'); // عدد الحصص الخاص بهذه المدرسة
        $table->timestamps();
        
        // ضمان عدم تكرار الإعداد لنفس المادة والمدرسة
        $table->unique(['school_id', 'subject_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_subject_settings');
    }
};
