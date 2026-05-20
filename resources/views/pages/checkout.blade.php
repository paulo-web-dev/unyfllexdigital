@extends('layouts.site')
@section('meta_title', 'Checkout — Unyflex Digital')

@section('content')

<script id="minisseries-data" type="application/json">
  {!! json_encode($minisseries) !!}
</script>

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
                      <label class="checkout-label">Nome completo *</label>
                      <input type="text" id="inputNome" class="checkout-input" placeholder="Maria Aparecida Silva">
                      <span class="field-err" id="err-nome"></span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="checkout-form-group">
                      <label class="checkout-label">CPF *</label>
                      <input type="text" id="inputCpf" class="checkout-input" placeholder="000.000.000-00" maxlength="14">
                      <span class="field-err" id="err-cpf"></span>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="checkout-form-group">
                      <label class="checkout-label">E-mail *</label>
                      <input type="email" id="inputEmail" class="checkout-input" placeholder="maria@prefeitura.sp.gov.br">
                      <span class="field-err" id="err-email"></span>
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
                  @foreach(['CREDIT_CARD'=>'Cartão de crédito','PIX'=>'PIX','BOLETO'=>'Boleto'] as $val => $lbl)
                  <button onclick="selectPayment('{{ $val }}', this)"
                          id="pay-tab-{{ $val }}"
                          class="pay-tab {{ $val === 'CREDIT_CARD' ? 'pay-tab-active' : '' }}"
                          style="flex:1;padding:10px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:all 0.2s;">
                    {{ $lbl }}
                  </button>
                  @endforeach
                </div>

                {{-- ══ CARTÃO ══════════════════════════════════════════════ --}}
                <div id="pay-CREDIT_CARD">
                  <div class="row g-3">

                    {{-- Número --}}
                    <div class="col-12">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Número do cartão *</label>
                        <input type="text" class="checkout-input" placeholder="0000 0000 0000 0000" maxlength="19" id="inputCardNum">
                        <span class="field-err" id="err-card_number"></span>
                      </div>
                    </div>

                    {{-- Nome --}}
                    <div class="col-md-6">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Nome no cartão *</label>
                        <input type="text" class="checkout-input" placeholder="MARIA A SILVA" id="inputCardName">
                        <span class="field-err" id="err-card_holder_name"></span>
                      </div>
                    </div>

                    {{-- Validade --}}
                    <div class="col-md-3">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Validade *</label>
                        <input type="text" class="checkout-input" placeholder="MM/AA" maxlength="5" id="inputCardVal">
                        <span class="field-err" id="err-card_expiry"></span>
                      </div>
                    </div>

                    {{-- CVV --}}
                    <div class="col-md-3">
                      <div class="checkout-form-group">
                        <label class="checkout-label">CVV *</label>
                        <input type="text" class="checkout-input" placeholder="000" maxlength="4" id="inputCardCvv">
                        <span class="field-err" id="err-card_cvv"></span>
                      </div>
                    </div>

                    {{-- Parcelas --}}
                    <div class="col-12">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Parcelas</label>
                        <select class="checkout-input" id="selectParcelas" style="cursor:pointer;"></select>
                      </div>
                    </div>

                    {{-- ── Endereço do titular ───────────────────────────── --}}
                    <div class="col-12" style="margin-top:4px;">
                      <div style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--fg-4);margin-bottom:12px;padding-top:8px;border-top:1px solid var(--line-1);">
                        Endereço do titular
                      </div>
                    </div>

                    {{-- CEP --}}
                    <div class="col-md-4">
                      <div class="checkout-form-group">
                        <label class="checkout-label">CEP *</label>
                        <input type="text" class="checkout-input" placeholder="00000-000" maxlength="9"
                               id="inputCardCep" oninput="buscarCep(this.value)">
                        <span class="field-err" id="err-card_cep"></span>
                      </div>
                    </div>

                    {{-- Endereço --}}
                    <div class="col-md-6">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Endereço *</label>
                        <input type="text" class="checkout-input" placeholder="Rua das Flores" id="inputCardEnd">
                        <span class="field-err" id="err-card_endereco"></span>
                      </div>
                    </div>

                    {{-- Número --}}
                    <div class="col-md-2">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Nº *</label>
                        <input type="text" class="checkout-input" placeholder="123" id="inputCardNumEnd">
                        <span class="field-err" id="err-card_numero"></span>
                      </div>
                    </div>

                    {{-- Bairro --}}
                    <div class="col-md-4">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Bairro *</label>
                        <input type="text" class="checkout-input" placeholder="Centro" id="inputCardBairro">
                        <span class="field-err" id="err-card_bairro"></span>
                      </div>
                    </div>

                    {{-- Cidade --}}
                    <div class="col-md-5">
                      <div class="checkout-form-group">
                        <label class="checkout-label">Cidade *</label>
                        <input type="text" class="checkout-input" placeholder="São Paulo" id="inputCardCidade">
                        <span class="field-err" id="err-card_cidade"></span>
                      </div>
                    </div>

                    {{-- UF --}}
                    <div class="col-md-3">
                      <div class="checkout-form-group">
                        <label class="checkout-label">UF *</label>
                        <input type="text" class="checkout-input" placeholder="SP" maxlength="2"
                               id="inputCardUf" style="text-transform:uppercase;">
                        <span class="field-err" id="err-card_uf"></span>
                      </div>
                    </div>

                  </div>
                </div>

                {{-- ══ PIX ═════════════════════════════════════════════════ --}}
                <div id="pay-PIX" style="display:none;text-align:center;padding:20px;">
                  <div id="pixContainer" style="display:none;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:28px;display:inline-block;">
                    <img id="pixQrImg" src="" alt="QR Code PIX" style="width:160px;height:160px;border-radius:8px;margin:0 auto 16px;display:block;">
                    <div style="font-size:13px;color:var(--fg-3);margin-bottom:10px;">Copie o código PIX abaixo:</div>
                    <div id="pixCopiaCola" style="background:var(--bg-3);border:1px solid var(--line-2);border-radius:8px;padding:10px 14px;font-family:var(--font-mono);font-size:11px;color:var(--brand-200);word-break:break-all;text-align:left;margin-bottom:12px;"></div>
                    <button onclick="copiarPix()" class="btn-ux btn-ux-ghost btn-ux-sm" style="width:100%;justify-content:center;">Copiar código PIX</button>
                  </div>
                  <div id="pixPlaceholder">
                    <div style="width:160px;height:160px;background:rgba(0,163,255,0.08);border:2px dashed rgba(0,163,255,0.3);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                      <span style="font-size:13px;color:var(--fg-4);">QR Code PIX</span>
                    </div>
                    <p style="color:var(--fg-3);font-size:14px;">O QR Code será gerado após confirmar o pedido.</p>
                  </div>
                  <p style="color:var(--success);font-size:14px;margin-top:14px;font-weight:600;">Acesso liberado em até 5 minutos após o pagamento</p>
                </div>

                {{-- ══ BOLETO ══════════════════════════════════════════════ --}}
                <div id="pay-BOLETO" style="display:none;text-align:center;padding:20px;">
                  <div id="boletoContainer" style="display:none;">
                    <a id="boletoUrl" href="#" target="_blank" class="btn-ux btn-ux-primary" style="margin-bottom:14px;display:inline-flex;">
                      <i data-lucide="download" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                      Baixar boleto
                    </a>
                    <div id="boletoLinha" style="background:var(--bg-3);border:1px solid var(--line-2);border-radius:8px;padding:10px 14px;font-family:var(--font-mono);font-size:11px;color:var(--fg-2);word-break:break-all;margin-bottom:12px;"></div>
                  </div>
                  <i data-lucide="file-text" style="width:48px;height:48px;stroke:var(--fg-4);fill:none;stroke-width:1;margin-bottom:16px;"></i>
                  <p style="color:var(--fg-3);font-size:15px;margin-bottom:16px;">Seu boleto será gerado após confirmar o pedido.<br>Vencimento: 3 dias úteis.</p>
                  <p style="background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.25);border-radius:10px;padding:12px;font-size:13px;color:var(--warning);">
                    O acesso é liberado após a confirmação do pagamento pelo banco (1–2 dias úteis).
                  </p>
                </div>

                {{-- Alerta geral --}}
                <div id="alertErro" style="display:none;background:rgba(255,77,77,0.10);border:1px solid rgba(255,77,77,0.3);border-radius:10px;padding:12px 16px;color:#ff6b6b;font-size:14px;margin-top:16px;"></div>

                {{-- Botão --}}
                <button id="btnFinalizar" onclick="finalizarPedido()" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-top:24px;">
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

            {{-- ===================== RESUMO ===================== --}}
            <div class="col-lg-5">
              <div class="checkout-card aos-fade aos-delay-2" style="position:sticky;top:120px;">

                <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:16px;">
                  Resumo do pedido
                </div>

                <div id="checkoutItemsList" style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;"></div>

                <a href="#" style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--fg-4);text-decoration:none;margin-bottom:18px;transition:color 0.2s;"
                   onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                  Editar carrinho
                </a>

                <div style="height:1px;background:var(--line-1);margin-bottom:16px;"></div>

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

                <div style="display:flex;justify-content:space-between;font-size:14px;color:var(--fg-3);margin-bottom:6px;">
                  <span id="checkoutQtyLabel">0 minisérie</span>
                  <span id="checkoutSubtotal">R$ 0,00</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-family:var(--font-display);font-weight:800;font-size:24px;color:#fff;margin-bottom:16px;">
                  <span>Total</span>
                  <span id="checkoutTotal">R$ 0,00</span>
                </div>

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

          </div>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- Modal de sucesso --}}
<div id="modalSucesso" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:20px;padding:40px;max-width:480px;width:90%;text-align:center;">
    <div style="width:64px;height:64px;border-radius:50%;background:rgba(43,217,161,0.15);border:2px solid rgba(43,217,161,0.4);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
      <i data-lucide="check" style="width:28px;height:28px;stroke:#6FE6BD;fill:none;stroke-width:2.5;"></i>
    </div>
    <h2 id="modalTitulo" style="font-family:var(--font-display);font-weight:800;font-size:24px;color:#fff;margin-bottom:8px;"></h2>
    <p id="modalMsg" style="color:var(--fg-3);font-size:15px;margin-bottom:24px;"></p>
    <a id="modalBtn" href="{{ route('dashboard') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;">
      Acessar minha área
    </a>
  </div>
</div>

<style>
.pay-tab { background:var(--bg-4,var(--bg-3));border:1px solid var(--line-2);color:var(--fg-3); }
.pay-tab-active,.pay-tab:hover { background:var(--bg-3);border-color:rgba(0,163,255,0.35);color:#fff; }
.field-err { font-size:11px;color:#ff6b6b;display:block;margin-top:3px;min-height:14px; }
.checkout-item-row { display:flex;align-items:center;gap:12px;padding:12px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-md);transition:border-color 0.2s; }
.checkout-item-row:hover { border-color:rgba(0,163,255,0.25); }
.checkout-item-thumb { width:56px;height:40px;border-radius:7px;object-fit:cover;background:var(--bg-3);border:1px solid var(--line-1);flex-shrink:0; }
.checkout-item-thumb-placeholder { width:56px;height:40px;border-radius:7px;background:linear-gradient(135deg,rgba(0,114,255,0.18),rgba(0,163,255,0.08));border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;color:var(--brand-300);flex-shrink:0; }
.checkout-item-name { flex:1;font-size:13px;font-weight:600;color:var(--fg-1);min-width:0; }
.checkout-item-price { font-size:13px;font-weight:700;color:#fff;white-space:nowrap; }
.checkout-item-remove { background:none;border:none;color:var(--fg-4);cursor:pointer;padding:4px;border-radius:6px;display:flex;align-items:center;transition:color 0.15s,background 0.15s;flex-shrink:0; }
.checkout-item-remove:hover { color:#ff4d4d;background:rgba(255,77,77,0.1); }
</style>
@endsection

@push('scripts')
<script>
const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const CATALOG = JSON.parse(document.getElementById('minisseries-data').textContent);

let metodoPagamento = 'CREDIT_CARD';

function fmt(val) {
  return 'R$ ' + val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ── Parcelas ──────────────────────────────────────────────────────────────
function buildParcelas(total) {
  const sel = document.getElementById('selectParcelas');
  if (!sel) return;
  sel.innerHTML = [
    { n: 1,  label: `1x de ${fmt(total)} (à vista)` },
    { n: 2,  label: `2x de ${fmt(total/2)} (sem juros)` },
    { n: 3,  label: `3x de ${fmt(total/3)} (sem juros)` },
    { n: 6,  label: `6x de ${fmt(total/6)} (sem juros)` },
    { n: 12, label: `12x de ${fmt(total/12)}` },
  ].map(o => `<option value="${o.n}">${o.label}</option>`).join('');
}

// ── Renderiza carrinho ────────────────────────────────────────────────────
function renderCheckout() {
  const cart    = window.UnyCart ? UnyCart.getCart() : [];
  const emptyEl = document.getElementById('checkoutEmpty');
  const mainEl  = document.getElementById('checkoutMain');
  const listEl  = document.getElementById('checkoutItemsList');

  if (!cart.length) { emptyEl.style.display = 'block'; mainEl.style.display = 'none'; return; }
  emptyEl.style.display = 'none'; mainEl.style.display = 'block';

  const itens = cart.map(item => {
    const meta = CATALOG.find(m => m.id == item.id) ?? {};
    return { ...item, valor: meta.valor ?? 998, thumb: meta.thumb ?? item.thumb };
  });

  const total = itens.reduce((s, i) => s + i.valor, 0);
  const qty   = itens.length;

  document.getElementById('checkoutQtyLabel').textContent  = qty === 1 ? '1 minisérie' : qty + ' miniséries';
  document.getElementById('checkoutSubtotal').textContent  = fmt(total);
  document.getElementById('checkoutTotal').textContent     = fmt(total);
  document.getElementById('btnFinalizarLabel').textContent = `Finalizar pedido — ${fmt(total)}`;

  buildParcelas(total);

  listEl.innerHTML = itens.map(item => `
    <div class="checkout-item-row" data-item-id="${item.id}">
      ${item.thumb
        ? `<img class="checkout-item-thumb" src="${item.thumb}" alt="${item.title}" onerror="this.style.display='none'">`
        : `<div class="checkout-item-thumb-placeholder"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="6 4 20 12 6 20 6 4"/></svg></div>`
      }
      <div class="checkout-item-name">${item.title}</div>
      <div class="checkout-item-price">${fmt(item.valor)}</div>
      <button class="checkout-item-remove" data-remove-id="${item.id}" aria-label="Remover">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  `).join('');

  listEl.querySelectorAll('.checkout-item-remove').forEach(btn => {
    btn.addEventListener('click', function () {
      if (window.UnyCart) UnyCart.removeItem(this.dataset.removeId);
      renderCheckout();
    });
  });
}

// ── Troca método de pagamento ──────────────────────────────────────────────
function selectPayment(val) {
  metodoPagamento = val;
  ['CREDIT_CARD','PIX','BOLETO'].forEach(v => {
    const panel = document.getElementById('pay-' + v);
    const tab   = document.getElementById('pay-tab-' + v);
    if (panel) panel.style.display = v === val ? '' : 'none';
    if (tab)   tab.classList.toggle('pay-tab-active', v === val);
  });
}

// ── Busca CEP via ViaCEP ───────────────────────────────────────────────────
let cepTimer = null;
async function buscarCep(valor) {
  const cep = valor.replace(/\D/g, '');
  if (cep.length !== 8) return;
  clearTimeout(cepTimer);
  cepTimer = setTimeout(async () => {
    try {
      const res  = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
      const data = await res.json();
      if (data.erro) return;
      const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
      set('inputCardEnd',    data.logradouro);
      set('inputCardBairro', data.bairro);
      set('inputCardCidade', data.localidade);
      set('inputCardUf',     data.uf?.toUpperCase());
      document.getElementById('inputCardNumEnd')?.focus();
    } catch (e) {}
  }, 500);
}

// ── Limpa / mostra erros ───────────────────────────────────────────────────
function limparErros() {
  document.querySelectorAll('.field-err').forEach(el => el.textContent = '');
  const al = document.getElementById('alertErro');
  if (al) { al.style.display = 'none'; al.textContent = ''; }
}

function mostrarErros(errors) {
  if (!errors) return;
  Object.entries(errors).forEach(([field, msgs]) => {
    const key = field.replace(/\./g, '_');
    const el  = document.getElementById('err-' + key);
    if (el) el.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
  });
}

function mostrarAlerta(msg) {
  const el = document.getElementById('alertErro');
  if (!el) return;
  el.textContent = msg;
  el.style.display = 'block';
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ── Finalizar pedido ───────────────────────────────────────────────────────
async function finalizarPedido() {
  limparErros();

  const cart = window.UnyCart ? UnyCart.getCart() : [];
  if (!cart.length) { mostrarAlerta('Adicione ao menos uma minisérie ao carrinho.'); return; }

  const itemCatalog = CATALOG.find(m => m.id == cart[0].id) ?? {};
  const classesId   = cart[0].id;
  const valor       = parseFloat(itemCatalog.valor ?? 998);
  const parcelas    = parseInt(document.getElementById('selectParcelas')?.value ?? '1');

  const payload = {
    nome:             document.getElementById('inputNome')?.value.trim()  ?? '',
    email:            document.getElementById('inputEmail')?.value.trim() ?? '',
    cpf:              document.getElementById('inputCpf')?.value.trim()   ?? '',
    whatsapp:         document.getElementById('inputWhats')?.value.trim() ?? '',
    orgao:            document.getElementById('inputOrgao')?.value.trim() ?? '',
    classes_id:       classesId,
    metodo_pagamento: metodoPagamento,
    valor,
    desconto:         0,
    valor_final:      valor,
    parcelas,
    ...(metodoPagamento === 'CREDIT_CARD' && {
      card_holder_name: document.getElementById('inputCardName')?.value.trim()                                   ?? '',
      card_number:      (document.getElementById('inputCardNum')?.value ?? '').replace(/\s+/g, ''),
      card_expiry:      document.getElementById('inputCardVal')?.value.trim()                                    ?? '',
      card_cvv:         document.getElementById('inputCardCvv')?.value.trim()                                    ?? '',
      card_cep:         (document.getElementById('inputCardCep')?.value ?? '').replace(/\D/g, ''),
      card_endereco:    document.getElementById('inputCardEnd')?.value.trim()                                    ?? '',
      card_numero:      document.getElementById('inputCardNumEnd')?.value.trim()                                 ?? '',
      card_bairro:      document.getElementById('inputCardBairro')?.value.trim()                                 ?? '',
      card_cidade:      document.getElementById('inputCardCidade')?.value.trim()                                 ?? '',
      card_uf:          (document.getElementById('inputCardUf')?.value.trim() ?? '').toUpperCase(),
    }),
  };

  const btn = document.getElementById('btnFinalizar');
  const lbl = document.getElementById('btnFinalizarLabel');
  btn.disabled    = true;
  lbl.textContent = 'Processando…';

  try {
    const res  = await fetch('{{ route('checkout.processar') }}', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body:    JSON.stringify(payload),
    });

    const data = await res.json();

    if (!res.ok || !data.success) {
      if (data.errors) mostrarErros(data.errors);
      else mostrarAlerta(data.message ?? 'Erro ao processar pagamento.');
      btn.disabled    = false;
      lbl.textContent = `Finalizar pedido — ${fmt(valor)}`;
      return;
    }

    // Sucesso por método
    if (metodoPagamento === 'PIX' && data.pix_copia_cola) {
      document.getElementById('pixPlaceholder').style.display = 'none';
      document.getElementById('pixContainer').style.display   = 'block';
      if (data.pix_qrcode) document.getElementById('pixQrImg').src = 'data:image/png;base64,' + data.pix_qrcode;
      document.getElementById('pixCopiaCola').textContent = data.pix_copia_cola;
      window._pixCopiaECola = data.pix_copia_cola;
      mostrarModal('PIX gerado!', data.mensagem ?? 'Escaneie o QR Code para pagar.');
    } else if (metodoPagamento === 'BOLETO' && data.boleto_url) {
      document.getElementById('boletoContainer').style.display = 'block';
      document.getElementById('boletoUrl').href = data.boleto_url;
      if (data.boleto_linha) document.getElementById('boletoLinha').textContent = data.boleto_linha;
      mostrarModal('Boleto gerado!', data.mensagem ?? 'Clique para baixar.');
    } else if (metodoPagamento === 'CREDIT_CARD') {
      if (window.UnyCart) UnyCart.clear?.();
      mostrarModal('Pagamento aprovado! 🎉', data.mensagem ?? 'Acesso liberado.');
      setTimeout(() => { window.location.href = data.redirect ?? '{{ route('dashboard') }}'; }, 2000);
    }

  } catch (e) {
    mostrarAlerta('Erro de conexão. Tente novamente.');
    btn.disabled    = false;
    lbl.textContent = `Finalizar pedido — ${fmt(valor)}`;
  }
}

function mostrarModal(titulo, msg) {
  document.getElementById('modalTitulo').textContent = titulo;
  document.getElementById('modalMsg').textContent    = msg;
  document.getElementById('modalSucesso').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}

function copiarPix() {
  if (!window._pixCopiaECola) return;
  navigator.clipboard.writeText(window._pixCopiaECola).then(() => alert('Código PIX copiado!')).catch(() => {});
}

// ── Máscaras ───────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  renderCheckout();
  document.addEventListener('cart:updated', renderCheckout);

  // CPF
  const cpf = document.getElementById('inputCpf');
  if (cpf) cpf.addEventListener('input', function () {
    let v = this.value.replace(/\D/g,'').slice(0,11);
    v = v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');
    this.value = v;
  });

  // Cartão
  const card = document.getElementById('inputCardNum');
  if (card) card.addEventListener('input', function () {
    let v = this.value.replace(/\D/g,'').slice(0,16);
    v = v.replace(/(\d{4})(?=\d)/g,'$1 ');
    this.value = v;
  });

  // Validade
  const val = document.getElementById('inputCardVal');
  if (val) val.addEventListener('input', function () {
    let v = this.value.replace(/\D/g,'').slice(0,4);
    if (v.length > 2) v = v.slice(0,2)+'/'+v.slice(2);
    this.value = v;
  });

  // CEP
  const cep = document.getElementById('inputCardCep');
  if (cep) cep.addEventListener('input', function () {
    let v = this.value.replace(/\D/g,'').slice(0,8);
    if (v.length > 5) v = v.slice(0,5)+'-'+v.slice(5);
    this.value = v;
  });

  // UF uppercase
  const uf = document.getElementById('inputCardUf');
  if (uf) uf.addEventListener('input', function () {
    this.value = this.value.toUpperCase().replace(/[^A-Z]/g,'').slice(0,2);
  });
});
</script>
@endpush
