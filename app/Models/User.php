<?php

namespace App\Models;

// ✅ 1. استدعاءات الـ Traits الأساسية
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory; // كان ناقصاً
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// ✅ 2. استدعاء الموديلات المرتبطة
use App\Models\StudentProfile;
use App\Models\School;
use App\Models\Subject; // كان ناقصاً للعلاقات بالأسفل

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'school_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ===========================
    // العلاقات (Relationships)
    // ===========================

    // public function school()
    // {
    //     return $this->belongsTo(School::class);
    // }

    public function children()
    {
        return $this->belongsToMany(
            User::class,
            'parent_student',
            'parent_id',
            'student_id'
        );
    }

    public function parents()
    {
        return $this->belongsToMany(
            User::class,
            'parent_student',
            'student_id',
            'parent_id'
        );
    }

    public function teachingSubjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subject',
            'teacher_id',
            'subject_id'
        )->withPivot('class_id')->withTimestamps();
    }

    public function studentSubjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'student_subject',
            'student_id',
            'subject_id'
        )->withPivot('class_id')->withTimestamps();
    }

    // علاقة المعلم بالمواد التي يدرسها (مكررة ولكن لا بأس بها إذا كنت تستخدمها)
    public function teaching()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
                    ->withPivot('class_id'); 
    }

        // علاقة الدرجات
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    // علاقة الحضور والغياب
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function studentProfile()
    {
        // علاقة الطالب بملفه الشخصي
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    public function school_class()
    {
        // علاقة "عبر وسيط": نصل للفصل من خلال البروفايل
        return $this->hasOneThrough(
            SchoolClass::class,      // الهدف: الفصل
            StudentProfile::class,   // الوسيط: البروفايل
            'user_id',               // المفتاح في البروفايل الذي يربطه باليوزر
            'id',                    // المفتاح في الفصل
            'id',                    // المفتاح في اليوزر
            'class_id'               // المفتاح في البروفايل الذي يربطه بالفصل
        );
    }
    // داخل كلاس User
    public function schoolClass()
    {
        // الطالب ينتمي إلى شعبة واحدة (التي جدولها هو classes)
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }
    
    public function preferences()
{
    return $this->hasMany(TeacherPreference::class, 'teacher_id');
}


} 
