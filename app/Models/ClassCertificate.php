<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Certificado da TURMA inteira ("Curso Livre Aprofundado" da área do assinante).
 *
 * Um por (student_id, classes_id); emitido quando o aluno atinge a nota mínima
 * (PanelCertificate::NOTA_MINIMA) na prova de TODOS os painéis da turma que têm
 * prova pronta. `aluno`, `titulo`, `horas` e `concluido_em` são congelados na
 * emissão. `token` é o "Código de autenticidade" validado pela mesma página
 * pública dos certificados por painel (CertificadoController).
 * Tabela criada por database/class_certificates.sql.
 */
class ClassCertificate extends Model
{
    /** Carga horária (horas) do certificado de turma — fixa, não proporcional. */
    public const HORAS = 20;

    protected $table = 'class_certificates';

    protected $fillable = [
        'student_id',
        'classes_id',
        'token',
        'aluno',
        'titulo',
        'horas',
        'provas_total',
        'provas_aprovadas',
        'concluido_em',
    ];

    protected $casts = ['concluido_em' => 'date'];

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'classes_id');
    }
}
