<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    use HasFactory;

    // ✅ أضف هذا السطر للسماح بحفظ هذه البيانات
    protected $fillable = [
        'title',
        'exam_date',
        'subject_id',
        'class_id',
        'teacher_id'
    ];

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