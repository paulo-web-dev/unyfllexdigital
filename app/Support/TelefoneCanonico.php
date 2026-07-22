<?php

namespace App\Support;

/**
 * Formato canônico de telefone do repo: só dígitos, sem '+', com DDI 55
 * sempre presente. Ex.: 5511987654321.
 *
 * ESCOPO DESTA CLASSE HOJE (Fatia 3): só `normalizar()`. É o suficiente para
 * gravar `chat_phone` a partir do `chat.phone` que a Uazapi manda.
 *
 * O QUE AINDA NÃO ESTÁ AQUI — e é de propósito: a derivação da variante do
 * 9º dígito, com a guarda de faixa 6-9. Isso é mecanismo de MATCHING, e
 * matching é Fatia 4, travada até a Q8c fechar a cobertura em linhas. Esta
 * classe existe agora para que a Fatia 4 tenha onde acrescentar `variante()`
 * em vez de nascer uma segunda normalização em outro canto do app.
 *
 * Quando `variante()` existir, `App\Models\LeadGuia::whatsappLink()` (que hoje
 * normaliza por conta própria, com `strlen <= 11 -> prefixa 55` e sem regra
 * nenhuma de 9º dígito) deve passar a usar esta classe.
 */
class TelefoneCanonico
{
    /**
     * Normaliza para o canônico de 12 ou 13 dígitos, ou devolve null quando o
     * valor não é recuperável.
     *
     * PREFIXAR 55 É O CAMINHO COMUM, NÃO UM FALLBACK. Medido na Fatia 0:
     * entre 86,9% e 99,2% dos registros de cada tabela estão bem formados e
     * sem DDI; o canônico já gravado é no máximo 2,8%. Quem escrever isto
     * tratando "sem DDI" como exceção acerta a minoria da base.
     *
     * NUNCA preenche com zero para forçar 13 dígitos: 12 é fixo ou celular
     * legado de 8, e é forma canônica legítima (ver CLAUDE.md).
     */
    public static function normalizar(?string $bruto): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $bruto);

        if ($d === '') {
            return null;
        }

        // Já canônico: 12 ou 13 dígitos com DDI.
        if (in_array(strlen($d), [12, 13], true) && str_starts_with($d, '55')) {
            return $d;
        }

        // Sem DDI: DDD + 8 (10) ou DDD + 9 (11). O caso comum.
        if (in_array(strlen($d), [10, 11], true)) {
            return '55' . $d;
        }

        // Comprimento que nenhuma regra recupera — `fora_do_padrao` no
        // vocabulário do diagnóstico. Não chutar.
        return null;
    }
}
