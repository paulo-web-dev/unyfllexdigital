<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Certificado emitido de um painel ("Curso Modular" da área do assinante).
 *
 * Um por (student_id, panel_id); `aluno`, `titulo` e `concluido_em` são
 * congelados na emissão (renomear turma/painel depois não muda o certificado).
 * `token` fica reservado para a futura página pública de validação (pendência).
 * Tabela criada por database/panel_certificates.sql.
 */
class PanelCertificate extends Model
{
    /** Fração mínima de acertos na prova para liberar o certificado. */
    public const NOTA_MINIMA = 0.7;

    /** Carga horária (horas) do certificado por painel. */
    public const HORAS = 12;

    protected $table = 'panel_certificates';

    protected $fillable = [
        'student_id',
        'panel_id',
        'token',
        'aluno',
        'titulo',
        'horas',
        'score',
        'total',
        'concluido_em',
    ];

    protected $casts = ['concluido_em' => 'date'];
}
