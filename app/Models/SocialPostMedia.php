<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostMedia extends Model
{
    protected $table = 'social_post_media';

    // A tabela só tem created_at (sem updated_at).
    public $timestamps = false;

    protected $fillable = [
        'social_post_id', 'path', 'media_type', 'sort_order',
        'ig_container_id', 'width', 'height', 'source', 'prompt', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }

    /** URL pública da mídia — é exatamente o que a Graph API baixa para publicar. */
    public function url(): string
    {
        return asset($this->path);
    }
}
