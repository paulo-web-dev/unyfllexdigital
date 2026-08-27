# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Unyflex Digital — Laravel 10 (PHP 8.1) monolith serving four surfaces off one codebase and one shared legacy MySQL database:

- **Public site** (`/`, `/minisseries`, `/blog`, `/guia-licitacoes`, `/checkout`) — `layouts/site.blade.php`, `layouts/blog.blade.php`
- **AVA** (student learning area, `/dashboard/*`) — `layouts/app.blade.php`
- **Assinante** (subscribers-only area, `/assinante`) — `layouts/assinante.blade.php`
- **Admin** (`/admin/*`) — `layouts/admin.blade.php`

Code, comments, route names, view names and user-facing strings are in **Portuguese** — keep new code consistent with that (e.g. `matriculas`, `cursos`, `gerar`, `aprovar`).

## Commands

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate   # first run only
php artisan serve                                   # http://localhost:8000
php artisan route:list                              # routes are the best map of the app

php artisan test                                    # or: vendor/bin/phpunit
vendor/bin/phpunit --filter NomeDoTeste             # single test
vendor/bin/pint                                     # code style (Laravel Pint)

php artisan config:cache && php artisan route:cache && php artisan view:cache   # production
```

- **Assets are NOT bundled.** Layouts load `public/css/*.css` and `public/js/app.js` via `asset()`, plus Bootstrap 5.3 and Lucide from CDN. `npm run dev` / `npm run build` (Vite) exist but no layout uses `@vite`; edit files under `public/` directly.
- `phpunit.xml` does not override `DB_CONNECTION`, so feature tests run against the database in `.env`. Only the stock example tests exist.

## Database — shared legacy schema, no migrations

The app points at the pre-existing production MySQL database shared with unyflex.com.br (users, students, classes, panels, enrollments, …). Consequences:

- **Do not write migrations for existing tables.** The 5 files in `database/migrations/` are Laravel boilerplate; `php artisan migrate` is not part of the workflow.
- New tables are created by hand-run SQL scripts kept in `database/*.sql` (see `funnel_events.sql`, `create_table_leads_guia.sql`). When adding a table, add a `.sql` file there and set `protected $table` explicitly on the model.
- Models declare `$fillable` to match real column names; column names are a mix of English/Portuguese legacy (`classes_id`, `id_antiga`, `canceledLog`, `payday`). Check the model before assuming a column.
- Several models (`Classes`, `Panel`, `ModularCourse`, …) set `log = Auth::user()->name` on create/update in `boot()` — that is the audit convention.

## Domain model (core "minissérie" flow)

- `Student` (`students`) is the customer record; `User` (`users`) is the login, linked via `users.student_id`. Checkout creates both.
- `Classes` (`classes`) = a course. A minissérie is `express=1`, sold when `status='able'`.
- `Panel` (`panels`, `classes_id`) = a module inside a course; `VideoLesson` (`video_lessons`, `panel_id`) = a "cápsula" (video). `Material` links to panels many-to-many through `material_panels`.
- `Enrollment` (`enrollments`) = a purchase: `student_id`, `classes_id`, `modality='minisserie'`, `status` in `checked | not_checked | scheduled_billing | canceled`, `wallet` = the seller's name (see referral below), `transaction_code` = Asaas payment id.
- `Subscription` (`subscriptions`) = all-access plan; `Subscription::scopeVigentes()` / `Student::isAssinante()` gate the `/assinante` area via the `subscriber` middleware.
- Progress: `ViewsMinisserie` records watched videos; `AccessLog::registrar()` records logins/course views (never throws).
- `ModularCourse` (+ `ModularCourseAsset`, `MediaKitAsset`, `PodcastAudio`, `CourseMaterial`, `AdCreative`, `CourseCover`, `CourseVideo`, `ModularEnrollment`) is the newer "apostila PDF → AI-generated course" product, separate from `Classes`. Status flow `rascunho → processando → publicado`. Uploads go to `public/` via `public_path()` and public URLs are built from `config('cursos_modulares.public_base_url')`.

## Auth & authorization

Roles come from the integer `users.power` (see `App\Enums\AdminRole`):

| power | role | access |
|---|---|---|
| >= 14 | Super Admin | everything |
| 13 | Comercial | admin dashboard, alunos, matrículas — **filtered to their own wallet** |
| <= 10 | Aluno | AVA only |

- Middleware aliases (`app/Http/Kernel.php`): `admin` (power >= 13), `admin.can:<gate>` (gates defined in `AuthServiceProvider`, e.g. `admin.cursos`, `admin.financeiro`, `admin.blog`, `admin.social`), `subscriber`.
- `App\Traits\EnrollmentScope` (used by `AdminController`) provides `enrollmentQuery()`, `isComercial()`, `isSuperAdmin()`. Any admin query over enrollments/students must go through it so comercial users only see `wallet = auth()->user()->name`.
- `AuthController::login` redirects by role: admin → `admin.dashboard`, active subscriber → `assinante.home`, else → `dashboard`.

## Checkout & payments (Asaas)

`CheckoutController` → `CheckoutRequest` → `CheckoutDTO::fromArray()` → `CheckoutService::processar()` → `AsaasService`.

- Credit card approved (`CONFIRMED`/`RECEIVED`) → enrollment `checked` and `PagamentoAprovado` event fires (`EnviarAcessoListener`).
- PIX/Boleto → enrollment `not_checked`; the checkout page JS polls `GET /checkout/status/{paymentId}` every 5s.
- `POST /webhooks/asaas` (`WebhookController`, exempt from CSRF in `VerifyCsrfToken`, validated by the `asaas-access-token` header vs `config('asaas.webhook_token')`) confirms/expires/cancels enrollments by `transaction_code`.
- Config in `config/asaas.php` (`ASAAS_API_KEY`, `ASAAS_BASE_URL` defaults to sandbox, `ASAAS_WEBHOOK_TOKEN`).

## Referral & funnel tracking (global middleware)

Both run on every request (`$middleware` in `Http/Kernel.php`):

- `TrackReferral`: `?ref=<token>` → stores a `ReferralClick` and sets a 30-day `referral` cookie. `CheckoutService::resolverWallet()` turns that cookie into `enrollments.wallet` (matches a `power=13` user's name, else the raw token, else `"Matrícula automatica ASAAS"`).
- `TrackFunnel`: sets a 1-year `_unyflex_sid` cookie and records `FunnelEvent` steps (`visita`, `visualizou`, `checkout`, `pagamento`, `converteu`) via `FunnelService::registrar()`, which swallows all exceptions. Skips `admin/`, `dashboard/`, `api/`, `webhooks/`.

## n8n AI-generation integration

Blog articles, social media art, and all modular-course assets are generated by external n8n workflows. The pattern is identical everywhere (`ModularCourseController`, `BlogGeneratorController`, `SocialGeneratorController`, `SocialPublisherController`, `CourseMaterialController`, `AdCreativeController`, `CourseCoverController`, `CourseVideoController`):

1. Admin action POSTs a payload to the n8n webhook URL with header `X-Webhook-Secret` (`dispararN8n()`), sets the record to `processando`.
2. n8n calls back `POST /api/n8n/...` (routes in `routes/api.php`, `api` group → no CSRF/session). The controller's `validarSecret()` checks `X-Webhook-Secret` with `hash_equals`, then stores drafts for admin approval/rejection.

Per-feature config: `config/cursos_modulares.php`, `config/blog.php`, `config/social.php` (env: `CURSOS_N8N_WEBHOOK`, `CURSOS_N8N_SECRET`, `CURSOS_PUBLIC_URL`, `BLOG_N8N_WEBHOOK`, `BLOG_CALLBACK_URL`, `SOCIAL_N8N_WEBHOOK`, `SOCIAL_CALLBACK_URL`, …). **Locally the n8n server cannot reach `localhost`** — set the `*_CALLBACK_URL` vars to a public tunnel URL when testing generation end-to-end.

## Routing notes

- Only `routes/web.php` and `routes/api.php` are loaded (`RouteServiceProvider`). `routes/checkout-rotas.php`, `routes/player-rotas.php` and `routes/social.php` are **paste-me snippets already merged into `web.php`**, not registered files.
- Same for `app/Http/Controllers/Admin/metodos-curso.php`, `panel-metodos.php`, `app/Services/checkout-funil-instrucao.php` and `app/Http/Middleware/LEIA-KERNEL.php` — instruction/snippet files, not autoloaded classes. Don't reference them; edit the real controller/service.
- `web.php` defines the route names `curso.show` and `player` twice (the later definition wins in `route()`); be careful when adding routes with those names.
- Admin sub-areas: blog routes need `admin.can:admin.blog`, social routes `admin.can:admin.social`, course/material/modular routes `admin.can:admin.cursos`; alunos/matrículas/assinaturas are open to comercial users.

## Views

- Public pages: `resources/views/pages/*.blade.php`; AVA: `pages/ava/`; admin: `pages/admin/` (older) and `admin/{blog,social}/` (newer). Shared partials in `partials/`, `components/course-card.blade.php`.
- Design tokens live in `public/css/colors_and_type.css` (site) and `public/css/unyflex/colors_and_type.css` (AVA/admin); AVA components in `public/css/unyflex/ava.css`, admin in `public/css/admin.css`.
- `resources/resources/views/` is a stray duplicate directory, not the view root.
