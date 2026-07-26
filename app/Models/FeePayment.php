<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'amount',
        'payment_date',
        'receipt_number',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
