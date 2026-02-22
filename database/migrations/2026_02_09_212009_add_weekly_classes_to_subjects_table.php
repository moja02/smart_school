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
        // إضافة عمود عدد الحصص الأسبوعية (الافتراضي 2)
        $table->integer('weekly_classes')->default(2)->after('name');
    });
}

public function down()
{
    Schema::table('subjects', function (Blueprint $table) {
        $table->dropColumn('weekly_classes');
    });
}
};
