<?php

namespace App\Console\Commands;

use App\Jobs\FilaPing;
use Illuminate\Console\Command;

class FilaPingCommand extends Command
{
    protected $signature   = 'fila:ping';
    protected $description = 'Despacha um job de diagnostico para provar que a fila esta de pe.';

    public function handle(): int
    {
        $agora = microtime(true);

        FilaPing::dispatch($agora);

        $this->info(sprintf('Job despachado em %.3f.', $agora));
        $this->line('Confira a linha em `jobs`, rode `php artisan schedule:run` e veja o log.');

        // Aviso util: com QUEUE_CONNECTION=sync o job ja rodou aqui mesmo, e o
        // teste nao prova nada sobre a fila.
        if (config('queue.default') === 'sync') {
            $this->warn('QUEUE_CONNECTION=sync — o job rodou inline, sem passar pela tabela `jobs`.');
        }

        return self::SUCCESS;
    }
}
