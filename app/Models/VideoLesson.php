<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoLesson extends Model
{
    use HasFactory;

    protected $table = 'video_lessons';

    protected $fillable = [
        'titulo',
        'link',
        'tasting_link',
        'bkp_link',
        'source',
        'subtitle',
        'status',
        'panel_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function panels()
    {
        return $this->belongsTo(Panel::class, 'panel_id', 'id');
    }
}
