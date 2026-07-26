<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'amount',
        'type',
        'description',
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
