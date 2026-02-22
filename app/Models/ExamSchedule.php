<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class ExamSchedule extends Model
{
    use HasFactory, LogsActivity;

    // ✅ أضف هذا السطر للسماح بحفظ هذه البيانات
    protected $fillable = [
        'title',
        'exam_date',
        'subject_id',
        'class_id',
        'teacher_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'exam_date', 'subject_id', 'class_id', 'teacher_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('مواعيد الامتحانات');
    }

    // أو يمكنك استخدام هذا السطر بدلاً من السابق للسماح بكل شيء (أسهل):
    // protected $guarded = [];

    public function subject()
{
    return $this->belongsTo(Subject::class);
}

// وتأكد أيضاً من وجود علاقة المعلم لمعرفة صاحب الامتحان
public function teacher()
{
    return $this->belongsTo(User::class, 'teacher_id');
}
}