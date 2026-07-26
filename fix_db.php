<?php
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Schema::table('schedules', function($table) {
        $table->dropForeign(['class_id']);
        $table->dropForeign(['subject_id']);
        $table->dropForeign(['teacher_id']);
    });
    echo "Foreign keys dropped successfully.\n";
} catch (\Exception $e) {
    echo "Error dropping foreign keys: " . $e->getMessage() . "\n";
}

try {
    // Also let's try to convert the MyISAM tables to InnoDB just in case
    \DB::statement('ALTER TABLE classes ENGINE=InnoDB');
    \DB::statement('ALTER TABLE subjects ENGINE=InnoDB');
    \DB::statement('ALTER TABLE users ENGINE=InnoDB');
    echo "Engines updated successfully.\n";
} catch (\Exception $e) {
    echo "Error updating engines: " . $e->getMessage() . "\n";
}
