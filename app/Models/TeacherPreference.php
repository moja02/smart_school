<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPreference extends Model
{
    protected $fillable = ['teacher_id', 'day_name', 'is_day_off', 'blocked_periods'];

    protected $casts = [
        'is_day_off' => 'boolean',
        'blocked_periods' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}