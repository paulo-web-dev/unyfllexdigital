# Fase 1 — Inbox própria de WhatsApp (Uazapi) — Plano de fatias

Contexto completo em `briefing-fase1-inbox-uazapi.md` (fora do repo). Este plano cobre só o MVP definido lá: **receber, ver, responder e atribuir**, com contexto de CRM na thread como diferencial.

## Divergências verificadas contra o repositório

O briefing foi conferido linha a linha contra o código e o schema (`COLUMNS.csv`, 248 tabelas).

1. **"Zero código WhatsApp" é impreciso.** Não há integração Uazapi/Chatwoot, mas `whatsapp` já existe como nome de campo (`leads_guia_licitacoes`, `negociacoes_comercial`, checkout) e `GuiaLicitacoesController.php:112` posta num webhook n8n hardcoded (`n8n.unyflex.com.br/webhook/guia-whatsapp`). Não conflita com o MVP, mas não é "zero".
2. **Não existe tabela `jobs`.** Só `failed_jobs` (migration padrão do Laravel). Migrar para `QUEUE_CONNECTION=database` exige criar `jobs` — vira o primeiro script em `database/sql/`, não é só mudança de `.env`.
3. **Não existe model Eloquent para a maior parte das tabelas de CRM.** `app/Models` tem `Classes`, `Student`, `Enrollment`, `LeadGuia` — nada para `negociacoes_comercial`, `contact`, `prematricula`, `courses`. O matching de CRM parte do zero. (`leads` e `tentativas_de_contato` também não têm model, e não devem ganhar um — divergência 9.) `app/Models/User.php` não tem nenhum relacionamento definido.
4. **`tentativas_de_contato` não tem coluna de telefone.** Só chega ao número via `id_lead` → `leads.celular`. Seu `forma_de_contato` é `enum('ligacao','mensagem','email','whatsapp')` — "whatsapp" ali é valor de enum, não número. **Com a divergência 9, esse único caminho até o número morre numa tabela vazia** — a tabela fica inalcançável para matching, tenha ela linhas ou não.
5. **Duas colunas de telefone são numéricas — MEDIDAS na Fatia 0, e o resultado corrige esta divergência.** São as únicas duas do banco; as outras 26 são `varchar`.
   - **`contact.telefone` (`int`, `NOT NULL`) estoura — confirmado, em amostra de 1 linha.** A tabela tem **1 registro**, de 10 dígitos, exatamente no teto (2147483647): valor limitado no insert, não erro. O mecanismo é real; a extensão do dano é uma linha.
   - **`corporativos.telefone` (`bigint`, `NOT NULL`) NÃO estoura.** `bigint` vai a 9223372036854775807 (19 dígitos). A objeção ali é de **representação**, não de capacidade: zero à esquerda é irrecuperável e `+`/DDI/formatação são inexprimíveis. **Medido:** 12 linhas, 8 com 10 dígitos e 4 com 11, `zeros = 0` — sem DDI, exatamente o padrão das demais tabelas, sem defeito próprio além disso.
   - **A hipótese do `0` em massa NÃO se confirmou.** `NOT NULL` sem default de fato transforma ausência em `0`, e isso continua valendo como risco para inserts futuros — mas **hoje `zeros = 0` nas duas**. A propriedade do schema é verdadeira; a contaminação não existe.
   - **Decisão: as duas ficam fora do matching por volume irrelevante (1 e 12 linhas contra ~17.376 telefones distintos), não por defeito.** A distinção importa — "coluna quebrada" e "coluna praticamente vazia" pedem decisões opostas se alguém repovoar essas tabelas.
   - Este item foi reaberto duas vezes: uma por generalizar o overflow do `int` para o `bigint`, outra pela medição acima. Nos dois casos a afirmação errada era sobre a causa, não sobre o tipo.
6. **O inventário de telefone é maior que o do briefing:** 30 colunas com nome de telefone/celular/whatsapp/fone no banco, incluindo `inscritos` (duas: `celular` e `fone`), `matcursomodular.txt_telefone`, `teachers.phone`, `users.telefone`, `roleta.fone`, `lp_workshop.whats`, `solicitacao_certificado.phone`, além de backups (`students2`, `studentsbkp`, `users2`, `ativos_*`). `users.telefone` é `varchar(14)` — exatamente o tamanho de `+5511987654321`, zero folga. **Medido na Fatia 0: 2.339 de 14.689 linhas (15,9%) já ocupam os 14 caracteres**, e valor no teto é indistinguível de valor truncado na gravação. **Hipótese não testada:** parte dos 805 `fora_do_padrao` de `users` (5,5%, a maior taxa das cinco colunas) pode ser efeito disso — plausível pela coincidência, **não medido**, e não afirmar sem medir. Não motiva `ALTER` na coluna legada; motiva a regra de coluna nova nascer `varchar(20)`.
7. **`vendor/` não está instalado** (gitignored). `composer.lock` fixa `laravel/framework 10.50.2`. Métodos do framework citados neste plano precisam ser conferidos no source após `composer install` — não estão afirmados como verificados.
8. Tudo o mais no briefing (stack, fila/scheduler vazios, ausência de suíte de testes, convenções `Admin/`/`Ava/`, `IsAdmin`/`AdminRole`/`power`, padrão de callback n8n, comportamento do webhook Asaas, nomes/colunas das demais tabelas) **confere**.
9. **`leads` está vazia em produção.** `SELECT COUNT(*) FROM leads` retorna **0** em `unipublicabrasil3` (confirmado com o Paulo, 21/07/2026). Não é schema errado nem tabela renomeada: a estrutura está lá, com colunas e collation, e o dado não. É abandono real.
   - **O schema não denuncia isso — só a contagem denuncia.** O levantamento inteiro deste plano foi feito sobre `COLUMNS.csv`, que mostra estrutura, não volume. Vale como aviso metodológico para as próximas tabelas: estrutura presente não é dado presente.
   - **Consequência de desenho, não só de escopo:** `negociacoes_comercial` assume sozinha o papel de fonte do funil comercial ativo — e 70,6% dos seus registros têm `whatsapp` inválido ou vazio — 2.122 de 3.006, medido (ver Fatia 0). Não sobra tabela de funil do mesmo porte para compensar.
   - `leads` sai do escopo ativo das Fatias 4 e 9; a regra de não reativá-la sem confirmação está no `CLAUDE.md`, em "Tabelas vazias em produção".

## Convenções do repo a reusar (não inventar padrão novo)

- **Config de integração:** arquivo dedicado por integração, não `config/services.php` (está stock). Modelo: `config/asaas.php`. App code lê por `config()`, nunca `env()` — `app/Services/AsaasService.php:16-17`. **Antipadrão a não copiar:** `config/cursos_modulares.php:16` e `config/social.php:8` commitaram secret literal como fallback.
- **Validação de secret de webhook:** método privado `validarSecret()` com `abort_unless` + `hash_equals` — `app/Http/Controllers/Admin/CourseVideoController.php:143-151`, repetido em 7 controllers. Não há middleware, é por controller.
- **Polling em Blade:** padrão pronto em `resources/views/pages/checkout.blade.php:618-656` — `setInterval` 5000ms, `fetch` com `X-CSRF-TOKEN` + `Accept: application/json`, `stopPolling()` explícito, timer em escopo de módulo, expiração pareada nas linhas 661-680.
- `app/Console/Commands/` e `app/Jobs/` **não existem** — as primeiras fatias criam esses diretórios e estabelecem a convenção.

## Perguntas em aberto (§4 do briefing) e onde cada uma bloqueia

| Pergunta | Bloqueia |
|---|---|
| Múltiplas URLs de webhook (fan-out nativo) | **Fatia 8** (modo sombra em produção). Não bloqueia a Fatia 2, que roda só na instância de teste. |
| Política de retentativa da Uazapi em falha de entrega | **Fatia 8** (afeta o cálculo de paridade). A rede de segurança da Fatia 1 já cobre o caso comum. |
| Integração Chatwoot atual é nativa ou via n8n | **Fatia 8** — e antes de qualquer mudança de config de instância. |
| Rate limits de envio | **Fatia 6** (texto avulso em teste é baixo risco) e **Fatia 8**. |
| Status de entrega/leitura, status de conexão da instância | Não bloqueia o MVP. Alerta de queda de instância entra depois da Fatia 8. |
| Formato do payload de mídia | Fora do MVP. |
| `fromMe` / `wasSentByApi` / `source` — mensagem enviada por outro cliente | **Não bloqueia.** É experimento nosso, ver Fatia 3. |
| Por que 70,6% de `negociacoes_comercial.whatsapp` é inválido (2.122 de 3.006) — migração incompleta de um sistema anterior, ou o campo só é preenchido em etapa posterior do funil? **Nota de precisão:** `invalido` é *vazio/nulo **ou** com caractere não-dígito* (`diagnostico-telefone.sql:225`), não estritamente nulo. | **Fatia 4.** Define se os 70,6% são perda a recuperar ou estado normal do topo do funil — decisões opostas. Não é pergunta para a Uazapi: é para quem opera o funil comercial. |
| Quanto da forma de 12 dígitos é fixo (núcleo 2–5) e portanto inalcançável por WhatsApp? | **Fatia 4**, e é a diferença entre teto e cobertura real. **Não depende de terceiros:** resolve rodando Q8/Q8b, já escritas em `docs/diagnostico-telefone.sql`. |
| `tentativas_de_contato` tem linhas, dado que `leads` está vazia? Se tiver, são órfãs de qual origem? | **Fatia 9**, e nem lá bloqueia — sem `leads`, a tabela é inalcançável por telefone de qualquer forma (divergência 4). É curiosidade útil sobre o histórico, não dependência. |

Nenhuma foi respondida por suposição.

## Pendências externas (destravar agora, não quando a fatia chegar)

Não são bloqueios técnicos — são pedidos de acesso com tempo de resposta de terceiros. Por isso saem agora, e não quando a fatia correspondente começar.

| Pendência | Bloqueia | Dono |
|---|---|---|
| Credencial de API do Chatwoot (account_id 2, inbox_id 4) — leitura basta | Fatia 8 | Gustavo, junto ao Bruno/Renato |
| Instância + número de teste Uazapi provisionados, com token próprio | Fatia 2 em diante | Gustavo |

**Resolvidas em 21/07/2026:**

- Dos 3 schemas com as mesmas tabelas (`unipublicabrasil3`, `4`, `5`), **`unipublicabrasil3` é produção** — confirmado com o Paulo. `docs/diagnostico-telefone.sql` foi escrito contra ele, e todas as collations e contagens deste plano vieram de lá. Os outros dois deixam de importar.
- **Acesso de leitura ao banco** — obtido, e as 7 queries da Fatia 0 rodaram. Resultado em `docs/diagnostico-telefone.md`.

A linha restante é o pressuposto que todo o plano já faz em silêncio desde a Fatia 2. Confirmar que a instância de teste existe de fato, e que não é a de produção sob outro nome.

---

## Fatia 0 — Diagnóstico de normalização de telefone — **CONCLUÍDA em 21/07/2026**

Rodou contra `unipublicabrasil3`. **Relatório agregado em `docs/diagnostico-telefone.md`**; queries em `docs/diagnostico-telefone.sql`. Os números abaixo substituem toda estimativa anterior deste plano.

### Resultado

**"Sem DDI" é a regra, não a exceção.** Entre 86,9% e 99,2% dos registros de cada tabela estão bem formados mas sem o `55`. O canônico gravado é resíduo: ≤2,8% em qualquer tabela, **zero** em `prematricula` e `leads_guia_licitacoes`.

Classes conforme `diagnostico-telefone.sql:225-231` — **`invalido` = vazio/nulo *ou* com caractere não-dígito**, não estritamente nulo; `fora_do_padrao` = só dígitos, em comprimento irrecuperável.

| coluna | linhas | inválido | fora do padrão | sem DDI | canônico | **aproveitável** | forma de 12 |
|---|---:|---:|---:|---:|---:|---:|---:|
| `students.phone` | 16.511 | 661 (4,0%) | 571 (3,5%) | 14.836 (89,9%) | 443 (2,7%) | **15.279 (92,5%)** | 5.023 |
| `users.telefone` | 14.689 | 705 (4,8%) | 805 (5,5%) | 12.763 (86,9%) | 416 (2,8%) | **13.179 (89,7%)** | 5.709 |
| `negociacoes_comercial.whatsapp` | 3.006 | 2.122 (70,6%) | 17 (0,6%) | 806 (26,8%) | 61 (2,0%) | **867 (28,8%)** | **754** |
| `prematricula.celular` | 580 | 4 (0,7%) | 39 (6,7%) | 537 (92,6%) | **0** | **537 (92,6%)** | 28 |
| `leads_guia_licitacoes.whatsapp` | 252 | 0 | 2 (0,8%) | 250 (99,2%) | **0** | **250 (99,2%)** | 1 |

`leads.celular` não aparece — tabela vazia, e a ausência da linha é o resultado esperado (divergência 9).

Na fonte principal do funil, `3.006 − 2.122 = 884`, mas a cobertura é **867**: os 17 de diferença são `fora_do_padrao`, uma terceira classe, não arredondamento.

**Sobreposição (Q6):** 61% dos ~17.376 telefones distintos aparecem em **≥2 tabelas** — há segunda fonte de confirmação na maioria dos casos.

**`users.telefone` no teto do `varchar(14)`:** 2.339 de 14.689 (15,9%) — ver divergência 6.

**`contact` (1 linha, no teto do `int`) e `corporativos` (12 linhas, `zeros = 0`)** saem do matching por volume irrelevante, não por defeito — ver divergência 5.

### A correção de leitura que o diagnóstico impôs: a Q7 mede a coisa mais estreita

A Q7 devolveu **379 núcleos (~2,2%)**, e é correta para a pergunta que faz — mas essa pergunta é *duplicação interna*: o mesmo assinante gravado nas duas formas **dentro da nossa base**. **Não é o caso de uso do matching.**

A Uazapi manda `chat.phone` **sempre com 13 dígitos**. Todo registro que normaliza para **12** nunca casa por igualdade de string — só pela variante. E a forma de 12 é **11.515 linhas, 38,2% de tudo que é aproveitável** — na fonte principal do funil, **754 de 867, ou 87%**.

**Logo a regra do 9º dígito não é tratamento de borda: é mecanismo de primeira ordem do matching.** Sem ela, `negociacoes_comercial` casa praticamente nada.

### O que ficou faltando — Q8/Q8b, escritas e não executadas

Nem todo 12 dígitos é derivável: a guarda do `CLAUDE.md` só insere `9` quando o assinante de 8 começa em **6–9**; fixo começa em **2–5** e é inalcançável por WhatsApp por este caminho. **Portanto 28,8% é teto, não previsão.** `Q8` (base toda) e `Q8b` (só `negociacoes_comercial`) foram acrescentadas ao `.sql` para separar `derivavel_celular_6a9` de `fixo_2a5_inalcancavel`. **Rodar antes de prometer qualquer número de cobertura à Fatia 4.**

Também não transcritos no relatório: padrões de formatação distintos (Q1) e distribuição fina de comprimento (Q2). Não alteram nenhuma conclusão acima.

### Desenho original da fatia (mantido para referência)

Read-only, sem código de aplicação, não depende de nenhuma outra fatia. Existe para desarmar cedo o maior risco de qualidade do projeto: o matching de CRM.

- **Entregue como SQL revisável em `docs/diagnostico-telefone.sql`, não como comando artisan.** O plano original previa `app/Console/Commands/`; um comando aqui só somaria dependências (PHP + `vendor/` + `.env`) a um trabalho feito uma vez, cujo valor está na revisão humana das queries antes de tocarem em dado real. Vira comando se precisar ser recorrente.
- Somente `SELECT` — nenhum `INSERT`/`UPDATE`/`DDL`. Rodar com usuário de banco somente-leitura, se existir.
- **Saída exclusivamente agregada — nenhum dado pessoal no relatório** (LGPD).
- **Escopo — funil comercial vivo:** `students.phone` (varchar 255), `negociacoes_comercial.whatsapp` (varchar 255), `leads_guia_licitacoes.whatsapp` (varchar 25), `prematricula.celular` (varchar 90), `users.telefone` (varchar 14).
- **`leads.celular` (varchar 50) saiu do escopo ativo, mas continua nas queries do `.sql`** (divergência 9 — tabela vazia). Custo zero, e serve de verificação de schema dentro do próprio relatório: **a ausência da linha `leads.celular` em Q1/Q2/Q3 é o esperado** — tabela sem linha não vira grupo no `GROUP BY`, então o rótulo não aparece (não aparece zerado: não aparece). **A presença dela é alarme:** ou o script rodou fora de `unipublicabrasil3`, ou a tabela foi repopulada — nos dois casos o relatório não vale e para até confirmação. Sem esta nota, quem ler a query conclui que `leads` ainda é fonte de matching — não é.
- **Fora do escopo:** backups e importações (`students2`, `studentsbkp`, `users2`, `ativos_parana`, `ativos_saoPaulo`, `ativos_santaCatarina`).
- ~~**Decisão explícita a tomar:** `contact.telefone` (int) e `corporativos.telefone` (bigint) provavelmente são inutilizáveis para matching confiável — o diagnóstico quantifica e decide.~~ **Decidido:** ficam fora, mas **por volume (1 e 12 linhas), não por defeito** — a previsão de `0` em massa não se confirmou. Ver divergência 5, reescrita com o medido.
- **Métricas por coluna:** total, nulos/vazios, distribuição de comprimento em dígitos, % com DDI 55, % com 9º dígito, número de padrões de formatação distintos.
- **Métricas pós-normalização:** telefones distintos, sobreposição entre tabelas (quantos números aparecem em ≥2 tabelas — é exatamente o que o painel vai unir), ambiguidade 8↔9 dígitos.
- **Quantos números casariam apenas pela variante do 9º dígito** (regra de comparação do `CLAUDE.md`). É o número que diz se isso é detalhe de borda ou uma fatia relevante da base — e sai de graça, já que o diagnóstico varre todas as colunas de qualquer forma.
- **Quantificar os problemas já conhecidos:** quantas linhas de `contact.telefone` estão exatamente em 2147483647 (sinal de valor limitado no insert); **distribuição de comprimento em dígitos de `corporativos.telefone`** (é o que mostra se há perda de zero à esquerda ou DDI ausente, já que overflow ali não existe); **contagem de linhas com valor `0`** nas duas colunas; e quantas de `users.telefone` já ocupam os 14 caracteres sem folga.

**Critério de pronto — ATENDIDO**, com uma ressalva registrada: `docs/diagnostico-telefone.md` traz a taxa de aderência ao canônico por coluna e a cobertura de matching, mas a cobertura de `negociacoes_comercial` está entregue como **teto (28,8%)**, não como número final — falta Q8/Q8b. O relatório diz isso em vez de omitir.

**A taxa de inválidos de `negociacoes_comercial.whatsapp` é métrica de primeira ordem do relatório** — 2.122 de 3.006 (70,6%). Com `leads` vazia, é o número que decide a viabilidade do painel de CRM.

**Fora:** qualquer escrita, qualquer correção de dado.

---

## Fatia 1 — Fundação: fila como rede de segurança + contrato de provedor — **CONCLUÍDA em 22/07/2026**

> **Estado. Critério de pronto fechado, observado.** Ambiente montado no mesmo dia (PHP 8.5.8, Composer 2.10.2, MySQL 9.7.1 local, schema `unyflex_dev` vazio). `php artisan fila:ping` gravou a linha em `jobs`; `php artisan schedule:run` executou o `queue:work`; `laravel.log` registrou `{"despachado_em":...,"executado_em":...,"atraso_s":0.33}`; `jobs` voltou a `COUNT(*) = 0`. O ciclo inteiro rodou, não foi inferido.
>
> Também verificado: `0001` aplicado à mão (`mysql unyflex_dev < ...up.sql`); a guarda de ambiente do `UazapiProvider` **resolve** com instância de teste e **lança** `RuntimeException` quando `UAZAPI_INSTANCE_NAME` aponta para um valor da denylist — testado com valor sentinela, sem precisar do nome real da instância de produção; `enviarTexto()` lança `LogicException` como portão da Fatia 6.
>
> **Ressalva de ambiente, não da fatia:** este PHP é 8.5, mais novo que o `^8.1` do `composer.json`. Ver "Deprecation do PHP 8.5" abaixo.
>
> Entregue: `database/sql/0001_*`, `config/uazapi.php`, `app/Contracts/WhatsappProviderContract.php`, `app/Services/Whatsapp/UazapiProvider.php`, `app/Jobs/FilaPing.php`, `app/Console/Commands/FilaPingCommand.php`, binding em `AppServiceProvider::register()`, schedule em `app/Console/Kernel.php`, chaves `UAZAPI_*` no `.env.example`.

- `database/sql/0001_create_jobs_table.up.sql` / `.down.sql` — tabela `jobs` padrão do Laravel 10.
- `.env` de dev: `QUEUE_CONNECTION=database`.
- Scheduler (`app/Console/Kernel.php`, hoje vazio) agenda `queue:work --stop-when-empty --max-time=50` a cada minuto. Sem supervisor em produção, conforme decisão do time.
- Interface `App\Contracts\WhatsappProviderContract` definida antes de qualquer chamada à Uazapi, com um único implementador (`UazapiProvider`). Isola o provedor para troca futura pela API oficial (risco #5).
- `config/uazapi.php` no formato de `config/asaas.php`, **sem secret literal como fallback**, com as chaves documentadas em `.env.example`.

**Critério de pronto:** um job de teste, disparado manualmente, é processado por `php artisan schedule:run`.

**Fora:** qualquer chamada real à Uazapi, qualquer UI.

---

## Fatia 2 — Receber e persistir cru (instância de teste) — **VERIFICADA POR CURL em 22/07/2026; falta a mensagem real**

> **Estado.** `0002` aplicado; `SHOW INDEX` confirma `wa_raw_message_id` **único sobre coluna nullable** e `wa_raw_processed_at`. Testado contra `php artisan serve`:
>
> | Teste | Esperado | Observado |
> |---|---|---|
> | Config de token vazia | 500, não 200 | **500**, corpo `"UAZAPI_INSTANCE_TOKEN nao configurado."` — é a guarda, conferido no corpo |
> | Token válido | 200 + 1 linha | **200**, linha com `payload` íntegro |
> | **Replay do mesmo corpo** | 200 + **nenhuma** linha nova | **200**, nenhuma linha nova |
> | Token errado | 401 | **401** |
> | Dois eventos **sem** `message.id` | 2 linhas, ambas `NULL` | **2 linhas**, ambas `message_id NULL` |
> | Contagem | distintos == com id | `total=3, com_id=1, distintos=1` |
>
> **O que NÃO foi verificado, e por quê:**
>
> - **Mensagem real e mensagem de grupo.** Exigem que a Uazapi alcance a aplicação; não há rota pública nem túnel nesta máquina. São os únicos dois critérios de pronto em aberto.
> - **Nomes de campo seguem sem confirmação.** `instance` e `event_type` gravaram nos testes, mas **os payloads de curl fui eu que escrevi, usando os próprios nomes candidatos** — isso prova o `primeiroPresente()`, não prova nada sobre a Uazapi. Se todos os candidatos errarem contra o payload real, as duas colunas ficam `NULL` e **nada se perde**: o `payload` cru é a autoridade. Reduzir a lista ao nome certo assim que o primeiro payload real chegar.
>
> **A armadilha do `hash_equals`.** `hash_equals('', '')` é `true`. Com `UAZAPI_INSTANCE_TOKEN` vazio, um POST sem token nenhum passaria. O `abort_if` antes da comparação é o que devolveu o 500 acima — verificado, não presumido.
>
> **Desvio observado:** POST com corpo vazio devolve **401, não o 400** de `Payload vazio.`. Corpo vazio não carrega token, então a validação de token dispara antes — auth primeiro é a ordem correta, mas o `abort_if(400)` só é alcançável quando o token vem por query string. Comportamento aceitável; anotado para não parecer defeito depois.

Exclusivamente na **instância e número de teste**. Sem fan-out, sem tráfego de produção, sem tocar em config de instância.

- Rota síncrona e leve. Valida o `token` do body contra o token configurado, seguindo o padrão `validarSecret()` (`hash_equals` + `abort_unless`) adaptado para ler do body em vez do header.
- Persiste o **payload cru inteiro** (tabela nova via SQL versionado, ex. `raw_whatsapp_events`) com índice único sobre `message.id` (`owner:messageid`) — reenvio do webhook não duplica.
- Grupos (`wa_isGroup`, `@g.us`) são persistidos como cru normalmente. O filtro de grupo é de **exibição**, nunca de ingestão.
- Nenhuma lógica de negócio dentro do request.

**Critério de pronto:** mandar uma mensagem real para o número de teste e ver o payload persistido; replay manual do mesmo webhook não cria duplicata. *(Replay: fechado. Mensagem real: pendente, falta rota pública.)*

**Fora:** UI, processamento estruturado, mídia, envio, tráfego de produção.

---

## Deprecation do PHP 8.5 (ambiente, não código)

Registrado em 22/07/2026, ao montar o ambiente com PHP 8.5.8 — mais novo que o `^8.1` do `composer.json` e que o 8.3 até onde o Laravel 10 é testado.

```
Deprecated: Constant PDO::MYSQL_ATTR_SSL_CA is deprecated since 8.5,
use Pdo\Mysql::ATTR_SSL_CA instead in config/database.php on line 62
```

Aparece em **toda** execução de `artisan` e no início de toda resposta HTTP com `display_errors` ligado — inclusive **antes do JSON do webhook**, o que sujaria o corpo da resposta para um cliente estrito. Em dev é ruído; em produção `display_errors` fica desligado.

**Não corrigir.** `Pdo\Mysql::` só existe a partir do PHP 8.4: trocar a constante quebraria a máquina de quem estiver em 8.1–8.3. É divergência de ambiente local, não defeito do repo. Se o time padronizar PHP ≥ 8.4, aí sim vale trocar — e aí é mudança em `config/database.php`, config compartilhada, com aviso antes (regra de ouro 9).

---

## Fatia 3 — Ver (processamento + lista + thread) — **CONSTRUÍDA em 22/07/2026; dois critérios em aberto**

> **Estado.** `0003` (conversations + messages) e `0004` (rastreio de falha no cru) aplicados em `unyflex_dev`. Entregue: `app/Support/TelefoneCanonico.php`, `app/Jobs/ProcessarEventoWhatsapp.php`, `app/Console/Commands/VarrerEventosCrus.php`, models `WhatsappConversation`/`WhatsappMessage`, `WhatsappInboxController`, views `admin/whatsapp/{index,thread}`, rotas em `routes/web.php`, agendamento de `whatsapp:varrer`, despacho `afterResponse` no webhook.
>
> **Verificado de fato:**
>
> | Teste | Observado |
> |---|---|
> | Processar cru → estruturado | conversa + mensagem criadas; `processed_at` preenchido |
> | Reprocessar o mesmo cru | **nenhuma duplicata** (`updateOrCreate` contra `wa_msg_provider_id`) |
> | Payload malformado | `process_error` gravado, `process_attempts` 1→2→3, e a **4ª varredura ignora** — o loop silencioso não acontece |
> | Payload de grupo | persistido com `is_group=1`, **ausente da lista** e **404 por link direto** |
> | Timestamp em ms | `1784724000000` → `2026-07-22 12:40:00`, sem cair em 1970 |
> | Views | ambas renderizam; a lista informa quantos grupos foram capturados e ocultados |
>
> **Defeito de desenho achado pelo próprio teste:** a primeira versão exigia um `chat.id` no payload e falhava quando só havia `chat.phone`. Corrigido com uma regra explícita — **1:1 é chaveado pelo telefone canônico, grupo pelo id `@g.us`**. A alternativa (fabricar `telefone@s.whatsapp.net`) foi rejeitada: se o payload real trouxer id em outro formato, as duas formas não colidiriam no índice único e a mesma conversa viraria duas linhas.
>
> **Em aberto, e não dá para fechar nesta máquina:**
>
> - **Aferição do `afterResponse`.** Confirmado por execução que `function_exists('fastcgi_finish_request')` é **false** aqui. Sob `artisan serve` o processamento roda antes da resposta — passou nos testes, mas isso **não prova** o ganho de latência. **Nenhum número de latência é afirmado até medir sob php-fpm.** Plano B inalterado: se não flushar, volta para a fila por cron; a persistência síncrona do cru não muda.
> - **Experimento `fromMe`/`wasSentByApi`/`source`** — exige mensagem real na instância de teste.
> - **O parser foi testado contra payloads que eu mesmo escrevi.** Os campos vindos do `CLAUDE.md` (`chat.phone`, `message.id`, `sender_pn`, `wa_isGroup`, ms) são firmes; os de **texto, tipo e timestamp** são listas de candidatos, mesma família da pendência de `instance`/`EventType`. Isso exercita o código, não confirma o formato da Uazapi.

- Processamento do cru → tabelas estruturadas (`conversations`, `messages`, via SQL versionado), disparado **logo após a resposta HTTP** do webhook com `dispatch(...)->afterResponse()` — mesmo processo, sem depender do worker. *(Assinatura confirmada em 22/07/2026 com `vendor/` instalado: `PendingDispatch::afterResponse()`, sem argumentos — `vendor/laravel/framework/src/Illuminate/Foundation/Bus/PendingDispatch.php:145`.)*
- **Por que não broadcaster.** O gargalo não é o transporte até o browser, é a ingestão: com worker via cron a cada minuto, uma mensagem espera de 0 a 60s (média ~30s) para virar linha estruturada. Um websocket entregando em 50ms um dado que chegou 30s atrasado não resolve o problema, só o disfarça. O `afterResponse` deve derrubar o piso de ~60s para ~1s no caminho comum, sem daemon, sem dependência nova e sem config compartilhada nova — **expectativa a aferir, ver abaixo**, não medição feita.
- **Durabilidade preservada.** O cru é persistido **sincronamente antes** da resposta (Fatia 2). O `afterResponse` é best-effort; o worker por cron vira **rede de segurança**, varrendo periodicamente os payloads crus ainda não processados. Se o `afterResponse` falhar, o processo morrer ou a app cair, a varredura reprocessa. A garantia do risco #2 continua de pé.
- **Ressalva:** `afterResponse` segura o processo php-fpm enquanto roda, então o processamento precisa ser leve. Trabalho pesado (download de mídia, quando entrar) vai para a fila real.

**Aferição obrigatória antes de dar a fatia por resolvida.** `dispatch(...)->afterResponse()` roda no ciclo `terminate` e só devolve a resposta antes do trabalho se `fastcgi_finish_request` existir e estiver habilitado sob php-fpm. Em `artisan serve` (servidor embutido do PHP) a função **não existe** — o cliente espera o job terminar, e um teste ali passaria dando a impressão errada.

- Endpoint isolado e descartável que despacha via `afterResponse` um job que só dorme e loga `microtime` na conclusão; o cliente loga o instante em que recebeu a resposta.
- **Passa** se o cliente recebe a resposta antes do log de conclusão. **Falha** se os dois instantes coincidem — a resposta ficou presa.
- Rodar no ambiente mais próximo de produção disponível (php-fpm), nunca em `artisan serve`. Conferir `function_exists('fastcgi_finish_request')` no ambiente alvo.
- **Plano B, se falhar:** volta para a fila via cron. Muda só a latência prometida (~30s p50), **não a segurança do dado** — o cru continua persistido sincronamente antes da resposta, e a varredura por cron continua sendo a rede de segurança.
- Enquanto essa aferição não for feita, **todo número de latência neste plano é provisório**.
- UI Blade: lista de conversas 1:1 (grupos persistidos, não exibidos — decisão #6) + thread com histórico. Atualização só por refresh manual nesta fatia.
- **Experimento a fazer aqui** (não é pergunta para a Uazapi): mandar uma mensagem pelo celular físico e outra pela API na instância de teste, capturar os dois payloads e comparar `fromMe`, `wasSentByApi` e `source`. Documentar o resultado no `CLAUDE.md`.

**Critério de pronto:** abrir o painel e ver a conversa de teste com texto correto e ordem cronológica; o experimento `fromMe` documentado; e a aferição do `afterResponse` feita sob php-fpm, com o número de latência confirmado ou corrigido.

**Fora:** atualização automática, resposta, atribuição, mídia.

---

## Fatia 4 — Painel de CRM, fatia fina

Antecipado de propósito: valida o matching por telefone enquanto ainda dá tempo de corrigir o desenho, em vez de descobrir o problema no fim.

- `chat.phone` normalizado para o **formato canônico** do `CLAUDE.md` (decisão #8 — nunca `sender`/`sender_lid`), com a cobertura já medida pela Fatia 0.
- Colunas novas de telefone nascem `varchar(20)`. Nenhuma coluna legada é alterada — a normalização é na leitura.
- **Prefixar `55` é o caminho comum da normalização, não um fallback** — medido na Fatia 0: 86,9% a 99,2% dos registros de cada tabela estão sem DDI, e o canônico gravado é ≤2,8% (zero em `prematricula` e `leads_guia_licitacoes`). Quem escrever a normalização tratando "sem DDI" como caso de exceção acerta a minoria.
- **Regra do 9º dígito desde o primeiro commit desta fatia** (`CLAUDE.md`, "Comparação"): o matching é onde ela nasce, então não é algo a retrofitar na Fatia 9. Match direto primeiro; falhando, testar a variante com e sem o 9º dígito, com a guarda de faixa 6–9; só então "não identificado". Um cadastro antigo de 8 dígitos e o `chat.phone` de 13 que a Uazapi manda são o mesmo assinante, e por igualdade de string nunca batem.
- **A regra do 9º dígito é mecanismo de primeira ordem, não borda — e o número que parece dizer o contrário mede outra coisa.** A Q7 devolveu 379 núcleos (~2,2%), mas conta *duplicação interna* (o mesmo assinante nas duas formas dentro da nossa base). O que importa aqui é que a Uazapi manda 13 dígitos sempre, e **38,2% da base aproveitável normaliza para 12** — em `negociacoes_comercial`, **87% (754 de 867)**. Sem a variante, a fonte principal do funil casa praticamente nada.
- Painel lateral na thread mostrando **apenas**: nome + qual tabela casou, ou "não identificado".
- Models Eloquent criados só para as tabelas que o matching precisar — **`negociacoes_comercial` primeiro**, por ser a fonte principal do funil (nenhum model existe hoje para ela nem para `prematricula`). Nada de model para `leads`: tabela vazia, ver divergência 9.
- **Teto de cobertura conhecido, medido, e é baixo.** `negociacoes_comercial` tem **867 de 3.006 (28,8%)** de telefone aproveitável, e `leads` está vazia. **A parcela fixa dentro da forma de 12 foi medida em 22/07/2026 (Q8/Q8b) e é pequena: 589 deriváveis contra 39 fixos, de 628 telefones distintos — 93,8% deriváveis.** A regra do 9º dígito entrega o que se esperava dela.
- **A cobertura tem duas unidades e elas não se dividem.** `867/3.006` e `754` são **linhas**; `589/628` são **telefones distintos** (Q8/Q8b deduplicam antes de classificar). As 754 linhas contêm 628 números — as 126 de diferença são repetição, não perda. **`589/754` não é taxa de nada.**
- **A cobertura em linhas ainda NÃO fechou, e é o que trava esta fatia.** A Q8c voltou 755 contra as 754 da Q3. A lógica das duas foi **provada equivalente** contra 35 casos de fronteira num MySQL local (zero discordâncias), então a causa provável é deriva de dado: a Q3 rodou em 21/07, a Q8c em 22/07, e a tabela é escrita todo dia. **A Q9 (reconciliação atômica, as duas classificações na mesma varredura) decide.** Enquanto ela não voltar, nenhuma taxa de cobertura em linhas entra em nada.
- **Quatro categorias de não-alcançável, não duas** (tabela no `CLAUDE.md`): `invalido` e `fora_do_padrao` são ausência de dado; `fixo_2a5_inalcancavel` é **dado válido sob guarda intencional** e nunca deve ser "corrigido"; `anomalo_0ou1` (bloco de 8 começando em 0/1 — 9 na base inteira, 0 em `negociacoes_comercial`) é **dado quebrado**. O painel não precisa mostrar as quatro, mas quem escrever o matching não pode tratá-las como a mesma coisa. **"Não identificado" será o estado comum, não a exceção**. Isso é fato de desenho, não defeito a corrigir depois: a UI trata esse estado como caminho normal — não como erro, não como espaço vazio num canto da tela. Se o painel só ficar apresentável quando há match, ele fica feio na maioria das conversas reais.

**Critério de pronto:** abrir a conversa de teste e ver o nome real de um registro de `negociacoes_comercial` ou `students` cujo telefone bate com `chat.phone`; casar também um cadastro gravado sem o 9º dígito; o estado "não identificado" só aparecer **depois** de a variante ter sido testada; e esse estado ser exercitado e revisado como tela, não só como ausência de dado.

**Fora:** funil, histórico de compra, turma, valor — tudo isso é Fatia 9. Nenhuma escrita nas tabelas de CRM.

---

## Fatia 5 — Atualização automática na tela

- Polling incremental por cursor a cada 5s, seguindo o padrão já existente em `checkout.blade.php:618-656` (inline `<script>`, `fetch` com `X-CSRF-TOKEN` + `Accept: application/json`, `stopPolling()` explícito).
- Endpoint leve devolvendo só o delta desde a última mensagem vista.
- **Latência total resultante — número provisório, pendente da aferição da Fatia 3:** p50 ~3,5s (≈1s de ingestão + meio intervalo de polling), contra os ~30s do desenho com worker por cron. Se o `afterResponse` não flushar a resposta no ambiente real, este número volta para ~32s e a fatia continua válida — só deixa de ser instantânea.
- **Reversível:** se o volume um dia justificar websocket, o broadcaster entra sem refazer modelo de dados nem processamento.

**Critério de pronto:** com a tela aberta, mandar mensagem de teste e vê-la aparecer sem F5 em até ~5s.

**Fora:** qualquer dependência nova (Pusher/Reverb/Echo).

---

## Fatia 6 — Responder (instância de teste)

- Envio de texto via `WhatsappProviderContract` → `UazapiProvider`, na instância de teste.
- Guarda em runtime: o provedor recusa envio quando o ambiente não é produção e a instância configurada é a de produção.
- Mensagem enviada aparece na thread como própria.

**Critério de pronto:** responder pela nossa tela e ver a mensagem chegar no WhatsApp real do número de teste.

**Fora:** mídia, respostas prontas, templates, **e qualquer envio em produção** — isso só depois da Fatia 8.

---

## Fatia 7 — Atribuir atendente

- Campo de atribuição próprio. `lead_assignedAttendant_id` da Uazapi é ignorado — não ler, não escrever (decisão #7).
- Atendente = usuário em `users`, usando `power`/`AdminRole` (13 = Comercial). Nota: `power` é `int` nullable, e `User.php` não tem nenhum relacionamento nem helper de papel definido hoje.
- A mudança de atribuição também aparece no polling da Fatia 5 — não só mensagem nova.

**Critério de pronto:** atribuir uma conversa de teste a um usuário Comercial e ver a mudança refletida em outra sessão aberta.

**Fora:** filas, times, SLA.

---

## Fatia 8 — Modo sombra em produção

Primeira fatia que toca tráfego real. **Recebe e armazena, nunca envia.** É a porta que libera o envio em produção.

**Como receber tráfego de produção sem tocar na config da instância** — ordem de preferência:

1. **SSE da Uazapi — hipótese, não capacidade confirmada.** A atração é ser aditivo: não mexe na configuração de webhook existente. **O que não se sabe:** a doc da Uazapi não diz se o SSE entrega histórico/backfill na reconexão ou só eventos da sessão aberta. Se for só ao vivo, qualquer queda de conexão vira lacuna permanente — e lacuna permanente reprova o critério de ≥99,5% por um motivo que não tem nada a ver com a nossa ingestão.
   **Teste que resolve, na instância de teste, antes de produção:** abrir o SSE, derrubar a conexão, mandar mensagens com ela caída, reconectar e verificar se as do intervalo chegam.
2. Segunda URL de webhook, se a instância aceitar fan-out nativo (pergunta em aberto). **Sobe para primeira opção se o SSE não tiver backfill.**
3. **Rejeitado em qualquer cenário:** nós recebermos e repassarmos ao Chatwoot. Isso nos coloca no caminho crítico da produção — exatamente o que o risco #3 proíbe.

A ordem acima fica **condicionada ao resultado do teste do item 1**. Nenhuma das duas primeiras está decidida.

**Chave de casamento — não depende de timestamp.** A janela de ±60s da primeira redação era do mesmo tamanho do p99 de latência que este plano se dá: registraria como "perda" a nossa própria fila atrasada, e o critério se autoinvalidaria. Correção:

- **Chave primária:** telefone no formato canônico + hash do conteúdo textual normalizado da mensagem.
- **Timestamp só como desempate**, quando o mesmo telefone repete conteúdo idêntico — aí sim com janela de **±120s**.
- **Paridade e latência são métricas independentes.** Latência alta vira alerta de latência, não perda de mensagem.

**Critério numérico de saída:**

- **7 dias corridos** de captura contínua, incluindo ≥1 fim de semana.
- **≥99,5% de paridade** de mensagens 1:1 contra o Chatwoot no período, pela chave acima.
- **0 duplicatas** exibidas (mesma `message.id` gerando duas linhas).
- **100% das divergências com causa classificada** (grupo, mídia, evento fora de escopo). Divergência sem causa identificada **zera o contador e reinicia a janela de 7 dias**.
- Latência `messageTimestamp` → persistência do cru: **p95 ≤ 10s, p99 ≤ 60s** — medida à parte, sem entrar no cálculo de paridade.
- **0 incidentes** em que nosso endpoint exigiu qualquer intervenção no fluxo do Chatwoot.

**Instrumento de aferição:** comando de comparação que faz o diff da nossa base contra a do Chatwoot na janela e emite o relatório. Depende da credencial listada em **Pendências externas**, no topo deste plano.

**Fora:** envio. Liberar envio em produção é decisão explícita **depois** de bater o critério.

---

## Fatia 9 — Painel de CRM completo

- Expande a Fatia 4: funil, histórico de compra, turma, valor.
- `students` → `enrollments` → `classes` → `courses`; `negociacoes_comercial` (ligada direto a `classes_id`); `leads_guia_licitacoes` (com UTMs).
- **Fora enquanto `leads` estiver vazia:** histórico de tentativas de contato via `id_lead` → `leads`. `tentativas_de_contato` não tem telefone próprio (divergência 4) e o único caminho até o número passa por uma tabela sem linhas (divergência 9) — reativar exige a confirmação descrita em "Tabelas vazias em produção" no `CLAUDE.md`.
- **A regra do 9º dígito vale para todas as tabelas do funil**, não só a que a Fatia 4 tocou. Cada nova tabela ligada ao painel entra pela mesma função de matching — não reimplementar comparação de telefone por tabela.

**Critério de pronto:** thread mostrando origem, funil, turma e valor de um lead real de teste.

**Fora:** edição do CRM pela inbox, busca avançada, qualquer escrita nas tabelas de CRM.

---

## Registrado para o futuro (fora do MVP)

`message.track_id` / `message.track_source` existem no schema da Uazapi (vazios no payload capturado). Se a Uazapi propaga origem de clique de anúncio (CTWA) até a mensagem, isso ligaria WhatsApp → campanha Meta Ads sem UTM manual. Avaliar quando os campos vierem preenchidos em produção.
