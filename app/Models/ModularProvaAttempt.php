<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro de tentativa de prova de um curso modular pelo aluno.
 */
class ModularProvaAttempt extends Model
{
    protected $table = 'modular_prova_attempts';

    protected $fillable = [
        'modular_course_id',
        'student_id',
        'score',
        'total',
        'answers',
    ];

    protected $casts = [
        'score' => 'integer',
        'total' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
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
