@extends('layouts.site')
@section('meta_title', 'Validação de certificado — Unyflex Digital')
@section('meta_description', 'Confira a autenticidade de um certificado emitido pela Unyflex Digital informando o código de autenticidade impresso no documento.')

@section('content')

<div style="padding-top:112px;">

  <section class="section-py" style="background:radial-gradient(ellipse 60% 50% at 50% -5%, rgba(0,163,255,0.15), transparent 55%), var(--bg-0);min-height:70vh;">
    <div class="container">
      <div class="row justify-content-center text-center mb-4">
        <div class="col-lg-7">
          <div class="section-eyebrow">Validação de certificado</div>
          <h1 class="section-title" style="font-size:clamp(28px,4vw,44px);">
            Confira a <span class="text-brand-gradient">autenticidade</span>
          </h1>
          <p style="font-size:16px;color:var(--fg-3);line-height:1.7;">
            Informe o código de autenticidade impresso no rodapé do certificado.
          </p>
        </div>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-7">

          <form method="get" action="{{ route('certificado.validar') }}" style="display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" name="codigo" value="{{ $token }}" placeholder="Código de autenticidade" required
                   maxlength="40" autocomplete="off" spellcheck="false"
                   style="flex:1 1 260px;min-width:0;font-family:var(--font-mono, monospace);font-size:15px;padding:13px 16px;border-radius:var(--r-md);border:1px solid var(--line-2);background:var(--bg-2);color:#fff;outline:none;">
            <button type="submit" class="btn-ux btn-ux-primary">Validar</button>
          </form>

          @if($token !== null)
            @if($cert)
              <div style="margin-top:28px;background:var(--bg-2);border:1px solid rgba(34,197,94,0.45);border-radius:var(--r-lg);padding:28px;box-shadow:inset 0 1px 0 rgba(255,255,255,0.04);">
                <div style="display:flex;align-items:center;gap:10px;color:#4ade80;font-weight:700;font-size:17px;">
                  <i data-lucide="badge-check" style="width:22px;height:22px;"></i>
                  Certificado válido
                </div>
                <p style="margin:6px 0 20px;color:var(--fg-3);font-size:14px;">
                  Este certificado foi emitido pela Unyflex Digital e confere com os dados abaixo.
                </p>

                <dl style="margin:0;display:grid;grid-template-columns:max-content 1fr;gap:10px 20px;font-size:15px;">
                  <dt style="color:var(--fg-3);font-weight:500;">Aluno</dt>
                  <dd style="margin:0;color:#fff;font-weight:600;">{{ $cert->aluno }}</dd>
                  <dt style="color:var(--fg-3);font-weight:500;">Curso</dt>
                  <dd style="margin:0;color:#fff;">{{ $cert->titulo }}</dd>
                  <dt style="color:var(--fg-3);font-weight:500;">Carga horária</dt>
                  <dd style="margin:0;color:#fff;">{{ $cert->horas }} horas</dd>
                  <dt style="color:var(--fg-3);font-weight:500;">Concluído em</dt>
                  <dd style="margin:0;color:#fff;">{{ $cert->concluido_em->format('d/m/Y') }}</dd>
                  <dt style="color:var(--fg-3);font-weight:500;">Código</dt>
                  <dd style="margin:0;color:var(--fg-3);font-family:var(--font-mono, monospace);font-size:13px;word-break:break-all;">{{ $cert->token }}</dd>
                </dl>
              </div>
            @else
              <div style="margin-top:28px;background:var(--bg-2);border:1px solid rgba(239,68,68,0.45);border-radius:var(--r-lg);padding:28px;">
                <div style="display:flex;align-items:center;gap:10px;color:#f87171;font-weight:700;font-size:17px;">
                  <i data-lucide="shield-x" style="width:22px;height:22px;"></i>
                  Certificado não encontrado
                </div>
                <p style="margin:6px 0 0;color:var(--fg-3);font-size:14px;line-height:1.6;">
                  Nenhum certificado corresponde ao código
                  <code style="color:#fff;word-break:break-all;">{{ $token }}</code>.
                  Confira se o código foi digitado exatamente como impresso (maiúsculas e minúsculas importam).
                </p>
              </div>
            @endif
          @endif

        </div>
      </div>
    </div>
  </section>

</div>

@endsection
