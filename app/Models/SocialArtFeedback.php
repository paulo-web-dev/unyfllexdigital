<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialArtFeedback extends Model
{
    protected $table = 'social_art_feedback';

    public $timestamps = false;

    protected $fillable = [
        'social_art_draft_id', 'versao', 'nao_gostou', 'mudanca_pedida', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'versao'     => 'integer',
    ];

    public function draft()
    {
        return $this->belongsTo(SocialArtDraft::class, 'social_art_draft_id');
    }
}
