<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Subject;

class FullCurriculumSeeder extends Seeder
{
    public function run()
    {
        // 1. تعريف المراحل والصفوف التابعة لها
        $stages = [
            'primary' => ['الصف الأول', 'الصف الثاني', 'الصف الثالث', 'الصف الرابع', 'الصف الخامس', 'الصف السادس'],
            'middle'  => ['الصف الأول إعدادي', 'الصف الثاني إعدادي', 'الصف الثالث إعدادي'],
            'secondary' => ['الصف الأول ثانوي', 'الصف الثاني ثانوي', 'الصف الثالث ثانوي'],
        ];

        // 2. المواد الافتراضية وعدد حصصها
        $commonSubjects = [
            ['name' => 'التربية الاسلامية', 'weekly_classes' => 4],
            ['name' => 'رياضيات', 'weekly_classes' => 3],
            ['name' => 'اللغة العربية', 'weekly_classes' => 6],
            ['name' => 'الجغرافيا', 'weekly_classes' => 5],
            ['name' => 'اللغة الإنجليزية', 'weekly_classes' => 4],
            ['name' => 'العلوم', 'weekly_classes' => 3],
            ['name' => 'الحاسوب', 'weekly_classes' => 1],
        ];

        foreach ($stages as $stageKey => $gradeNames) {
            foreach ($gradeNames as $gradeName) {
                // إنشاء الصف (Grade) وربطه بالمرحلة (Stage)
                $grade = Grade::create([
                    'name' => $gradeName,
                    'stage' => $stageKey, // primary, middle, or secondary
                    
                ]);

                // إضافة المواد لهذا الصف
                foreach ($commonSubjects as $sub) {
                    Subject::create([
                        'name' => $sub['name'],
                        'weekly_classes' => $sub['weekly_classes'],
                        'grade_id' => $grade->id,
                        'school_id' => null, // مادة عامة
                        
                    ]);
                }
            }
        }
    }
}