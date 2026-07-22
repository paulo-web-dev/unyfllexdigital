<?php

namespace App\Contracts;

/**
 * Contrato do provedor de WhatsApp.
 *
 * Existe para que a Uazapi possa ser trocada (ex. API oficial do WhatsApp) sem
 * reescrever a aplicacao: nenhuma chamada direta a API do provedor deve aparecer
 * fora do implementador deste contrato.
 *
 * Superficie minima de proposito — cresce quando uma fatia precisar, nao antes.
 */
interface WhatsappProviderContract
{
    /**
     * Envia uma mensagem de texto.
     *
     * @param  string $telefoneCanonico  So digitos, com DDI 55: 5511987654321.
     * @return string                    Id da mensagem no provedor.
     */
    public function enviarTexto(string $telefoneCanonico, string $texto): string;

    /** Nome da instancia atualmente configurada. */
    public function instanciaAtual(): string;
}
