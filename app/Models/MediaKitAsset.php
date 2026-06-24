<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Peças de divulgação geradas de um curso modular: card (feed) e story.
 * Um registro por (curso, tipo). Imagem salva em public/storage/media-kit/...
 * Tabela: media_kit_assets (criar via SQL).
 */
class MediaKitAsset extends Model
{
    use HasFactory;

    protected $table = 'media_kit_assets';

    public const STATUSES = [
        'gerando'             => 'Gerando',
        'aguardando_revisao'  => 'Aguardando revisão',
        'aprovado'            => 'Aprovado',
        'reprovado'           => 'Reprovado',
    ];

    public const TIPOS = [
        'card'  => 'Card (feed)',
        'story' => 'Story (vertical)',
    ];

    protected $fillable = [
        'modular_course_id',
        'type',
        'image_path',
        'caption',
        'status',
        'feedback',
        'version',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
    }

    public function typeLabel(): string
    {
        return self::TIPOS[$this->type] ?? ucfirst((string) $this->type);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /** URL pública da imagem, montada a partir da base pública configurada. */
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
