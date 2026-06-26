<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vídeo de resumo (estilo NotebookLM: slides narrados) de um curso modular.
 *
 * O .mp4 fica hospedado no video-assembler (VPS), servido por HTTPS em
 * https://videos.unygov.com.br/videos/...  — aqui guardamos apenas a URL.
 * Um vídeo por curso (updateOrCreate por modular_course_id).
 */
class CourseVideo extends Model
{
    protected $fillable = [
        'modular_course_id',
        'title',
        'video_url',
        'duration',
        'slides',
        'status',
        'feedback',
        'version',
    ];

    protected $casts = [
        'duration' => 'integer',
        'slides'   => 'integer',
        'version'  => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
    }

    /** True se já existe um vídeo pronto com URL. */
    public function hasVideo(): bool
    {
        return $this->status === 'pronto' && ! empty($this->video_url);
    }

    /** Duração formatada (mm:ss), se conhecida. */
    public function durationHuman(): ?string
    {
        $s = (int) $this->duration;
        if ($s <= 0) {
            return null;
        }
        return sprintf('%d:%02d', intdiv($s, 60), $s % 60);
    }
}
