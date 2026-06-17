<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>Painel de Leads · Unyflex</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{background:#0E2F4F;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:system-ui,sans-serif}
  .box{width:100%;max-width:380px;background:#fff;border-radius:18px;padding:34px 30px;box-shadow:0 20px 50px -20px rgba(0,0,0,.5)}
  .brand{font-weight:800;font-size:1.2rem;color:#0A2540;text-align:center}
  .brand small{display:block;font-weight:600;font-size:.7rem;letter-spacing:.08em;text-transform:uppercase;color:#1B4D8F;margin-top:3px}
  .btn-primary{background:#1D6FF2;border:none;font-weight:600;padding:11px}
  .btn-primary:hover{background:#155ad1}
  label{font-weight:600;font-size:.85rem}
</style>
</head>
<body>
  <div class="box">
    <div class="brand">Unyflex Digital<small>Painel de Leads</small></div>
    <hr class="my-4">
    @if($errors->any())
      <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('leads.login') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <input type="text" name="usuario" class="form-control" value="{{ old('usuario') }}" autofocus required>
      </div>
      <div class="mb-4">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Entrar</button>
    </form>
  </div>
</body>
</html>
