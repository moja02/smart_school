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
        // إضافة خانة أعمال السنة (Default 40)
        $table->integer('works_score')->default(40)->after('name');
        
        // إضافة خانة الامتحان النهائي (Default 60)
        $table->integer('final_score')->default(60)->after('works_score');
        
        // إضافة خانة المجموع الكلي (Default 100)
        $table->integer('total_score')->default(100)->after('final_score');
    });
}

public function down(): void
{
    Schema::table('subjects', function (Blueprint $table) {
        $table->dropColumn(['works_score', 'final_score', 'total_score']);
    });
}
};
