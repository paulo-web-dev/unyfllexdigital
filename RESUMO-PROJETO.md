# Unyflex Digital — Resumo do projeto (foco: Cursos Modulares e Matrículas)

> Contexto para usar em conversas com o Claude. Gerado a partir do código em 2026-10-05 (commit `0df4981`).

## 1. Visão geral

- **Stack:** Laravel 10, PHP 8.1, MySQL. Blade + Bootstrap 5.3 + Lucide (CDN). Os assets **não passam pelo Vite**: CSS/JS ficam direto em `public/css` e `public/js`.
- **Um monolito, quatro áreas:**
  - **Site público** (`/`, `/minisseries`, `/blog`, `/checkout`), com o layout `layouts/site`.
  - **AVA**, a área do aluno (`/dashboard/*`), com `layouts/app`.
  - **Área do Assinante** (`/assinante`), com `layouts/assinante` e o middleware `subscriber`.
  - **Admin** (`/admin/*`), com `layouts/admin`.
- **Banco legado compartilhado** com o unyflex.com.br, em **produção**. **Não há migrations** para as tabelas existentes. Tabelas novas são criadas por scripts SQL rodados à mão (`database/*.sql`), e os models declaram `protected $table`.
- O código, as rotas, as views e os textos estão em **português** (`matriculas`, `cursos`, `gerar`, `aprovar`).
- **Auditoria:** vários models preenchem `log = Auth::user()->name` no `creating`/`updating`.
- **Perfis** (definidos por `users.power`):
  - `>=14`: Super Admin.
  - `13`: Comercial. Vê só a própria carteira, ou seja, `enrollments.wallet = seu nome`.
  - `<=10`: Aluno.
  - Os gates ficam em `AuthServiceProvider`: `admin.cursos`, `admin.financeiro`, `admin.blog`, `admin.social`.

## 2. Entidades centrais (legado)

| Model | Tabela | O que é |
|---|---|---|
| `Student` | `students` | Cadastro do cliente/aluno |
| `User` | `users` | Login. Liga ao aluno por `users.student_id` |
| `Classes` | `classes` | Turma/curso. Minissérie = `express='1'`; gravado = `unyflex=1, express='0'`; à venda quando `status='able'` |
| `Panel` | `panels` (`classes_id`) | Módulo/painel dentro da turma |
| `VideoLesson` | `video_lessons` (`panel_id`) | Aula/"cápsula" (vídeo) |
| `Enrollment` | `enrollments` | Matrícula em uma turma (`Classes`) |
| `Subscription` | `subscriptions` | Assinatura que dá acesso a tudo |

## 3. Matrículas

Há **dois sistemas de matrícula independentes**:

### 3.1 `enrollments` — matrícula em turma/minissérie (legado)

Campos principais:

- `student_id`, `classes_id`
- `modality`: `minisserie | distance_learning | in_person | hybrid`
- `status`: `checked` (pago/ativo) `| not_checked` (pendente) `| scheduled_billing | canceled`
- `value`, `discount`, `final_value`, `payment_method`
- `start_date`, `end_date`, `payday`
- `transaction_code`: id do pagamento no Asaas
- `wallet`: vendedor/carteira
- `plano`, `log`, `canceledLog`, `canceledData`

Os nomes das colunas misturam inglês e português (`id_antiga`, `canceledLog`, `payday`).

**Origem A: checkout público (Asaas).** O caminho é `CheckoutController → CheckoutRequest → CheckoutDTO → CheckoutService::processar() → AsaasService`.

1. Valida que a turma é uma minissérie à venda (`express='1'`, `status='able'`).
2. Resolve o aluno: procura por CPF e depois por e-mail. Se não existir, cria o `Student` e o `User` (power 1, senha = hash do CPF). Se existir, garante que há um `User` vinculado.
3. Bloqueia a compra se já houver matrícula `checked` ou `scheduled_billing` na mesma turma.
4. Resolve a **wallet** pelo cookie `referral`, criado pelo middleware `TrackReferral` a partir de `?ref=` e válido por 30 dias:
   - se o cookie bate com o nome de um usuário `power=13`, usa esse nome;
   - se não bate, usa o token do cookie como está;
   - sem cookie, usa `"Matrícula automatica ASAAS"`.
5. Cria o cliente e a cobrança no Asaas e grava o `Enrollment` dentro de uma transação. A matrícula sai com `modality='minisserie'`, `plano='Anual'`, `end_date` = hoje + 1 ano e `log='checkout_automatico'`.
6. **Cartão** aprovado (`CONFIRMED`/`RECEIVED`) gera a matrícula `checked` e dispara o evento `PagamentoAprovado` (listener `EnviarAcessoListener`).
7. **PIX/Boleto** gera a matrícula `not_checked`. A página de checkout consulta `GET /checkout/status/{paymentId}` a cada 5 segundos.
8. O **webhook** `POST /webhooks/asaas` (`WebhookController`, sem CSRF, validado pelo header `asaas-access-token`) localiza a matrícula pelo `transaction_code`:
   - `PAYMENT_CONFIRMED`/`RECEIVED`: muda para `checked` e dispara `PagamentoAprovado`;
   - `PAYMENT_OVERDUE`: muda para `not_checked`;
   - `PAYMENT_DELETED`/`REFUNDED`: muda para `canceled`.
9. O funil (`FunnelService::registrar`) grava as etapas `pagamento` e `converteu`.

**Origem B: admin manual** (`AdminController`, rotas `/admin/matriculas`, `matriculas.create/store/edit/update`).

- A listagem mostra só turmas minissérie e tem KPIs: total, hoje, checked, pendentes, scheduled e receita.
- **Toda consulta passa por `EnrollmentScope::enrollmentQuery()`** (trait). O usuário comercial só vê `wallet = auth()->user()->name`. Ao criar, o comercial tem a wallet forçada para o próprio nome. Ao editar, retorna 403 se a matrícula não for da carteira dele.

**Uso no AVA:**

- `DashboardController` e `CursosAvaController` listam as matrículas do aluno.
- `PlayerController` libera o acesso à turma quando existe matrícula **ou** assinatura vigente (`Student::isAssinante()`).

### 3.2 `modular_enrollments` — matrícula em Curso Modular (produto novo)

- Model `ModularEnrollment`. É separada do `enrollments`.
- Campos: `modular_course_id`, `student_id`, `status`, `source`, `value`, `transaction_code`, `start_date`, `end_date`, `log`.
  - `status`: `ativo | pendente | cancelado`
  - `source`: `manual | compra`
- **Hoje só existe a matrícula manual** (`Admin\ModularEnrollmentController`):
  - `POST /admin/cursos-modulares/{id}/matriculas` recebe `ident` (e-mail ou CPF), procura o `Student` e faz `updateOrCreate` com `status='ativo'`, `source='manual'`.
  - `PATCH .../matriculas/{matricula}/cancelar` muda para `status='cancelado'`.
- O valor `source='compra'` está previsto no comentário do model, mas **não há checkout nem webhook que crie matrícula modular**: o checkout atual só vende `Classes` minissérie.
- **Acesso no AVA:** a matrícula `ativo` **ou** uma assinatura vigente.

## 4. Cursos Modulares (`modular_courses`) — apostila PDF → curso gerado por IA

### 4.1 Conceito

O admin envia uma **apostila em PDF**. Workflows externos do **n8n** geram com IA:

- roteiros (resumo, podcast, vídeo);
- materiais de estudo (PDFs de resumo, cartões didáticos, prova);
- áudio do podcast (Gemini TTS);
- vídeo-resumo no estilo NotebookLM;
- capa e peças de divulgação (media kit e criativos de anúncios).

O aluno estuda em `/dashboard/modulares/{slug}`.

### 4.2 Models e tabelas (todas por FK `modular_course_id`)

| Model | Tabela | Conteúdo | Status |
|---|---|---|---|
| `ModularCourse` | `modular_courses` | `title`, `slug`, `description`, `apostila_path/original_name/mime/size`, `pages`, `status`, `log` | `rascunho → processando → publicado` |
| `ModularCourseAsset` | `modular_course_assets` | Roteiros em texto, um por (curso, `type`): `resumo`, `podcast`, `video`; mais `feedback` e `version` | `gerando → aguardando_revisao → aprovado`, ou `reprovado` (volta para `gerando` com feedback) |
| `CourseMaterial` | `course_materials` | Materiais do aluno. `type`: `resumo` (PDFs), `cartoes` (deck em JSON no `content`), `notas`, `prova` (JSON de questões); campos `pdf_path`, `sort_order`, `version` | `gerando`, `pronto`, `erro` |
| `PodcastAudio` | `podcast_audios` | Áudio(s) do podcast, em partes (`part`, `audio_path` .wav) | `gerando`, `pronto`, `erro` |
| `CourseVideo` | `course_videos` | Vídeo-resumo. O .mp4 fica no VPS `videos.unygov.com.br`; aqui só se guarda `video_url`, `duration`, `slides` | `pronto`, … |
| `CourseCover` | `course_covers` | Capa 16:9 para o site | `pronto`, … |
| `MediaKitAsset` | `media_kit_assets` | `card` (feed) e `story`, com `caption` | Igual ao `ModularCourseAsset` |
| `AdCreative` | `ad_creatives` | Criativos: `feed`, `story`, `whatsapp`, `prova` (prova social) | — |
| `ModularEnrollment` | `modular_enrollments` | Matrícula (ver 3.2) | `ativo`, `pendente`, `cancelado` |
| `ModularProvaAttempt` | `modular_prova_attempts` | Tentativa de prova: `score`, `total`, `answers` (JSON) | — |

**Arquivos:**

- Os uploads e os arquivos gerados são gravados em `public/` com `public_path()`: apostila, `storage/media-kit`, `storage/podcast-audio` e materiais em `.../{curso_id}/{type}/`.
- As URLs públicas são montadas com `config('cursos_modulares.public_base_url')` (env `CURSOS_PUBLIC_URL`, padrão `https://digital.unyflex.com.br`). A IA precisa conseguir baixar a apostila por essa URL.

### 4.3 Fluxo admin (`/admin/cursos-modulares`, gate `admin.cursos`)

Controllers: `ModularCourseController`, `CourseMaterialController`, `CourseVideoController`, `CourseCoverController`, `AdCreativeController`, `ModularEnrollmentController`.

1. **Criar o curso** com o upload do PDF. Ações básicas: `index`, `create`, `store`, `show`, `download`, `destroy`.
2. **Gerar os roteiros** (`POST {id}/gerar`): cria ou zera os 3 assets como `gerando`, muda o curso para `processando` e dispara o n8n.
3. O **callback do n8n** (`POST /api/n8n/cursos-modulares/assets`) grava o conteúdo como `aguardando_revisao` e devolve o curso para `rascunho`.
4. **Revisão:**
   - aprovar, editar o texto ou excluir o asset;
   - **reprovar** exige feedback, incrementa a `version`, volta o asset para `gerando` e dispara o n8n de novo.
   - Quando **os 3 tipos** (`resumo`, `podcast`, `video`) ficam `aprovado`, `talvezConcluir()` muda o curso para `publicado`.
5. **Media kit** (card e story): `media-kit/gerar`, com aprovar, reprovar, editar legenda e excluir. O botão `gerar-tudo` dispara roteiros e media kit juntos.
6. **Áudio do podcast** (`podcast-audio/gerar`): exige o roteiro de podcast. Apaga as partes antigas, cria um registro `gerando` e o n8n devolve os trechos em `/api/n8n/cursos-modulares/podcast-audio`.
7. **Materiais** (`resumo-pdf/gerar`, `cartoes/gerar`, `prova/gerar`):
   - o callback `/api/n8n/cursos-modulares/materiais` recebe `type` e `materials[]`, cada item com `pdf_base64` **ou** `content`;
   - o callback apaga o que existia do tipo, grava os itens novos como `pronto` e, se nada vier, cria um registro `erro`.
8. **Capa** (`capa/gerar`), **criativos de anúncio** (`ads/gerar`) e **vídeo** (`video/gerar`) têm cada um o seu callback em `/api/n8n/cursos-modulares/{capa|ads|video}`.
9. **Matricular ou cancelar alunos** é feito na própria página do curso (ver 3.2).

### 4.4 Integração com o n8n (mesmo padrão em todas as features)

- **Disparo:** `dispararN8n($payload, $url)` faz um `POST` com o header `X-Webhook-Secret` e timeout de 20 s. O payload inclui `callback_url`.
- **Retorno:** o n8n chama `POST /api/n8n/...`, rotas do grupo `api` (sem CSRF e sem sessão). `validarSecret()` compara o header com `hash_equals`.
- **Config:** `config/cursos_modulares.php`, com as envs `CURSOS_N8N_WEBHOOK`, `CURSOS_N8N_SECRET`, `CURSOS_PUBLIC_URL` e os `tipos` de roteiro.
- **Em ambiente local**, o n8n não alcança `localhost`. É preciso um túnel público para testar o callback.

### 4.5 Lado do aluno (`Ava\ModularStudyController`)

- `GET /dashboard/modulares` lista os cursos com matrícula `ativo`, junto com a capa `pronto`.
- `GET /dashboard/modulares/{slug}` libera o acesso com matrícula ativa **ou** assinatura vigente e registra `AccessLog::registrar('curso_view', ...)`. A página mostra:
  - os resumos em PDF;
  - os cartões interativos;
  - a prova (JSON de `course_materials` com `type='prova'`, formato `[{enunciado, alternativas[], correta, comentario}]`);
  - os áudios do podcast e o vídeo;
  - as últimas 10 tentativas e a melhor nota.

  O layout é `layouts.assinante` para assinante e `layouts.app` para os demais.
- `POST /dashboard/modulares/{id}/prova/resultado` grava a `ModularProvaAttempt` com `score`, `total` e `answers`.

## 5. Área do Assinante e a nomenclatura "Curso Modular" (atenção!)

O catálogo do assinante (`AssinanteCatalogoService`) **renomeou os produtos só na apresentação**. Código, chaves e banco não mudaram.

| O que é no banco | Nome exibido | Tipo no catálogo |
|---|---|---|
| Painel de minissérie | "Curso Minissérie" | `minisserie` |
| **Painel de turma gravada** | **"Curso Modular"** | `gravado` |
| Turma gravada com algum painel de mais de 1 aula (card adicional, além dos painéis) | "Curso Livre Aprofundado" | `livre` |
| **`modular_courses`** | **"Apostilas e Materiais Pós-Graduação"** | `modular` |

⚠️ O nome "Curso Modular" **mudou de dono**:

- No **admin e no código**, `ModularCourse` continua sendo o produto apostila → IA.
- Na **vitrine do assinante**, "Curso Modular" quer dizer **painel de turma gravada**.

Outras regras do catálogo:

- Só entram painéis `able` com pelo menos 1 vídeo com link.
- A deduplicação é por (`course_id`, título do painel) e fica a turma mais recente.
- As turmas listadas em `assinante_catalogo_ocultos` ficam escondidas.
- O cache dura 10 minutos.

**Provas e certificados dos painéis** (paralelo ao modular):

- `PanelProva` (`panel_provas`) é gerada pelo mesmo workflow de prova do n8n via `PanelProvaService`. A fonte é `panels.content` e o callback é `/api/n8n/paineis/prova`.
- As alternativas são **embaralhadas de forma determinística** em `PanelProva::questoes()`, e a nota é recalculada no servidor.
- `PanelProvaAttempt` registra as tentativas.
- **`PanelCertificate`** (12 h) sai com a melhor nota >= 70% (`NOTA_MINIMA = 0.7`) no painel de minissérie ou de turma gravada só com painéis de 1 aula.
- **`ClassCertificate`** (20 h) vale para o "Curso Livre Aprofundado": exige nota mínima em **todas** as provas dos painéis da turma.
- A validação pública fica em `/certificado/validar/{token}` (`CertificadoController`).

## 6. Pontos de atenção encontrados no código

1. **Prova modular confia na nota enviada pelo navegador.** `provaResultado` aceita o `score` do cliente (só limita a `<= total`). A prova de painel, ao contrário, recalcula no servidor. Hoje isso tem pouco impacto porque a prova modular não emite certificado. Mas, se um dia emitir, é preciso recalcular no servidor e embaralhar as alternativas, como em `PanelProva`.
2. **Não existe venda de Curso Modular.** `source='compra'` e `value`/`transaction_code` estão no model, mas o `CheckoutService` só aceita `Classes` minissérie (`express='1'`). Vender o modular exige estender o checkout e o webhook para criar `ModularEnrollment` (`pendente → ativo`).
3. **O player libera minissérie com qualquer matrícula.** O primeiro acesso em `PlayerController` checa só se o `Enrollment` existe, **sem filtrar status**: matrícula `not_checked` ou `canceled` também entra. Outros pontos do mesmo controller (prova e certificado) exigem `status='checked'`. Vale confirmar se isso é intencional.
4. **Matrícula modular não guarda a carteira do vendedor.** Ela não tem `wallet` e também não passa pelo `EnrollmentScope`, então o comercial não tem visão dela. A gestão é só de quem tem o gate `admin.cursos`.
5. **O curso só fica `publicado` quando os 3 roteiros estão aprovados.** Materiais, áudio, vídeo e capa não entram nessa regra. A tela do aluno também não checa `status='publicado'`: a matrícula ou a assinatura bastam.
6. **Banco de produção.** O `.env` aponta para o MySQL de produção compartilhado. Não rode `migrate`, seeds ou `php artisan test` sem querer de verdade. Tabelas novas entram por um `.sql` em `database/`.

## 7. Mapa rápido de arquivos

- **Rotas:** `routes/web.php` (admin em `/admin/cursos-modulares/*` e `/admin/matriculas/*`; AVA em `/dashboard/modulares/*`) e `routes/api.php` (callbacks `/api/n8n/...`).
- **Controllers admin:** `app/Http/Controllers/Admin/{ModularCourse,ModularEnrollment,CourseMaterial,CourseVideo,CourseCover,AdCreative,Admin}Controller.php`
- **Controllers AVA:** `app/Http/Controllers/Ava/{ModularStudy,Player,CursosAva,SubscriptionArea}Controller.php`
- **Checkout e pagamento:** `CheckoutController`, `app/Services/{CheckoutService,AsaasService}.php`, `WebhookController`, `app/Events/PagamentoAprovado`, `app/Listeners/EnviarAcessoListener`
- **Escopo de carteira:** `app/Traits/EnrollmentScope.php`; papéis em `app/Enums/AdminRole.php`
- **Catálogo do assinante:** `app/Services/AssinanteCatalogoService.php`
- **Config:** `config/cursos_modulares.php`, `config/asaas.php`, `config/assinante.php`
- **SQL das tabelas novas:** `database/*.sql`
- **Views:**
  - admin: `resources/views/pages/admin/`
  - aluno: `resources/views/pages/ava/modulares.blade.php` e `modular-show.blade.php`
