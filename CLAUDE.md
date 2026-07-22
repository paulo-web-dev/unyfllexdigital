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

O diretório passou a existir em 22/07/2026, com os dois primeiros pares: `0001_create_jobs_table` (fila) e `0002_create_whatsapp_raw_events` (payload cru da Uazapi). Numeração sequencial de 4 dígitos, `CREATE TABLE IF NOT EXISTS` no `.up`, `DROP TABLE IF EXISTS` no `.down`, e um comentário de cabeçalho dizendo o que a reversão destrói quando a perda for irreversível — o cru da Uazapi não se reconstrói, o provedor não reenvia sob demanda.

## Integração Uazapi (WhatsApp)

Provedor: Uazapi (`https://unyflexdigital.uazapi.com`, doc em `https://docs.uazapi.com/`). Hoje o fluxo em produção é `Uazapi → Chatwoot (account_id 2, inbox_id 4, canal Channel::Api) → webhook Chatwoot → n8n`. O `Channel::Api` sugere integração nativa Uazapi↔Chatwoot, não bridge via n8n — **confirmar antes de mexer em config de instância**.

Payload de mensagem recebida (webhook):

- Autenticação: `body.token` **dentro do body**, não em header. Validar comparando com o token da instância configurado, a cada request.
- Idempotência: `body.message.id` (formato `owner:messageid`) — índice único, chave de deduplicação.
- Timestamps em milissegundos.
- Telefone real do remetente: `body.chat.phone` (limpo) ou `body.message.sender_pn`. **Nunca** `body.message.sender`/`sender_lid` — isso é um LID interno da Uazapi, não telefone.
- Grupos chegam pela mesma instância (`wa_isGroup`, `@g.us`).

A integração fica isolada atrás de um contrato próprio (`WhatsappProviderContract` ou equivalente) — ver `plan.md`, Fatia 1 — para permitir trocar de provedor (ex. API oficial do WhatsApp) sem reescrever a aplicação.

### Configuração e amarração à instância de teste

Segue a convenção de config do repo: **um arquivo dedicado por integração** (como `config/asaas.php`), nunca `config/services.php`. `env()` só dentro do arquivo de config; app code lê por `config()` — ver `app/Services/AsaasService.php:16-17`.

`config/uazapi.php` com as chaves `UAZAPI_BASE_URL`, `UAZAPI_INSTANCE_NAME`, `UAZAPI_INSTANCE_TOKEN`, documentadas em `.env.example`.

**Sem secret literal como valor padrão.** `config/cursos_modulares.php:16` e `config/social.php:8` commitaram um secret como fallback — não repetir isso aqui. Default vazio.

**Amarração de dev à instância de teste (risco #1 em código, não em disciplina):** o provedor recusa envio quando `app()->environment() !== 'production'` e a instância configurada é a de produção (comparação contra `UAZAPI_PROD_INSTANCE_NAME`, uma denylist). As ~23h de WhatsApp perdidas por misturar experimento com produção não podem depender de alguém lembrar de conferir o `.env`.

A validação do `token` do body do webhook segue o padrão `validarSecret()` de `app/Http/Controllers/Admin/CourseVideoController.php:143-151` (`hash_equals` + `abort_unless`), adaptado para ler o token do body em vez do header.

## Modelo de dados (CRM comercial)

Sem contato unificado — telefone espalhado em ≥5 tabelas, formatos possivelmente inconsistentes. Fontes vivas:

- **`negociacoes_comercial` — fonte principal do funil comercial** (ligada direto a `classes_id`). Assumiu esse papel quando `leads` foi confirmada vazia (ver abaixo). **Limitação central, medida em 21/07/2026: 2.122 de 3.006 registros (70,6%) têm `whatsapp` inválido** — vazio/nulo **ou** com caractere não-dígito. Sobram **867 aproveitáveis (28,8%)**. A causa é desconhecida — pode ser migração incompleta de um sistema anterior ou preenchimento só em etapa posterior do funil; não presumir uma nem outra. Como não sobra outra tabela de funil ativo do mesmo porte, esses 28,8% são o **teto** de cobertura do matching, não um detalhe de qualidade de dado. E é teto mesmo: **754 dos 867 (87%) estão na forma de 12 dígitos**, ou seja, só casam com a Uazapi pela variante do 9º dígito — e a parcela deles que for telefone fixo não casa de jeito nenhum. Números completos em `docs/diagnostico-telefone.md`.
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

Hoje **não existem models Eloquent** para `negociacoes_comercial`, `contact`, `prematricula` nem `courses` — só `Classes`, `Student`, `Enrollment`, `LeadGuia`. Qualquer integração que precise ler essas tabelas cria o model correspondente; não presuma que já existe. **Exceção:** `leads` e `tentativas_de_contato` também não têm model e **não devem ganhar um** — ver "Tabelas vazias em produção".

## Regras de ouro (inbox Uazapi)

1. **Instância/número de produção nunca em dev.** Desenvolvimento e teste sempre na instância e número de teste, separados do que atende comercial hoje. Já perdemos ~23h de WhatsApp em produção por misturar isso — não repetir.
2. **Payload cru primeiro, processamento depois da resposta.** O cru é persistido **sincronamente** antes de responder ao webhook — essa é a única parte que não pode falhar. O processamento roda logo após a resposta HTTP (`afterResponse`), fora do caminho do request. O worker por cron é **rede de segurança**, varrendo payloads crus não processados. Trabalho pesado (mídia) vai para a fila real, nunca para o `afterResponse`, que segura o processo php-fpm. **Ressalva:** o ganho de latência do `afterResponse` depende de `fastcgi_finish_request` sob php-fpm e ainda não foi aferido no servidor real — em `artisan serve` ele não flusha, e o cliente espera. Se não flushar em produção, o processamento volta para a fila por cron; o que **não** muda é a persistência síncrona do cru antes da resposta.
3. **Idempotência obrigatória por `message.id`.** Webhook repete entrega; duplicata não pode aparecer na tela.
4. **Matching de CRM por `chat.phone`, nunca por `sender_lid`/`sender`** — sempre normalizado para o formato canônico (só dígitos, sem `+`, DDI 55: `5511987654321`). Ver "Formato canônico de telefone".
5. **Atribuição de atendente é nossa.** `lead_assignedAttendant_id` da Uazapi é ignorado — não ler, não escrever.
6. **Provedor isolado atrás de interface.** Nenhuma chamada direta à API da Uazapi espalhada pelo app — sempre pelo contrato.
7. **Sem migrations para schema de negócio.** Mudança de schema é SQL versionado em `database/sql/`, par up/down, aplicado manualmente.
8. **Grupos ficam fora do MVP, mas não fora da ingestão.** Persistir **todo** payload cru, inclusive de grupo (`wa_isGroup`, `@g.us`); exibir **apenas conversas 1:1**. O filtro é de exibição, nunca de entrada — descartar payload de grupo na ingestão joga fora dado que não volta.
9. **O que não fazer:** não substituir o Chatwoot em produção (rodar em paralelo, modo sombra até provar captura completa); não habilitar envio real sem checkpoint explícito; não logar conteúdo de conversa fora do necessário (LGPD); não usar dado real em ambiente de teste; não refatorar código fora do escopo da tarefa (ex. webhook Asaas); mudança que afeta schema compartilhado, fila, config ou fluxo de produção — avisar antes de aplicar.
