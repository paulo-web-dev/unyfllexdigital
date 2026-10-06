{{--
  Selos de confiança. `itens` = chaves de config('assinatura_vitrine.selos').
  `extras` = selos avulsos [['icone' => ..., 'texto' => ..., 'variante' => 'destaque|economia']]
  (ex.: "Mais vantajoso", "Economize 20%").
--}}
@props([
    'itens'  => ['mec', 'empenho', 'nf', 'carga', 'catalogo', 'vigencia'],
    'extras' => [],
])
@php
    $defs = config('assinatura_vitrine.selos', []);
    $cursos = in_array('catalogo', $itens, true)
        ? app(\App\Services\AssinaturaVitrineService::class)->resumo()['cursos_marketing']
        : '';
    $lista = collect($extras)->concat(
        collect($itens)->filter(fn ($k) => isset($defs[$k]))->map(fn ($k) => [
            'icone' => $defs[$k]['icone'],
            'texto' => str_replace(':cursos', $cursos, $defs[$k]['texto']),
            'variante' => '',
        ])
    );
@endphp
<ul {{ $attributes->merge(['class' => 'vt-selos']) }}>
  @foreach($lista as $s)
    <li class="vt-selo {{ !empty($s['variante']) ? 'vt-selo--' . $s['variante'] : '' }}">
      <i data-lucide="{{ $s['icone'] ?? 'check' }}"></i><span>{{ $s['texto'] }}</span>
    </li>
  @endforeach
</ul>
