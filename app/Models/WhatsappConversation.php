<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappConversation extends Model
{
    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'wa_chat_id',
        'chat_phone',
        'is_group',
        'nome_exibicao',
        'ultima_mensagem_em',
        'atendente_id',
        'atribuida_em',
        'atribuida_por_id',
    ];

    protected $casts = [
        'is_group'           => 'boolean',
        'ultima_mensagem_em' => 'datetime',
        'atribuida_em'       => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'conversation_id');
    }

    /**
     * Atendente responsável pela conversa (Fatia 7).
     *
     * Sem FK no banco (0005), então o id pode ficar órfão se o usuário for
     * removido — a relação devolve null e a tela diz "atendente removido".
     */
    public function atendente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendente_id');
    }

    /** Quem fez a atribuição — não necessariamente quem atende. */
    public function atribuidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atribuida_por_id');
    }

    /**
     * Rótulo humano do estado de atribuição — Fatia 5.
     *
     * Mora aqui, e não no Blade, porque ele é renderizado em DOIS lugares: na
     * carga da página e no payload do polling. Duas versões do mesmo texto
     * divergem na primeira mudança, e a divergência apareceria como "a tela
     * mudou sozinha para algo diferente do que o F5 mostra".
     */
    public function rotuloAtribuicao(): string
    {
        if ($this->atendente_id && ! $this->atendente) {
            // Sem FK no banco (0005): id órfão é estado previsto.
            return "Atendente removido (id {$this->atendente_id})";
        }

        if ($this->atendente) {
            return 'Atribuída a ' . $this->atendente->name
                . ($this->atribuida_em ? ' em ' . $this->atribuida_em->format('d/m/Y H:i') : '');
        }

        return 'Não atribuída';
    }

    /**
     * Impressão digital do estado de atribuição, para o polling detectar
     * mudança.
     *
     * É HASH DE PROPÓSITO, e isso não é decoração. O polling não pode
     * sincronizar o campo escondido `atendente_atual_id` do formulário — esse
     * campo é a guarda de sobrescrita da Fatia 7, e sincronizá-lo faria o
     * <select> desatualizado de alguém passar por cima da atribuição de outra
     * pessoa SEM o aviso. Devolvendo hash em vez do id, o JS não consegue
     * fazer isso nem por descuido: o dado necessário não está no payload.
     */
    public function assinaturaAtribuicao(): string
    {
        return md5($this->atendente_id . '|' . $this->atribuida_em?->toIso8601String());
    }

    /** Conversas de um atendente; null filtra as não atribuídas. */
    public function scopeAtribuidaA(Builder $query, ?int $userId): Builder
    {
        return $userId === null
            ? $query->whereNull('atendente_id')
            : $query->where('atendente_id', $userId);
    }
}
