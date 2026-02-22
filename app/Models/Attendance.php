<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Attendance extends Model
{
    //protected $fillable = ['student_id', 'class_id', 'attendance_date', 'status'];
    protected $fillable = ['user_id', 'date', 'status'];
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('سجل الغياب والحضور');
    }
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}