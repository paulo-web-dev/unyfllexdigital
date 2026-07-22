<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappRawEvent extends Model
{
    protected $table = 'whatsapp_raw_events';

    protected $fillable = [
        'message_id',
        'instance',
        'event_type',
        'payload',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'received_at'  => 'datetime',
        'processed_at' => 'datetime',
    ];
}
