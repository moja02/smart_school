<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class SchoolClass extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'classes';
    protected $guarded = [];
    protected $fillable = [
        'name', 
        'section', 
        'grade_id', 
        'school_id'
    ];

    public function students()
    {
        // العلاقة مع جدول StudentProfile
        return $this->hasMany(StudentProfile::class, 'class_id');
    }

    // العلاقة المهمة لجلب المواد
    public function subjects() {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'class_id', 'subject_id')
                    ->withPivot('teacher_id');
    }
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'section', 'grade_id'])
            ->logOnlyDirty()
            ->useLogName('إدارة الفصول');
    }
}

