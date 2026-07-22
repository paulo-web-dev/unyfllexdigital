{{--
  Identificação do contato — Fatia 4.

  MESMO PESO VISUAL NOS DOIS ESTADOS. "Não identificado" é o caminho comum,
  não um erro: o teto de cobertura medido em `negociacoes_comercial` é 28,8%.
  Nada de tarja vermelha, nada de espaço vazio num canto — se o painel só
  ficasse apresentável quando há match, ele ficaria feio na maioria das
  conversas reais.

  SÓ NOME E PROCEDÊNCIA. Funil, turma, valor e histórico são Fatia 9.
--}}
<div class="card mb-3">
  <div class="card-body py-2">
    <div class="d-flex flex-wrap align-items-baseline gap-2">
      <span class="small text-muted">Contato</span>

      @if ($contato)
        <strong>{{ $contato->nome }}</strong>
        <span class="badge bg-light text-dark border">{{ $contato->fonte }}</span>

        @if ($contato->casouPelaVariante())
          {{-- Diz COMO casou: a variante é derivação do 9º dígito, não
               igualdade. Quem confere contra o CRM precisa saber disso. --}}
          <span class="small text-muted">casou pela variante do 9º dígito</span>
        @endif

        @if ($contato->outros > 0)
          <span class="small text-muted">(+{{ $contato->outros }} registro{{ $contato->outros > 1 ? 's' : '' }} com este telefone)</span>
        @endif
      @else
        <span class="text-muted">Não identificado</span>
        <span class="small text-muted">nenhum cadastro com este telefone — nem pela variante do 9º dígito</span>
      @endif
    </div>
  </div>
</div>
