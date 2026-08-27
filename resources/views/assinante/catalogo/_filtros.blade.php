{{-- Filtros server-side (GET). Selects enviam o formulário ao mudar; sem JS de filtragem no cliente. --}}
@php use App\Services\AssinanteCatalogoService as Cat; @endphp

<form method="GET" action="{{ route('assinante.home') }}" class="as-filtros" id="as-filtros">

  <div class="as-busca">
    <label for="f-busca">Buscar</label>
    <svg viewBox="0 0 24 24" style="top:calc(50% + 10px);"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
    <input id="f-busca" class="as-in" type="search" name="busca" value="{{ $filtros['busca'] }}"
           placeholder="Título da turma ou do curso…" autocomplete="off" maxlength="80">
  </div>

  <div>
    <label for="f-tipo">Tipo</label>
    <select id="f-tipo" class="as-in" name="tipo" data-autosubmit>
      <option value="">Todos os tipos</option>
      @foreach(Cat::TIPOS as $valor => $label)
        <option value="{{ $valor }}" @selected($filtros['tipo'] === $valor)>{{ $label }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label for="f-categoria">Assunto</label>
    <select id="f-categoria" class="as-in" name="categoria" data-autosubmit>
      <option value="">Todos os assuntos</option>
      @foreach($meta['categorias'] as $c)
        <option value="{{ $c['slug'] }}" @selected($filtros['categoria'] === $c['slug'])>{{ $c['titulo'] }} ({{ $c['paineis'] }})</option>
      @endforeach
      @if($meta['sem_categoria'] > 0)
        <option value="{{ Cat::SEM_CATEGORIA }}" @selected($filtros['categoria'] === Cat::SEM_CATEGORIA)>Sem categoria ({{ $meta['sem_categoria'] }})</option>
      @endif
    </select>
  </div>

  <div>
    <label for="f-ordem">Ordenar</label>
    <select id="f-ordem" class="as-in" name="ordem" data-autosubmit>
      @foreach(Cat::ORDENACOES as $valor => $label)
        <option value="{{ $valor }}" @selected($filtros['ordem'] === $valor)>{{ $label }}</option>
      @endforeach
    </select>
  </div>

  <div class="as-filtros__acoes">
    <label class="as-check" for="f-assistido">
      <input id="f-assistido" type="checkbox" name="assistido" value="1" @checked($filtros['assistido']) data-autosubmit>
      Continuar assistindo
    </label>
    <button type="submit" class="as-btn as-btn--primary">Filtrar</button>
  </div>
</form>

@php
  $ativos = [];
  if ($filtros['busca'] !== '')     $ativos['busca']     = '“' . $filtros['busca'] . '”';
  if ($filtros['tipo'] !== '')      $ativos['tipo']      = Cat::TIPOS[$filtros['tipo']];
  if ($filtros['categoria'] !== '') {
      $ativos['categoria'] = $filtros['categoria'] === Cat::SEM_CATEGORIA
          ? 'Sem categoria'
          : (collect($meta['categorias'])->firstWhere('slug', $filtros['categoria'])['titulo'] ?? $filtros['categoria']);
  }
  if ($filtros['assistido'])        $ativos['assistido'] = 'Continuar assistindo';
@endphp

@if($ativos)
  <div class="as-chips">
    <span>Filtros:</span>
    @foreach($ativos as $campo => $label)
      <a class="as-chip" href="{{ request()->fullUrlWithQuery([$campo => null, 'page' => null]) }}" title="Remover filtro">
        {{ $label }}
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </a>
    @endforeach
    <a class="as-btn as-btn--ghost" style="padding:5px 10px;font-size:12px;" href="{{ route('assinante.home') }}">Limpar tudo</a>
  </div>
@endif

@push('scripts')
<script>
  (function () {
    var form = document.getElementById('as-filtros');
    if (!form) return;
    form.querySelectorAll('[data-autosubmit]').forEach(function (el) {
      el.addEventListener('change', function () { form.submit(); });
    });
  })();
</script>
@endpush
