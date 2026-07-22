<?php

namespace App\Support;

/**
 * Resultado do matching de CRM — Fatia 4. Sem comportamento: é o que o painel
 * da thread precisa dizer, e nada além disso.
 *
 * `readonly` NA PROPRIEDADE, não na classe: `readonly class` só existe no PHP
 * 8.2 e o `composer.json` permite 8.1.
 *
 * SÓ NOME E PROCEDÊNCIA. Funil, turma, valor e histórico de compra são Fatia 9
 * — e enquanto não forem pedidos, não passam por aqui. Menos dado pessoal
 * atravessando a aplicação é menos superfície de LGPD.
 */
final class ContatoIdentificado
{
    public function __construct(
        public readonly string $nome,

        /** Rótulo humano da tabela que casou — o painel diz de onde veio. */
        public readonly string $fonte,

        /**
         * Como casou: 'canonico' (igualdade direta) ou 'variante' (derivação
         * do 9º dígito). O `CLAUDE.md` manda registrar isto junto do
         * resultado, para o painel poder dizer que casou pela variante.
         */
        public readonly string $forma,

        /**
         * Quantos OUTROS registros da mesma fonte casaram com o mesmo
         * telefone. A Q8b mediu 126 linhas de repetição só em
         * `negociacoes_comercial` — o mesmo número em negociações diferentes.
         * Esconder isso faria quem conferir contra o CRM ver divergência sem
         * explicação.
         */
        public readonly int $outros,
    ) {
    }

    public function casouPelaVariante(): bool
    {
        return $this->forma === 'variante';
    }
}
