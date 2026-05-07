/* ================================================================
   Unyflex Digital — app.js
   Módulos: Navbar, Countdown, FAQ, Stats, AOS, Popup, Filtros
   ================================================================ */

(function () {
  'use strict';

  /* ── Navbar scroll effect ─────────────────────────────────── */
  const navbar = document.querySelector('.site-navbar');
  if (navbar) {
    const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ── Mobile menu ──────────────────────────────────────────── */
  const toggle = document.querySelector('.navbar-toggle');
  const mobileMenu = document.querySelector('.mobile-menu');
  if (toggle && mobileMenu) {
    toggle.addEventListener('click', () => {
      const open = mobileMenu.classList.toggle('open');
      toggle.classList.toggle('open', open);
      document.body.style.overflow = open ? 'hidden' : '';
    });
    mobileMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        toggle.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }

  /* ── Countdown Timer ──────────────────────────────────────── */
  function initCountdown() {
    const els = document.querySelectorAll('[data-countdown]');
    if (!els.length) return;

    // Deadline: 7 dias a partir de hoje (persiste em localStorage)
    const STORAGE_KEY = 'unyflex_promo_deadline';
    let deadline = localStorage.getItem(STORAGE_KEY);
    if (!deadline || Date.now() > Number(deadline)) {
      deadline = Date.now() + 7 * 24 * 60 * 60 * 1000;
      localStorage.setItem(STORAGE_KEY, deadline);
    }
    deadline = Number(deadline);

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
      const diff = Math.max(0, deadline - Date.now());
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);

      els.forEach(el => {
        const dd = el.querySelector('[data-cd-days]');
        const hh = el.querySelector('[data-cd-hours]');
        const mm = el.querySelector('[data-cd-mins]');
        const ss = el.querySelector('[data-cd-secs]');
        if (dd) dd.textContent = pad(d);
        if (hh) hh.textContent = pad(h);
        if (mm) mm.textContent = pad(m);
        if (ss) ss.textContent = pad(s);
      });

      if (diff > 0) setTimeout(tick, 1000);
    }

    tick();
  }

  /* ── FAQ Accordion ────────────────────────────────────────── */
  function initFAQ() {
    document.querySelectorAll('.faq-item').forEach(item => {
      const question = item.querySelector('.faq-question');
      if (!question) return;
      question.addEventListener('click', () => {
        const isOpen = item.classList.contains('open');
        // Fechar todos
        document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
        // Abrir o clicado se estava fechado
        if (!isOpen) item.classList.add('open');
      });
    });
  }

  /* ── Animate on Scroll ────────────────────────────────────── */
  function initAOS() {
    const items = document.querySelectorAll('.aos-fade');
    if (!items.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    items.forEach(el => observer.observe(el));
  }

  /* ── Animated Stats ───────────────────────────────────────── */
  function animateNumber(el) {
    const target = parseFloat(el.dataset.target);
    const suffix = el.dataset.suffix || '';
    const prefix = el.dataset.prefix || '';
    const duration = 1600;
    const start = performance.now();

    function update(now) {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out cubic
      const ease = 1 - Math.pow(1 - progress, 3);
      const value = target * ease;
      el.textContent = prefix + (Number.isInteger(target) ? Math.round(value) : value.toFixed(1)) + suffix;
      if (progress < 1) requestAnimationFrame(update);
    }

    requestAnimationFrame(update);
  }

  function initStats() {
    const els = document.querySelectorAll('[data-stat-number]');
    if (!els.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateNumber(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    els.forEach(el => observer.observe(el));
  }

  /* ── Popup de conversão ───────────────────────────────────── */
  function initPopup() {
    const overlay = document.querySelector('.popup-overlay');
    if (!overlay) return;

    const POPUP_KEY = 'unyflex_popup_shown';
    if (sessionStorage.getItem(POPUP_KEY)) return;

    // Mostrar após 8 segundos na página
    const timer = setTimeout(() => {
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
      sessionStorage.setItem(POPUP_KEY, '1');
    }, 8000);

    // Fechar
    overlay.querySelectorAll('.popup-close, [data-popup-close]').forEach(btn => {
      btn.addEventListener('click', () => {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      });
    });

    overlay.addEventListener('click', e => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });

    // Exit intent
    document.addEventListener('mouseleave', function onLeave(e) {
      if (e.clientY <= 0 && !sessionStorage.getItem(POPUP_KEY)) {
        clearTimeout(timer);
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        sessionStorage.setItem(POPUP_KEY, '1');
        document.removeEventListener('mouseleave', onLeave);
      }
    });
  }

  /* ── Course filter chips ──────────────────────────────────── */
  function initFilters() {
    document.querySelectorAll('.filter-chip-group').forEach(group => {
      group.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', () => {
          group.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
          chip.classList.add('active');

          const filter = chip.dataset.filter;
          document.querySelectorAll('[data-category]').forEach(card => {
            const show = filter === 'todos' || card.dataset.category === filter;
            card.closest('.course-col')?.style.setProperty('display', show ? '' : 'none');
            card.style.display = show ? '' : 'none';
          });
        });
      });
    });
  }

  /* ── Player tabs ──────────────────────────────────────────── */
  function initPlayerTabs() {
    document.querySelectorAll('.player-tab-group').forEach(group => {
      const panels = document.querySelectorAll('.player-tab-panel');
      group.querySelectorAll('.player-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          group.querySelectorAll('.player-tab-btn').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');

          const target = btn.dataset.tab;
          panels.forEach(p => {
            p.style.display = p.dataset.panel === target ? 'block' : 'none';
          });

          if (window.lucide) lucide.createIcons();
        });
      });
    });
  }

  /* ── Lesson list interactivity ────────────────────────────── */
  function initLessonList() {
    document.querySelectorAll('.player-lesson-item').forEach(item => {
      item.addEventListener('click', () => {
        document.querySelectorAll('.player-lesson-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        const num = item.querySelector('.player-lesson-num');
        if (num) {
          document.querySelectorAll('.player-lesson-num').forEach(n => n.classList.remove('active'));
          num.classList.add('active');
        }
      });
    });
  }

  /* ── Smooth scroll for anchor links ──────────────────────── */
  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  }

  /* ── Urgency bar dismiss ──────────────────────────────────── */
  function initUrgencyBar() {
    const bar = document.querySelector('.urgency-bar-dismiss');
    if (!bar) return;
    bar.addEventListener('click', () => {
      const urgencyBar = document.querySelector('.urgency-bar');
      if (urgencyBar) {
        urgencyBar.style.height = urgencyBar.offsetHeight + 'px';
        urgencyBar.style.overflow = 'hidden';
        urgencyBar.style.transition = 'height 0.3s, padding 0.3s, opacity 0.3s';
        requestAnimationFrame(() => {
          urgencyBar.style.height = '0';
          urgencyBar.style.padding = '0';
          urgencyBar.style.opacity = '0';
        });
        setTimeout(() => {
          urgencyBar.remove();
          document.body.classList.remove('has-urgency-bar');
          document.querySelector('.site-navbar')?.style.setProperty('top', '0');
        }, 350);
      }
    });
  }

  /* ── Lucide icons init ────────────────────────────────────── */
  function initIcons() {
    if (window.lucide) lucide.createIcons();
  }

  /* ── Bootstrap overrides (dark inputs, etc.) ──────────────── */
  function initBootstrapDark() {
    document.querySelectorAll('.form-control, .form-select').forEach(el => {
      el.style.setProperty('--bs-body-bg', 'var(--bg-1)');
    });
  }

  /* ── Init all ─────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', () => {
    initIcons();
    initCountdown();
    initFAQ();
    initAOS();
    initStats();
    initPopup();
    initFilters();
    initPlayerTabs();
    initLessonList();
    initSmoothScroll();
    initUrgencyBar();
    initBootstrapDark();
  });

})();
