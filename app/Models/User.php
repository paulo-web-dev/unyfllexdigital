<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /*
    |----------------------------------------------------------------------
    | Tabela: users
    | Campos reais conforme banco de dados do projeto
    |----------------------------------------------------------------------
    | id, name, cpf, power, teacher_id, student_id, embaixador_id,
    | corporativo_id, email, telefone, setor, funcao, photo, avatar,
    | meta, email_verified_at, password, two_factor_secret,
    | two_factor_recovery_codes, ...
    |----------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'password',
        'telefone',
        'setor',
        'funcao',
        'photo',
        'avatar',
        'power',
        'teacher_id',
        'student_id',
        'embaixador_id',
        'corporativo_id',
        'meta',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'meta'              => 'integer',
        'power'             => 'integer',
    ];

    /*
    |----------------------------------------------------------------------
    | Helpers de perfil
    |----------------------------------------------------------------------
    */

    /**
     * Retorna as iniciais do nome para o avatar (ex: "MA").
     */
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', trim($this->name));
        $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
        $last  = strtoupper(substr(end($parts) ?? 'X', 0, 1));
        return $first === $last ? $first : $first . $last;
    }

    /**
     * Primeiro nome para saudações ("Olá, Maria").
     */
    public function getFirstNameAttribute(): string
    {
        return explode(' ', trim($this->name))[0] ?? 'Usuário';
    }

    /**
     * Cargo/função formatado para o chip do topbar.
     */
    public function getRoleAttribute(): string
    {
        return $this->funcao ?? $this->setor ?? 'Servidor público';
    }

    /**
     * URL do avatar — usa photo se disponível, senão as iniciais.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->photo
            ? 'https://unyflex.com.br/storage/usuarios/' . $this->photo
            : null;
    }

    /*
    |----------------------------------------------------------------------
    | Assinatura
    |----------------------------------------------------------------------
    */

    /**
     * True se o aluno vinculado (students) tem assinatura ativa e dentro da validade.
     * Regra única usada por login, redirects e área do assinante.
     */
    public function assinaturaVigente(): bool
    {
        if (! $this->student_id) {
            return false;
        }
        $student = Student::find($this->student_id);
        return $student ? $student->isAssinante() : false;
    }

    /** Rota "home" do usuário: área do assinante ou AVA de matrículas. */
    public function rotaHome(): string
    {
        return $this->assinaturaVigente() ? route('assinante.home') : route('dashboard');
    }
}
