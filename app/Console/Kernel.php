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
        // Worker de fila. Nao ha supervisor em producao: o cron chama
        // `schedule:run` a cada minuto e o worker esvazia a fila e morre.
        // --max-time=50 mantem a janela abaixo do minuto; withoutOverlapping
        // impede dois workers concorrentes quando um ciclo estoura.
        $schedule->command('queue:work --stop-when-empty --max-time=50')
                 ->everyMinute()
                 ->withoutOverlapping();

        // Rede de seguranca da regra de ouro 2: o afterResponse do webhook e
        // best-effort, e esta varredura repesca o cru que ficou sem
        // processamento. Nao substitui o afterResponse — cobre a falha dele.
        $schedule->command('whatsapp:varrer')
                 ->everyMinute()
                 ->withoutOverlapping();
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
