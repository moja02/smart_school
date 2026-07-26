<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$child = \App\Models\StudentProfile::where('user_id', 32)->first(); 
echo 'Profile ID: ' . $child->id . ' | User ID: ' . $child->user_id;
