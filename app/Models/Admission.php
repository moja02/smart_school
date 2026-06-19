<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_name',
        'parent_email',
        'parent_phone',
        'student_name',
        'student_dob',
        'grade_id',
        'status',
        'notes',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
