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
            $table->dropColumn('grading_locked');
            $table->boolean('grading_locked_sem1')->default(false)->after('academic_year');
            $table->boolean('grading_locked_sem2')->default(false)->after('grading_locked_sem1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['grading_locked_sem1', 'grading_locked_sem2']);
            $table->boolean('grading_locked')->default(false)->after('academic_year');
        });
    }
};
