<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',    // الطالب
        'subject_id', // المادة
        'score',      // الدرجة
        'term',       // الفصل الدراسي (الأول/الثاني) - اختياري
    ];

    // علاقة: الدرجة تنتمي لطالب معين
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // علاقة: الدرجة تنتمي لمادة معينة
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}