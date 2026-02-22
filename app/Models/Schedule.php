<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id', // تأكد من وجود هذا الحقل في قاعدة البيانات
        'day',
        'period',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
    
    // العلاقة مع المعلم
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}