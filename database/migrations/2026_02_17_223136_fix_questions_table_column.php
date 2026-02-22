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
    Schema::table('questions', function (Blueprint $table) {
        // 1. إذا كان class_id موجوداً، نغير اسمه إلى section_id
        if (Schema::hasColumn('questions', 'class_id')) {
            $table->renameColumn('class_id', 'section_id');
        } 
        // 2. إذا لم يكن class_id ولا section_id موجودين، نضيف section_id
        elseif (!Schema::hasColumn('questions', 'section_id')) {
            $table->unsignedBigInteger('section_id')->after('subject_id');
        }
    });
}

public function down()
{
    Schema::table('questions', function (Blueprint $table) {
        if (Schema::hasColumn('questions', 'section_id')) {
            $table->renameColumn('section_id', 'class_id');
        }
    });
}
};
