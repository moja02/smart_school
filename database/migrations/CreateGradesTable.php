<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradesTable extends Migration
{
    public function up()
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete(); // ربط الدرجات بالطالب
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete(); // ربط الدرجات بالمادة
            $table->string('term'); // الفصل الدراسي
            $table->unsignedInteger('total_score'); // الدرجة الإجمالية
            $table->unsignedInteger('max_score'); // الحد الأقصى للدرجة
            $table->timestamps(); // تاريخ الإنشاء والتحديث
        });
    }

    public function down()
    {
        Schema::dropIfExists('grades'); // حذف الجدول إذا تم التراجع عن الـ migration
    }
}
