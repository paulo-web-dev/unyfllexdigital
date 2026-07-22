<?php

namespace Tests\Unit;

use App\Support\TelefoneCanonico;
use PHPUnit\Framework\TestCase;

/**
 * ESTE TESTE FICA NO REPO. Não toca banco nem dado real — é função pura, e
 * segue o precedente de `tests/Feature/UazapiEnvioTest.php`. Os testes das
 * Fatias 3/5/7 foram descartáveis pelo motivo oposto: tocavam `unyflex_dev`.
 *
 * Estende `PHPUnit\Framework\TestCase` (não o `Tests\TestCase`) de propósito:
 * sem app, sem banco, sem config — e portanto sem a deprecation do PHP 8.5 em
 * `config/database.php:62` sujando a saída.
 *
 * Telefones aqui são inventados. Os DDDs e blocos foram escolhidos para
 * exercitar as faixas da guarda, não para corresponder a números reais.
 */
class TelefoneCanonicoTest extends TestCase
{
    // ── normalizar(): o caminho comum é prefixar 55, não é fallback ────────

    public function test_normaliza_sem_ddi_prefixando_55(): void
    {
        // 11 dígitos (DDD + 9) e 10 dígitos (DDD + 8) — entre 86,9% e 99,2%
        // da base de cada tabela está nesta forma.
        $this->assertSame('5511987654321', TelefoneCanonico::normalizar('11987654321'));
        $this->assertSame('551187654321', TelefoneCanonico::normalizar('1187654321'));
    }

    public function test_normaliza_limpando_formatacao(): void
    {
        $this->assertSame('5511987654321', TelefoneCanonico::normalizar('+55 (11) 98765-4321'));
    }

    public function test_normalizar_nunca_completa_com_zero_para_forcar_13(): void
    {
        // 12 dígitos é forma canônica legítima (fixo ou celular legado).
        $this->assertSame('551187654321', TelefoneCanonico::normalizar('551187654321'));
    }

    // ── variante(): 13 <-> 12 ─────────────────────────────────────────────

    public function test_de_13_para_12_remove_o_nono_digito(): void
    {
        $this->assertSame('551187654321', TelefoneCanonico::variante('5511987654321'));
    }

    public function test_de_12_para_13_insere_o_nono_digito(): void
    {
        $this->assertSame('5511987654321', TelefoneCanonico::variante('551187654321'));
    }

    /**
     * A propriedade que a guarda simétrica dá de graça. Se alguém aplicar a
     * faixa 6-9 num sentido só, isto quebra.
     */
    public function test_roundtrip_nos_dois_sentidos(): void
    {
        foreach (['5511987654321', '5521998887777', '5531966665555'] as $treze) {
            $doze = TelefoneCanonico::variante($treze);
            $this->assertNotNull($doze, "não derivou o 12 de {$treze}");
            $this->assertSame($treze, TelefoneCanonico::variante($doze));
        }
    }

    // ── A guarda, e os dois motivos OPOSTOS de ela recusar ─────────────────

    /**
     * FIXO — dado VÁLIDO sob guarda intencional. Inserir o `9` aqui inventaria
     * um celular que pode existir e ser de outra pessoa. É o caso que mais
     * importa: um falso positivo aqui vira mensagem para um estranho.
     */
    public function test_fixo_2a5_nao_deriva_variante(): void
    {
        foreach (['551123456789', '551134567890', '551145678901', '551156789012'] as $fixo) {
            $this->assertNull(
                TelefoneCanonico::variante($fixo),
                "derivou variante de fixo ({$fixo}) — inventaria o celular de outra pessoa"
            );
        }
    }

    /**
     * A MESMA GUARDA NO SENTIDO 13 -> 12, que é o menos óbvio dos dois.
     *
     * `5511923456789` é um celular perfeitamente válido HOJE (11 92345-6789).
     * O que ele não tem é forma legada: o bloco `23456789` começa em 2, faixa
     * de fixo, então esse par de 8 dígitos nunca foi deste assinante. Derivar
     * `551123456789` afirmaria uma equivalência que não existe — e esse número
     * pode ser o fixo de outra pessoa.
     *
     * Este caso entrou DEPOIS: a primeira versão do teste só cobria a guarda
     * no sentido 12 -> 13, e uma mutação que a removesse só deste lado
     * passaria despercebida. Achado quebrando o código de propósito.
     */
    public function test_13_sem_forma_legada_nao_deriva(): void
    {
        foreach (['5511923456789', '5511934567890', '5511945678901'] as $celular) {
            $this->assertNull(
                TelefoneCanonico::variante($celular),
                "derivou forma legada de {$celular}, cujo bloco cai na faixa de fixo"
            );
        }
    }

    /**
     * ANÔMALO — dado QUEBRADO, não guarda intencional. Nenhum número
     * brasileiro válido começa assim (9 na base inteira).
     */
    public function test_bloco_comecando_em_0_ou_1_nao_deriva(): void
    {
        $this->assertNull(TelefoneCanonico::variante('551101234567'));
        $this->assertNull(TelefoneCanonico::variante('551112345678'));
    }

    // ── Recusas ───────────────────────────────────────────────────────────

    public function test_ddi_diferente_de_55_nao_deriva(): void
    {
        // A regra do 9º dígito é da Anatel; aplicá-la a outro país é chute.
        $this->assertNull(TelefoneCanonico::variante('351912345678'));
        $this->assertNull(TelefoneCanonico::variante('12125550123'));
    }

    public function test_13_digitos_sem_9_na_posicao_esperada_nao_deriva(): void
    {
        // Não é caso de 9º dígito — não mexer.
        $this->assertNull(TelefoneCanonico::variante('5511887654321'));
    }

    public function test_entrada_nao_canonica_vira_null_em_vez_de_chute(): void
    {
        foreach ([null, '', 'abc', '11987654321', '+55 11 98765-4321', '5511', '551198765432112'] as $bruto) {
            $this->assertNull(
                TelefoneCanonico::variante($bruto),
                'aceitou entrada não canônica: ' . var_export($bruto, true)
            );
        }
    }

    /**
     * O encaixe entre as duas funções: normalizar primeiro, derivar depois.
     * É o caminho que a maioria da base percorre até casar com a Uazapi —
     * prefixar 55 (-> 12) e depois derivar (-> 13).
     */
    public function test_caminho_completo_do_registro_legado_ate_o_formato_da_uazapi(): void
    {
        $canonico = TelefoneCanonico::normalizar('(11) 8765-4321');

        $this->assertSame('551187654321', $canonico);
        $this->assertSame('5511987654321', TelefoneCanonico::variante($canonico));
    }
}
