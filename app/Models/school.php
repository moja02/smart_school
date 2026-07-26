<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['name', 'code', 'grading_locked_sem1', 'grading_locked_sem2', 'academic_year', 'address', 'phone'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
    public function grades()
{
    // هذه العلاقة تعني: هات الصفوف المرتبطة بهذه المدرسة
    // عبر الجدول الوسيط 'school_grade'
    return $this->belongsToMany(Grade::class, 'school_grade', 'school_id', 'grade_id');
}
}
