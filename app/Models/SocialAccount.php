<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table = 'social_accounts';

    protected $fillable = [
        'platform', 'name', 'ig_user_id', 'fb_page_id',
        'access_token', 'token_expires_at', 'status',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(SocialPost::class);
    }

    /** Dias restantes até o token expirar (null se não houver data). */
    public function tokenDaysLeft(): ?int
    {
        if (!$this->token_expires_at) {
            return null;
        }
        return (int) now()->diffInDays($this->token_expires_at, false);
    }

    /** Situação do token: sem_token | ok | expirando | expirado. */
    public function tokenStatus(): string
    {
        if (!$this->access_token) {
            return 'sem_token';
        }
        $days = $this->tokenDaysLeft();
        if ($days === null) {
            return 'ok';
        }
        if ($days < 0) {
            return 'expirado';
        }
        if ($days <= 7) {
            return 'expirando';
        }
        return 'ok';
    }

    /** Token mascarado para exibição no painel (nunca mostra o token inteiro). */
    public function maskedToken(): string
    {
        if (!$this->access_token) {
            return '—';
        }
        $t = $this->access_token;
        return substr($t, 0, 6) . '…' . substr($t, -4);
    }
}
