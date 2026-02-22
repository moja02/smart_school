<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class StudentProfile extends Model
{
    protected $guarded = [];

    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'phone',      
        'address',    
        'birth_date', 
    ];
    // العلاقة مع المستخدم الأساسي (الاسم، الايميل..)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع الفصل الدراسي
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // ✅ هذه هي العلاقة الناقصة: الطالب يتبع ولي أمر
    public function parent()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function assessmentMarks()
    {
        return $this->hasMany(AssessmentMark::class, 'student_id');
    }
}