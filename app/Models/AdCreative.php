<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Criativos de anúncio (ADS) de um curso modular.
 * slug: feed | story | whatsapp | prova (extensível).
 */
class AdCreative extends Model
{
    protected $fillable = [
        'modular_course_id',
        'slug',
        'title',
        'image_path',
        'caption',
        'status',
        'version',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    /** slug => rótulo amigável (e dimensão informativa). */
    public const SLUGS = [
        'feed'     => 'Meta Ads — Feed (1080×1080)',
        'story'    => 'Meta Ads — Stories/Reels (1080×1920)',
        'whatsapp' => 'WhatsApp — Lista (1080×1350)',
        'prova'    => 'Prova social (1080×1080)',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
    }

    public function tituloLabel(): string
    {
        return $this->title ?: (self::SLUGS[$this->slug] ?? ucfirst((string) $this->slug));
    }

    public function imageUrl(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }
        $base = rtrim((string) config('cursos_modulares.public_base_url'), '/');
        return $base . '/' . ltrim($this->image_path, '/');
    }

    public function hasImage(): bool
    {
        return ! empty($this->image_path);
    }
}
