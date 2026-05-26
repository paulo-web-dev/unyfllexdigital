<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('token', 100)->index();   // nome ou token do vendedor
            $table->string('ip', 45)->nullable();     // IPv4/IPv6
            $table->date('clicked_at')->nullable();   // para deduplicar por dia
            $table->timestamps();

            // Índice único: 1 clique por IP por token por dia
            $table->unique(['token', 'ip', 'clicked_at'], 'ref_token_ip_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_clicks');
    }
};
