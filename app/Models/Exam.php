<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $table = 'exams';
    
    protected $fillable = [
        'title', 
        'exam_date', 
        'max_score', 
        'subject_id', 
        'class_id'
    ];

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