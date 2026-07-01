<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPost extends Model
{
    protected $table = 'social_posts';

    protected $fillable = [
        'social_account_id', 'type', 'status', 'caption', 'first_comment',
        'scheduled_for', 'published_at', 'ig_media_id', 'permalink',
        'error_message', 'retry_count', 'source', 'created_by',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'published_at'  => 'datetime',
    ];

    public const TYPES = [
        'feed_image' => 'Imagem (feed)',
        'carousel'   => 'Carrossel',
        'reel'       => 'Reel',
        'story'      => 'Story',
    ];

    public const STATUSES = [
        'rascunho'   => 'Rascunho',
        'aprovado'   => 'Aprovado',
        'agendado'   => 'Agendado',
        'publicando' => 'Publicando',
        'publicado'  => 'Publicado',
        'falhou'     => 'Falhou',
    ];

    public function account()
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }

    public function media()
    {
        return $this->hasMany(SocialPostMedia::class)->orderBy('sort_order');
    }

    /** Posts vencidos e prontos para publicar (usado pela Fase 3). */
    public function scopeDue($q)
    {
        return $q->where('status', 'agendado')
                 ->whereNotNull('scheduled_for')
                 ->where('scheduled_for', '<=', now());
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Cor (hex) do badge por status. */
    public function statusColor(): string
    {
        return [
            'rascunho'   => '#97A3B8',
            'aprovado'   => '#FFB547',
            'agendado'   => '#00A3FF',
            'publicando' => '#7C5CFF',
            'publicado'  => '#2BD9A1',
            'falhou'     => '#FF5C7A',
        ][$this->status] ?? '#97A3B8';
    }

    /** URL da primeira mídia (para thumbnail). */
    public function thumb(): ?string
    {
        $m = $this->media->first();
        return $m ? $m->url() : null;
    }
}
