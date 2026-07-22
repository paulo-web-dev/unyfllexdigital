<?php

namespace App\Console\Commands;

use App\Jobs\ProcessarEventoWhatsapp;
use App\Models\WhatsappRawEvent;
use Illuminate\Console\Command;

/**
 * Rede de segurança da regra de ouro 2.
 *
 * O caminho comum é o `afterResponse` do webhook processar o cru logo depois
 * da resposta HTTP. Ele é best-effort: se o processo morrer, se o php-fpm
 * reciclar, se a app cair entre a gravação e o processamento, o cru fica
 * parado com processed_at NULL. Este comando varre exatamente isso.
 *
 * Roda a cada minuto pelo scheduler. Processa INLINE (não enfileira): já
 * estamos fora do request, e enfileirar só adicionaria uma volta pela tabela
 * `jobs` sem ganho nenhum.
 */
class VarrerEventosCrus extends Command
{
    protected $signature = 'whatsapp:varrer {--limite=100} {--tentativas=3}';

    protected $description = 'Processa payloads crus da Uazapi ainda não processados (rede de segurança do afterResponse)';

    public function handle(): int
    {
        $limite     = (int) $this->option('limite');
        $tentativas = (int) $this->option('tentativas');

        // A condição de desistência é o ponto do comando. Sem
        // `process_attempts < N`, um payload que o parser não entende volta
        // para cá a cada minuto, para sempre, falhando sempre — um loop que
        // só aparece no log ou na carga do banco.
        $crus = WhatsappRawEvent::query()
            ->whereNull('processed_at')
            ->where('process_attempts', '<', $tentativas)
            ->orderBy('id')
            ->limit($limite)
            ->get();

        $ok = 0;

        foreach ($crus as $cru) {
            ProcessarEventoWhatsapp::dispatchSync($cru->id);

            // O job engole a exceção de propósito (grava process_error e
            // segue, para que um payload ruim não derrube a varredura
            // inteira). Então o sucesso se lê no banco, não no retorno.
            if ($cru->fresh()?->processed_at !== null) {
                $ok++;
            }
        }

        $falhas = $crus->count() - $ok;

        // Contar "varridos" como "processados" seria mentira: nas tentativas
        // que falham o evento e lido e nao processado.
        $this->info("Varredura: {$crus->count()} evento(s) lido(s), {$ok} processado(s), {$falhas} com falha.");

        // Visibilidade do que desistiu. Sem esta linha, um payload que falhou
        // 3 vezes sai silenciosamente do radar — que é justamente o defeito
        // que a coluna process_attempts existe para evitar, só que mais
        // devagar.
        $desistidos = WhatsappRawEvent::query()
            ->whereNull('processed_at')
            ->where('process_attempts', '>=', $tentativas)
            ->count();

        if ($desistidos > 0) {
            $this->warn("{$desistidos} evento(s) desistidos apos {$tentativas} tentativas. Ver process_error.");
        }

        return self::SUCCESS;
    }
}
