<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['student_id', 'body'];

    // العلاقة مع StudentProfile
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
