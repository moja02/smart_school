<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Exam extends Model
{
    use HasFactory , LogsActivity;

    protected $table = 'exams';
    
    protected $fillable = [
        'title', 
        'exam_date', 
        'max_score', 
        'subject_id', 
        'class_id'
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'exam_date', 'max_score', 'subject_id', 'class_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('الامتحانات الأساسية');
    }
    // علاقة الامتحان بالمادة
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // علاقة الامتحان بالفصل
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}