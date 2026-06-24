<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Áudio do podcast de um curso modular (gerado pelo Gemini TTS a partir do
 * roteiro de podcast). Um registro por curso. Arquivo .wav salvo em
 * public/storage/podcast-audio/... Tabela: podcast_audios (criar via SQL).
 */
class PodcastAudio extends Model
{
    use HasFactory;

    protected $table = 'podcast_audios';

    public const STATUSES = [
        'gerando' => 'Gerando',
        'pronto'  => 'Pronto',
        'erro'    => 'Erro',
    ];

    protected $fillable = [
        'modular_course_id',
        'audio_path',
        'status',
        'version',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /** URL pública do áudio, montada a partir da base pública configurada. */
    public function audioUrl(): ?string
    {
        if (empty($this->audio_path)) {
            return null;
        }
        $base = rtrim((string) config('cursos_modulares.public_base_url'), '/');
        return $base . '/' . ltrim($this->audio_path, '/');
    }

    public function hasAudio(): bool
    {
        return ! empty($this->audio_path);
    }
}
