<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{

    // تعريف الحقول القابلة للتعديل (fillable)
    protected $fillable = [
        'student_id',   // الطالب الذي حصل على هذه الدرجة
        'subject_id',   // المادة الدراسية
        'total_score',  // الدرجة التي حصل عليها الطالب
        'max_score',    // الحد الأقصى للدرجة
    ];

    /**
     * العلاقة بين الـ Grade و StudentProfile
     * حيث أن كل درجة تخص طالب معين
     */
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    /**
     * العلاقة بين الـ Grade و Subject
     * حيث أن كل درجة تخص مادة معينة
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    use HasFactory;
    protected $guarded = [];

    // علاقة الدرجة بالطالب
    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    // العلاقة مع الفصول (اختياري الآن)
    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_grade');
    }
}
