<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Matrícula do aluno em um CURSO MODULAR (produto próprio).
 * Separada do enrollments acadêmico (que é por turma/Classes).
 * source: manual | compra   status: ativo | pendente | cancelado
 */
class ModularEnrollment extends Model
{
    protected $table = 'modular_enrollments';

    protected $fillable = [
        'modular_course_id',
        'student_id',
        'status',
        'source',
        'value',
        'transaction_code',
        'start_date',
        'end_date',
        'log',
    ];

    protected $casts = [
        'value'      => 'float',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public const STATUSES = [
        'ativo'     => 'Ativo',
        'pendente'  => 'Pendente',
        'cancelado' => 'Cancelado',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
    }

    /** Aluno (tabela students). */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    /** Usuário de login vinculado ao aluno (User.student_id). */
    public function aluno()
    {
        return $this->belongsTo(User::class, 'student_id', 'student_id');
    }

    public function scopeAtivo($q)
    {
        return $q->where('status', 'ativo');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }
}
