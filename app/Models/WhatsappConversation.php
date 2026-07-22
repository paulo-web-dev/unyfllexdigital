<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'is_group'           => 'boolean',
        'ultima_mensagem_em' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'conversation_id');
    }
}
