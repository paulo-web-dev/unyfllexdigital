<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Prova de um painel ("Curso Modular" da área do assinante).
 *
 * Uma linha por painel (UNIQUE panel_id); `content` guarda o JSON de questões no
 * mesmo formato das provas dos cursos modulares (course_materials type='prova'):
 * [{enunciado, alternativas[], correta, comentario}].
 * Status: gerando | pronto | erro. Tabela criada por database/panel_provas.sql.
 */
class PanelProva extends Model
{
    protected $table = 'panel_provas';

    protected $fillable = [
        'panel_id',
        'title',
        'content',
        'status',
        'version',
    ];

    public function panel()
    {
        return $this->belongsTo(Panel::class, 'panel_id');
    }

    /** Questões decodificadas (array vazio se o JSON for inválido). */
    public function questoes(): array
    {
        return json_decode((string) $this->content, true) ?: [];
    }
}
