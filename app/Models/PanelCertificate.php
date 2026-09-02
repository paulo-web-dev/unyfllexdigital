<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Certificado emitido de um painel ("Curso Modular" da área do assinante).
 *
 * Um por (student_id, panel_id); `aluno`, `titulo`, `horas` e `concluido_em` são
 * congelados na emissão (renomear turma/painel depois não muda o certificado).
 * `token` é o "Código de autenticidade" impresso no certificado, consultado
 * pela página pública /certificado/validar/{token} (CertificadoController).
 * Tabela criada por database/panel_certificates.sql.
 *
 * Regras (2026-09): libera só com melhor nota >= 70% na prova do painel.
 * Carga horária por tipo da turma: minissérie 12h; turma gravada
 * ("Curso Livre Aprofundado") 20h.
 */
class PanelCertificate extends Model
{
    /** Fração mínima de acertos na prova para liberar o certificado. */
    public const NOTA_MINIMA = 0.7;

    /** Carga horária (horas) do painel de minissérie. */
    public const HORAS_MINISSERIE = 12;

    /** Carga horária (horas) do painel de turma gravada ("Curso Livre Aprofundado"). */
    public const HORAS_GRAVADO = 20;

    /** @deprecated use horasPara(); mantido para leituras antigas. */
    public const HORAS = self::HORAS_MINISSERIE;

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

    /** Carga horária do certificado conforme o tipo da turma (classes.express). */
    public static function horasPara(Classes $classe): int
    {
        return (string) $classe->express === '1' ? self::HORAS_MINISSERIE : self::HORAS_GRAVADO;
    }

    public function panel()
    {
        return $this->belongsTo(Panel::class, 'panel_id');
    }
}
