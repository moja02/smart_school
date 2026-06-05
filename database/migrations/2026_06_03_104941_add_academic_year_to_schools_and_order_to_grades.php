<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('academic_year')->nullable()->after('name');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('academic_year');
        });
    }
};
