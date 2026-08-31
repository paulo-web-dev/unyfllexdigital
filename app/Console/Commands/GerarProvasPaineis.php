<?php

namespace App\Console\Commands;

use App\Models\Panel;
use App\Models\PanelProva;
use App\Services\AssinanteCatalogoService;
use App\Services\PanelProvaService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

/**
 * Geração (em massa ou pontual) das provas dos painéis do catálogo do assinante.
 *
 * Retomável: painéis com prova 'pronto' ou 'gerando' são pulados, então rodar de
 * novo continua de onde parou. Use --sleep para não sobrecarregar o n8n.
 *
 *   php artisan paineis:gerar-provas --dry-run              # só lista
 *   php artisan paineis:gerar-provas --ids=123 --ids=456    # piloto
 *   php artisan paineis:gerar-provas --limit=50 --sleep=10  # massa, em lotes
 */
class GerarProvasPaineis extends Command
{
    protected $signature = 'paineis:gerar-provas
        {--ids=* : Gera só para estes painéis (repetível)}
        {--limit=0 : Máximo de disparos nesta execução (0 = sem limite)}
        {--sleep=8 : Segundos de espera entre disparos}
        {--callback-base= : Base pública do callback (padrão: cursos_modulares.public_base_url)}
        {--dry-run : Não dispara nada, só lista o que seria gerado}';

    protected $description = 'Dispara no n8n a geração das provas dos painéis do catálogo do assinante';

    public function handle(AssinanteCatalogoService $catalogo, PanelProvaService $service): int
    {
        $ids = array_map('intval', (array) $this->option('ids'));
        if (! $ids) {
            $ids = $catalogo->idsPaineisExibiveis();
        }

        $limit = (int) $this->option('limit');
        $sleep = max(0, (int) $this->option('sleep'));
        $dry   = (bool) $this->option('dry-run');
        $base  = $this->option('callback-base') ?: null;

        $this->info(count($ids) . ' painéis candidatos. Callback: ' . $service->callbackUrl($base));

        try {
            $existentes = PanelProva::whereIn('panel_id', $ids)
                ->whereIn('status', ['pronto', 'gerando'])
                ->pluck('status', 'panel_id');
        } catch (QueryException $e) {
            $this->error('Tabela panel_provas não existe — rode database/panel_provas.sql antes.');

            return self::FAILURE;
        }

        $disparados = 0;
        $pulados    = 0;
        $semFonte   = 0;
        $falhas     = 0;

        foreach ($ids as $id) {
            if ($limit > 0 && $disparados >= $limit) {
                $this->warn("Limite de {$limit} disparos atingido. Rode de novo para continuar.");
                break;
            }

            if (isset($existentes[$id])) {
                $pulados++;
                continue; // já pronta ou gerando — retomada
            }

            $panel = Panel::find($id);
            if (! $panel) {
                $this->warn("Painel #{$id} não encontrado — pulado.");
                continue;
            }

            $fonte = PanelProvaService::fonte($panel);
            if (mb_strlen($fonte) < PanelProvaService::MIN_FONTE) {
                $semFonte++;
                $this->line("· #{$id} sem resumo suficiente (" . mb_strlen($fonte) . ' chars) — pulado.');
                continue;
            }

            if ($dry) {
                $disparados++;
                $this->line("· #{$id} geraria: " . optional($panel->classes)->title . ' — ' . $panel->title);
                continue;
            }

            [$ok, $msg] = $service->gerar($panel, $base);
            if ($ok) {
                $disparados++;
                $this->info("✓ #{$id} disparado ({$disparados})");
            } else {
                $falhas++;
                $this->error("✗ #{$id}: {$msg}");
                if (str_contains($msg, 'panel_provas')) {
                    return self::FAILURE; // sem tabela, não adianta continuar
                }
            }

            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        $this->newLine();
        $this->info(($dry ? '[dry-run] ' : '') . "Disparados: {$disparados} · já prontos/gerando: {$pulados} · sem fonte: {$semFonte} · falhas: {$falhas}");

        return self::SUCCESS;
    }
}
