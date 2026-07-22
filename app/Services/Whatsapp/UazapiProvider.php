<?php

namespace App\Services\Whatsapp;

use App\Contracts\WhatsappProviderContract;
use App\Support\TelefoneCanonico;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Implementador do WhatsappProviderContract para a Uazapi.
 *
 * O CAMINHO DE ENVIO ESTA ESCRITO E TESTADO, MAS DESLIGADO (Fatia 6). O portao
 * e `config('uazapi.envio_habilitado')`, default false: com ele fechado,
 * enviarTexto() lanca LogicException e nenhum pacote sai. Ligar e checkpoint
 * humano, nunca efeito colateral de outra tarefa.
 *
 * SAO DUAS TRAVAS INDEPENDENTES, e confundi-las e o jeito de se machucar:
 *   * o PORTAO decide se enviamos alguma coisa;
 *   * a GUARDA DE AMBIENTE decide contra QUAL INSTANCIA.
 * Abrir o portao nao libera a instancia de producao em dev.
 */
class UazapiProvider implements WhatsappProviderContract
{
    private string $baseUrl;
    private string $instancia;
    private string $token;

    public function __construct()
    {
        $this->baseUrl   = rtrim((string) config('uazapi.base_url'), '/');
        $this->instancia = (string) config('uazapi.instance_name');
        $this->token     = (string) config('uazapi.instance_token');

        // No construtor de proposito: um .env errado falha na primeira resolucao
        // do container, nao na hora em que alguem aperta "enviar".
        $this->guardaDeAmbiente();
    }

    /**
     * Amarracao de dev a instancia de teste (risco #1 do briefing).
     *
     * Ja perdemos ~23h de WhatsApp em producao por misturar experimento com a
     * instancia que atende o comercial. Isto esta em codigo, e nao em disciplina,
     * porque nao pode depender de alguem lembrar de conferir o .env.
     */
    private function guardaDeAmbiente(): void
    {
        if (app()->environment() === 'production') {
            return;
        }

        // LE A CONFIG AGORA, NAO O VALOR DO CONSTRUTOR. Isto foi corrigido em
        // 22/07/2026 depois de uma sonda mostrar o buraco: comparando
        // $this->instancia (congelada na construcao), uma troca de config
        // DEPOIS do objeto pronto passava batido e o envio saia. A guarda que
        // so confere o que ja conferiu nao guarda nada.
        $instancia = (string) config('uazapi.instance_name');
        $proibidas = (array) config('uazapi.prod_instances');

        if ($instancia !== '' && in_array($instancia, $proibidas, true)) {
            throw new \RuntimeException(sprintf(
                'Uazapi: instancia de producao (%s) configurada em ambiente "%s". Recusado. '
                . 'Aponte UAZAPI_INSTANCE_NAME para a instancia de teste.',
                $instancia,
                app()->environment()
            ));
        }
    }

    /**
     * Envia texto pela Uazapi. POST /send/text.
     *
     * A ORDEM DAS CHECAGENS E O DESENHO. Cada uma barra algo que a seguinte ja
     * nao conseguiria desfazer — envio nao tem "ctrl+z", e a API aceita quase
     * qualquer coisa no campo `number`.
     */
    public function enviarTexto(string $telefoneCanonico, string $texto): string
    {
        // 1. PORTAO. Fechado por padrao. Nada e lido, nada sai.
        if (! config('uazapi.envio_habilitado', false)) {
            throw new \LogicException(
                'Envio pela Uazapi desabilitado (Fatia 6). Ligar exige checkpoint '
                . 'explicito: UAZAPI_ENVIO_HABILITADO no .env.'
            );
        }

        // 2. GUARDA DE AMBIENTE DE NOVO, AQUI — e agora ela vale de verdade.
        // A versao anterior desta linha era teatro: guardaDeAmbiente() comparava
        // $this->instancia, congelada no construtor, entao rechamar so repetia a
        // conferencia ja feita. Uma sonda mostrou que trocar a config depois de
        // construir o objeto ENVIAVA. A guarda passou a ler config() no ato;
        // esta chamada e o que fecha a janela entre construir e enviar.
        $this->guardaDeAmbiente();

        // 3. Config ausente e erro de servidor, nunca envio silencioso. Mesmo
        // raciocinio do validarToken() do webhook: config vazia nao vira
        // permissao.
        if ($this->token === '') {
            throw new \RuntimeException('Uazapi: UAZAPI_INSTANCE_TOKEN nao configurado.');
        }

        if ($this->baseUrl === '') {
            throw new \RuntimeException('Uazapi: UAZAPI_BASE_URL nao configurada.');
        }

        // 4. O TELEFONE TEM QUE CHEGAR CANONICO — E NAO E CONSERTADO AQUI.
        // Normalizar neste ponto esconderia um chamador errado, e o alvo errado
        // num envio e mensagem para um estranho, nao um log feio. A API aceita
        // `@g.us`, `@s.whatsapp.net`, `@lid` e `@newsletter` no mesmo campo, ou
        // seja: ela NAO nos protege de mandar para o alvo errado. Esta linha e
        // que protege.
        if (TelefoneCanonico::normalizar($telefoneCanonico) !== $telefoneCanonico) {
            throw new \InvalidArgumentException(
                'Uazapi: telefone precisa chegar no formato canonico (so digitos, DDI 55). '
                . 'Normalize antes de chamar — este metodo nao adivinha destinatario.'
            );
        }

        // 5. Texto vazio: a API responderia 400, entao nem gasta o request.
        if (trim($texto) === '') {
            throw new \InvalidArgumentException('Uazapi: texto vazio.');
        }

        // 6. HTTP. TOKEN VAI EM HEADER AQUI — no webhook ele vem no BODY.
        // A assimetria e do provedor (securitySchemes.token, in: header), e e o
        // erro mais facil de cometer neste arquivo.
        $resposta = Http::withHeaders(['token' => $this->token])
            ->timeout(30)
            ->post("{$this->baseUrl}/send/text", [
                'number' => $telefoneCanonico,
                'text'   => $texto,
            ]);

        if ($resposta->failed()) {
            $this->lancarFalha($resposta, $telefoneCanonico);
        }

        // Mesma ambiguidade ja registrada no CLAUDE.md: `id` e interno da Uazapi
        // e `messageid` e o do provedor. O primeiro payload real decide qual
        // reconcilia com o eco do webhook.
        $id = (string) ($resposta->json('id') ?? $resposta->json('messageid') ?? '');

        if ($id === '') {
            // NAO LANCA DE PROPOSITO. Depois de um 200, lancar faria o chamador
            // achar que falhou e reenviar — duplicando mensagem para uma pessoa
            // real. Perder o id e pior de rastrear e melhor de sofrer.
            Log::warning('uazapi: envio aceito sem id na resposta', [
                'telefone' => $this->mascarar($telefoneCanonico),
                'status'   => $resposta->status(),
            ]);
        }

        return $id;
    }

    /**
     * Traduz a falha da API em excecao — sem NUNCA logar o texto da mensagem
     * (LGPD). So status, erro do provedor e telefone mascarado.
     */
    private function lancarFalha(Response $resposta, string $telefone): never
    {
        $erro = (string) ($resposta->json('error') ?? 'sem detalhe');

        // O 500 da Uazapi carrega diagnostico do proprio WhatsApp (ex.:
        // provider_code 463, WHATSAPP_REACHOUT_TIMELOCK = restricao temporaria
        // por volume/qualidade). Isso e informacao, nao ruido: sem ela, "erro
        // 500" viraria caca ao fantasma.
        $codigoProvedor = $resposta->json('provider_code');
        $chaveErro      = $resposta->json('error_key');

        Log::error('uazapi: falha ao enviar texto', array_filter([
            'telefone'      => $this->mascarar($telefone),
            'status'        => $resposta->status(),
            'erro'          => $erro,
            'provider_code' => $codigoProvedor,
            'error_key'     => $chaveErro,
        ], fn ($v) => $v !== null));

        $detalhe = match ($resposta->status()) {
            401     => 'token da instancia recusado',
            429     => 'limite de requisicoes excedido',
            default => $erro,
        };

        throw new \RuntimeException(sprintf(
            'Uazapi: envio falhou (HTTP %d): %s%s',
            $resposta->status(),
            $detalhe,
            $codigoProvedor !== null ? sprintf(' [provider_code %s, %s]', $codigoProvedor, $chaveErro ?? '-') : ''
        ));
    }

    /** Telefone em log fica mascarado: e dado pessoal (LGPD). */
    private function mascarar(string $telefone): string
    {
        return strlen($telefone) <= 6
            ? '***'
            : substr($telefone, 0, 4) . str_repeat('*', strlen($telefone) - 6) . substr($telefone, -2);
    }

    public function instanciaAtual(): string
    {
        return $this->instancia;
    }
}
