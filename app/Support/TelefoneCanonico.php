<?php

namespace App\Support;

/**
 * Formato canônico de telefone do repo: só dígitos, sem '+', com DDI 55
 * sempre presente. Ex.: 5511987654321.
 *
 * ESCOPO DESTA CLASSE: `normalizar()` (Fatia 3) e `variante()` (22/07/2026).
 *
 * `variante()` entrou ANTES da Fatia 4 de propósito, e a distinção importa:
 * a Q9 do diagnóstico decide TAXAS DE COBERTURA em linhas, não o algoritmo.
 * A derivação do 9º dígito é transformação determinística da Anatel, fechada
 * no CLAUDE.md, e não depende de número nenhum ainda por medir. O que
 * continua travado até a Q9 é a Fatia 4 propriamente dita: models de CRM,
 * painel e o matching contra as tabelas.
 *
 * As duas funções moram aqui, e só aqui — normalização de telefone tem UM
 * lugar. `App\Models\LeadGuia::whatsappLink()` ainda normaliza por conta
 * própria (`strlen <= 11 -> prefixa 55`, sem regra de 9º dígito) e deveria
 * passar a usar esta classe; não foi alterado por ser link `wa.me` de página
 * viva, fora do escopo desta tarefa.
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

    /**
     * Devolve a OUTRA forma do mesmo assinante — 13 <-> 12 — ou null quando a
     * derivação não é legítima.
     *
     * POR QUE ISTO EXISTE. A Uazapi manda `chat.phone` sempre com 13 dígitos.
     * 38,2% da base aproveitável normaliza para 12 — em `negociacoes_comercial`,
     * 87% (754 de 867). Sem esta função, por igualdade de string a fonte
     * principal do funil casa praticamente nada. É mecanismo de PRIMEIRA ORDEM
     * do matching, não tratamento de borda.
     *
     * NÃO É MATCH DIFUSO. A adição do 9º dígito foi transformação
     * determinística da Anatel: as duas formas são um par exato. Nada de
     * distância de edição, "quase igual" ou prefixo parcial.
     *
     * ENTRADA JÁ CANÔNICA. Recebe o que `normalizar()` devolve; qualquer outra
     * coisa vira null em vez de ser adivinhada. Normalizar aqui dentro
     * esconderia um chamador que pulou a normalização.
     */
    public static function variante(?string $canonico): ?string
    {
        $d = (string) $canonico;

        // Só DDI 55: a regra do 9º dígito é da Anatel, não é universal.
        if (! preg_match('/^55\d{10,11}$/', $d)) {
            return null;
        }

        $ddd = substr($d, 2, 2);

        if (strlen($d) === 13) {
            // 13 -> 12: remove o `9` logo após o DDD. Se aquele dígito não for
            // `9`, não é caso de 9º dígito — não mexer.
            if ($d[4] !== '9') {
                return null;
            }

            $assinante = substr($d, 5); // os 8 dígitos legados

            return self::derivavel($assinante) ? '55' . $ddd . $assinante : null;
        }

        // 12 -> 13: insere o `9` na mesma posição.
        $assinante = substr($d, 4); // 8 dígitos

        return self::derivavel($assinante) ? '55' . $ddd . '9' . $assinante : null;
    }

    /**
     * A GUARDA. Só deriva quando o bloco de 8 dígitos começa em 6-9.
     *
     * Ela recusa dois casos por motivos OPOSTOS, e confundi-los leva alguém a
     * "consertar" o que está certo:
     *
     * - 2-5 = telefone FIXO. Dado válido, correto no banco, e continua
     *   correto — só não é alcançável por WhatsApp por este caminho. Inserir o
     *   `9` num fixo inventa um celular que pode existir e ser DE OUTRA
     *   PESSOA. Nunca "corrigir".
     * - 0-1 = `anomalo_0ou1` do diagnóstico: dado QUEBRADO. Nenhum número
     *   brasileiro válido começa assim (9 ocorrências na base inteira).
     *
     * Aplicada nos DOIS sentidos, o que dá de graça a propriedade
     * `variante(variante($x)) === $x` para todo par legítimo.
     */
    private static function derivavel(string $assinante): bool
    {
        return strlen($assinante) === 8
            && $assinante[0] >= '6'
            && $assinante[0] <= '9';
    }
}
