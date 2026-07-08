<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'student_id', 'status', 'start_date', 'end_date',
        'plano', 'observacao', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    /** True se a assinatura está ativa e dentro da validade. */
    public function estaVigente(): bool
    {
        return $this->status === 'ativo'
            && $this->end_date
            && $this->end_date->endOfDay()->gte(Carbon::now());
    }

    /** Rótulo do estado real (considera validade). */
    public function estadoLabel(): string
    {
        if ($this->status === 'cancelado') {
            return 'Cancelada';
        }
        return $this->estaVigente() ? 'Ativa' : 'Expirada';
    }

    public function estadoColor(): string
    {
        return [
            'Ativa'     => '#2BD9A1',
            'Expirada'  => '#FFB547',
            'Cancelada' => '#FF5C7A',
        ][$this->estadoLabel()] ?? '#8A94A6';
    }

    /** Dias restantes até expirar (negativo se já expirou). */
    public function diasRestantes(): int
    {
        if (!$this->end_date) {
            return 0;
        }
        return (int) Carbon::now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    /** Assinaturas vigentes (ativas e não expiradas). */
    public function scopeVigentes($query)
    {
        return $query->where('status', 'ativo')
            ->whereDate('end_date', '>=', Carbon::now()->toDateString());
    }
}
