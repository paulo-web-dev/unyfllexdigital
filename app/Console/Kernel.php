<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Vitrine /assinatura: regrava os caches (TTL 60 min) a cada 50 min.
        // Cron não tem "a cada 50 min" ('*/50' = minutos 0 e 50, com intervalo de 10 min entre eles);
        // por isso roda a cada minuto e só passa quando o minuto da época é múltiplo de 50.
        $schedule->command('vitrine:aquecer-cache')
            ->everyMinute()
            ->when(fn () => intdiv(time(), 60) % 50 === 0)
            ->withoutOverlapping(30)
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
