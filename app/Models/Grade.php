<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{

    // $guarded is used below, removing incorrect $fillable

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
