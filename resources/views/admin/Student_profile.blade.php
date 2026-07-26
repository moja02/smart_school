<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    /**
     * العلاقة العكسية بين ملف الطالب والمستخدم.
     * كل ملف طالب ينتمي إلى مستخدم واحد.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
