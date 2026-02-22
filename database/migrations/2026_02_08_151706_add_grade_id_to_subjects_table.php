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
    Schema::table('subjects', function (Blueprint $table) {
        // إضافة عمود لربط المادة بالمرحلة الدراسية
        $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('subjects', function (Blueprint $table) {
        $table->dropForeign(['grade_id']);
        $table->dropColumn('grade_id');
    });
}
};
