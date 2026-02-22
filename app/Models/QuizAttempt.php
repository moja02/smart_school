<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $guarded = [];

    // العلاقة مع الطالب
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    // العلاقة مع الدرس
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}