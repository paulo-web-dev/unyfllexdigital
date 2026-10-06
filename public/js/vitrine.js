/* ═══════════════════════════════════════════════════════════════════════════
   Vitrine pública da Assinatura Premium (/assinatura) — layouts/vitrine
   - Menu mobile, setas dos carrosséis
   - fbq('track','Contact') no clique dos CTAs [data-vt-contact] (nunca Lead)
   - Repasse de utm_* e fbclid para os links internos
   - Busca/ordenação da lista de categorias
   - Calculadora (preços e mensagens vêm do config via data-vt-calc)
   ═══════════════════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2 });
  function dinheiro(v) { return brl.format(v).replace(/ /g, ' '); }

  // ── Ícones ────────────────────────────────────────────────────────────────
  function icones() { if (window.lucide) window.lucide.createIcons(); }

  // ── Menu mobile ───────────────────────────────────────────────────────────
  function menu() {
    var botao = document.querySelector('[data-vt-menu]');
    var nav = document.getElementById('vt-menu');
    if (!botao || !nav) return;
    botao.addEventListener('click', function () {
      var aberto = nav.classList.toggle('aberto');
      botao.setAttribute('aria-expanded', aberto ? 'true' : 'false');
    });
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) { nav.classList.remove('aberto'); botao.setAttribute('aria-expanded', 'false'); }
    });
  }

  // ── Carrosséis ────────────────────────────────────────────────────────────
  function carrosseis() {
    document.addEventListener('click', function (e) {
      var seta = e.target.closest('[data-vt-scroll]');
      if (!seta) return;
      var trilho = seta.closest('.vt-faixa') && seta.closest('.vt-faixa').querySelector('[data-vt-trilho]');
      if (!trilho) return;
      trilho.scrollBy({ left: Number(seta.getAttribute('data-vt-scroll')) * trilho.clientWidth * 0.85, behavior: 'smooth' });
    });
  }

  // ── Meta Pixel: Contact no clique dos CTAs ────────────────────────────────
  function rastreioContato() {
    document.addEventListener('click', function (e) {
      var cta = e.target.closest('[data-vt-contact]');
      if (!cta || typeof window.fbq !== 'function') return;
      window.fbq('track', 'Contact', { content_name: cta.getAttribute('data-vt-contact') || 'Assinatura Premium' });
    });
  }

  // ── utm_* e fbclid nos links internos ─────────────────────────────────────
  var CHAVE_UTM = 'vt_utm';

  function parametrosCampanha() {
    var atuais = {};
    new URLSearchParams(window.location.search).forEach(function (valor, chave) {
      if (/^utm_/.test(chave) || chave === 'fbclid') atuais[chave] = valor;
    });
    try {
      if (Object.keys(atuais).length) sessionStorage.setItem(CHAVE_UTM, JSON.stringify(atuais));
      else atuais = JSON.parse(sessionStorage.getItem(CHAVE_UTM) || '{}');
    } catch (err) { /* sessionStorage indisponível: usa só a URL atual */ }
    return atuais;
  }

  function preservarUtm() {
    var params = parametrosCampanha();
    var chaves = Object.keys(params);
    if (!chaves.length) return;

    document.querySelectorAll('a[href]').forEach(function (a) {
      var url;
      try { url = new URL(a.getAttribute('href'), window.location.href); } catch (err) { return; }
      if (url.origin !== window.location.origin || url.protocol.indexOf('http') !== 0) return;
      chaves.forEach(function (k) { if (!url.searchParams.has(k)) url.searchParams.set(k, params[k]); });
      a.setAttribute('href', url.pathname + url.search + url.hash);
    });

    // Formulários GET internos (busca da categoria) também levam os parâmetros.
    document.querySelectorAll('form[method="get"]').forEach(function (form) {
      chaves.forEach(function (k) {
        if (form.querySelector('input[name="' + k + '"]')) return;
        var campo = document.createElement('input');
        campo.type = 'hidden'; campo.name = k; campo.value = params[k];
        form.appendChild(campo);
      });
    });
  }

  // ── Lista de categorias: busca e ordenação no cliente ────────────────────
  function listaCategorias() {
    var raiz = document.querySelector('[data-vt-lista-cat]');
    if (!raiz) return;
    var busca = raiz.querySelector('[data-vt-cat-busca]');
    var ordem = raiz.querySelector('[data-vt-cat-ordem]');
    var lista = raiz.querySelector('[data-vt-cat-itens]');
    var vazio = raiz.querySelector('[data-vt-cat-vazio]');
    var itens = Array.prototype.slice.call(lista.children);

    function normalizar(s) { return (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); }

    function aplicar() {
      var termo = normalizar(busca.value.trim());
      var modo = ordem.value;
      var visiveis = 0;

      itens.sort(function (a, b) {
        if (modo === 'qtd') return Number(b.dataset.qtd) - Number(a.dataset.qtd);
        var r = a.dataset.nome.localeCompare(b.dataset.nome, 'pt-BR', { sensitivity: 'base' });
        return modo === 'za' ? -r : r;
      }).forEach(function (li) {
        var ok = !termo || normalizar(li.dataset.nome).indexOf(termo) !== -1;
        li.hidden = !ok;
        if (ok) visiveis++;
        lista.appendChild(li);
      });
      vazio.hidden = visiveis > 0;
    }

    busca.addEventListener('input', aplicar);
    ordem.addEventListener('change', aplicar);
    aplicar();
  }

  // ── Calculadora ───────────────────────────────────────────────────────────
  /**
   * Combinação mais barata de pacotes que cubra AO MENOS `n` servidores
   * (pode sobrar licença, ex.: 9 servidores => Corporativo 10).
   * Programação dinâmica sobre os pacotes do config.
   */
  function melhorCombinacao(n, pacotes) {
    var custo = [0];
    var escolha = [null];
    for (var i = 1; i <= n; i++) {
      custo[i] = Infinity;
      for (var p = 0; p < pacotes.length; p++) {
        var anterior = Math.max(0, i - pacotes[p].usuarios);
        var c = custo[anterior] + pacotes[p].preco;
        if (c < custo[i] - 0.001) { custo[i] = c; escolha[i] = p; }
      }
    }
    var qtd = pacotes.map(function () { return 0; });
    var licencas = 0;
    for (var r = n; r > 0;) {
      var pk = escolha[r];
      qtd[pk]++;
      licencas += pacotes[pk].usuarios;
      r = Math.max(0, r - pacotes[pk].usuarios);
    }
    return { custo: custo[n], qtd: qtd, licencas: licencas };
  }

  function descreverCombinacao(comb, pacotes) {
    var partes = [];
    // Maior pacote primeiro.
    pacotes.map(function (p, i) { return { p: p, q: comb.qtd[i] }; })
      .sort(function (a, b) { return b.p.usuarios - a.p.usuarios; })
      .forEach(function (x) { if (x.q) partes.push((x.q > 1 ? x.q + '× ' : '') + x.p.nome); });
    return partes.join(' + ');
  }

  function calculadora() {
    var raiz = document.querySelector('[data-vt-calc]');
    if (!raiz) return;

    var cfg;
    try { cfg = JSON.parse(raiz.getAttribute('data-vt-calc')); } catch (err) { return; }
    var pacotes = cfg.pacotes || [];
    if (!pacotes.length) return;

    var maiorPacote = pacotes.reduce(function (m, p) { return Math.max(m, p.usuarios); }, 1);
    var inServ = raiz.querySelector('input[name="servidores"]');
    var inCursos = raiz.querySelector('input[name="cursos"]');
    function out(nome) { return raiz.querySelector('[data-out="' + nome + '"]'); }

    function inteiro(input) {
      var min = Number(input.min) || 1, max = Number(input.max) || 999;
      var v = Math.round(Number(input.value));
      if (!isFinite(v) || v < min) v = min;
      if (v > max) v = max;
      return v;
    }

    function mensagem(modelo, vars) {
      return Object.keys(vars).reduce(function (t, k) { return t.split(':' + k).join(vars[k]); }, modelo || '');
    }

    function calcular() {
      var n = inteiro(inServ);
      var cursos = inteiro(inCursos);

      var avulsa = n * cursos * cfg.avulsa;
      var comb = melhorCombinacao(n, pacotes);
      var economia = avulsa - comb.custo;
      var porServidor = comb.custo / n;

      out('avulsa').textContent = dinheiro(avulsa);
      out('avulsa-det').textContent = n + (n === 1 ? ' servidor' : ' servidores') + ' × ' + cursos + (cursos === 1 ? ' curso' : ' cursos') + ' × ' + dinheiro(cfg.avulsa);
      out('assinatura').textContent = dinheiro(comb.custo);

      var det = descreverCombinacao(comb, pacotes) + ' · ' + dinheiro(porServidor) + ' por servidor';
      if (comb.licencas > n) {
        var sobra = comb.licencas - n;
        det += ' · inclui ' + sobra + (sobra === 1 ? ' licença extra' : ' licenças extras');
      }
      out('combinacao').textContent = det;

      var caixaEco = out('economia').parentNode;
      if (economia >= 0) {
        out('economia-rotulo').textContent = 'Economia no ano';
        out('economia').textContent = dinheiro(economia);
        caixaEco.classList.remove('negativa');
      } else {
        out('economia-rotulo').textContent = 'A avulsa sai mais barata em';
        out('economia').textContent = dinheiro(-economia);
        caixaEco.classList.add('negativa');
      }

      // Avisos: break-even, licença sobrando ou upsell para o próximo pacote.
      var avisos = [];
      if (economia < 0) {
        var minimo = Math.ceil(comb.custo / (n * cfg.avulsa));
        avisos.push('A assinatura compensa a partir de ' + minimo + ' cursos por servidor no ano, e dá acesso ao catálogo inteiro.');
      }
      if (comb.licencas > n) {
        var extra = comb.licencas - n;
        avisos.push('O plano mais barato para ' + n + ' servidores já inclui ' + extra + (extra === 1 ? ' licença' : ' licenças') +
          ' a mais: dá para incluir mais ' + extra + (extra === 1 ? ' servidor' : ' servidores') + ' sem custo adicional.');
      } else {
        for (var d = 1; d <= 2; d++) {
          var prox = melhorCombinacao(n + d, pacotes);
          var porProx = prox.custo / (n + d);
          if (porProx < porServidor - 0.5) {
            avisos.push('Com mais ' + d + (d === 1 ? ' servidor' : ' servidores') + ', o valor cai para ' + dinheiro(porProx) +
              ' por servidor (' + descreverCombinacao(prox, pacotes) + '), contra ' + dinheiro(porServidor) + ' hoje.');
            break;
          }
        }
      }
      var upsell = out('upsell');
      upsell.textContent = avisos.join(' ');
      upsell.hidden = !avisos.length;

      // Acima do maior pacote: proposta sob medida.
      var sob = out('sob-medida');
      var acima = n > maiorPacote;
      sob.hidden = !acima;
      sob.textContent = acima ? 'Acima de ' + maiorPacote + ' servidores, proposta sob medida: fale com um consultor.' : '';

      var texto = acima
        ? mensagem(cfg.mensagens.sob_medida, { servidores: n })
        : mensagem(cfg.mensagens.calculadora, { servidores: n, cursos: cursos });
      var cta = out('cta');
      cta.href = cfg.whatsapp + '?text=' + encodeURIComponent(texto);
      cta.setAttribute('data-vt-contact', acima ? 'Calculadora: sob medida' : 'Calculadora: ' + descreverCombinacao(comb, pacotes));
    }

    raiz.addEventListener('click', function (e) {
      var b = e.target.closest('[data-passo]');
      if (!b) return;
      var input = raiz.querySelector('input[name="' + b.getAttribute('data-alvo') + '"]');
      input.value = inteiro(input) + Number(b.getAttribute('data-passo'));
      input.value = inteiro(input);
      calcular();
    });
    [inServ, inCursos].forEach(function (input) {
      input.addEventListener('input', function () { if (input.value !== '') calcular(); });
      input.addEventListener('blur', function () { input.value = inteiro(input); calcular(); });
    });

    calcular();
  }

  // ── "Por dentro da plataforma": miniaturas + lightbox ─────────────────────
  var SVG = {
    fechar: '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    ant: '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    prox: '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
  };

  function plataforma() {
    var raiz = document.querySelector('[data-vt-plat]');
    if (!raiz) return;

    var telas = Array.prototype.slice.call(raiz.querySelectorAll('.vt-plat-tela'));
    var minis = Array.prototype.slice.call(raiz.querySelectorAll('[data-vt-tela]'));
    var imagens = telas.map(function (t) {
      var img = t.querySelector('img');
      return { src: img.getAttribute('src'), legenda: img.getAttribute('alt') };
    });

    function ativar(i) {
      telas.forEach(function (t, k) { t.classList.toggle('ativa', k === i); });
      minis.forEach(function (m, k) {
        m.classList.toggle('ativa', k === i);
        m.setAttribute('aria-selected', k === i ? 'true' : 'false');
      });
    }

    minis.forEach(function (m) {
      m.addEventListener('click', function () { ativar(Number(m.getAttribute('data-vt-tela'))); });
    });

    // Lightbox: criado sob demanda, um só por página.
    var lb, lbImg, lbLegenda, atual = 0, focoAnterior = null;

    function montar() {
      lb = document.createElement('div');
      lb.className = 'vt-lb';
      lb.hidden = true;
      lb.setAttribute('role', 'dialog');
      lb.setAttribute('aria-modal', 'true');
      lb.setAttribute('aria-label', 'Telas da plataforma');
      lb.innerHTML =
        '<button type="button" class="vt-lb-btn vt-lb-fechar" aria-label="Fechar">' + SVG.fechar + '</button>' +
        (imagens.length > 1
          ? '<button type="button" class="vt-lb-btn vt-lb-ant" aria-label="Imagem anterior">' + SVG.ant + '</button>' +
            '<button type="button" class="vt-lb-btn vt-lb-prox" aria-label="Próxima imagem">' + SVG.prox + '</button>'
          : '') +
        '<img alt=""><p class="vt-lb-legenda"></p>';
      document.body.appendChild(lb);
      lbImg = lb.querySelector('img');
      lbLegenda = lb.querySelector('.vt-lb-legenda');

      lb.addEventListener('click', function (e) {
        if (e.target.closest('.vt-lb-fechar') || e.target === lb) return fechar();
        if (e.target.closest('.vt-lb-ant')) return mostrar(atual - 1);
        if (e.target.closest('.vt-lb-prox')) return mostrar(atual + 1);
      });
      document.addEventListener('keydown', function (e) {
        if (lb.hidden) return;
        if (e.key === 'Escape') fechar();
        else if (e.key === 'ArrowLeft') mostrar(atual - 1);
        else if (e.key === 'ArrowRight') mostrar(atual + 1);
      });
    }

    function mostrar(i) {
      atual = (i + imagens.length) % imagens.length;
      lbImg.src = imagens[atual].src;
      lbImg.alt = imagens[atual].legenda;
      lbLegenda.textContent = imagens[atual].legenda + (imagens.length > 1 ? '  ·  ' + (atual + 1) + '/' + imagens.length : '');
      ativar(atual);
    }

    function abrir(i) {
      if (!lb) montar();
      focoAnterior = document.activeElement;
      mostrar(i);
      lb.hidden = false;
      document.body.classList.add('vt-lb-aberto');
      lb.querySelector('.vt-lb-fechar').focus();
    }

    function fechar() {
      lb.hidden = true;
      document.body.classList.remove('vt-lb-aberto');
      if (focoAnterior) focoAnterior.focus();
    }

    raiz.addEventListener('click', function (e) {
      var z = e.target.closest('[data-vt-zoom]');
      if (z) abrir(Number(z.getAttribute('data-vt-zoom')));
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    plataforma();
    icones();
    menu();
    carrosseis();
    rastreioContato();
    preservarUtm();
    listaCategorias();
    calculadora();
  });
})();
