<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Curso Modular — produto novo (apostila PDF -> curso modular).
 * Tabela: modular_courses (criar via SQL no phpMyAdmin, sem migration).
 */
class ModularCourse extends Model
{
    use HasFactory;

    protected $table = 'modular_courses';

    /** Fluxo de status: rascunho -> processando -> publicado. */
    public const STATUSES = [
        'rascunho'    => 'Rascunho',
        'processando' => 'Processando',
        'publicado'   => 'Publicado',
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'apostila_path',
        'apostila_original_name',
        'apostila_mime',
        'apostila_size',
        'pages',
        'status',
        'log',
    ];

    protected $casts = [
        'apostila_size' => 'integer',
        'pages'         => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    /** Preenche o campo log com o nome do admin (mesmo padrao do model Panel). */
    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($m) => $m->log = Auth::check() ? Auth::user()->name : '');
        static::updating(fn ($m) => $m->log = Auth::check() ? Auth::user()->name : '');
    }

    /** Rotulo amigavel do status. */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /** Tamanho do arquivo da apostila formatado (KB / MB). */
    public function sizeHuman(): string
    {
        $bytes = (int) $this->apostila_size;
        if ($bytes <= 0) {
            return '—';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }
        return number_format($bytes / 1024, 0, ',', '.') . ' KB';
    }

    /** True se ja existe uma apostila anexada. */
    public function hasApostila(): bool
    {
        return ! empty($this->apostila_path);
    }

    /** Conteúdo gerado (resumo / podcast / vídeo) deste curso. */
    public function assets()
    {
        return $this->hasMany(ModularCourseAsset::class, 'modular_course_id');
    }

    /** Peças de divulgação (card / story) deste curso. */
    public function mediaKitAssets()
    {
        return $this->hasMany(MediaKitAsset::class, 'modular_course_id');
    }

    /** Áudio do podcast deste curso (um por curso). */
    public function podcastAudios()
    {
        return $this->hasMany(PodcastAudio::class, 'modular_course_id');
    }

    public function courseMaterials()
    {
        return $this->hasMany(CourseMaterial::class, 'modular_course_id');
    }

    public function adCreatives()
    {
        return $this->hasMany(AdCreative::class, 'modular_course_id');
    }

    public function coverArt()
    {
        return $this->hasMany(CourseCover::class, 'modular_course_id');
    }

    /** Vídeo(s) de resumo (estilo NotebookLM) deste curso. */
    public function videos()
    {
        return $this->hasMany(CourseVideo::class, 'modular_course_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ModularEnrollment::class, 'modular_course_id');
    }

    /** URL pública do card (usado como capa do curso no admin), se existir. */
    public function cardImageUrl(): ?string
    {
        $card = $this->mediaKitAssets->firstWhere('type', 'card');
        if (! $card || empty($card->image_path)) {
            return null;
        }
        $base = rtrim((string) config('cursos_modulares.public_base_url'), '/');
        return $base . '/' . ltrim($card->image_path, '/');
    }

    /** URL pública da apostila, montada a partir da base pública configurada. */
    public function apostilaUrl(): ?string
    {
        if (empty($this->apostila_path)) {
            return null;
        }
        if (Str::startsWith($this->apostila_path, ['http://', 'https://'])) {
            return $this->apostila_path;
        }
        $base = rtrim((string) config('cursos_modulares.public_base_url'), '/');
        return $base . '/' . ltrim($this->apostila_path, '/');
    }
}
