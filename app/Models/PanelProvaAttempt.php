<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tentativa de prova de painel pelo aluno/assinante.
 * Espelho de ModularProvaAttempt com panel_id no lugar de modular_course_id.
 * Tabela criada por database/panel_provas.sql.
 */
class PanelProvaAttempt extends Model
{
    protected $table = 'panel_prova_attempts';

    protected $fillable = [
        'panel_id',
        'student_id',
        'score',
        'total',
        'answers',
    ];

    protected $casts = [
        'score' => 'integer',
        'total' => 'integer',
    ];

    public function panel()
    {
        return $this->belongsTo(Panel::class, 'panel_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    /** Percentual de acerto (0-100). */
    public function percent(): int
    {
        return $this->total > 0 ? (int) round(($this->score / $this->total) * 100) : 0;
    }
}
