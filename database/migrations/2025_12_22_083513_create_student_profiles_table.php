<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // public function up(): void
    // {
    //     Schema::create('student_profiles', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignId('user_id')
    //             ->constrained()
    //             ->cascadeOnDelete();

    //         $table->string('class_name')->nullable();
    //         $table->timestamps();
    //     });
    // }
    public function up()
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // تأكد أن اسم الجدول هنا صحيح (classes أو school_classes حسب تسميتك السابقة)
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');

            // ✅ أضف هذه الأعمدة الثلاثة الناقصة
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
