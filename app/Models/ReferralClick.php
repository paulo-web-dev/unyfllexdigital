<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralClick extends Model
{
    protected $table = 'referral_clicks';

    protected $fillable = [
        'token',
        'ip',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'date',
    ];
}
