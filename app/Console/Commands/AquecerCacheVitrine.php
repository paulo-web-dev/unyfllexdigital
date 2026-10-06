<?php

namespace App\Console\Commands;

use App\Services\AssinaturaVitrineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Recalcula e regrava os caches da vitrine pública da assinatura (/assinatura).
 *
 * As páginas (home, planos, categorias e cada categoria) não têm cache próprio: todas
 * derivam de dois snapshots do AssinaturaVitrineService (contadores e cards do catálogo).
 * O comando regrava os dois — e o meta() da área do assinante, que é a fonte dos
 * contadores — e depois monta os dados de cada página para conferir que saem do cache.
 *
 * Agendado no Kernel a cada 50 min (TTL de 60 min): o visitante nunca paga o cálculo.
 *
 *   php artisan vitrine:aquecer-cache
 */
class AquecerCacheVitrine extends Command
{
    protected $signature = 'vitrine:aquecer-cache';

    protected $description = 'Recalcula e regrava os caches da vitrine da Assinatura Premium (/assinatura)';

    public function handle(AssinaturaVitrineService $vitrine): int
    {
        $inicio = microtime(true);

        try {
            $vitrine->aquecer();

            // Daqui em diante tudo sai do cache recém-gravado.
            $resumo     = $vitrine->resumo();
            $carrosseis = $vitrine->carrosseis();
            $planos     = $vitrine->planos();
            $categorias = $vitrine->categorias();

            $linhas = $categorias->map(fn ($cat) => [
                $cat->slug,
                $cat->cursos,
                $vitrine->cursosDaCategoria($cat, '', 1)->total(),
            ])->all();
        } catch (\Throwable $e) {
            Log::error('vitrine:aquecer-cache falhou: ' . $e->getMessage(), ['exception' => $e]);
            $this->error('Falhou: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Cache da vitrine regravado em %.1fs — %d cursos (%s), %d apostilas, %d categorias, %d carrosséis, %d planos.',
            microtime(true) - $inicio,
            $resumo['cursos'],
            $resumo['cursos_marketing'],
            $resumo['apostilas'],
            $categorias->count(),
            $carrosseis->count(),
            count($planos)
        ));

        if ($this->output->isVerbose()) {
            $this->table(['categoria', 'contagem', 'cards na página'], $linhas);
        }

        return self::SUCCESS;
    }
}
