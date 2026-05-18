<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'name',
        'fingerprint',
        'cpf',
        'password',
        'email',
        'phone',
        'cnpj',
        'cep',
        'street',
        'house_number',
        'district',
        'city',
        'state',
        'photo',
        'status',
        'nascimento',
        'cargo',
        'entidade',
        'pos',
        'minisserie',
        'instagram',
        'avaliacao',
        'log',
    ];

    protected $hidden = ['password', 'fingerprint', 'created_at', 'updated_at'];

    protected $casts = [
        'nascimento' => 'date',
        'minisserie' => 'string',
        'avaliacao'  => 'string',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────────

    public function user()
    {
        return $this->hasOne(User::class, 'student_id', 'id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id', 'id');
    }
}
