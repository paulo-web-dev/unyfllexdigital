<?php

namespace App\Services\Whatsapp;

use App\Contracts\WhatsappProviderContract;

/**
 * Implementador do WhatsappProviderContract para a Uazapi.
 *
 * NESTA FATIA O ENVIO NAO ESTA LIGADO. enviarTexto() existe para fechar o
 * contrato e lanca se for chamado — habilitar envio real e checkpoint explicito
 * (Fatia 6), nunca efeito colateral de outra tarefa.
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

        $proibidas = (array) config('uazapi.prod_instances');

        if ($this->instancia !== '' && in_array($this->instancia, $proibidas, true)) {
            throw new \RuntimeException(sprintf(
                'Uazapi: instancia de producao (%s) configurada em ambiente "%s". Recusado. '
                . 'Aponte UAZAPI_INSTANCE_NAME para a instancia de teste.',
                $this->instancia,
                app()->environment()
            ));
        }
    }

    public function enviarTexto(string $telefoneCanonico, string $texto): string
    {
        throw new \LogicException(
            'Envio pela Uazapi ainda nao habilitado (Fatia 6). Ligar exige checkpoint explicito.'
        );
    }

    public function instanciaAtual(): string
    {
        return $this->instancia;
    }
}
