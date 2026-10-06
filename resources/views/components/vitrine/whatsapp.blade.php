{{--
  Link de conversão da vitrine: abre o WhatsApp em nova aba e dispara fbq('track','Contact')
  no clique (vitrine.js, via data-vt-contact). Nunca Lead.
  Uso: <x-vitrine.whatsapp :mensagem="..." content-name="Corporativo 10" class="vt-btn vt-btn-primary">Texto</x-vitrine.whatsapp>
--}}
@props([
    'mensagem'    => null,
    'contentName' => 'Assinatura Premium',
])
<a href="{{ \App\Services\AssinaturaVitrineService::whatsapp($mensagem) }}"
   target="_blank" rel="noopener"
   data-vt-contact="{{ $contentName }}"
   {{ $attributes }}>{{ $slot }}</a>
