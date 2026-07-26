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

    // العلاقة مع أولياء الأمور تتم عبر موديل User والجدول الوسيط parent_student
    public function parent()
    {
        return $this->hasOneThrough(
            User::class,
            ParentStudent::class,
            'student_id',
            'id',
            'user_id',
            'parent_id'
        );
    }

    public function assessmentMarks()
    {
        return $this->hasMany(AssessmentMark::class, 'student_id');
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class, 'student_id');
    }

    public function getRemainingFeesAttribute()
    {
        return max(0, $this->total_fees - $this->paid_fees);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'class_id', 'total_fees', 'paid_fees', 'wallet_balance', 'daily_limit'])
            ->logOnlyDirty()
            ->useLogName('سجلات الطلاب الأكاديمية والمحفظة');
    }
}