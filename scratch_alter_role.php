<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'parent', 'manager') NOT NULL DEFAULT 'student'");
echo "Role enum updated successfully.\n";
