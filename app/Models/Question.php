<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Question extends Model {
    protected $guarded = []; // السماح بالحفظ
    protected $casts = ['options' => 'array']; // تحويل الخيارات تلقائياً
    protected $fillable = [
    'lesson_id', 
    'content', 
    'type', 
    'options', 
    'correct_answer', 
    'feedback',
    'score' // ✅ تمت الإضافة
];
    public function lesson() { return $this->belongsTo(Lesson::class); }
}