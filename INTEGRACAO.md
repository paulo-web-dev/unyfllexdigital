# Unyflex Digital — Site Marketing
## Guia de Integração Laravel

---

## Estrutura gerada

```
unyflex-site/
├── app/Http/Controllers/
│   ├── HomeController.php
│   ├── CursoController.php
│   ├── PageController.php
│   └── DashboardController.php
├── public/
│   ├── css/
│   │   ├── colors_and_type.css   ← Design System tokens
│   │   ├── ava.css               ← Componentes AVA
│   │   └── site.css              ← CSS do site marketing
│   ├── js/
│   │   └── app.js                ← JS modular (countdown, FAQ, popup, stats…)
│   ├── fonts/
│   │   └── Poppins-Medium.ttf
│   └── img/
│       └── logo-unyflex.png
├── resources/views/
│   ├── layouts/
│   │   └── site.blade.php        ← Layout master com SEO/OG
│   ├── partials/
│   │   ├── navbar.blade.php
│   │   ├── mobile-menu.blade.php
│   │   ├── footer.blade.php
│   │   └── popup.blade.php
│   └── pages/
│       ├── home.blade.php        ← Home completa (10 seções)
│       ├── cursos.blade.php      ← Catálogo com filtros
│       ├── curso-show.blade.php  ← Player interno
│       ├── checkout.blade.php    ← Checkout visual
│       ├── sobre.blade.php
│       ├── contato.blade.php
│       └── login.blade.php
├── routes/
│   └── web.php
├── package.json
└── vite.config.js
```

---

## Passos para integrar

### 1. Copie os arquivos

Cole os arquivos deste ZIP **sobre** o seu projeto Laravel existente.
Se algum arquivo já existir, substitua.

### 2. Instale dependências JS (se usar Vite)

```bash
npm install
npm run build
```

> Os assets CSS/JS já estão em `public/` e funcionam sem Vite.
> O Vite é opcional — apenas para hot-reload em desenvolvimento.

### 3. Verifique as rotas

```bash
php artisan route:list
```

### 4. Suba o servidor

```bash
php artisan serve
# Acesse: http://localhost:8000
```

---

## URLs disponíveis

| URL                     | Rota          | Descrição            |
|-------------------------|---------------|----------------------|
| `/`                     | `home`        | Home page completa   |
| `/minisseries`          | `cursos`      | Catálogo             |
| `/minisseries/{slug}`   | `curso.show`  | Página do curso      |
| `/checkout`             | `checkout`    | Checkout visual      |
| `/sobre`                | `sobre`       | Sobre a empresa      |
| `/contato`              | `contato`     | Formulário contato   |
| `/login`                | `login`       | Tela de login        |
| `/dashboard`            | `dashboard`   | Redireciona para AVA |

---

## Autenticação

O projeto não inclui autenticação ativa.
Para adicionar Laravel Breeze:

```bash
composer require laravel/breeze
php artisan breeze:install blade
php artisan migrate
```

Depois proteja as rotas do dashboard:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', ...);
});
```

---

## Variáveis de ambiente relevantes

```env
APP_NAME="Unyflex Digital"
APP_URL=https://unyflexdigital.com.br
MAIL_FROM_ADDRESS=contato@unyflexdigital.com.br
```

---

## Checklist de produção

- [ ] `APP_ENV=production` e `APP_DEBUG=false` no `.env`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] `npm run build` (assets minificados)
- [ ] SSL configurado no servidor
- [ ] Imagem `public/img/og-image.png` criada (1200×630px) para Open Graph
- [ ] Substituir dados de contato (WhatsApp, e-mail) nos partials
- [ ] Integrar gateway de pagamento real no checkout
