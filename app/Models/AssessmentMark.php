<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentMark extends Model
{
    protected $guarded = []; // السماح بحفظ جميع البيانات

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