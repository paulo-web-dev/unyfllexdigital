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

    /** Conversas de um atendente; null filtra as não atribuídas. */
    public function scopeAtribuidaA(Builder $query, ?int $userId): Builder
    {
        return $userId === null
            ? $query->whereNull('atendente_id')
            : $query->where('atendente_id', $userId);
    }
}
