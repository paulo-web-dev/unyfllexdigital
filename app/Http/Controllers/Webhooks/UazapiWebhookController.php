<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
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
 * Nenhuma logica de negocio aqui dentro. O processamento estruturado entra na
 * Fatia 3, via afterResponse + varredura por cron como rede de seguranca
 * (regra de ouro 2). O que nao pode falhar e a gravacao do cru.
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
        DB::table('whatsapp_raw_events')->insertOrIgnore([
            'message_id'  => $this->primeiroPresente($request, ['message.id']),
            'instance'    => $this->primeiroPresente($request, ['instance', 'instance_name', 'owner']),
            'event_type'  => $this->primeiroPresente($request, ['EventType', 'event_type', 'event', 'type']),
            'payload'     => $cru,
            'received_at' => now()->format('Y-m-d H:i:s.v'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

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
     * Os nomes exatos de `instance` e `EventType` ainda NAO foram conferidos
     * contra um payload real da Uazapi — por isso a lista de candidatos. Nada
     * se perde se todos errarem: estas colunas sao conveniencia de indice, e o
     * `payload` cru continua sendo a autoridade. Reduzir a lista ao nome certo
     * assim que a primeira mensagem real chegar.
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
