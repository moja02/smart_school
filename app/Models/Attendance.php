<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //protected $fillable = ['student_id', 'class_id', 'attendance_date', 'status'];
    protected $fillable = ['user_id', 'date', 'status'];
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}