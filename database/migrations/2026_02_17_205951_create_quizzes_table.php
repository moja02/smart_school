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
    Schema::create('quizzes', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->unsignedBigInteger('subject_id');
        $table->unsignedBigInteger('section_id'); // الشعبة/الفصل
        $table->integer('duration')->default(30); // مدة الاختبار بالدقائق
        $table->boolean('is_active')->default(0); // هل الاختبار متاح للطلاب؟
        $table->timestamps();
    });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
