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
            <span>Pagamento seguro · Acesso imediato</span>
          </div>
          <h1 style="font-family:var(--font-display);font-weight:800;font-size:clamp(28px,3.5vw,40px);color:#fff;letter-spacing:-0.025em;margin-bottom:10px;">
            Finalizar Compra
          </h1>
          <p style="font-size:16px;color:var(--fg-3);">Revise seus itens e preencha os dados abaixo para garantir seu acesso.</p>
        </div>

        {{-- CARRINHO VAZIO --}}
        <div id="checkoutEmpty" style="display:none;text-align:center;padding:60px 20px;">
          <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="color:var(--fg-4);margin-bottom:20px;">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          <h3 style="font-family:var(--font-display);font-weight:700;font-size:22px;color:#fff;margin-bottom:10px;">Seu carrinho está vazio</h3>
          <p style="color:var(--fg-3);margin-bottom:24px;">Adicione miniséries ao carrinho antes de finalizar a compra.</p>
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
            Explorar miniséries
          </a>
        </div>

        {{-- CHECKOUT PRINCIPAL --}}
        <div id="checkoutMain" style="display:none;">
          <div class="row g-4">

            {{-- ===================== FORMULÁRIO ===================== --}}
            <div class="col-lg-7">
              <div class="checkout-card aos-fade">

                {{-- Dados pessoais --}}
                <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:20px;">Seus dados</div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="checkout-form-group">
                      <label class="checkout-label">Nome completo</label>
                      <input type="text" id="inputNome" class="checkout-input" placeholder="Maria Aparecida Silva">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="checkout-form-group">
                      <label class="checkout-label">CPF</label>
                      <input type="text" id="inputCpf" class="checkout-input" placeholder="000.000.000-00" maxlength="14">
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="checkout-form-group">
                      <label class="checkout-label">E-mail</label>
                      <input type="email" id="inputEmail" class="checkout-input" placeholder="maria@prefeitura.sp.gov.br">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="checkout-form-group">
                      <label class="checkout-label">WhatsApp</label>
                      <input type="tel" id="inputWhats" class="checkout-input" placeholder="(11) 9 0000-0000">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="checkout-form-group">
                      <label class="checkout-label">Órgão / Prefeitura</label>
                      <input type="text" id="inputOrgao" class="checkout-input" placeholder="Prefeitura de São Paulo">
                    </div>
                  </div>
                </div>

                <div style="height:1px;background:var(--line-1);margin:24px 0;"></div>

                {{-- Pagamento --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:8px;">
                  <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);">Pagamento</div>
                  <div style="display:flex;gap:6px;">
                    @foreach(['VISA','MASTER','PIX','BOLETO'] as $c)
                    <span class="card-icon">{{ $c }}</span>
                    @endforeach
                  </div>
                </div>

                {{-- Tabs --}}
                <div style="display:flex;gap:6px;margin-bottom:20px;">
                  @foreach(['cartao'=>'Cartão de crédito','pix'=>'PIX','boleto'=>'Boleto'] as $val => $lbl)
                  <button onclick="selectPayment('{{ $val }}', this)"
                          id="pay-tab-{{ $val }}"
                          class="pay-tab {{ $val === 'cartao' ? 'pay-tab-active' : '' }}"
                          style="flex:1;padding:10px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:all 0.2s;">
                    {{ $lbl }}
                  </button>
                  @endforeach
                </div>

                {{-- Cartão --}}
                <div id="pay-cartao">
                  <div class="row g-3">
                    <div class="col-12">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Número do cartão</label>
                        <input type="text" class="checkout-input" placeholder="0000 0000 0000 0000" maxlength="19" id="inputCardNum">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Nome no cartão</label>
                        <input type="text" class="checkout-input" placeholder="MARIA A SILVA" id="inputCardName">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Validade</label>
                        <input type="text" class="checkout-input" placeholder="MM/AA" maxlength="5" id="inputCardVal">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="checkout-form-group">
                        <label class="checkout-label">CVV</label>
                        <input type="text" class="checkout-input" placeholder="000" maxlength="3" id="inputCardCvv">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Parcelas</label>
                        <select class="checkout-input" id="selectParcelas" style="cursor:pointer;"></select>
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
                    O acesso é liberado após a confirmação do pagamento pelo banco (1–2 dias úteis).
                  </p>
                </div>

                {{-- Botão finalizar --}}
                <button id="btnFinalizar" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-top:24px;">
                  <i data-lucide="lock" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                  <span id="btnFinalizarLabel">Finalizar pedido — R$ 0,00</span>
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

            {{-- ===================== RESUMO DO PEDIDO ===================== --}}
            <div class="col-lg-5">
              <div class="checkout-card aos-fade aos-delay-2" style="position:sticky;top:120px;">

                <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:16px;">
                  Resumo do pedido
                </div>

                {{-- Lista dinâmica de itens do carrinho --}}
                <div id="checkoutItemsList" style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                  {{-- Preenchido via JS --}}
                </div>

                {{-- Link para editar carrinho --}}
                <a href="#"
                   style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--fg-4);text-decoration:none;margin-bottom:18px;transition:color 0.2s;"
                   onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"/>
                  </svg>
                  Editar carrinho
                </a>

                <div style="height:1px;background:var(--line-1);margin-bottom:16px;"></div>

                {{-- O que está incluso --}}
                <div style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--fg-4);margin-bottom:12px;">Incluso em cada minisérie</div>
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:18px;">
                  @foreach([
                    'Cápsulas de 10 a 20 minutos',
                    'Certificado Faculdade Unypublica',
                    'Materiais, modelos e checklists',
                    'Versão podcast de cada cápsula',
                    'Suporte pedagógico durante o acesso',
                  ] as $it)
                  <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--fg-2);">
                    <i data-lucide="check-circle" style="width:14px;height:14px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
                    {{ $it }}
                  </div>
                  @endforeach
                </div>

                <div style="height:1px;background:var(--line-1);margin-bottom:16px;"></div>

                {{-- Totais --}}
                <div style="display:flex;justify-content:space-between;font-size:14px;color:var(--fg-3);margin-bottom:6px;">
                  <span id="checkoutQtyLabel">0 minisérie</span>
                  <span id="checkoutSubtotal">R$ 0,00</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-family:var(--font-display);font-weight:800;font-size:24px;color:#fff;margin-bottom:16px;">
                  <span>Total</span>
                  <span id="checkoutTotal">R$ 0,00</span>
                </div>

                {{-- Countdown --}}
                <div data-countdown class="countdown-wrap" style="margin:0 0 16px;">
                  <div class="countdown-label">Preço garantido por:</div>
                  <div class="countdown-timer">
                    <div class="countdown-unit"><span class="countdown-num" data-cd-days style="font-size:22px;min-width:44px;padding:8px 6px;">07</span><div class="countdown-lbl">Dias</div></div>
                    <div class="countdown-sep" style="font-size:18px;">:</div>
                    <div class="countdown-unit"><span class="countdown-num" data-cd-hours style="font-size:22px;min-width:44px;padding:8px 6px;">00</span><div class="countdown-lbl">Horas</div></div>
                    <div class="countdown-sep" style="font-size:18px;">:</div>
                    <div class="countdown-unit"><span class="countdown-num" data-cd-mins style="font-size:22px;min-width:44px;padding:8px 6px;">00</span><div class="countdown-lbl">Min</div></div>
                    <div class="countdown-sep" style="font-size:18px;">:</div>
                    <div class="countdown-unit"><span class="countdown-num" data-cd-secs style="font-size:22px;min-width:44px;padding:8px 6px;">00</span><div class="countdown-lbl">Seg</div></div>
                  </div>
                </div>

                <div class="vagas-bar">
                  <i data-lucide="alert-triangle" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;flex-shrink:0;"></i>
                  Restam <strong>23 vagas</strong> neste preço
                </div>
              </div>
            </div>

          </div>{{-- /row --}}
        </div>{{-- /checkoutMain --}}

      </div>
    </div>
  </div>
</div>

{{-- ================================================================
     ESTILOS EXTRAS — adicione ao site.css
     ================================================================ --}}
<style>
.pay-tab {
  background: var(--bg-4, var(--bg-3));
  border: 1px solid var(--line-2);
  color: var(--fg-3);
}
.pay-tab-active,
.pay-tab:hover {
  background: var(--bg-3);
  border-color: rgba(0,163,255,0.35);
  color: #fff;
}

.checkout-item-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: var(--bg-1);
  border: 1px solid var(--line-2);
  border-radius: var(--r-md);
  transition: border-color 0.2s;
}
.checkout-item-row:hover { border-color: rgba(0,163,255,0.25); }

.checkout-item-thumb {
  width: 56px;
  height: 40px;
  border-radius: 7px;
  object-fit: cover;
  background: var(--bg-3);
  border: 1px solid var(--line-1);
  flex-shrink: 0;
}
.checkout-item-thumb-placeholder {
  width: 56px;
  height: 40px;
  border-radius: 7px;
  background: linear-gradient(135deg,rgba(0,114,255,0.18),rgba(0,163,255,0.08));
  border: 1px solid rgba(0,163,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--brand-300);
  flex-shrink: 0;
}
.checkout-item-name {
  flex: 1;
  font-size: 13px;
  font-weight: 600;
  color: var(--fg-1);
  min-width: 0;
}
.checkout-item-price {
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  white-space: nowrap;
}
.checkout-item-remove {
  background: none;
  border: none;
  color: var(--fg-4);
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  transition: color 0.15s, background 0.15s;
  flex-shrink: 0;
}
.checkout-item-remove:hover {
  color: #ff4d4d;
  background: rgba(255,77,77,0.1);
}
</style>

@push('scripts')
<script>
const PRICE_PER_COURSE = 998;

function fmt(val) {
  return 'R$ ' + val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function buildParcelas(total) {
  const sel = document.getElementById('selectParcelas');
  if (!sel) return;
  const opts = [
    { n: 1,  label: `1x de ${fmt(total)} (à vista)` },
    { n: 2,  label: `2x de ${fmt(total/2)} (sem juros)` },
    { n: 3,  label: `3x de ${fmt(total/3)} (sem juros)` },
    { n: 6,  label: `6x de ${fmt(total/6)} (sem juros)` },
    { n: 12, label: `12x de ${fmt(total/12)}` },
  ];
  sel.innerHTML = opts.map(o => `<option value="${o.n}">${o.label}</option>`).join('');
}

function renderCheckout() {
  const cart = (window.UnyCart ? UnyCart.getCart() : []);

  const emptyEl  = document.getElementById('checkoutEmpty');
  const mainEl   = document.getElementById('checkoutMain');
  const listEl   = document.getElementById('checkoutItemsList');
  const qtyLbl   = document.getElementById('checkoutQtyLabel');
  const subEl    = document.getElementById('checkoutSubtotal');
  const totalEl  = document.getElementById('checkoutTotal');
  const btnLbl   = document.getElementById('btnFinalizarLabel');

  if (cart.length === 0) {
    emptyEl.style.display = 'block';
    mainEl.style.display  = 'none';
    return;
  }

  emptyEl.style.display = 'none';
  mainEl.style.display  = 'block';

  const qty   = cart.length;
  const total = qty * PRICE_PER_COURSE;

  // Qty / totais
  qtyLbl.textContent  = qty === 1 ? '1 minisérie' : qty + ' miniséries';
  subEl.textContent   = fmt(total);
  totalEl.textContent = fmt(total);
  if (btnLbl) btnLbl.textContent = `Finalizar pedido — ${fmt(total)}`;

  // Parcelas dinâmicas
  buildParcelas(total);

  // Lista de itens
  listEl.innerHTML = cart.map(item => `
    <div class="checkout-item-row" data-item-id="${item.id}">
      ${item.thumb
        ? `<img class="checkout-item-thumb" src="${item.thumb}" alt="${item.title}" onerror="this.style.display='none'">`
        : `<div class="checkout-item-thumb-placeholder">
             <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="6 4 20 12 6 20 6 4"/></svg>
           </div>`
      }
      <div class="checkout-item-name">${item.title}</div>
      <div class="checkout-item-price">${fmt(PRICE_PER_COURSE)}</div>
      <button class="checkout-item-remove" data-remove-id="${item.id}" aria-label="Remover ${item.title}">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
  `).join('');

  // Listeners de remoção
  listEl.querySelectorAll('.checkout-item-remove').forEach(btn => {
    btn.addEventListener('click', function () {
      if (window.UnyCart) UnyCart.removeItem(this.dataset.removeId);
      renderCheckout();
    });
  });
}

function selectPayment(val) {
  ['cartao','pix','boleto'].forEach(v => {
    document.getElementById('pay-' + v).style.display = v === val ? '' : 'none';
    const tab = document.getElementById('pay-tab-' + v);
    if (tab) {
      tab.classList.toggle('pay-tab-active', v === val);
    }
  });
}

// Máscaras simples
document.addEventListener('DOMContentLoaded', function () {
  renderCheckout();

  // Re-renderiza se o carrinho mudar (ex: removido pelo dropdown do navbar)
  document.addEventListener('cart:updated', renderCheckout);

  // Máscara CPF
  const cpfInput = document.getElementById('inputCpf');
  if (cpfInput) {
    cpfInput.addEventListener('input', function () {
      let v = this.value.replace(/\D/g,'').slice(0,11);
      v = v.replace(/(\d{3})(\d)/,'$1.$2')
           .replace(/(\d{3})(\d)/,'$1.$2')
           .replace(/(\d{3})(\d{1,2})$/,'$1-$2');
      this.value = v;
    });
  }

  // Máscara Cartão
  const cardInput = document.getElementById('inputCardNum');
  if (cardInput) {
    cardInput.addEventListener('input', function () {
      let v = this.value.replace(/\D/g,'').slice(0,16);
      v = v.replace(/(\d{4})(?=\d)/g,'$1 ');
      this.value = v;
    });
  }

  // Máscara Validade
  const valInput = document.getElementById('inputCardVal');
  if (valInput) {
    valInput.addEventListener('input', function () {
      let v = this.value.replace(/\D/g,'').slice(0,4);
      if (v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
      this.value = v;
    });
  }
});
</script>
@endpush

@endsection