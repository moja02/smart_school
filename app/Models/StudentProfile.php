<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class StudentProfile extends Model
{
    protected $guarded = [];

    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'class_id', 
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'class_id'])
            ->logOnlyDirty()
            ->useLogName('سجلات الطلاب الأكاديمية');
    }
}