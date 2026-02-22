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
    Schema::table('grades', function (Blueprint $table) {
        // عمود يحدد المرحلة (0: ابتدائي، 1: إعدادي، 2: ثانوي)
        // أو يمكن استخدام enum للنصوص
        $table->enum('stage', ['primary', 'middle', 'secondary'])->default('primary')->after('name');
    });
}

public function down()
{
    Schema::table('grades', function (Blueprint $table) {
        $table->dropColumn('stage');
    });
}
};
