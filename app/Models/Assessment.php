<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $guarded = []; // السماح بحفظ جميع البيانات

    // علاقة التقييم بالمادة
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // علاقة التقييم بالدرجات المرصودة له
    public function marks()
    {
        return $this->hasMany(AssessmentMark::class);
    }
}