<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // تأكد أن اسم الجدول هنا يطابق الموجود عندك (classes)
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');    // اسم الفصل (مثلاً: الأول الابتدائي)
            $table->string('section'); // ✅ هذا هو العمود الناقص (أ، ب، ج)
            
            // ربط الفصل بالمرحلة الدراسية
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            
            $table->unsignedBigInteger('school_id')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
