<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessarEventoWhatsapp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Webhook da Uazapi — INSTANCIA DE TESTE.
 *
 * Faz tres coisas e nada mais:
 *   1. valida o token (que a Uazapi manda no BODY, nao em header);
 *   2. persiste o payload cru, sincronamente, antes de responder;
 *   3. responde 200.
 *
 * Nenhuma logica de negocio aqui dentro. O processamento estruturado (Fatia 3)
 * roda em ProcessarEventoWhatsapp, despachado com ->afterResponse() DEPOIS da
 * gravacao do cru, com a varredura por cron (whatsapp:varrer) como rede de
 * seguranca (regra de ouro 2). O que nao pode falhar e a gravacao do cru.
 *
 * ORDEM IMPORTA: cru primeiro, sincrono; despacho depois. Se o despacho
 * falhar, o cru ja esta salvo e a varredura o repesca. Se fosse ao contrario,
 * uma falha na gravacao deixaria um job apontando para linha inexistente.
 *
 * Grupos (wa_isGroup, @g.us) sao persistidos como qualquer outro payload: o
 * filtro de grupo e de EXIBICAO, nunca de ingestao (regra de ouro 8).
 */
class UazapiWebhookController extends Controller
{
    public function receber(Request $request): JsonResponse
    {
        $this->validarToken($request);

        $cru = $request->getContent();
        abort_if($cru === '', 400, 'Payload vazio.');

        // insertOrIgnore faz a idempotencia (regra de ouro 3) sem caminho de
        // excecao: reenvio do mesmo message.id colide com o indice unico
        // `wa_raw_message_id`, vira zero linhas afetadas e resposta 200 normal.
        $inseridas = DB::table('whatsapp_raw_events')->insertOrIgnore([
            'message_id'  => $this->primeiroPresente($request, ['message.id', 'data.message.id']),
            'instance'    => $this->primeiroPresente($request, ['instance', 'instance_name', 'owner']),
            'event_type'  => $this->primeiroPresente($request, ['EventType', 'event_type', 'event', 'type']),
            'payload'     => $cru,
            'received_at' => now()->format('Y-m-d H:i:s.v'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Só despacha se ALGO foi inserido. Reenvio ignorado pelo índice único
        // já foi processado antes (ou será pela varredura) — reprocessar seria
        // trabalho à toa. `lastInsertId` só é confiável sob esta guarda: numa
        // linha ignorada não há id novo.
        if ($inseridas > 0) {
            ProcessarEventoWhatsapp::dispatch((int) DB::getPdo()->lastInsertId())
                ->afterResponse();
        }

        return response()->json(['ok' => true]);
    }

    // ───────────────────────────── HELPERS ─────────────────────────────

    /**
     * Padrao validarSecret() do repo (CourseVideoController:143-151), adaptado
     * para ler o token do BODY em vez do header.
     */
    private function validarToken(Request $request): void
    {
        $configurado = (string) config('uazapi.instance_token');

        // ARMADILHA: hash_equals('', '') e TRUE. Sem esta linha, um token de
        // config vazio faria um POST sem token nenhum passar. Config ausente e
        // erro de servidor — nunca autorizacao concedida.
        abort_if($configurado === '', 500, 'UAZAPI_INSTANCE_TOKEN nao configurado.');

        abort_unless(
            hash_equals($configurado, (string) $request->input('token')),
            401,
            'Token invalido.'
        );
    }

    /**
     * Primeiro valor escalar presente entre as chaves dadas, ou null.
     *
     * CONFERIDO EM 22/07/2026 contra o spec oficial (openapi-bundled.json,
     * uazapiGO 2.1.1, schema `WebhookEvent`): `instance` e `event` sao nomes
     * REAIS — o schema declara o corpo como `{event, instance, data}`, com
     * `event` num enum (message/status/presence/group/connection). `EventType`,
     * `event_type`, `type`, `instance_name` e `owner` continuam na lista porque
     * o mesmo schema diz que `data` "segue o que o backend envia em callHook" e
     * nao traz exemplo de payload — a doc nao descarta a forma plana que o
     * briefing descreveu.
     *
     * OLHO NO PLURAL: a configuracao do webhook usa `events: ["messages"]`,
     * enquanto o enum de `WebhookEvent` diz `"message"`. Ambiguidade a resolver
     * ao configurar a instancia de teste, nao aqui.
     *
     * Nada se perde se todos errarem: estas colunas sao conveniencia de indice,
     * e o `payload` cru continua sendo a autoridade.
     */
    private function primeiroPresente(Request $request, array $chaves): ?string
    {
        foreach ($chaves as $chave) {
            $valor = $request->input($chave);

            if (is_scalar($valor) && (string) $valor !== '') {
                return (string) $valor;
            }
        }

        return null;
    }
}
