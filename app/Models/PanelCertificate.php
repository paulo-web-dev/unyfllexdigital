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
 * Regras (2026-09-02): libera só com melhor nota >= 70% na prova do painel; vale
 * para painel de minissérie ("Curso Minissérie") e de turma gravada só de painéis
 * de 1 aula ("Curso Modular"), sempre 12h. Turma gravada com algum painel de mais
 * de 1 aula ("Curso Livre Aprofundado") NÃO emite por painel: o certificado é da
 * turma inteira (ClassCertificate, 20h).
 */
class PanelCertificate extends Model
{
    /** Fração mínima de acertos na prova para liberar o certificado (painel e turma). */
    public const NOTA_MINIMA = 0.7;

    /** Carga horária (horas) do certificado por painel. */
    public const HORAS_PAINEL = 12;

    /** @deprecated use HORAS_PAINEL; mantido para leituras antigas. */
    public const HORAS = self::HORAS_PAINEL;

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

    public function panel()
    {
        return $this->belongsTo(Panel::class, 'panel_id');
    }
}
