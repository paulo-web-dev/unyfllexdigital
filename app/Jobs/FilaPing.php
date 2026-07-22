<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job de diagnostico da fila. Nao faz trabalho de negocio: so registra quando
 * rodou, para provar que enfileiramento + scheduler + worker estao de pe.
 *
 * Fica no repo porque a Fatia 3 reusa este par (job + comando) para aferir se
 * dispatch()->afterResponse() realmente devolve a resposta antes de trabalhar
 * sob php-fpm.
 */
class FilaPing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private float $despachadoEm)
    {
    }

    public function handle(): void
    {
        $agora = microtime(true);

        Log::info('fila:ping executado', [
            'despachado_em' => $this->despachadoEm,
            'executado_em'  => $agora,
            'atraso_s'      => round($agora - $this->despachadoEm, 3),
        ]);
    }
}
