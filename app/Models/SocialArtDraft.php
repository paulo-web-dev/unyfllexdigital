<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialArtDraft extends Model
{
    protected $fillable = [
        'batch_id', 'social_account_id', 'ref', 'tipo', 'pedido', 'briefing',
        'versao', 'status', 'image_path', 'html', 'caption', 'first_comment',
        'foto_url', 'scheduled_date', 'horario', 'social_post_id',
    ];

    protected $casts = [
        'briefing'       => 'array',
        'scheduled_date' => 'date',
        'versao'         => 'integer',
    ];

    public function feedbacks()
    {
        return $this->hasMany(SocialArtFeedback::class);
    }

    /** URL pública da arte gerada. */
    public function url(): ?string
    {
        return $this->image_path ? asset($this->image_path) : null;
    }

    /** Rótulo amigável do formato. */
    public function tipoLabel(): string
    {
        return $this->tipo === 'story' ? 'Story' : 'Feed';
    }

    /** Cor do status para a UI. */
    public function statusColor(): string
    {
        return [
            'gerando'  => '#8A94A6',
            'revisao'  => '#00a3ff',
            'aprovado' => '#2BD9A1',
            'reprovado'=> '#FF5C7A',
        ][$this->status] ?? '#8A94A6';
    }

    /** Resumo do briefing para exibir no card. */
    public function briefingResumo(): string
    {
        $b = $this->briefing ?? [];
        $partes = [];
        if (!empty($b['objetivo'])) {
            $partes[] = $b['objetivo'];
        }
        if (!empty($b['estilo']) && $b['estilo'] !== 'auto') {
            $partes[] = ucfirst($b['estilo']);
        }
        if (!empty($b['tom'])) {
            $partes[] = $b['tom'];
        }
        if (isset($b['foto_real'])) {
            $partes[] = $b['foto_real']
                ? ('foto: ' . (!empty($b['categoria']) ? $b['categoria'] : 'qualquer'))
                : 'sem foto';
        }
        return implode(' · ', $partes);
    }
}
