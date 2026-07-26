<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'section_id',
        'school_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    /**
     * العلاقة مع المعلم
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * العلاقة مع المادة
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * العلاقة مع الفصل/الشعبة
     */
    public function section()
    {
        return $this->belongsTo(SchoolClass::class, 'section_id');
    }

    /**
     * العلاقة مع المدرسة
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * دالة لعرض حجم الملف بشكل قابل للقراءة
     */
    public function getReadableFileSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * دالة لعرض أيقونة الملف حسب نوعه
     */
    public function getFileIconAttribute()
    {
        $icons = [
            'pdf'  => 'fas fa-file-pdf text-danger',
            'doc'  => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls'  => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'ppt'  => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'jpg'  => 'fas fa-file-image text-info',
            'jpeg' => 'fas fa-file-image text-info',
            'png'  => 'fas fa-file-image text-info',
            'zip'  => 'fas fa-file-archive text-secondary',
            'rar'  => 'fas fa-file-archive text-secondary',
            'txt'  => 'fas fa-file-alt text-muted',
            'mp4'  => 'fas fa-file-video text-danger',
            'mp3'  => 'fas fa-file-audio text-purple',
        ];

        return $icons[$this->file_type] ?? 'fas fa-file text-secondary';
    }
}
