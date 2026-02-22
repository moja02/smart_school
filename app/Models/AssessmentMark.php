<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AssessmentMark extends Model
{
    protected $guarded = []; // السماح بحفظ جميع البيانات

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // سيقوم بتسجيل أي تغيير في أي حقل
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('درجات التقييم');
    }
    
    // علاقة الدرجة بالطالب
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    // علاقة الدرجة بالتقييم
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}