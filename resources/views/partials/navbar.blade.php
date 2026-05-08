<nav class="site-navbar" id="siteNavbar">
  <a href="{{ route('home') }}" class="navbar-logo">
    <div class="navbar-logo-mark">
      <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
    </div>
    <div>
      <div class="navbar-brand-text">UNYFLEX <span>DIGITAL</span></div>
      <span class="navbar-brand-sub">By Faculdade Unypublica · Reconhecida MEC</span>
    </div>
  </a>

  <ul class="navbar-links">
    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Início</a></li>
    <li><a href="{{ route('cursos') }}" class="{{ request()->routeIs('cursos*') ? 'active' : '' }}">Minisséries</a></li>
    <li><a href="{{ route('sobre') }}" class="{{ request()->routeIs('sobre') ? 'active' : '' }}">Sobre</a></li>
    <li><a href="{{ route('contato') }}" class="{{ request()->routeIs('contato') ? 'active' : '' }}">Contato</a></li>
  </ul>

  <div class="navbar-cta">
    <a href="{{ route('login') }}" class="btn-ux btn-ux-ghost btn-ux-sm">Entrar</a>

    {{-- Carrinho com dropdown --}}
    <div class="navbar-cart-wrap" id="navbarCartWrap">
      <button class="navbar-cart-btn" id="navbarCartBtn" aria-label="Carrinho" aria-expanded="false">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/>
          <circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span class="navbar-cart-count" id="navbarCartCount" style="display:none;">0</span>
      </button>

      {{-- Dropdown balão --}}
      <div class="cart-dropdown" id="cartDropdown" aria-hidden="true">
        <div class="cart-dropdown-arrow"></div>

        <div class="cart-dropdown-header">
          <span class="cart-dropdown-title">Meu Carrinho</span>
          <span class="cart-dropdown-qty" id="cartDropdownQty">0 item</span>
        </div>

        {{-- Lista de itens --}}
        <div class="cart-dropdown-items" id="cartDropdownItems">
          {{-- Preenchido via JS --}}
        </div>

        {{-- Estado vazio --}}
        <div class="cart-dropdown-empty" id="cartDropdownEmpty">
          <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" style="color:var(--fg-4);margin-bottom:10px;">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          <p>Seu carrinho está vazio</p>
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-sm" style="margin-top:8px;">Ver minisséries</a>
        </div>

        {{-- Footer com total + botão --}}
        <div class="cart-dropdown-footer" id="cartDropdownFooter" style="display:none;">
          <div class="cart-dropdown-total">
            <span>Total</span>
            <strong id="cartDropdownTotal">R$ 0,00</strong>
          </div>
          <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary" style="width:100%;justify-content:center;margin-top:12px;">
            <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
            Finalizar Compra
          </a>
          <a href="#" style="display:block;text-align:center;font-size:12px;color:var(--fg-4);margin-top:10px;text-decoration:none;">
            Ver carrinho completo →
          </a>
        </div>
      </div>
    </div>
  </div>

  <button class="navbar-toggle" id="navbarToggle" aria-label="Menu">
    <span></span>
    <span></span>
    <span></span>
  </button>
</nav>

{{-- ================================================================
     CSS — adicione ao seu site.css
     ================================================================ --}}
<style>
/* ---- Cart button ---- */
.navbar-cart-wrap {
  position: relative;
}

.navbar-cart-btn {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: var(--r-md, 10px);
  border: 1px solid var(--line-2);
  background: var(--bg-2);
  color: var(--fg-2);
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s, background 0.2s;
  flex-shrink: 0;
}
.navbar-cart-btn:hover,
.navbar-cart-btn.active {
  border-color: var(--brand-400);
  color: var(--brand-300);
  background: rgba(0,163,255,0.08);
}

.navbar-cart-count {
  position: absolute;
  top: -7px;
  right: -7px;
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  background: linear-gradient(135deg,#0072FF,#00A3FF);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid var(--bg-1);
  line-height: 1;
  pointer-events: none;
}

/* ---- Dropdown ---- */
.cart-dropdown {
  position: absolute;
  top: calc(100% + 14px);
  right: 0;
  width: 320px;
  background: var(--bg-2);
  border: 1px solid var(--line-2);
  border-radius: var(--r-xl, 16px);
  box-shadow: 0 24px 60px -12px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04);
  z-index: 9999;
  padding: 0;
  overflow: hidden;

  /* Fechado por padrão */
  opacity: 0;
  transform: translateY(8px) scale(0.97);
  pointer-events: none;
  transform-origin: top right;
  transition: opacity 0.22s ease, transform 0.22s cubic-bezier(.16,1,.3,1);
}
.cart-dropdown.open {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

/* Setinha */
.cart-dropdown-arrow {
  position: absolute;
  top: -6px;
  right: 12px;
  width: 12px;
  height: 12px;
  background: var(--bg-2);
  border-top: 1px solid var(--line-2);
  border-left: 1px solid var(--line-2);
  transform: rotate(45deg);
  border-radius: 2px 0 0 0;
}

.cart-dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 18px 12px;
  border-bottom: 1px solid var(--line-1);
}
.cart-dropdown-title {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
}
.cart-dropdown-qty {
  font-size: 12px;
  color: var(--fg-4);
  background: var(--bg-3);
  border-radius: var(--r-pill);
  padding: 2px 10px;
  border: 1px solid var(--line-1);
}

/* Lista de itens */
.cart-dropdown-items {
  max-height: 260px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--line-2) transparent;
}
.cart-dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 18px;
  border-bottom: 1px solid var(--line-1);
  transition: background 0.15s;
}
.cart-dropdown-item:last-child { border-bottom: none; }
.cart-dropdown-item:hover { background: var(--bg-3); }

.cart-item-thumb {
  width: 48px;
  height: 36px;
  border-radius: 6px;
  object-fit: cover;
  background: var(--bg-3);
  flex-shrink: 0;
  border: 1px solid var(--line-1);
}
.cart-item-thumb-placeholder {
  width: 48px;
  height: 36px;
  border-radius: 6px;
  background: linear-gradient(135deg,rgba(0,114,255,0.2),rgba(0,163,255,0.1));
  border: 1px solid rgba(0,163,255,0.2);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--brand-300);
}
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--fg-1);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 3px;
}
.cart-item-price {
  font-size: 12px;
  font-weight: 700;
  color: var(--brand-300);
}
.cart-item-remove {
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
.cart-item-remove:hover {
  color: #ff4d4d;
  background: rgba(255,77,77,0.1);
}

/* Vazio */
.cart-dropdown-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 32px 18px;
  color: var(--fg-3);
  font-size: 13px;
  text-align: center;
}

/* Footer */
.cart-dropdown-footer {
  padding: 16px 18px 18px;
  border-top: 1px solid var(--line-1);
  background: var(--bg-1);
}
.cart-dropdown-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: var(--fg-2);
}
.cart-dropdown-total strong {
  font-size: 20px;
  font-weight: 800;
  color: #fff;
  font-family: var(--font-display);
}

/* ---- Toast (canto inferior DIREITO, posição correta) ---- */
.cart-toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  left: auto;
  z-index: 99999;
  background: var(--bg-2);
  border: 1px solid var(--line-2);
  border-left: 3px solid var(--success, #00C878);
  border-radius: var(--r-lg, 12px);
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 20px 50px -10px rgba(0,0,0,0.5);
  min-width: 280px;
  max-width: 340px;
  transform: translateY(20px);
  opacity: 0;
  transition: transform 0.3s cubic-bezier(.16,1,.3,1), opacity 0.3s ease;
  pointer-events: none;
}
.cart-toast.visible {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}
.cart-toast-icon {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: rgba(0,200,120,0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--success, #00C878);
}
.cart-toast-body { flex: 1; min-width: 0; }
.cart-toast-title { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.cart-toast-sub {
  font-size: 11px;
  color: var(--fg-3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cart-toast-action {
  font-size: 12px;
  font-weight: 600;
  color: var(--brand-300);
  text-decoration: none;
  white-space: nowrap;
  flex-shrink: 0;
}
.cart-toast-action:hover { color: var(--brand-200); }

/* Estado "no carrinho" do botão */
.btn-add-to-cart.in-cart {
  background: rgba(0,200,120,0.12) !important;
  border-color: rgba(0,200,120,0.35) !important;
  color: var(--success, #00C878) !important;
  cursor: default;
}
</style>

{{-- ================================================================
     TOAST HTML (coloque antes do </body>)
     ================================================================ --}}
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
  <div class="cart-toast-icon">
    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="20 6 9 17 4 12"/>
    </svg>
  </div>
  <div class="cart-toast-body">
    <div class="cart-toast-title">Adicionado ao carrinho!</div>
    <div class="cart-toast-sub" id="cartToastSub"></div>
  </div>
  <a href="#" class="cart-toast-action">Ver →</a>
</div>

{{-- ================================================================
     JAVASCRIPT
     ================================================================ --}}
<script>
/* ---- UnyCart: gerenciador de carrinho ---- */
window.UnyCart = (function () {
  const STORAGE_KEY = 'unyflex_cart';
  const PRICE_PER_COURSE = 998; // R$ 998 por minisérie

  function getCart() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
    catch { return []; }
  }

  function saveCart(cart) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    updateBadge(cart.length);
    renderDropdown(cart);
    document.dispatchEvent(new CustomEvent('cart:updated', { detail: { cart } }));
  }

  function addItem(item) {
    const cart = getCart();
    if (cart.find(i => String(i.id) === String(item.id))) return { added: false };
    item.price = PRICE_PER_COURSE;
    cart.push(item);
    saveCart(cart);
    return { added: true };
  }

  function removeItem(id) {
    saveCart(getCart().filter(i => String(i.id) !== String(id)));
  }

  function clearCart() { saveCart([]); }

  function formatPrice(val) {
    return 'R$ ' + val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function updateBadge(count) {
    const badge = document.getElementById('navbarCartCount');
    if (!badge) return;
    badge.textContent = count > 99 ? '99+' : count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  }

  function renderDropdown(cart) {
    const itemsEl   = document.getElementById('cartDropdownItems');
    const emptyEl   = document.getElementById('cartDropdownEmpty');
    const footerEl  = document.getElementById('cartDropdownFooter');
    const totalEl   = document.getElementById('cartDropdownTotal');
    const qtyEl     = document.getElementById('cartDropdownQty');
    if (!itemsEl) return;

    const qty = cart.length;
    qtyEl.textContent = qty === 0 ? '0 itens' : qty === 1 ? '1 item' : qty + ' itens';

    if (qty === 0) {
      itemsEl.innerHTML = '';
      emptyEl.style.display = 'flex';
      footerEl.style.display = 'none';
      return;
    }

    emptyEl.style.display = 'none';
    footerEl.style.display = 'block';
    totalEl.textContent = formatPrice(qty * PRICE_PER_COURSE);

    itemsEl.innerHTML = cart.map(item => `
      <div class="cart-dropdown-item" data-item-id="${item.id}">
        ${item.thumb
          ? `<img class="cart-item-thumb" src="${item.thumb}" alt="${item.title}" onerror="this.style.display='none'">`
          : `<div class="cart-item-thumb-placeholder">
               <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="6 4 20 12 6 20 6 4"/></svg>
             </div>`
        }
        <div class="cart-item-info">
          <div class="cart-item-name" title="${item.title}">${item.title}</div>
          <div class="cart-item-price">${formatPrice(PRICE_PER_COURSE)}</div>
        </div>
        <button class="cart-item-remove" data-remove-id="${item.id}" aria-label="Remover ${item.title}">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    `).join('');

    // Listeners de remoção
    itemsEl.querySelectorAll('.cart-item-remove').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const id = this.dataset.removeId;
        UnyCart.removeItem(id);
        // Restaura botão de adicionar na página
        const addBtn = document.querySelector(`.btn-add-to-cart[data-course-id="${id}"]`);
        if (addBtn) {
          addBtn.classList.remove('in-cart');
          addBtn.querySelector('.btn-cart-label').textContent = 'Adicionar';
        }
      });
    });
  }

  function init() {
    const cart = getCart();
    updateBadge(cart.length);
    renderDropdown(cart);
  }

  return { getCart, addItem, removeItem, clearCart, init, renderDropdown };
})();

/* ---- Toggle do dropdown ---- */
document.addEventListener('DOMContentLoaded', function () {
  UnyCart.init();

  const btn      = document.getElementById('navbarCartBtn');
  const dropdown = document.getElementById('cartDropdown');
  const wrap     = document.getElementById('navbarCartWrap');

  if (!btn || !dropdown) return;

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    const isOpen = dropdown.classList.contains('open');
    dropdown.classList.toggle('open', !isOpen);
    btn.classList.toggle('active', !isOpen);
    btn.setAttribute('aria-expanded', String(!isOpen));
    dropdown.setAttribute('aria-hidden', String(isOpen));

    // Re-renderiza ao abrir para garantir dados frescos
    if (!isOpen) UnyCart.renderDropdown(UnyCart.getCart());
  });

  // Fecha ao clicar fora
  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) {
      dropdown.classList.remove('open');
      btn.classList.remove('active');
      btn.setAttribute('aria-expanded', 'false');
    }
  });

  // Fecha com ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      dropdown.classList.remove('open');
      btn.classList.remove('active');
    }
  });

  /* ---- Botões "Adicionar ao carrinho" na página ---- */
  const cart = UnyCart.getCart();
  document.querySelectorAll('.btn-add-to-cart').forEach(addBtn => {
    if (cart.find(i => String(i.id) === String(addBtn.dataset.courseId))) {
      setInCart(addBtn);
    }
    addBtn.addEventListener('click', function () {
      const item = {
        id:    this.dataset.courseId,
        title: this.dataset.courseTitle,
        price: 998,
        thumb: this.dataset.courseThumb,
        slug:  this.dataset.courseSlug,
      };
      const result = UnyCart.addItem(item);
      if (result.added) {
        setInCart(this);
        showToast(item.title);
      } else {
        // Já no carrinho: abre dropdown
        dropdown.classList.add('open');
        btn.classList.add('active');
      }
    });
  });

  function setInCart(addBtn) {
    addBtn.classList.add('in-cart');
    const label = addBtn.querySelector('.btn-cart-label');
    if (label) label.textContent = 'No carrinho ✓';
  }

  /* ---- Toast ---- */
  let toastTimer;
  function showToast(title) {
    const toast = document.getElementById('cartToast');
    const sub   = document.getElementById('cartToastSub');
    if (!toast) return;
    sub.textContent = title;
    toast.classList.add('visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('visible'), 4500);
  }

  /* ---- Sync ao remover do carrinho ---- */
  document.addEventListener('cart:updated', function (e) {
    const ids = e.detail.cart.map(i => String(i.id));
    document.querySelectorAll('.btn-add-to-cart').forEach(addBtn => {
      if (!ids.includes(String(addBtn.dataset.courseId))) {
        addBtn.classList.remove('in-cart');
        const label = addBtn.querySelector('.btn-cart-label');
        if (label) label.textContent = 'Adicionar';
      }
    });
  });
});
</script>