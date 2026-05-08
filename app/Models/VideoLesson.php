<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoLesson extends Model
{
    use HasFactory;

    protected $table = 'video_lessons';

    protected $fillable = [
        'link',
        'tasting_link',
        'source',
        'subtitle',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function panels()
    {
        return $this->belongsToMany(Panel::class, 'video_lessons_panels', 'id', 'panel_id');
    }
}
