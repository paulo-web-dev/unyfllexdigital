<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelEvent extends Model
{
    protected $table = 'funnel_events';

    protected $fillable = [
        'session_id',
        'etapa',
        'origem',
        'referral',
        'classes_id',
        'ip',
        'cidade',
        'estado',
        'pais',
        'user_agent',
    ];

    // Etapas em ordem do funil
    const ETAPAS = [
        'visita',
        'visualizou',
        'carrinho',
        'checkout',
        'pagamento',
        'converteu',
    ];
}
