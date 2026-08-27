<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vínculo da estrutura AMAI (master / ponto focal / usuário).
 * Tabela: amai_vinculos — criada por SQL manual (database/amai_vinculos.sql), sem migration.
 * Remoção é lógica (removed_at): libera vaga e preserva histórico de uso.
 */
class AmaiVinculo extends Model
{
    protected $table = 'amai_vinculos';

    public const MASTER      = 'master';
    public const PONTO_FOCAL = 'ponto_focal';
    public const USUARIO     = 'usuario';

    protected $fillable = [
        'user_id', 'papel', 'municipio', 'parent_user_id', 'cota',
        'created_by', 'removed_at', 'removed_by',
    ];

    protected $casts = [
        'cota'       => 'integer',
        'removed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pontoFocal()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    /** Vínculos ativos (não removidos). */
    public function scopeAtivos($query)
    {
        return $query->whereNull('removed_at');
    }

    public function scopeUsuarios($query)
    {
        return $query->where('papel', self::USUARIO);
    }

    public function scopePontosFocais($query)
    {
        return $query->where('papel', self::PONTO_FOCAL);
    }

    public function isMaster(): bool
    {
        return $this->papel === self::MASTER;
    }

    public function isPontoFocal(): bool
    {
        return $this->papel === self::PONTO_FOCAL;
    }

    public function isUsuario(): bool
    {
        return $this->papel === self::USUARIO;
    }
}
