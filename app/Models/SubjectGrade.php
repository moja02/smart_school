<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectGrade extends Model
{
    use HasFactory;

    // اسم الجدول في قاعدة البيانات
    protected $table = 'subject_grades';

    // الحقول القابلة للتعبئة
    protected $fillable = ['subject_id', 'grade_id']; // تأكدنا من استخدام grade_id وليس grade_level

    // علاقة مع جدول المراحل (اختياري، لكن مفيد)
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }
}