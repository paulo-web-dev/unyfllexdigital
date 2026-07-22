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

### Alcançabilidade da forma de 12 — Q8 e Q8b, executadas em 22/07/2026

Nem todo número de 12 dígitos é derivável para 13. A guarda do `CLAUDE.md` só permite inserir o `9` quando o assinante de 8 começa em **6–9**; fixo brasileiro começa em **2–5** e não pode ser colapsado, sob pena de inventar um celular de outra pessoa.

**Resultado — e a unidade é `telefones distintos`, não linhas:**

| recorte | derivável 6–9 | fixo 2–5 | anômalo 0/1 | **total distintos** | **% derivável** |
|---|---:|---:|---:|---:|---:|
| base toda | 6.416 | 436 | 9 | **6.861** | **93,5%** |
| `negociacoes_comercial` | 589 | 39 | **0** | **628** | **93,8%** |

**A parcela fixa é bem menor do que o item 3 temia.** O medo era que a forma de 12 fosse majoritariamente fixo, derrubando a cobertura muito abaixo do teto de 28,8%. Não é o caso: 93,8% dos distintos de 12 dígitos em `negociacoes_comercial` são celular derivável. A regra do 9º dígito **entrega** o que se esperava dela.

`anomalo_0ou1` é classe nova, não prevista no `CLAUDE.md` — bloco de 8 dígitos começando em 0 ou 1, que não é fixo nem celular. **9 números na base inteira, 0 em `negociacoes_comercial`.** O zero é medido, não faltante: `GROUP BY` não gera grupo vazio (ressalva no cabeçalho do `.sql:518-519`). Ver a tabela das quatro categorias no `CLAUDE.md`.

---

## 3b. Os 126 que "faltavam" — reconciliação Q3 × Q8b

Ao comparar Q8b com Q3 aparece um buraco: a Q3 aponta **754** candidatos de 12 dígitos em `negociacoes_comercial` (`sem_ddi_10` 705 + `canonico12` 49), mas a Q8b soma **628** (589 + 39 + 0). Faltariam 126.

**Não faltam. As duas queries contam coisas diferentes:**

- **Q3 conta LINHAS** — `COUNT(*)` agrupado por coluna e classe, sem deduplicação.
- **Q8/Q8b contam TELEFONES DISTINTOS** — a subquery interna faz `WHERE CHAR_LENGTH(tel) = 12 GROUP BY tel` (`.sql:606-607`) *antes* da classificação, então o `COUNT(*)` externo já opera sobre valores únicos.

```
754 linhas  →  628 telefones distintos
               126 = o mesmo número repetido em negociações diferentes
```

**Verificado que o mapeamento Q3→Q8 é exato**, sem vazamento de classe. Sob o `CASE` da Q8 (`.sql:530-536`), só duas classes da Q3 produzem um `tel` de 12 caracteres:

| classe Q3 | `tel` resultante | entra na Q8? |
|---|---|---|
| `canonico12` | `d` (12) | **sim** |
| `sem_ddi_10` | `CONCAT('55', d)` (12) | **sim** |
| `canonico13` | `d` (13) | não |
| `sem_ddi_11` | `CONCAT('55', d)` (13) | não |
| `fora_do_padrao`, `invalido` | `''` | não |

**Corroboração na base toda:** as 11.515 linhas do item 3 correspondem a 6.861 distintos — deduplicação de 40,4%, contra 16,7% dentro de `negociacoes_comercial` sozinha. A dedup cruzada ser bem maior que a intra-tabela é exatamente o que a Q6 previa ao medir 61% dos telefones distintos aparecendo em ≥2 tabelas. Os dois números se sustentam mutuamente.

### O `+1` da Q8c — e o erro de método que ele expôs

A Q8c voltou em 22/07/2026 com `derivavel 704 + fixo 51 = **755**`, contra as **754** linhas de forma de 12 da Q3. A verificação embutida da própria Q8c manda parar nesse caso, e é o que foi feito: **nenhuma taxa de cobertura de `negociacoes_comercial` é publicada até isto fechar.**

**A lógica não é a causa — isso foi provado, não presumido.** Em 22/07/2026 as duas classificações foram executadas lado a lado num MySQL 9.7 local, sobre uma tabela fabricada com **35 casos de fronteira** (comprimentos 0/2/9/10/11/12/13/14/20; `10 dígitos começando com 55`; `11 dígitos começando com 55`; separadores conhecidos; `\n`, `\t`, `_`, letra; dígito não-ASCII; bloco de assinante em 0, 1, 5, 6 e 9; `NULL`; vazio). Resultado: **zero discordâncias**. Q3 e Q8c particionam de forma idêntica — inclusive no caso que mais parece armadilha, o valor de 10 dígitos que já começa com `55` (as duas contam) e no de 11 dígitos começando com `55` (nenhuma conta).

**A causa provável é o método, não a query.** A Q3 rodou em 21/07 e a Q8c em 22/07, e `negociacoes_comercial` recebe inserção do comercial **todo dia**. Duas medições feitas em dias diferentes sobre uma tabela viva **não podem ser subtraídas** — a diferença tanto pode ser divergência de lógica quanto uma negociação nova. Uma linha em ~24h é exatamente a ordem de grandeza esperada.

**A correção do método é a Q9**, acrescentada ao `.sql`: calcula as duas classificações **na mesma varredura**, com `total_linhas` e uma contagem explícita de `discordancias`. Critério de leitura fixado antes de rodar:

- `discordancias = 0` e `total_linhas = 3.007` → o `+1` era a negociação nova. **Encerrado.**
- `discordancias = 0` e `total_linhas = 3.006` → a hipótese da deriva cai; o próximo suspeito é Q3 e Q8c terem rodado contra **schemas diferentes** (`unipublicabrasil3` × `4` × `5`), risco que o cabeçalho do `.sql` já registra.
- `discordancias > 0` → divergência real, e a Q9b dá a assinatura das linhas afetadas.

**Lição que fica registrada:** número tirado de `negociacoes_comercial` **carrega data**. Comparar duas execuções separadas dessa tabela não decide nada — se duas medidas precisam ser comparadas, elas precisam sair da mesma varredura.

---

## 3c. `REGEXP '^[0-9]+$'` não é teste estrito de "só dígitos"

Achado independente, descoberto ao montar a prova de equivalência acima. No MySQL 9.7 local:

```sql
SELECT '1198765432\n' REGEXP '^[0-9]+$';   -- 1  (!)
SELECT '1198765432\t' REGEXP '^[0-9]+$';   -- 0
```

O `$` do ICU — motor de regex do MySQL 8+ — casa **também antes de um terminador de linha final**. Um valor terminado em `\n` passa no teste de "só dígitos" enquanto o mesmo valor com `\t` não passa.

**Consequência, se existir na base:** um celular de 10 dígitos com `\n` no fim tem `CHAR_LENGTH` 11, é classificado `sem_ddi_11` em vez de `sem_ddi_10`, e normaliza para uma string de 13 **caracteres** com um newline dentro — que jamais casaria com o `chat.phone` da Uazapi. É um não-match silencioso, o tipo de falha que o `CLAUDE.md` manda evitar.

**Isto não explica o `+1`**: Q3 e Q8c usam o mesmo `REGEXP`, então o efeito é idêntico nas duas e não produz assimetria.

**Ainda não medido em produção.** A **Q10** foi acrescentada ao `.sql` para contar, por coluna, quantas linhas têm `\n`, `\r` ou `\t` sobrevivendo à limpeza (a cadeia de `REPLACE` remove espaço `-` `(` `)` `.` `+` `/` — nunca removeu controle). Se der zero em todas, é curiosidade teórica e nada muda.

**Não alterar a cadeia de limpeza por causa disto sem re-executar o diagnóstico inteiro** — mudar a limpeza muda todos os números já publicados aqui.

### A armadilha que isto cria

**`589 / 754` não é taxa de cobertura** — numerador em telefones distintos, denominador em linhas. Nenhuma razão entre um número desta seção e um número do item 2 ou 3 significa alguma coisa.

Duas perguntas legítimas e diferentes, cada uma com sua unidade:

- *"Chega uma mensagem no WhatsApp — consigo identificar a pessoa?"* → **por telefone distinto.** Respondida: 93,8%.
- *"Quanto do meu funil comercial eu alcanço?"* → **por linha de negociação.** **Ainda não respondida** — depende da **Q8c**, acrescentada ao `.sql` e ainda não executada.

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
- **Q8 e Q8b** — executadas em 22/07/2026, resultados no item 3. **Q8c** também, e ela **não fechou contra a Q3** (755 × 754) — ver item 3b. **Q9/Q9b** (reconciliação atômica) e **Q10** (caracteres de controle) foram escritas e **ainda não executadas**. Enquanto a Q9 não voltar, **nenhuma taxa de cobertura de `negociacoes_comercial` em linhas é válida.**
- **Causa dos 70,6% de `negociacoes_comercial`** — pergunta em aberto no `plan.md`, para quem opera o funil comercial. Não é investigação de schema, e não deve ser presumida.

---

## 8. O que isto define para a Fatia 4

1. **Normalizar prefixando `55`** é o caminho comum (86,9%–99,2% dos registros), não um fallback.
2. **A regra do 9º dígito é obrigatória desde o primeiro commit** — 38,2% da base útil só casa por ela, e 87% da fonte principal do funil.
3. **"Não identificado" será o estado comum**, não a exceção — a UI trata isso como caminho normal.
4. **A variante do 9º dígito entrega:** 93,8% dos telefones distintos de 12 dígitos em `negociacoes_comercial` são celular derivável, contra 6,2% de fixo inalcançável. O teto de 28,8% (em linhas) não é corroído de forma relevante pela parcela fixa.
5. **Sempre rotular a unidade.** Linhas e telefones distintos convivem neste relatório e não se dividem entre si. A cobertura em linhas depende da **Q9**, ainda não executada — a Q8c sozinha não fecha contra a Q3 (item 3b).
7. **Número de `negociacoes_comercial` carrega data.** A tabela é escrita todo dia. Duas medições de execuções diferentes não são comparáveis; se precisam ser comparadas, têm que sair da mesma varredura.
6. **Quatro categorias, não duas.** `fixo_2a5_inalcancavel` é guarda intencional sobre dado válido; `anomalo_0ou1` é dado quebrado. A UI e qualquer futura limpeza precisam distinguir os dois — ver `CLAUDE.md`.
