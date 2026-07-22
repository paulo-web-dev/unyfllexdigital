<?php

namespace Tests\Feature;

use App\Services\Whatsapp\UazapiProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Fatia 6 — caminho de envio, com o portao FECHADO.
 *
 * Nao toca banco e nao usa dado real: so Http::fake() e config(). Por isso este
 * teste FICA no repo, diferente dos descartaveis das fatias anteriores.
 *
 * A assercao que mais importa aqui nao e sobre o que o codigo faz, e sobre o
 * que ele NAO faz: Http::assertNothingSent() em todo caminho de recusa. Um
 * teste que so verificasse a excecao passaria mesmo se o pacote tivesse saido
 * antes dela.
 */
class UazapiEnvioTest extends TestCase
{
    private const TEL = '5511987654321';

    protected function setUp(): void
    {
        parent::setUp();

        // Instancia de TESTE. O nome nao pode colidir com a denylist, senao a
        // guarda de ambiente dispara e mascara o que se quer medir.
        config([
            'uazapi.base_url'       => 'https://teste.uazapi.example',
            'uazapi.instance_name'  => 'instancia-de-teste',
            'uazapi.instance_token' => 'token-de-teste',
            'uazapi.prod_instances' => ['instancia-de-producao'],
            'uazapi.envio_habilitado' => false,
        ]);

        Http::preventStrayRequests();
    }

    private function provedor(): UazapiProvider
    {
        // Instanciado direto (nao pelo container) para que cada teste veja a
        // config que acabou de definir — o construtor le config no momento da
        // criacao.
        return new UazapiProvider();
    }

    private function abrirPortao(): void
    {
        config(['uazapi.envio_habilitado' => true]);
    }

    // ─────────────────────────── O PORTAO ───────────────────────────

    public function test_portao_fechado_por_padrao_e_nada_sai(): void
    {
        Http::fake();

        $this->expectException(\LogicException::class);

        try {
            $this->provedor()->enviarTexto(self::TEL, 'oi');
        } finally {
            Http::assertNothingSent();
        }
    }

    /**
     * A trava que custou ~23h de WhatsApp: abrir o portao NAO pode liberar a
     * instancia de producao fora de producao.
     */
    public function test_portao_aberto_nao_libera_instancia_de_producao(): void
    {
        $this->abrirPortao();
        config(['uazapi.instance_name' => 'instancia-de-producao']);
        Http::fake();

        $this->expectException(\RuntimeException::class);

        try {
            $this->provedor()->enviarTexto(self::TEL, 'oi');
        } finally {
            Http::assertNothingSent();
        }
    }

    /**
     * REGRESSAO DE UM BURACO REAL, achado por sonda em 22/07/2026.
     *
     * O provedor e construido com a instancia de teste (construtor aprova) e a
     * config muda para a de producao DEPOIS. Enquanto a guarda comparava
     * $this->instancia — congelada na construcao — este caminho ENVIAVA.
     *
     * O teste acima nao pegava isso: la a config ja estava errada antes do
     * construtor, entao quem recusava era o construtor, e a guarda do envio
     * nunca era exercitada.
     */
    public function test_troca_de_instancia_depois_de_construido_ainda_e_recusada(): void
    {
        $this->abrirPortao();
        $provedor = $this->provedor();          // construido com a de teste

        config(['uazapi.instance_name' => 'instancia-de-producao']);
        Http::fake();

        $this->expectException(\RuntimeException::class);

        try {
            $provedor->enviarTexto(self::TEL, 'oi');
        } finally {
            Http::assertNothingSent();
        }
    }

    // ─────────────────────────── CAMINHO FELIZ ───────────────────────────

    public function test_envia_texto_e_devolve_id(): void
    {
        $this->abrirPortao();

        Http::fake([
            '*/send/text' => Http::response(['id' => 'r1a2b3c4', 'messageid' => 'ABCD1234'], 200),
        ]);

        $id = $this->provedor()->enviarTexto(self::TEL, 'ola, tudo bem?');

        $this->assertSame('r1a2b3c4', $id);

        Http::assertSent(function (Request $req) {
            return $req->url() === 'https://teste.uazapi.example/send/text'
                && $req->method() === 'POST'
                // TOKEN EM HEADER — no webhook ele vem no body. E a assimetria
                // mais facil de errar neste arquivo.
                && $req->header('token') === ['token-de-teste']
                && $req->data() === ['number' => self::TEL, 'text' => 'ola, tudo bem?'];
        });
    }

    public function test_usa_messageid_quando_nao_ha_id(): void
    {
        $this->abrirPortao();
        Http::fake(['*/send/text' => Http::response(['messageid' => 'ABCD1234'], 200)]);

        $this->assertSame('ABCD1234', $this->provedor()->enviarTexto(self::TEL, 'oi'));
    }

    /**
     * 200 sem id nenhum: NAO lanca. Lancar faria o chamador reenviar e duplicar
     * mensagem para uma pessoa real.
     */
    public function test_resposta_sem_id_nao_lanca_e_devolve_vazio(): void
    {
        $this->abrirPortao();
        Http::fake(['*/send/text' => Http::response(['response' => ['status' => 'success']], 200)]);

        $this->assertSame('', $this->provedor()->enviarTexto(self::TEL, 'oi'));
    }

    // ─────────────────────────── CONFIG AUSENTE ───────────────────────────

    public function test_token_vazio_recusa_sem_enviar(): void
    {
        $this->abrirPortao();
        config(['uazapi.instance_token' => '']);
        Http::fake();

        $this->expectException(\RuntimeException::class);

        try {
            $this->provedor()->enviarTexto(self::TEL, 'oi');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_base_url_vazia_recusa_sem_enviar(): void
    {
        $this->abrirPortao();
        config(['uazapi.base_url' => '']);
        Http::fake();

        $this->expectException(\RuntimeException::class);

        try {
            $this->provedor()->enviarTexto(self::TEL, 'oi');
        } finally {
            Http::assertNothingSent();
        }
    }

    // ─────────────────────────── DESTINATARIO ───────────────────────────

    /**
     * A API aceitaria TODOS estes no campo `number` (a doc lista @g.us,
     * @s.whatsapp.net, @lid, @newsletter). Quem protege somos nos.
     */
    public function test_telefone_nao_canonico_recusa_sem_enviar(): array
    {
        $this->abrirPortao();
        Http::fake();

        $invalidos = [
            '11987654321',                 // sem DDI
            '(11) 98765-4321',             // com mascara
            '+5511987654321',              // com +
            '5511987654321@s.whatsapp.net',// JID
            '107615941234567@lid',         // LID
            '120363999@g.us',              // grupo
            '',
        ];

        foreach ($invalidos as $valor) {
            try {
                $this->provedor()->enviarTexto($valor, 'oi');
                $this->fail("Deveria ter recusado: {$valor}");
            } catch (\InvalidArgumentException $e) {
                // esperado
            }
        }

        Http::assertNothingSent();

        return $invalidos;
    }

    public function test_texto_vazio_recusa_sem_enviar(): void
    {
        $this->abrirPortao();
        Http::fake();

        foreach (['', '   ', "\n\t"] as $texto) {
            try {
                $this->provedor()->enviarTexto(self::TEL, $texto);
                $this->fail('Deveria ter recusado texto vazio.');
            } catch (\InvalidArgumentException $e) {
                // esperado
            }
        }

        Http::assertNothingSent();
    }

    // ─────────────────────────── ERROS DA API ───────────────────────────

    public function test_401_fala_de_token(): void
    {
        $this->abrirPortao();
        Http::fake(['*/send/text' => Http::response(['error' => 'Invalid token'], 401)]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/401.*token/i');

        $this->provedor()->enviarTexto(self::TEL, 'oi');
    }

    public function test_429_fala_de_limite(): void
    {
        $this->abrirPortao();
        Http::fake(['*/send/text' => Http::response(['error' => 'Rate limit exceeded'], 429)]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/429.*limite/i');

        $this->provedor()->enviarTexto(self::TEL, 'oi');
    }

    /**
     * O 500 da Uazapi carrega diagnostico do proprio WhatsApp. Sem preservar o
     * provider_code, "erro 500" vira caca ao fantasma.
     */
    public function test_500_preserva_diagnostico_do_provedor(): void
    {
        $this->abrirPortao();
        Http::fake(['*/send/text' => Http::response([
            'error'         => 'WhatsApp server error 463: temporary restriction',
            'error_key'     => 'WHATSAPP_REACHOUT_TIMELOCK',
            'provider_code' => 463,
        ], 500)]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/463.*WHATSAPP_REACHOUT_TIMELOCK/s');

        $this->provedor()->enviarTexto(self::TEL, 'oi');
    }

    // ─────────────────────────── LGPD ───────────────────────────

    /**
     * Conteudo de conversa nao vai para log (regra de ouro 9). O telefone vai
     * mascarado.
     */
    public function test_falha_nao_loga_o_texto_da_mensagem(): void
    {
        $this->abrirPortao();
        Http::fake(['*/send/text' => Http::response(['error' => 'boom'], 500)]);

        $segredo = 'dado sensivel do aluno que nao pode vazar';
        $capturado = [];

        Log::listen(function ($evento) use (&$capturado) {
            $capturado[] = $evento->message . ' ' . json_encode($evento->context);
        });

        try {
            $this->provedor()->enviarTexto(self::TEL, $segredo);
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString($segredo, $e->getMessage());
        }

        $this->assertNotEmpty($capturado, 'A falha deveria ter sido logada.');

        foreach ($capturado as $linha) {
            $this->assertStringNotContainsString($segredo, $linha);
            $this->assertStringNotContainsString(self::TEL, $linha, 'Telefone deve ir mascarado.');
        }
    }
}
