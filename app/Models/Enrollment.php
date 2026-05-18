<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'classes_id',
        'modality',
        'value',
        'discount',
        'final_value',
        'status',
        'payment_method',
        'start_date',
        'end_date',
        'payday',
        'invoice',
        'payment_slip',
        'transaction_code',
        'wallet',
        'company',
        'entidade',
        'plano',
        'id_antiga',
        'id_aluno_antigo',
        'log',
        'canceledLog',
        'canceledData',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'payday'      => 'date',
        'value'       => 'float',
        'discount'    => 'float',
        'final_value' => 'float',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    // ── Relacionamentos ───────────────────────────────────────────────────

    public function aluno()
    {
        return $this->belongsTo(User::class, 'student_id', 'student_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'classes_id', 'id');
    }
}
