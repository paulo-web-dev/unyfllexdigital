# CLAUDE.md

Guia deste repositório para quem (humano ou agente) for trabalhar aqui. Trabalho em time nas branches `PAULO` e `GUSTAVO`.

## Stack

- Laravel 10, PHP ^8.1, Sanctum para auth.
- Blade + Bootstrap 5 + JS vanilla. **Sem SPA** (sem React/Vue). Vite é opcional, só para hot-reload em dev — o build final usa `public/css/site.css` e `public/js/app.js` diretamente.
- MySQL, mas **o schema não é gerido por migrations**: há só 5 migrations no repo (boilerplate do Laravel + Sanctum + uma tabela nova), contra ~248 tabelas reais no banco. Ver "Schema e SQL versionado" abaixo.

## Comandos

```bash
composer install
npm install && npm run dev   # ou npm run build — opcional, ver acima
php artisan serve
php artisan test             # phpunit — hoje só os testes default do Laravel, sem suíte real
php artisan schedule:run     # dispara o scheduler (cron chama isto a cada minuto em produção)
php artisan queue:work --stop-when-empty --max-time=50
```

Não há supervisor de fila em produção — o worker roda via cron + scheduler, não como processo persistente.

**PHP 8.5 emite uma deprecation em toda execução.** `config/database.php:62` usa `PDO::MYSQL_ATTR_SSL_CA`, deprecada em favor de `Pdo\Mysql::ATTR_SSL_CA`. Sai no stderr do `artisan` e, com `display_errors` ligado, **antes do corpo da resposta HTTP** — o que polui JSON de API em dev. **Não trocar:** `Pdo\Mysql::` só existe no PHP ≥ 8.4 e a troca quebraria quem estiver em 8.1–8.3, faixa que o `composer.json` (`^8.1`) permite. Só faz sentido quando o time padronizar 8.4+.

**Isto já quebrou uma feature inteira, em silêncio — leia antes de debugar qualquer coisa que consuma JSON em dev.** Em 22/07/2026 a poluição do corpo derrubou o polling da inbox (Fatia 5): `res.json()` estoura no cliente, o laço conta 3 falhas e se desliga sozinho **15 segundos** depois de carregar a página. Não há erro no servidor, não há log, e o único sinal na tela é um texto cinza no canto trocando "Atualizando automaticamente" por "Atualização automática pausada". O recurso nunca funcionou uma vez no navegador, e **19 testes de servidor passaram verdes o tempo todo** — o test client do Laravel entrega o objeto de resposta, nunca os bytes brutos. **`assertJson` verde não é o mesmo que JSON válido no fio.** Sintoma a reconhecer: tudo certo no banco, tudo certo no controller, e a tela não atualiza.

Correção **local, fora do repo, não versionada** — cada um faz na sua máquina, e o PHP do Homebrew não cria esse diretório sozinho:

```ini
; /opt/homebrew/etc/php/8.5/conf.d/99-display-errors-off.ini   (mkdir -p no conf.d)
display_errors = Off
```

Espelha o que o `HandleExceptions` do Laravel faz de qualquer jeito, só que desde o começo do bootstrap — `LoadConfiguration` roda **antes** dele, e é essa janela que deixa a deprecation escapar. Os erros continuam no `error_log` (terminal), só saem do corpo HTTP. Depois de criar, **reiniciar o `artisan serve`**: o processo em execução carregou o ini antigo. Teste que vale: `curl -s <endpoint> | head -c 1` tem que devolver `{` — verificar "tem JSON no meio" não serve, porque tinha.

## Arquitetura e convenções vivas

- **Controllers:** `app/Http/Controllers/Admin/` (área administrativa) e `app/Http/Controllers/Ava/` (área do aluno). Controllers soltos na raiz de `Controllers/` são páginas públicas/marketing.
- **Autorização por `power`:** middleware `IsAdmin` (`app/Http/Middleware/IsAdmin.php`) bloqueia quando `auth()->user()->power < 13`. O enum `App\Enums\AdminRole` (`app/Enums/AdminRole.php`) mapeia esse inteiro em papel: `power >= 14` → Super Admin, `power === 13` → Comercial, qualquer outro valor → Aluno (o enum trata isso como `default` do `match`, não como uma checagem literal `<= 10` — não assuma que 11/12 têm tratamento próprio).
- **Callbacks de automação (n8n):** ficam em `routes/api.php`, fora do grupo CSRF, protegidos por header `X-Webhook-Secret` verificado no controller. Use este padrão para qualquer webhook novo que não seja de usuário autenticado.
- **Arquivos-instrução como patch entre membros do time:** `app/Http/Middleware/LEIA-KERNEL.php` e `routes/checkout-rotas.php` são arquivos PHP não executados — comentários com instruções de o que colar manualmente em `Kernel.php`/`web.php`. É assim que o time troca mudanças de rota/middleware sem conflito de merge direto nesses arquivos centrais. Se você criar uma mudança desse tipo, siga o mesmo padrão: um arquivo-instrução novo, não edição direta.
- **Webhook Asaas (`app/Http/Controllers/WebhookController.php::asaas`)** processa tudo síncrono dentro do request e não persiste o payload cru (só loga). **Não é o padrão a seguir** para o webhook Uazapi — é uma divergência conhecida e antiga. Não refatore o Asaas como parte de outro trabalho.

## Schema e SQL versionado

Sem migrations para tabelas de negócio. Toda mudança de schema é um script SQL em `database/sql/`, sempre em par:

```
database/sql/NNNN_descricao.up.sql
database/sql/NNNN_descricao.down.sql
```

Aplicado manualmente no banco (não roda via `artisan migrate`). Mantém reversibilidade sem violar a convenção do time de gerir schema fora de migrations.

Pares existentes: `0001_create_jobs_table`, `0002_create_whatsapp_raw_events`, `0003_create_whatsapp_conversations_messages`, `0004_alter_whatsapp_raw_events_erro`, `0005_alter_whatsapp_conversations_atendente`.

O diretório passou a existir em 22/07/2026, com os dois primeiros pares: `0001_create_jobs_table` (fila) e `0002_create_whatsapp_raw_events` (payload cru da Uazapi). Numeração sequencial de 4 dígitos, `CREATE TABLE IF NOT EXISTS` no `.up`, `DROP TABLE IF EXISTS` no `.down`, e um comentário de cabeçalho dizendo o que a reversão destrói quando a perda for irreversível — o cru da Uazapi não se reconstrói, o provedor não reenvia sob demanda.

## Integração Uazapi (WhatsApp)

Provedor: Uazapi (`https://unyflexdigital.uazapi.com`, doc em `https://docs.uazapi.com/`). **A doc é um SPA e não entrega conteúdo por fetch/curl — o spec real está em `https://docs.uazapi.com/openapi-bundled.json`** (uazapiGO 2.1.1, OpenAPI 3.1, schemas `Message`, `Chat`, `WebhookEvent`). Quem tentar ler a doc pela URL de cima recebe só o título da página e conclui, errado, que não há documentação. Hoje o fluxo em produção é `Uazapi → Chatwoot (account_id 2, inbox_id 4, canal Channel::Api) → webhook Chatwoot → n8n`. O `Channel::Api` sugere integração nativa Uazapi↔Chatwoot, não bridge via n8n — **confirmar antes de mexer em config de instância**.

Payload de mensagem recebida (webhook):

- Autenticação: `body.token` **dentro do body**, não em header. Validar comparando com o token da instância configurado, a cada request.
- Idempotência: `body.message.id` (formato `owner:messageid`) — índice único, chave de deduplicação.
- Timestamps em milissegundos.
- Telefone real do remetente: `body.chat.phone` (limpo) ou `body.message.sender_pn`. **Nunca** `body.message.sender`/`sender_lid` — isso é um LID interno da Uazapi, não telefone.
- Grupos chegam pela mesma instância (`wa_isGroup`, `@g.us`).

**Nomes de campo conferidos contra o spec em 22/07/2026.** Antes disso o parser carregava listas de candidatos inventadas a partir do briefing. O que a doc fechou e o que ela derrubou:

| o que | nome real (schema) | o que saiu do código |
|---|---|---|
| texto | `message.text` | `body`, `caption` — **não existem**; e `content` é "conteúdo bruto (JSON serializado ou texto)", que como fallback gravaria JSON dentro de `texto` |
| tipo | `message.messageType` | `type`, `mediaType` — não existem |
| timestamp | `message.messageTimestamp` (integer, ms explícito) | `timestamp`, `t` — não existem |
| grupo, na mensagem | `message.isGroup` | `message.wa_isGroup` — o prefixo `wa_` só existe no schema `Chat` |
| nome exibido | `chat.name` → `chat.wa_contactName` → `chat.wa_name` (push name) → `message.senderName` | — |

**`chat.id` NÃO é o JID.** A doc define `chat.id` como "ID único da conversa (`r` + 7 bytes aleatórios em hex)" — id interno da Uazapi. O JID completo é **`chat.wa_chatid`**. O parser chegou a ter `chat.id` em primeiro lugar na busca do chat id, o que teria chaveado grupo por `r1a2b3c4` em vez do `@g.us`, contra a regra logo abaixo. Falha silenciosa: a coluna preenche, o índice único não reclama, e só aparece ao cruzar com o provedor. **Não reintroduzir `chat.id` como identificador de conversa.** O mesmo vale para `message.id`, que é interno (`messageid` é o do provedor).

**O que a doc NÃO resolveu, e continua lista de candidatos:**

- **O envelope.** O schema `WebhookEvent` declara `{event, instance, data}` com `data` livre ("segue o que o backend envia em `callHook`") e **não traz exemplo de payload de mensagem**. Nosso código lê a forma plana (`message.*`, `chat.*`), vinda do briefing. A doc não confirma nem refuta — então o parser tenta os dois, plana primeiro e `data.` depois (`ProcessarEventoWhatsapp::caminhos()`). Um dos dois lados morre no primeiro payload real.
- **A chave de idempotência.** A doc separa `message.id` (interno) de `message.messageid` (do provedor); este arquivo diz que `message.id` chega como `owner:messageid`. Os dois são únicos, então a dedução funciona de qualquer jeito — **mas não trocar a chave do índice único com linhas já gravadas por causa de leitura de doc**. Decide o primeiro payload real.
- **`fromMe` / `wasSentByApi` / `source` existem e estão documentados** (respectivamente: enviada pelo usuário, enviada via API, plataforma de origem). O experimento da Fatia 3 encolheu para observar os **valores** numa mensagem real — os nomes já estão confirmados.

**Configuração de webhook — uma recomendação e uma armadilha.** A doc recomenda `excludeMessages: ["wasSentByApi"]` para evitar loop quando a automação envia pela API (vale para a Fatia 6). A **mesma** lista de filtros oferece `isGroupNo`, que **descartaria mensagem de grupo na ingestão e violaria a regra de ouro 8** — grupo não é exibido, mas é sempre persistido. Não marcar. Ambiguidade a resolver ao configurar a instância: a config usa `events: ["messages"]` (plural), o enum de `WebhookEvent` diz `"message"` (singular).

A integração fica isolada atrás de um contrato próprio (`WhatsappProviderContract` ou equivalente) — ver `plan.md`, Fatia 1 — para permitir trocar de provedor (ex. API oficial do WhatsApp) sem reescrever a aplicação.

**Chave da conversa (`whatsapp_conversations.wa_chat_id`), decidida na Fatia 3:** conversa **1:1 é chaveada pelo telefone canônico**; **grupo, pelo id `@g.us`** do provedor. Grupo não tem telefone único, e o telefone é a identidade que a regra de ouro 4 manda usar. **Não fabricar um chat id sintético** (`telefone@s.whatsapp.net`): se o payload real trouxer id em formato diferente, as duas formas não colidem no índice único e a mesma conversa vira duas linhas.

**Normalização de telefone tem um lugar só:** `app/Support/TelefoneCanonico.php` — `normalizar()` (dígitos + DDI) e `variante()` (derivação do 9º dígito, com a guarda 6–9). **Não criar uma segunda normalização em outro canto do app**; `LeadGuia::whatsappLink()` é o exemplo do que não repetir (normaliza por conta própria, sem regra de 9º dígito).

`variante()` recebe entrada **já canônica** e devolve `null` para qualquer outra coisa — de propósito: normalizar lá dentro esconderia um chamador que pulou a normalização. A guarda 6–9 vale **nos dois sentidos**, o que garante `variante(variante($x)) === $x`. O sentido menos óbvio é 13 → 12: um celular válido hoje cujo bloco pós-`9` caia em 2–5 **não tem forma legada**, e derivá-la afirmaria equivalência com um número que pode ser de outra pessoa.

### Configuração e amarração à instância de teste

Segue a convenção de config do repo: **um arquivo dedicado por integração** (como `config/asaas.php`), nunca `config/services.php`. `env()` só dentro do arquivo de config; app code lê por `config()` — ver `app/Services/AsaasService.php:16-17`.

`config/uazapi.php` com as chaves `UAZAPI_BASE_URL`, `UAZAPI_INSTANCE_NAME`, `UAZAPI_INSTANCE_TOKEN`, documentadas em `.env.example`.

**Sem secret literal como valor padrão.** `config/cursos_modulares.php:16` e `config/social.php:8` commitaram um secret como fallback — não repetir isso aqui. Default vazio.

**Amarração de dev à instância de teste (risco #1 em código, não em disciplina):** o provedor recusa envio quando `app()->environment() !== 'production'` e a instância configurada é a de produção (comparação contra `UAZAPI_PROD_INSTANCE_NAME`, uma denylist). As ~23h de WhatsApp perdidas por misturar experimento com produção não podem depender de alguém lembrar de conferir o `.env`.

**A guarda lê `config()` no ato da chamada, não o valor do construtor** — corrigido em 22/07/2026. A versão anterior guardava `$this->instancia`, fixada na construção, e uma sonda mostrou o buraco: trocar a config **depois** do objeto pronto passava batido e o envio saía. Quem mexer aqui não deve "otimizar" isso de volta para a propriedade.

### O portão de envio (Fatia 6) — duas travas, e elas são independentes

Confundir as duas é o jeito de se machucar:

| trava | responde | onde |
|---|---|---|
| **portão** `uazapi.envio_habilitado` | *enviamos alguma coisa?* | default `false` → `LogicException`, nenhum pacote sai |
| **guarda de ambiente** | *contra qual instância?* | denylist `UAZAPI_PROD_INSTANCE_NAME` fora de produção |

**Abrir o portão não desarma a guarda.** Ligar `UAZAPI_ENVIO_HABILITADO` é checkpoint humano explícito — nunca efeito colateral de outra tarefa, e a decisão é do dono do número.

**Limite conhecido desta guarda, registrado e não resolvido:** ela compara o **nome** da instância, mas o que identifica a instância no fio é o **token**. Um `UAZAPI_INSTANCE_NAME` de teste com `UAZAPI_INSTANCE_TOKEN` de produção passaria pela guarda. Fechar isso exigiria uma denylist de token (e portanto o token de produção no `.env` de dev) — decisão de quem opera, não do código. Enquanto não houver, **conferir o par nome+token junto** antes de abrir o portão.

**Envio: `POST /send/text`, e o token vai em HEADER.** Isto é o **oposto do webhook**, onde o token chega no *body* — assimetria do provedor (`securitySchemes.token`, `in: header`), e o erro mais fácil de cometer. Obrigatórios: só `number` e `text`; `delay`, `linkPreview`, `async`, `readchat` existem e estão fora do MVP.

**O campo `number` aceita `@g.us`, `@s.whatsapp.net`, `@lid` e `@newsletter`** — ou seja, **a API não protege contra alvo errado**. Por isso `enviarTexto()` recusa qualquer telefone que não chegue já canônico, e **não normaliza no envio**: normalizar ali esconderia um chamador errado, e alvo errado num envio é mensagem para um estranho.

**Erro da API:** 401 (token), 429 (limite), 500 com `provider_code`/`error_key` — o 463/`WHATSAPP_REACHOUT_TIMELOCK` é restrição temporária do WhatsApp por volume/qualidade, e o código é preservado na exceção porque sem ele "erro 500" vira caça ao fantasma. **Conteúdo de mensagem nunca vai para log; telefone vai mascarado.**

**Resposta 200 sem id não lança** — devolve string vazia e loga. Lançar depois de um 200 faria o chamador reenviar e duplicar mensagem para uma pessoa real.

A validação do `token` do body do webhook segue o padrão `validarSecret()` de `app/Http/Controllers/Admin/CourseVideoController.php:143-151` (`hash_equals` + `abort_unless`), adaptado para ler o token do body em vez do header.

## Modelo de dados (CRM comercial)

Sem contato unificado — telefone espalhado em ≥5 tabelas, formatos possivelmente inconsistentes. Fontes vivas:

- **`negociacoes_comercial` — fonte principal do funil comercial** (ligada direto a `classes_id`). Assumiu esse papel quando `leads` foi confirmada vazia (ver abaixo). **Limitação central, medida em 21/07/2026: 2.122 de 3.006 registros (70,6%) têm `whatsapp` inválido** — vazio/nulo **ou** com caractere não-dígito. Sobram **867 aproveitáveis (28,8%)**. A causa é desconhecida — pode ser migração incompleta de um sistema anterior ou preenchimento só em etapa posterior do funil; não presumir uma nem outra. Como não sobra outra tabela de funil ativo do mesmo porte, esses 28,8% são o **teto** de cobertura do matching, não um detalhe de qualidade de dado. E é teto mesmo: **754 dos 867 (87%) estão na forma de 12 dígitos**, ou seja, só casam com a Uazapi pela variante do 9º dígito. **A parcela fixa dessas foi medida em 22/07/2026 e é pequena: dos 628 telefones distintos de 12 dígitos, 589 (93,8%) são deriváveis e 39 são fixos inalcançáveis.**

**`negociacoes_comercial` é escrita todo dia — número dela carrega data.** Duas medições feitas em execuções diferentes **não são comparáveis**: a Q3 (21/07) e a Q8c (22/07) diferiram em exatamente 1 linha, e a lógica das duas foi provada equivalente contra 35 casos de fronteira. Se dois números precisam ser comparados, têm que sair da **mesma varredura**. **A Q9 rodou em 22/07/2026 e fechou: `discordancias = 0`, `q3_forma12 = q8c_forma12 = 755` sobre `total_linhas = 3.008`** — o `+1` era deriva de dado, como se suspeitava. A cobertura em linhas está destravada. **O que continua valendo é o método, não só o número:** duas execuções separadas desta tabela seguem não sendo subtraíveis.

**`REGEXP '^[0-9]+$'` não é teste estrito de "só dígitos" no MySQL 8+.** O `$` do ICU casa antes de um terminador de linha final: `'1198765432\n'` **passa**, `'...\t'` não. Um telefone com `\n` no fim vira `sem_ddi_11` e normaliza para 13 *caracteres* com newline dentro — não-match silencioso. Quem escrever validação de telefone em SQL não deve confiar nesse regexp.

**A Q10 mediu o caso na base em 22/07/2026 e ele é desprezível: 2 linhas** (`students.phone` e `users.telefone`, uma cada), **zero em `negociacoes_comercial`** — nenhuma taxa publicada está contaminada. **Não corrigir por `UPDATE`.** E o caminho da aplicação é imune por construção: `TelefoneCanonico::normalizar()` limpa com `preg_replace('/\D+/','')`, que remove controle antes de medir comprimento. **O risco é de validação escrita em SQL, não do matching da inbox.**

**CUIDADO COM A UNIDADE — isto já enganou uma leitura.** `754`, `867` e `3.006` são **linhas**. `589`, `39` e `628` são **telefones distintos** (Q8/Q8b deduplicam com `GROUP BY tel` antes de classificar). As 754 linhas contêm 628 números distintos; as 126 de diferença são o mesmo telefone repetido em negociações diferentes — não são registros perdidos. **Dividir um pelo outro (`589/754`) não produz taxa de cobertura nenhuma.** A cobertura em linhas depende da Q8c, ainda não executada. Ver `docs/diagnostico-telefone.md`.
- `students` → `enrollments` → `classes` → `courses`
- `leads_guia_licitacoes` (com UTMs), `contact`, `prematricula`

### Tabelas vazias em produção

Estrutura presente não é dado presente. O schema não denuncia isto — só a contagem denuncia.

- **`leads` está vazia** em `unipublicabrasil3` (o schema de produção): existe com colunas e collation, `COUNT(*) = 0`, confirmado em 21/07/2026. **Não é fonte de matching.**
- **`tentativas_de_contato`** não tem coluna de telefone própria — só alcança número via `id_lead` → `leads.celular`. Com `leads` vazia, o caminho não leva a lugar nenhum, **tenha `tentativas_de_contato` linhas ou não**.
- **Regra: nenhuma fatia futura trata `leads` como fonte válida por padrão.** Reativar exige confirmação explícita de que a tabela voltou a ser populada — ver a tabela no schema não basta, foi exatamente o que enganou antes.
- Não criar model Eloquent para `leads` nem para `tentativas_de_contato`.

### Formato canônico de telefone

Toda coluna nova da inbox e todo matching de CRM usam **um único formato**: só dígitos, sem `+`, sem espaço, traço ou parêntese, com DDI 55 sempre presente.

```
5511987654321
```

**A base não está nesse formato, e não é por pouco — medido em 21/07/2026 (`docs/diagnostico-telefone.md`).** Entre **86,9% e 99,2%** dos registros de cada tabela estão bem formados mas **sem o `55`**; o canônico gravado é resíduo (**≤2,8%** em qualquer tabela, **zero** em `prematricula` e `leads_guia_licitacoes`).

**Consequência prática para quem escrever normalização:** prefixar `55` em número de 10 ou 11 dígitos é o **caminho comum**, não um fallback de exceção. E o caminho completo que a maioria dos registros percorre até casar com a Uazapi é duplo — **prefixar `55` (→ 12 dígitos) e depois derivar a variante do 9º dígito (→ 13)**, porque `chat.phone` vem sempre com 13. Quem tratar qualquer um dos dois passos como caso raro acerta a minoria da base.

**Armazenamento:**

- **13 dígitos** é celular com 9º dígito — o caso do MVP. **12 dígitos** é fixo ou celular legado de 8 dígitos; o canônico aceita os dois e **nunca preenche com zero** para forçar 13.
- **Colunas novas nascem `varchar(20)`**, não `char(13)`. `users.telefone` é `varchar(14)` — exatamente o tamanho de `+5511987654321`, folga zero: qualquer formatação trunca em silêncio. **E não é risco teórico: 2.339 das 14.689 linhas (15,9%) já ocupam os 14 caracteres**, e valor no teto é indistinguível de valor truncado na gravação. É o exemplo do que não repetir; 7 bytes de folga custam nada perto de descobrir o truncamento depois.
- **Colunas legadas não são alteradas.** Schema compartilhado exige aviso antes (regra de ouro 9). A normalização acontece na leitura, no matching — nunca por `UPDATE` em massa.
- `contact.telefone` (`int`) e `corporativos.telefone` (`bigint`) são as duas únicas colunas de telefone numéricas do banco e não comportam esse formato: `int` estoura acima de 2147483647, e ambas perdem zero à esquerda; sendo `NOT NULL` sem default, ausência de telefone vira `0`. **O diagnóstico da Fatia 0 resolveu o que estava em aberto: as duas ficam fora do matching, mas por VOLUME — `contact` tem 1 linha, `corporativos` tem 12** (contra ~17.376 telefones distintos na base). A contaminação por `0` que se temia **não existe hoje** (`zeros = 0` nas duas); o overflow do `int` é real, mas medido em uma única linha. A propriedade do schema continua valendo como risco para inserts futuros — o que não se confirmou foi o dano atual. Se alguma das duas for repovoada, reavaliar: "coluna quebrada" e "coluna praticamente vazia" pedem decisões opostas.

**Comparação — a regra do 9º dígito.** Aceitar dois comprimentos resolve o armazenamento, não o matching: um celular cadastrado com 8 dígitos (12 no canônico) e o mesmo assinante hoje com o 9º dígito (13 — que é o que a Uazapi sempre manda) nunca batem por igualdade de string. Isso troca um erro visível por um **não-match silencioso**. **E é a maioria dos casos, não a borda: 38,2% da base aproveitável (11.515 linhas) normaliza para 12 dígitos** e portanto só casa pela variante — em `negociacoes_comercial`, 87%. Por isso:

- **Nunca declarar "não encontrado" sem testar a variante.** Match direto pelo canônico primeiro; falhando, testar a forma com e sem o 9º dígito; só então concluir que não há match.
- **Derivação da variante** (apenas DDI 55): de 13 dígitos, remover o `9` imediatamente após o DDD → 12. De 12 dígitos, inserir `9` na mesma posição → 13.
- **Guarda obrigatória contra falso positivo:** só derivar a variante quando o número de 8 dígitos começa com **6–9** (faixa de celular). Fixo brasileiro começa em 2–5 — inserir `9` num fixo inventa um celular que pode existir e ser de outra pessoa.
- **Isto não é match difuso.** A adição do 9º dígito foi transformação determinística da Anatel: as duas formas são um par exato. Não usar distância de edição, não estender para "quase igual", não aceitar prefixo parcial.
- **Registrar qual forma casou** junto ao resultado, para o painel poder dizer que casou pela variante.
- **Match pela variante não autoriza corrigir a origem.** Nada de `UPDATE` na coluna legada — normalização continua sendo na leitura.

**As quatro categorias de não-alcançável — e por que não podem ser tratadas igual.** Medidas em 22/07/2026 (Q8/Q8b). Todas terminam em "não casa", mas por motivos opostos, e confundi-las leva alguém a "consertar" o que está certo:

| categoria | o que é | volume (telefones distintos) | o que fazer |
|---|---|---|---|
| `invalido` | vazio/nulo, ou com caractere não-dígito | 70,6% de `negociacoes_comercial` | ausência de dado — nada a derivar |
| `fora_do_padrao` | só dígitos, comprimento que nenhuma regra recupera | 17 linhas em `negociacoes_comercial` | ausência de dado |
| `fixo_2a5_inalcancavel` | **dado válido.** Fixo, e a guarda 6–9 proíbe derivar a variante | 436 na base; 39 em `negociacoes_comercial` | **nunca "corrigir".** Inserir o `9` inventaria o celular de outra pessoa |
| `anomalo_0ou1` | bloco de 8 dígitos começa em **0 ou 1** | **9 na base inteira**; 0 em `negociacoes_comercial` | **dado quebrado** — nenhum número brasileiro válido começa assim. Candidato a limpeza na origem |

A distinção que importa: **`fixo` é guarda intencional, `anomalo` é defeito.** O fixo está correto no banco e continua correto — só não é alcançável por WhatsApp. O anômalo não deveria existir.

`anomalo_0ou1` também é sinal de que a heurística de comprimento tem limite: um valor de 10 dígitos é presumido DDD + assinante de 8, e quando a posição 5 do canônico dá 0/1 essa premissa falhou (número truncado, sem DDD, ou outra coisa). Volume desprezível, valor diagnóstico real.

**Zero medido ≠ medição faltando.** `anomalo_0ou1` não aparece no resultado de `negociacoes_comercial` porque `GROUP BY` não produz grupo vazio — é zero, não lacuna. Vale para toda leitura das queries do diagnóstico.

Hoje **não existem models Eloquent** para `negociacoes_comercial`, `contact`, `prematricula` nem `courses` — só `Classes`, `Student`, `Enrollment`, `LeadGuia`. Qualquer integração que precise ler essas tabelas cria o model correspondente; não presuma que já existe. **Exceção:** `leads` e `tentativas_de_contato` também não têm model e **não devem ganhar um** — ver "Tabelas vazias em produção".

## Atualização automática da inbox (polling, Fatia 5)

Sem broadcaster e sem dependência nova: `setInterval` de 5s + `fetch`, no padrão de `resources/views/pages/checkout.blade.php:618-656`. Dois endpoints somente-leitura no grupo admin — `admin.whatsapp.mensagens` (delta da thread) e `admin.whatsapp.novidades` (contagem para o banner da lista).

**Cursor de atualização incremental é `id`, NUNCA timestamp.** Vale para os dois endpoints, e a razão é a mesma nos dois: `whatsapp_messages.enviada_em` vem do provedor e pode ser **mais antiga** que uma mensagem já exibida — entrega atrasada, ou reprocessamento pela varredura por cron, que é rede de segurança por desenho. Um cursor por tempo pula essa mensagem **para sempre**, e o sintoma é uma mensagem que simplesmente nunca aparece na tela, sem erro nenhum. O preço, assumido: o delta pode trazer mensagem que pertence ao **meio** da thread, então o JS insere por posição (`data-enviada-em`) em vez de empilhar.

**Na lista, contar só por tempo não funciona — e o motivo não é óbvio.** `ProcessarEventoWhatsapp:116` só atualiza `ultima_mensagem_em` quando a mensagem é **mais nova** que a última. Mensagem atrasada entra na tabela sem tocar a conversa, o `updated_at` não sobe, e a conversa nunca seria anunciada. Por isso `novidades()` usa **duas fontes**: id de mensagem (pega a atrasada) e `updated_at` (pega mudança de atribuição, que não cria mensagem nenhuma).

**`updated_at >= $desde`, não `>`.** A coluna é `TIMESTAMP` (precisão de segundo) e o marco é o instante do render: com `>`, conversa tocada no mesmo segundo do carregamento fica de fora, e como o marco não avança sozinho, nunca é anunciada. O `>=` troca isso por um banner eventualmente supérfluo, que some no primeiro "Atualizar".

**O marco de tempo vem do servidor**, gravado num `data-` no render — nunca `Date.now()` no cliente.

**Polling NÃO sincroniza campo de guarda de concorrência.** O `atendente_atual_id` escondido no formulário da thread é a guarda check-then-write da Fatia 7: ele diz "era isto que eu via quando decidi". Se o polling o atualizasse, o `<select>` desatualizado de alguém passaria por cima da atribuição de outra pessoa **sem o aviso** — a atualização automática teria piorado a corrida que a guarda tenta estreitar. O polling atualiza o rótulo visível e avisa; o payload **nem devolve o id do atendente** (só rótulo + hash md5), para que a sincronização não seja possível por descuido. Não "otimizar" isso devolvendo o id.

O balão de mensagem mora em `resources/views/admin/whatsapp/_mensagem.blade.php` e é renderizado **pelo servidor nos dois caminhos** (carga da página e delta): o texto vem de estranhos no WhatsApp, e em Blade ele é escapado por padrão. Não montar balão em JS.

## Regras de ouro (inbox Uazapi)

1. **Instância/número de produção nunca em dev.** Desenvolvimento e teste sempre na instância e número de teste, separados do que atende comercial hoje. Já perdemos ~23h de WhatsApp em produção por misturar isso — não repetir.
2. **Payload cru primeiro, processamento depois da resposta.** O cru é persistido **sincronamente** antes de responder ao webhook — essa é a única parte que não pode falhar. O processamento roda logo após a resposta HTTP (`afterResponse`), fora do caminho do request. O worker por cron é **rede de segurança**, varrendo payloads crus não processados. Trabalho pesado (mídia) vai para a fila real, nunca para o `afterResponse`, que segura o processo php-fpm.

**Aferido em 22/07/2026, sob php-fpm 8.5.8 local:** a resposta sai em ~15ms e o job conclui ~2s depois (sonda com `sleep(2)`); sob `artisan serve`, o mesmo código prende o cliente por 2,19s. O mecanismo é `fastcgi_finish_request`, e o contraste isola isso. **`afterResponse()` executa EM PROCESSO mesmo com job `ShouldQueue`** — `Dispatcher::dispatchAfterResponse()` registra um callback `terminating` que chama `dispatchSync` (`vendor/.../Illuminate/Bus/Dispatcher.php:264-269`); a tabela `jobs` ficou em 0 antes e depois. **Limite:** medido no fpm de desenvolvimento, não no servidor de produção — lá a função pode estar em `disable_functions`, e a checagem é uma linha (`function_exists`). Se não flushar em produção, o processamento volta para a fila por cron; o que **não** muda é a persistência síncrona do cru antes da resposta.
3. **Idempotência obrigatória por `message.id`.** Webhook repete entrega; duplicata não pode aparecer na tela.
4. **Matching de CRM por `chat.phone`, nunca por `sender_lid`/`sender`** — sempre normalizado para o formato canônico (só dígitos, sem `+`, DDI 55: `5511987654321`). Ver "Formato canônico de telefone".
5. **Atribuição de atendente é nossa.** `lead_assignedAttendant_id` da Uazapi é ignorado — não ler, não escrever. Implementada em 22/07/2026 (`0005`): `whatsapp_conversations.atendente_id`/`atribuida_em`/`atribuida_por_id`, sem FK (como o resto do schema) e **sem log de reatribuição** — reatribuir sobrescreve, por decisão. **Atendente elegível é Comercial estrito (`power === 13`)**, via `User::comercial()` → `AdminRole::COMERCIAL->value`; super admin administra a inbox mas não é atribuível. A regra vive no enum e na validação, nunca num `13` literal — e nunca só no `<select>`, que é forjável. Reversão do `0005` **destrói a atribuição sem volta**: ela não existe no cru nem no provedor.
6. **Provedor isolado atrás de interface.** Nenhuma chamada direta à API da Uazapi espalhada pelo app — sempre pelo contrato.
7. **Sem migrations para schema de negócio.** Mudança de schema é SQL versionado em `database/sql/`, par up/down, aplicado manualmente.
8. **Grupos ficam fora do MVP, mas não fora da ingestão.** Persistir **todo** payload cru, inclusive de grupo (`wa_isGroup`, `@g.us`); exibir **apenas conversas 1:1**. O filtro é de exibição, nunca de entrada — descartar payload de grupo na ingestão joga fora dado que não volta.
9. **O que não fazer:** não substituir o Chatwoot em produção (rodar em paralelo, modo sombra até provar captura completa); não habilitar envio real sem checkpoint explícito; não logar conteúdo de conversa fora do necessário (LGPD); não usar dado real em ambiente de teste; não refatorar código fora do escopo da tarefa (ex. webhook Asaas); mudança que afeta schema compartilhado, fila, config ou fluxo de produção — avisar antes de aplicar.
