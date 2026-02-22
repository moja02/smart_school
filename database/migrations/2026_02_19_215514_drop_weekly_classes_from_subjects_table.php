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
        Schema::table('subjects', function (Blueprint $table) {
            // هذا السطر يحذف العمود من الداتا بيز
            $table->dropColumn('weekly_classes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // هذا السطر يرجعه لو درت تراجع (Rollback)
            $table->integer('weekly_classes')->default(0);
        });
    }
};
