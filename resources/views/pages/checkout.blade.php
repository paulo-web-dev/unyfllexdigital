@extends('layouts.site')
@section('meta_title', 'Checkout — Unyflex Digital')

@section('content')

<div class="checkout-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">

        <div class="text-center mb-5 aos-fade">
          <div class="hero-eyebrow" style="justify-content:center;">
            <span class="dot"></span>
            <span>Oferta especial · Encerra em breve</span>
          </div>
          <h1 style="font-family:var(--font-display);font-weight:800;font-size:clamp(28px,3.5vw,40px);color:#fff;letter-spacing:-0.025em;margin-bottom:10px;">
            Trilha Completa de Gestão Pública
          </h1>
          <p style="font-size:16px;color:var(--fg-3);">Acesso a todas as miniséries por 1 ano — preencha abaixo para garantir sua vaga.</p>
        </div>

        <div class="row g-4">

          {{-- Formulário --}}
          <div class="col-lg-7">
            <div class="checkout-card aos-fade">
              <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:20px;">Seus dados</div>

              <div class="row g-3">
                <div class="col-md-6">
                  <div class="checkout-form-group">
                    <label class="checkout-label">Nome completo</label>
                    <input type="text" class="checkout-input" placeholder="Maria Aparecida Silva">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="checkout-form-group">
                    <label class="checkout-label">CPF</label>
                    <input type="text" class="checkout-input" placeholder="000.000.000-00">
                  </div>
                </div>
                <div class="col-12">
                  <div class="checkout-form-group">
                    <label class="checkout-label">E-mail</label>
                    <input type="email" class="checkout-input" placeholder="maria@prefeitura.sp.gov.br">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="checkout-form-group">
                    <label class="checkout-label">WhatsApp</label>
                    <input type="tel" class="checkout-input" placeholder="(11) 9 0000-0000">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="checkout-form-group">
                    <label class="checkout-label">Órgão/Prefeitura</label>
                    <input type="text" class="checkout-input" placeholder="Prefeitura de São Paulo">
                  </div>
                </div>
              </div>

              <div style="height:1px;background:var(--line-1);margin:24px 0;"></div>
              <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:20px;">
                Pagamento
                <div class="checkout-card-icons" style="display:inline-flex;margin-left:12px;vertical-align:middle;">
                  @foreach(['VISA','MASTER','PIX','BOLETO'] as $c)
                  <span class="card-icon">{{ $c }}</span>
                  @endforeach
                </div>
              </div>

              {{-- Tabs pagamento --}}
              <div style="display:flex;gap:6px;margin-bottom:20px;">
                @foreach(['cartao'=>'Cartão de crédito','pix'=>'PIX','boleto'=>'Boleto'] as $val => $lbl)
                <button onclick="selectPayment('{{ $val }}', this)" id="pay-tab-{{ $val }}" style="flex:1;padding:10px;border-radius:10px;background:{{ $val==='cartao'?'var(--bg-3)':'var(--bg-4)' }};border:1px solid {{ $val==='cartao'?'rgba(0,163,255,0.35)':'var(--line-2)' }};color:{{ $val==='cartao'?'#fff':'var(--fg-3)' }};font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:all 0.2s;">
                  {{ $lbl }}
                </button>
                @endforeach
              </div>

              {{-- Cartão --}}
              <div id="pay-cartao" style="">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="checkout-form-group">
                      <label class="checkout-label">Número do cartão</label>
                      <input type="text" class="checkout-input" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="checkout-form-group">
                      <label class="checkout-label">Nome no cartão</label>
                      <input type="text" class="checkout-input" placeholder="MARIA A SILVA">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="checkout-form-group">
                      <label class="checkout-label">Validade</label>
                      <input type="text" class="checkout-input" placeholder="MM/AA" maxlength="5">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="checkout-form-group">
                      <label class="checkout-label">CVV</label>
                      <input type="text" class="checkout-input" placeholder="000" maxlength="3">
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="checkout-form-group">
                      <label class="checkout-label">Parcelas</label>
                      <select class="checkout-input" style="cursor:pointer;">
                        <option>1x de R$ 998,00 (à vista)</option>
                        <option>2x de R$ 510,00 (sem juros)</option>
                        <option>3x de R$ 345,00 (sem juros)</option>
                        <option>6x de R$ 178,00 (sem juros)</option>
                        <option>12x de R$ 95,00</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              {{-- PIX --}}
              <div id="pay-pix" style="display:none;text-align:center;padding:20px;">
                <div style="background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:28px;display:inline-block;">
                  <div style="width:160px;height:160px;background:rgba(0,163,255,0.08);border:2px dashed rgba(0,163,255,0.3);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <span style="font-size:13px;color:var(--fg-4);">QR Code PIX</span>
                  </div>
                  <div style="font-size:13px;color:var(--fg-3);margin-bottom:10px;">Copie o código PIX abaixo:</div>
                  <div style="background:var(--bg-3);border:1px solid var(--line-2);border-radius:8px;padding:10px 14px;font-family:var(--font-mono);font-size:11px;color:var(--brand-200);word-break:break-all;text-align:left;margin-bottom:12px;">00020126580014BR.GOV.BCB.PIX0136...</div>
                  <button class="btn-ux btn-ux-ghost btn-ux-sm" style="width:100%;justify-content:center;">Copiar código PIX</button>
                </div>
                <p style="color:var(--success);font-size:14px;margin-top:14px;font-weight:600;">Acesso liberado em até 5 minutos após o pagamento</p>
              </div>

              {{-- Boleto --}}
              <div id="pay-boleto" style="display:none;text-align:center;padding:20px;">
                <i data-lucide="file-text" style="width:48px;height:48px;stroke:var(--fg-4);fill:none;stroke-width:1;margin-bottom:16px;"></i>
                <p style="color:var(--fg-3);font-size:15px;margin-bottom:16px;">Seu boleto será gerado após confirmar o pedido.<br>Vencimento: 3 dias úteis.</p>
                <p style="background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.25);border-radius:10px;padding:12px;font-size:13px;color:var(--warning);">
                  O acesso à plataforma é liberado após a confirmação do pagamento pelo banco (1-2 dias úteis).
                </p>
              </div>

              <button class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-top:24px;">
                <i data-lucide="lock" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                Finalizar pedido — R$ 998,00
              </button>

              <div style="display:flex;justify-content:center;gap:16px;margin-top:14px;flex-wrap:wrap;">
                @foreach(['Ambiente 100% seguro','Dados criptografados','Garantia de 7 dias'] as $t)
                <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--fg-4);">
                  <i data-lucide="shield-check" style="width:12px;height:12px;stroke:var(--success);fill:none;stroke-width:1.75;"></i>
                  {{ $t }}
                </div>
                @endforeach
              </div>
            </div>
          </div>

          {{-- Resumo do pedido --}}
          <div class="col-lg-5">
            <div class="checkout-card aos-fade aos-delay-2" style="position:sticky;top:120px;">
              <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:16px;">Resumo do pedido</div>

              <div style="display:flex;gap:14px;align-items:flex-start;padding:14px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-md);margin-bottom:16px;">
                <div style="width:64px;height:64px;border-radius:10px;background:var(--grad-brand-soft);border:1px solid var(--line-2);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                  <img src="{{ asset('img/logo-unyflex.png') }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
                </div>
                <div>
                  <div style="font-weight:700;color:#fff;font-size:15px;margin-bottom:4px;">Trilha Completa de Gestão Pública</div>
                  <div style="font-size:12px;color:var(--fg-3);">26 miniséries · 184 cápsulas · 1 ano de acesso</div>
                </div>
              </div>

              <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                @foreach(['Acesso a todas as 26 miniséries','184+ cápsulas de 10-20 min','Certificados Faculdade Unypublica','Materiais, mapas e checklists','Versão podcast de cada cápsula','Suporte por 1 ano'] as $it)
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--fg-2);">
                  <i data-lucide="check-circle" style="width:14px;height:14px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
                  {{ $it }}
                </div>
                @endforeach
              </div>

              <div style="height:1px;background:var(--line-1);margin-bottom:16px;"></div>

              <div style="display:flex;justify-content:space-between;font-size:14px;color:var(--fg-3);margin-bottom:8px;">
                <span>Valor original</span>
                <span style="text-decoration:line-through;">R$ 1.990,00</span>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:14px;color:var(--success);margin-bottom:12px;">
                <span>Desconto 50% OFF</span>
                <span>− R$ 992,00</span>
              </div>
              <div style="display:flex;justify-content:space-between;font-family:var(--font-display);font-weight:800;font-size:22px;color:#fff;">
                <span>Total</span>
                <span>R$ 998,00</span>
              </div>

              <div data-countdown class="countdown-wrap" style="margin:16px 0;">
                <div class="countdown-label">Preço garantido por:</div>
                <div class="countdown-timer">
                  <div class="countdown-unit"><span class="countdown-num" data-cd-days style="font-size:24px;min-width:48px;padding:8px 6px;">07</span><div class="countdown-lbl">Dias</div></div>
                  <div class="countdown-sep" style="font-size:20px;">:</div>
                  <div class="countdown-unit"><span class="countdown-num" data-cd-hours style="font-size:24px;min-width:48px;padding:8px 6px;">00</span><div class="countdown-lbl">Horas</div></div>
                  <div class="countdown-sep" style="font-size:20px;">:</div>
                  <div class="countdown-unit"><span class="countdown-num" data-cd-mins style="font-size:24px;min-width:48px;padding:8px 6px;">00</span><div class="countdown-lbl">Min</div></div>
                  <div class="countdown-sep" style="font-size:20px;">:</div>
                  <div class="countdown-unit"><span class="countdown-num" data-cd-secs style="font-size:24px;min-width:48px;padding:8px 6px;">00</span><div class="countdown-lbl">Seg</div></div>
                </div>
              </div>

              <div class="vagas-bar">
                <i data-lucide="alert-triangle" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;flex-shrink:0;"></i>
                Restam <strong>23 vagas</strong> neste preço
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function selectPayment(val, btn) {
  ['cartao','pix','boleto'].forEach(v => {
    document.getElementById('pay-' + v).style.display = v === val ? '' : 'none';
    const tab = document.getElementById('pay-tab-' + v);
    if (tab) {
      tab.style.background = v === val ? 'var(--bg-3)' : 'var(--bg-4)';
      tab.style.borderColor = v === val ? 'rgba(0,163,255,0.35)' : 'var(--line-2)';
      tab.style.color = v === val ? '#fff' : 'var(--fg-3)';
    }
  });
}
</script>
@endpush
@endsection
