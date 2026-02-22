<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    // protected $fillable = ['school_id','name','teacher_id','academic_year'];
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
    'name', 
    'weekly_classes', // ✅ تأكد من وجود هذا
    'grade_id', 
    'school_id',
    'works_score', // ✅ درجة الأعمال
    'final_score', // ✅ درجة النهائي
    'total_score'  // ✅ المجموع
    ];

    // ✅ دالة ذكية لجلب توزيع الدرجات (تتبع نفس منطقك في عدد الحصص)
    public function getGradeDistribution($schoolId = null)
    {
        $schoolId = $schoolId ?? auth()->user()->school_id;

        // 1. إذا كانت المادة مخصصة للمدرسة، نأخذ الدرجات منها مباشرة
        if ($this->school_id == $schoolId) {
            return [
                'works' => $this->works_score ?? 40,
                'final' => $this->final_score ?? 60,
                'total' => $this->total_score ?? 100,
            ];
        }

        // 2. البحث عن إعدادات مخصصة لهذه المدرسة في جدول الإعدادات
        $customSetting = \DB::table('school_subject_settings')
                            ->where('school_id', $schoolId)
                            ->where('subject_id', $this->id)
                            ->first();

        if ($customSetting && isset($customSetting->works_score)) {
            return [
                'works' => $customSetting->works_score,
                'final' => $customSetting->final_score,
                'total' => $customSetting->total_score,
            ];
        }

        // 3. الافتراضي
        return [
            'works' => $this->works_score ?? 40,
            'final' => $this->final_score ?? 60,
            'total' => $this->total_score ?? 100,
        ];
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function teachers()
    {
    return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id');
    }
    public function grades()
    {
        return $this->hasMany(SubjectGrade::class, 'subject_id');
    }

    // إضافة علاقة تربط المادة بصف واحد (العلاقة العكسية)
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function getClassesCount($schoolId = null)
{
    // إذا لم يتم تمرير مدرسة، نستخدم مدرسة المستخدم الحالي
    $schoolId = $schoolId ?? auth()->user()->school_id;

    // 1. إذا كانت المادة خاصة بهذه المدرسة أصلاً، نرجع قيمتها المباشرة
    if ($this->school_id == $schoolId) {
        return $this->weekly_classes;
    }

    // 2. إذا كانت عامة، نبحث هل يوجد لها إعداد خاص في الجدول الجديد؟
    $customSetting = \DB::table('school_subject_settings')
                        ->where('school_id', $schoolId)
                        ->where('subject_id', $this->id)
                        ->first();

    if ($customSetting) {
        return $customSetting->weekly_classes; // ✅ إرجاع القيمة الخاصة
    }

    // 3. إذا لم يوجد تخصيص، نرجع القيمة العامة الافتراضية
    return $this->weekly_classes;
}

}
