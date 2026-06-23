<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Conteúdo gerado de um curso modular: resumo, roteiro de podcast e
 * roteiro de vídeo. Um registro por (curso, tipo) — atualizado a cada
 * geração/refação. Tabela: modular_course_assets (criar via SQL).
 */
class ModularCourseAsset extends Model
{
    use HasFactory;

    protected $table = 'modular_course_assets';

    /** Fluxo: gerando -> aguardando_revisao -> aprovado | reprovado(->gerando). */
    public const STATUSES = [
        'gerando'             => 'Gerando',
        'aguardando_revisao'  => 'Aguardando revisão',
        'aprovado'            => 'Aprovado',
        'reprovado'           => 'Reprovado',
    ];

    protected $fillable = [
        'modular_course_id',
        'type',
        'content',
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

    /** Rótulo amigável do tipo (Resumo, Roteiro de Podcast, ...). */
    public function typeLabel(): string
    {
        return config('cursos_modulares.tipos')[$this->type] ?? ucfirst((string) $this->type);
    }

    /** Rótulo amigável do status. */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function hasContent(): bool
    {
        return ! empty(trim((string) $this->content));
    }
}
