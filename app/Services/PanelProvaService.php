<?php

namespace App\Services;

use App\Models\Panel;
use App\Models\PanelProva;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Geração da prova de um painel via n8n, reusando o workflow de prova dos
 * cursos modulares (mesmo webhook, mesmo formato de questões).
 *
 * Diferenças em relação ao fluxo modular (CourseMaterialController::gerarProva):
 *  - a fonte é panels.content (o resumo que o player exibe), não o asset 'resumo';
 *  - o payload manda course_id = ID DO PAINEL (o workflow ecoa esse campo) e
 *    callback_url apontando para POST /api/n8n/paineis/prova, que grava em
 *    panel_provas. Premissa: o workflow lê callback_url do payload (todos os
 *    disparos atuais o enviam). Se o callback estiver fixado no n8n, é preciso
 *    duplicar o workflow lá apontando para a rota nova.
 */
class PanelProvaService
{
    /** Tamanho mínimo (caracteres, sem HTML) do resumo do painel para gerar prova. */
    public const MIN_FONTE = 200;

    /** Texto-fonte da prova: panels.content sem HTML/entidades. */
    public static function fonte(Panel $panel): string
    {
        $texto = html_entity_decode((string) $panel->content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = trim(strip_tags($texto));

        return $texto === '-' ? '' : $texto;
    }

    /**
     * Dispara a geração da prova do painel.
     * Retorna [ok, mensagem]. Não lança exceção se a tabela ainda não existir.
     */
    public function gerar(Panel $panel, ?string $callbackBase = null): array
    {
        $fonte = self::fonte($panel);
        if (mb_strlen($fonte) < self::MIN_FONTE) {
            return [false, "Painel #{$panel->id} sem resumo suficiente (mínimo " . self::MIN_FONTE . ' caracteres em Conteúdo/Resumo).'];
        }

        try {
            $versao = ((int) PanelProva::where('panel_id', $panel->id)->max('version')) + 1;
            PanelProva::where('panel_id', $panel->id)->delete();
            $placeholder = PanelProva::create([
                'panel_id' => $panel->id,
                'title'    => 'Gerando...',
                'status'   => 'gerando',
                'version'  => $versao,
            ]);
        } catch (QueryException $e) {
            return [false, 'Tabela panel_provas não existe — rode database/panel_provas.sql antes de gerar.'];
        }

        $turma  = (string) optional($panel->classes)->title;
        $titulo = trim($panel->title) !== '' && trim($panel->title) !== '-' ? trim($panel->title) : "Painel {$panel->id}";

        $ok = $this->dispararN8n([
            // Ecoado pelo workflow de volta ao callback: aqui é o ID DO PAINEL.
            'course_id'    => $panel->id,
            'origem'       => 'painel',
            'title'        => trim($turma . ' — ' . $titulo, ' —'),
            'resumo'       => $fonte,
            'callback_url' => $this->callbackUrl($callbackBase),
            'version'      => $versao,
        ]);

        if (! $ok) {
            $placeholder->update(['status' => 'erro', 'title' => 'Falha ao acionar o n8n']);

            return [false, 'Não consegui acionar o n8n (prova do painel). Confira a URL do webhook.'];
        }

        return [true, "Geração da prova do painel #{$panel->id} disparada."];
    }

    /** URL pública do callback (o n8n precisa alcançá-la — localhost não funciona). */
    public function callbackUrl(?string $base = null): string
    {
        $base = $base ?: (string) config('cursos_modulares.public_base_url');
        $base = rtrim($base ?: url('/'), '/');

        return $base . '/api/n8n/paineis/prova';
    }

    /** Mesmo webhook de prova dos cursos modulares. */
    public function webhook(): string
    {
        return config('cursos_modulares.n8n_prova_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/prova';
    }

    private function dispararN8n(array $payload): bool
    {
        try {
            $resp = Http::withHeaders(['X-Webhook-Secret' => config('cursos_modulares.n8n_secret')])
                ->timeout(20)
                ->post($this->webhook(), $payload);

            return $resp->successful();
        } catch (\Throwable $e) {
            Log::warning('n8n prova painel: ' . $e->getMessage());

            return false;
        }
    }
}
