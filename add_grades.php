<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grade;
use Illuminate\Support\Facades\DB;

$schoolId = 1;

$grades = [
    'الثاني الثانوي - علمي',
    'الثاني الثانوي - أدبي',
    'الثالث الثانوي - علمي',
    'الثالث الثانوي - أدبي',
];

foreach ($grades as $gradeName) {
    $grade = Grade::firstOrCreate([
        'name' => $gradeName,
        'school_id' => $schoolId,
    ]);
    
    DB::table('school_grade')->updateOrInsert(
        ['school_id' => $schoolId, 'grade_id' => $grade->id],
        ['created_at' => now(), 'updated_at' => now()]
    );
    
    echo "تم إضافة: " . $gradeName . "\n";
}
