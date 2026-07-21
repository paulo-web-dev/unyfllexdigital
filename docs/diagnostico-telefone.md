# Fatia 0 — Diagnóstico de normalização de telefone

**Schema:** `unipublicabrasil3` (produção, confirmado com o Paulo).
**Data da execução:** 21/07/2026.
**Queries:** `docs/diagnostico-telefone.sql` (somente leitura).
**LGPD:** só agregado. Nenhum telefone, nome, e-mail ou id de pessoa neste relatório.

---

## 1. O resultado em uma frase

**Gravar telefone com DDI é a exceção nesta base, não a regra.** Entre 86,9% e 99,2% dos registros de cada tabela estão bem formados mas **sem o `55`** — recuperáveis por prefixo. O formato canônico tal como definido no `CLAUDE.md` (`5511987654321`) representa **no máximo 2,8%** de qualquer tabela, e **zero** em duas delas.

A consequência de desenho: prefixar `55` não é caminho de exceção da normalização, é o caminho comum.

---

## 2. Aderência ao canônico, por coluna (Q3)

Classes conforme `diagnostico-telefone.sql:225-231`. **`invalido` = vazio/nulo *ou* com caractere não-dígito** após remover a formatação conhecida — não é estritamente "nulo". `fora_do_padrao` = só dígitos, mas em comprimento que nenhuma regra recupera.

| coluna | linhas | inválido | fora do padrão | sem DDI (10+11) | canônico (12+13) | **aproveitável** |
|---|---:|---:|---:|---:|---:|---:|
| `students.phone` | 16.511 | 661 (4,0%) | 571 (3,5%) | 14.836 (89,9%) | 443 (2,7%) | **15.279 (92,5%)** |
| `users.telefone` | 14.689 | 705 (4,8%) | 805 (5,5%) | 12.763 (86,9%) | 416 (2,8%) | **13.179 (89,7%)** |
| `negociacoes_comercial.whatsapp` | 3.006 | 2.122 (70,6%) | 17 (0,6%) | 806 (26,8%) | 61 (2,0%) | **867 (28,8%)** |
| `prematricula.celular` | 580 | 4 (0,7%) | 39 (6,7%) | 537 (92,6%) | **0** | **537 (92,6%)** |
| `leads_guia_licitacoes.whatsapp` | 252 | 0 | 2 (0,8%) | 250 (99,2%) | **0** | **250 (99,2%)** |

`leads.celular` não aparece: a tabela está vazia, e `GROUP BY` sobre zero linhas não produz grupo. **A ausência é o resultado esperado** — ver "VERIFICAÇÃO EMBUTIDA" no cabeçalho do `.sql`.

Os cinco totais fecham exatamente contra a soma das classes, e o de `users` (14.689) bate com o denominador medido à parte no item 5.

### `negociacoes_comercial` — a fonte principal do funil, e a pior das cinco

70,6% inválido/vazio. A conta dos aproveitáveis: `3.006 − 2.122 = 884`, mas a cobertura é **867** — os 17 de diferença são `fora_do_padrao`, uma terceira classe, não arredondamento.

**Isso é teto de cobertura do matching, não detalhe de qualidade.** Com `leads` vazia, não sobra tabela de funil ativo do mesmo porte para compensar.

---

## 3. A regra do 9º dígito é maior do que a Q7 mostra

A Q7 devolveu **379 núcleos (~2,2% dos ~17.376 distintos)**. É um número correto para a pergunta que ela faz — mas essa pergunta é mais estreita do que o matching:

> **Q7 mede duplicação interna:** o mesmo assinante gravado nas duas formas (12 e 13) **dentro da nossa base**.

O caso de uso real é outro. A Uazapi manda `chat.phone` **sempre com 13 dígitos**. Todo registro nosso que normaliza para **12** nunca casa por igualdade de string com uma mensagem recebida — só pela variante. E a forma de 12 (`sem_ddi_10 + canonico12`) é:

| coluna | forma de 12 | como % dos aproveitáveis |
|---|---:|---:|
| `students.phone` | 5.023 | 32,9% |
| `users.telefone` | 5.709 | 43,3% |
| `negociacoes_comercial.whatsapp` | **754** | **87,0%** |
| `prematricula.celular` | 28 | 5,2% |
| `leads_guia_licitacoes.whatsapp` | 1 | 0,4% |
| **total** | **11.515** | **38,2%** (de 30.112 linhas aproveitáveis) |

**A fonte principal do funil é a que mais depende da variante: 87% dos seus 867 registros úteis estão na forma de 12.** Sem a regra do 9º dígito implementada, `negociacoes_comercial` casa praticamente nada.

Conclusão: a regra do 9º dígito não é tratamento de borda, é **mecanismo de primeira ordem** do matching, e precisa existir desde o primeiro commit da Fatia 4.

### O que ainda não foi medido — Q8

Nem todo número de 12 dígitos é derivável para 13. A guarda do `CLAUDE.md` só permite inserir o `9` quando o assinante de 8 começa em **6–9**; fixo brasileiro começa em **2–5** e não pode ser colapsado, sob pena de inventar um celular de outra pessoa. **A parcela fixa dessas 11.515 é inalcançável por WhatsApp por este caminho.**

Portanto **28,8% é teto, não previsão.** As queries **Q8** (base toda) e **Q8b** (só `negociacoes_comercial`) foram acrescentadas ao `.sql` para separar `derivavel_celular_6a9` de `fixo_2a5_inalcancavel`. **Ainda não executadas** — é o número que falta para a Fatia 4.

---

## 4. Sobreposição entre tabelas (Q6)

**61% dos ~17.376 telefones distintos aparecem em ≥2 tabelas.** Na maioria dos casos existe segunda fonte para confirmar a identidade do contato — o que sustenta o painel de CRM da Fatia 4/9 mostrando *qual* tabela casou.

---

## 5. `users.telefone` — risco de truncamento silencioso

**2.339 de 14.689 (15,9%)** ocupam exatamente os 14 caracteres do `varchar(14)`. A coluna tem folga zero para `+5511987654321`, e um valor no teto é indistinguível de um valor truncado na gravação.

**Hipótese não testada:** parte dos 805 `fora_do_padrao` de `users` (5,5%, a maior taxa das cinco colunas) pode ser efeito desse truncamento. Plausível pela coincidência entre o grupo no teto e a taxa fora do padrão — **não medido**, e não deve ser afirmado sem medir.

Isto **não** motiva `ALTER` na coluna legada: schema compartilhado, e a normalização é na leitura. Motiva a regra já registrada no `CLAUDE.md` de que **coluna nova nasce `varchar(20)`**.

---

## 6. Veredito sobre as duas colunas numéricas

O `plan.md` (divergência 5) previa que `NOT NULL` sem default transformaria ausência de telefone em `0` em massa. **Não se confirmou.**

| coluna | tipo | linhas | medido |
|---|---|---:|---|
| `contact.telefone` | `int` | **1** | 10 dígitos, **no teto do int** (2147483647). `zeros = 0`. |
| `corporativos.telefone` | `bigint` | **12** | 8 com 10 dígitos, 4 com 11. `zeros = 0`. Sem DDI — o mesmo padrão das demais tabelas. |

**Decisão: as duas ficam fora do matching por volume irrelevante (1 e 12 linhas contra ~17.376 distintos), não por defeito.** A distinção importa: "coluna quebrada" e "coluna praticamente vazia" pedem decisões opostas se alguém repovoar essas tabelas.

Duas ressalvas honestas:

- O overflow do `int` **é real** — a única linha de `contact` está saturada no teto. A amostra é de uma linha, então isso confirma o mecanismo, não a extensão do dano.
- A propriedade do schema (`NOT NULL` sem default → `0`) **continua verdadeira** e continua valendo como risco para inserts futuros. O que não se confirmou foi contaminação *hoje*.

---

## 7. O que este relatório não cobre

Registrado para não passar por completo:

- **Padrões de formatação distintos por coluna (Q1)** e **distribuição fina de comprimento (Q2)** — as queries existem e rodaram, mas a saída não foi transcrita aqui. Não altera nenhuma conclusão acima, que se apoia em Q3/Q4/Q5/Q6/Q7.
- **Q8 e Q8b** — escritas, não executadas. É a lacuna que importa (item 3).
- **Causa dos 70,6% de `negociacoes_comercial`** — pergunta em aberto no `plan.md`, para quem opera o funil comercial. Não é investigação de schema, e não deve ser presumida.

---

## 8. O que isto define para a Fatia 4

1. **Normalizar prefixando `55`** é o caminho comum (86,9%–99,2% dos registros), não um fallback.
2. **A regra do 9º dígito é obrigatória desde o primeiro commit** — 38,2% da base útil só casa por ela, e 87% da fonte principal do funil.
3. **"Não identificado" será o estado comum**, não a exceção — a UI trata isso como caminho normal.
4. **Rodar Q8/Q8b antes de prometer qualquer número de cobertura.** 28,8% em `negociacoes_comercial` é teto.
