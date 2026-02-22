<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration {
//     public function up(): void {
//         Schema::table('users', function (Blueprint $table) {
//             /*if (!Schema::hasColumn('users', 'school_id')) {
//                 $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete()->after('id');
//             }*/
//             if (!Schema::hasColumn('users', 'role')) {
//                 $table->string('role')->default('student')->after('password');
//             }
//         });
//     }

//     public function down(): void {
//         Schema::table('users', function (Blueprint $table) {
//             if (Schema::hasColumn('users', 'school_id')) {
//                 $table->dropConstrainedForeignId('school_id');
//             }
//             if (Schema::hasColumn('users', 'role')) {
//                 $table->dropColumn('role');
//             }
//         });
//     }
// };
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
        Schema::table('users', function (Blueprint $table) {
            // إضافة عمود الصلاحية (Role)
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student')->after('email');
            }

            // إضافة عمود معرف المدرسة (School ID)
            if (!Schema::hasColumn('users', 'school_id')) {
                // نجعله nullable مبدئياً لتجنب المشاكل، ونربطه بجدول schools
                $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade')->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn(['school_id', 'role']);
        });
    }
};
