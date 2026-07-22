<?php

namespace App\Services;

use App\Support\ContatoIdentificado;
use App\Support\TelefoneCanonico;
use Illuminate\Support\Facades\DB;

/**
 * Quem é o dono deste telefone? — Fatia 4.
 *
 * SOMENTE LEITURA. Nenhuma escrita em tabela de CRM, aqui nem em lugar nenhum
 * desta fatia. Match pela variante NÃO autoriza corrigir a origem: a
 * normalização é na leitura, nunca por UPDATE em coluna legada.
 *
 * NÃO REIMPLEMENTA NORMALIZAÇÃO. `TelefoneCanonico` é o único lugar onde a
 * regra de telefone mora; aqui só se consome `normalizar()` e `variante()`.
 */
class IdentificacaoContato
{
    /**
     * Fontes na ordem de precedência, e os nomes de coluna de cada uma.
     *
     * Valores CONSTANTES, nunca entrada de usuário — é o que autoriza
     * interpolá-los no SQL em `semFormatacao()`. Quem acrescentar fonte aqui
     * tem que manter essa propriedade.
     *
     * `negociacoes_comercial` é a fonte principal do funil e ENTRA AQUI ASSIM
     * QUE O DDL DELA FOR CONHECIDO — ela não existe no `unyflex_dev` e os
     * nomes das colunas não podem ser adivinhados.
     */
    private const FONTES = [
        [
            'tabela'   => 'students',
            'telefone' => 'phone',
            'nome'     => 'name',
            'rotulo'   => 'Aluno (students)',
        ],
    ];

    /**
     * Caracteres que a busca ignora na coluna legada.
     *
     * É DELIBERADAMENTE A MESMA LISTA DO DIAGNÓSTICO
     * (`docs/diagnostico-telefone.sql`), e essa é a razão de ser dela: as
     * taxas de cobertura já publicadas passam a PREVER o comportamento deste
     * matcher, em vez de descrever outra coisa.
     *
     * Ela é mais estreita que o `preg_replace('/\D+/')` do PHP — um valor com
     * letra ou caractere de controle no meio não casa. Medido: 2 linhas na
     * base inteira, zero em `negociacoes_comercial`.
     */
    private const RUIDO = [' ', '-', '(', ')', '.', '+', '/'];

    /**
     * Devolve o contato, ou null para "não identificado".
     *
     * "NÃO IDENTIFICADO" É O CAMINHO NORMAL, não um erro: o teto de cobertura
     * medido em `negociacoes_comercial` é 28,8%. Quem consumir isto trata null
     * como estado comum.
     */
    public function identificar(?string $chatPhone): ?ContatoIdentificado
    {
        $canonico = TelefoneCanonico::normalizar($chatPhone);

        if ($canonico === null) {
            return null;
        }

        // ORDEM POR FORMA, NÃO POR FONTE. Um match DIRETO em qualquer tabela
        // vale mais que um DERIVADO em outra: a variante é dedução, ainda que
        // determinística. Testar todas as fontes pelo canônico antes de
        // derivar impede que uma dedução passe na frente de uma igualdade.
        foreach (['canonico', 'variante'] as $forma) {
            $candidatas = $this->candidatas($canonico, $forma);

            if ($candidatas === []) {
                // Só acontece na 'variante', e é a guarda 6-9 fazendo o
                // trabalho dela: fixo (2-5) e anômalo (0/1) não derivam.
                // Inserir o `9` num fixo inventaria o celular de outra pessoa.
                continue;
            }

            foreach (self::FONTES as $fonte) {
                $achado = $this->buscar($fonte, $candidatas, $forma);

                if ($achado !== null) {
                    return $achado;
                }
            }
        }

        // Só chega aqui depois de a variante ter sido testada — o CLAUDE.md
        // proíbe declarar "não encontrado" antes disso.
        return null;
    }

    /**
     * As formas de dígitos que um cadastro legítimo poderia ter guardado.
     *
     * São as MESMAS classes que a Q3 do diagnóstico mede, e não por acaso:
     * `canonico13`/`sem_ddi_11` para a forma direta, `canonico12`/`sem_ddi_10`
     * para a derivada. Prefixar 55 é o caminho comum da base (86,9% a 99,2%
     * dos registros de cada tabela estão sem DDI), então a forma curta não é
     * um extra — é a que mais casa.
     *
     * @return list<string>
     */
    private function candidatas(string $canonico, string $forma): array
    {
        $base = $forma === 'canonico'
            ? $canonico
            : TelefoneCanonico::variante($canonico);

        if ($base === null) {
            return [];
        }

        // `substr($base, 2)` remove o DDI 55 — a forma como a maioria da base
        // está gravada. `array_values(array_unique(...))` por segurança; as
        // duas nunca colidem no formato canônico.
        return array_values(array_unique([$base, substr($base, 2)]));
    }

    /**
     * @param  array{tabela:string,telefone:string,nome:string,rotulo:string}  $fonte
     * @param  list<string>  $candidatas
     */
    private function buscar(array $fonte, array $candidatas, string $forma): ?ContatoIdentificado
    {
        $coluna = $this->semFormatacao($fonte['telefone']);
        $marcas = implode(',', array_fill(0, count($candidatas), '?'));

        // Varredura completa: a cadeia de REPLACE inutiliza qualquer índice.
        // É escolha consciente — `negociacoes_comercial` tem ~3 mil linhas e
        // `users` ~14,7 mil. Se alguma fonte chegar a centenas de milhares, o
        // caminho é uma coluna canônica materializada, não um LIKE.
        $linhas = DB::table($fonte['tabela'])
            ->select($fonte['nome'] . ' as nome')
            ->whereRaw("{$coluna} IN ({$marcas})", $candidatas)
            // `id` desc, e não `created_at`: id existe em toda fonte e é
            // ordem de inserção. Mais recente primeiro — é ele que o painel
            // mostra.
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        if ($linhas->isEmpty()) {
            return null;
        }

        $nome = trim((string) $linhas->first()->nome);

        return new ContatoIdentificado(
            nome:   $nome === '' ? 'Sem nome no cadastro' : $nome,
            fonte:  $fonte['rotulo'],
            forma:  $forma,
            outros: $linhas->count() - 1,
        );
    }

    /**
     * Expressão SQL que remove a formatação da coluna.
     *
     * Mora AQUI e só aqui — uma cópia por fonte divergiria na primeira
     * mudança, e a divergência apareceria como "casa numa tabela e não na
     * outra", que é o pior sintoma possível deste código.
     *
     * SEM REGEXP, de propósito: `REGEXP '^[0-9]+$'` no MySQL 8+ casa antes de
     * um terminador de linha final (o `$` do ICU), e um telefone com `\n` no
     * fim passaria por válido. Comparação por igualdade não tem essa borda.
     *
     * O nome da coluna vem de `self::FONTES`, constante do código — nunca de
     * entrada de usuário. Só os VALORES buscados são parametrizados.
     */
    private function semFormatacao(string $coluna): string
    {
        $expressao = $coluna;

        foreach (self::RUIDO as $caractere) {
            $expressao = "REPLACE({$expressao}, '{$caractere}', '')";
        }

        return $expressao;
    }
}
