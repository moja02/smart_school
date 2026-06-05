<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionArchive extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'scores_snapshot' => 'array',
    ];
}
