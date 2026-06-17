<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadGuia extends Model
{
    protected $table = 'leads_guia_licitacoes';

    protected $fillable = [
        'nome', 'email', 'whatsapp', 'cidade', 'cargo',
        'origem', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
        'contatado', 'contatado_em', 'baixou', 'baixado_em', 'observacoes',
        'ip', 'user_agent',
    ];

    protected $casts = [
        'contatado'    => 'boolean',
        'baixou'       => 'boolean',
        'contatado_em' => 'datetime',
        'baixado_em'   => 'datetime',
    ];

    /**
     * Link direto do WhatsApp do lead (somente digitos, com 55 + DDD).
     */
    public function whatsappLink(): string
    {
        $num = preg_replace('/\D/', '', (string) $this->whatsapp);
        if (strlen($num) <= 11) {
            $num = '55' . $num;
        }
        return 'https://wa.me/' . $num;
    }
}
