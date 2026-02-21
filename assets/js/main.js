/* ============================================================
   main.js v3 — Command Palette + Theme Toggle + Micro Interactions
   Vanilla JS, no libraries, production-ready
   ============================================================ */

(function () {
    'use strict';

    /* ── 0. Helpers ─────────────────────────────────────────── */
    const qs = (sel, root) => (root ?? document).querySelector(sel);
    const qsa = (sel, root) => [...(root ?? document).querySelectorAll(sel)];

    /* ── 1. Theme (dark/light) ─────────────────────────────── */
    const html = document.documentElement;
    const THEME_KEY = 'portfolio-theme';
    const themeToggle = qs('.theme-toggle');

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem(THEME_KEY, theme);
    }

    // Init: respect stored preference, then OS preference
    const storedTheme = localStorage.getItem(THEME_KEY);
    const osDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(storedTheme || (osDark ? 'dark' : 'light'));

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });
    }

    /* ── 2. Scroll progress + topbar shrink ────────────────── */
    const topbar = qs('.topbar');

    function onScroll() {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        const pct = max > 0 ? (window.scrollY / max * 100).toFixed(1) : 0;
        html.style.setProperty('--scroll-pct', pct + '%');
        if (topbar) topbar.classList.toggle('scrolled', window.scrollY > 40);
        updateScrollspy();
    }

    let rafPending = false;
    window.addEventListener('scroll', function () {
        if (!rafPending) {
            requestAnimationFrame(function () { onScroll(); rafPending = false; });
            rafPending = true;
        }
    }, { passive: true });

    /* ── 3. Smooth scroll helper ────────────────────────────── */
    function scrollToSection(id) {
        const el = qs(id.startsWith('#') ? id : '#' + id);
        if (!el) return;
        const top = el.getBoundingClientRect().top + window.scrollY - 70;
        window.scrollTo({ top, behavior: 'smooth' });
    }

    /* ── 4. Scrollspy ───────────────────────────────────────── */
    function updateScrollspy() {
        const sections = qsa('section[id]');
        let current = '';
        sections.forEach(function (sec) {
            if (window.scrollY >= sec.offsetTop - 130) current = sec.id;
        });
    }

    // Anchor links
    qsa('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            const target = qs(a.getAttribute('href'));
            if (target) { e.preventDefault(); scrollToSection(a.getAttribute('href')); }
        });
    });

    /* ── 5. Command Palette ─────────────────────────────────── */
    const backdrop = qs('#palette-backdrop');
    const input = qs('#palette-input');
    const list = qs('#palette-list');
    const cmdBtn = qs('.cmd-trigger');

    /* Nav items — extend freely */
    const NAV_ITEMS = [
        { icon: '🏠', label: 'Home', shortcut: 'G H', target: '#home' },
        { icon: '👤', label: 'About', shortcut: 'G A', target: '#about' },
        { icon: '⚙️', label: 'Tech Stack', shortcut: 'G T', target: '#tech' },
        { icon: '💼', label: 'Experience', shortcut: 'G E', target: '#experience' },
        { icon: '🚀', label: 'Projects', shortcut: 'G P', target: '#projects' },
        { icon: '✉️', label: 'Contact', shortcut: 'G C', target: '#contact' },
        { icon: '🌙', label: 'Toggle Theme', shortcut: 'G D', action: function () { if (themeToggle) themeToggle.click(); } },
    ];

    let paletteOpen = false;
    let activeIdx = -1;

    function renderPalette(query) {
        if (!list) return;
        const q = (query || '').toLowerCase().trim();
        const items = q
            ? NAV_ITEMS.filter(function (n) { return n.label.toLowerCase().includes(q); })
            : NAV_ITEMS;

        if (!items.length) {
            list.innerHTML = '<li class="palette-empty">No results for "' + query + '"</li>';
            activeIdx = -1;
            return;
        }

        list.innerHTML = items.map(function (item, i) {
            return '<li class="palette-item" data-idx="' + i + '" data-target="' + (item.target || '') + '" ' +
                (item.action ? 'data-action="true"' : '') +
                ' role="option">' +
                '<span class="palette-item-icon">' + item.icon + '</span>' +
                '<span class="palette-item-label">' + item.label + '</span>' +
                '<span class="palette-item-shortcut">' + item.shortcut + '</span>' +
                '</li>';
        }).join('');

        activeIdx = 0;
        updatePaletteActive();

        // Store filtered items for keyboard nav
        list._items = items;
    }

    function updatePaletteActive() {
        qsa('.palette-item', list).forEach(function (el, i) {
            el.classList.toggle('active', i === activeIdx);
        });
    }

    function selectPaletteItem() {
        const items = list._items || NAV_ITEMS;
        const item = items[activeIdx < 0 ? 0 : activeIdx];
        if (!item) return;
        closePalette();
        if (item.action) { setTimeout(item.action, 60); }
        if (item.target) { setTimeout(function () { scrollToSection(item.target); }, 60); }
    }

    function openPalette() {
        if (!backdrop) return;
        paletteOpen = true;
        backdrop.classList.add('open');
        if (input) { input.value = ''; input.focus(); }
        renderPalette('');
    }

    function closePalette() {
        if (!backdrop) return;
        paletteOpen = false;
        backdrop.classList.remove('open');
    }

    // Triggers
    if (cmdBtn) cmdBtn.addEventListener('click', openPalette);
    if (backdrop) backdrop.addEventListener('click', function (e) { if (e.target === backdrop) closePalette(); });

    if (input) {
        input.addEventListener('input', function () { renderPalette(input.value); activeIdx = 0; updatePaletteActive(); });
        input.addEventListener('keydown', function (e) {
            const items = qsa('.palette-item', list);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, items.length - 1);
                updatePaletteActive();
                items[activeIdx]?.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
                updatePaletteActive();
                items[activeIdx]?.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                selectPaletteItem();
            }
        });
    }

    // Click on list items
    if (list) {
        list.addEventListener('click', function (e) {
            const item = e.target.closest('.palette-item');
            if (!item) return;
            activeIdx = +item.dataset.idx;
            selectPaletteItem();
        });
    }

    // Global keyboard shortcut: Ctrl+K / Cmd+K
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            paletteOpen ? closePalette() : openPalette();
        }
        if (e.key === 'Escape' && paletteOpen) closePalette();
    });

    /* ── 6. IntersectionObserver scroll reveal ──────────────── */
    const revealObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            const delay = +(el.dataset.delay || 0);
            setTimeout(function () { el.classList.add('visible'); }, delay);
            revealObs.unobserve(el);
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    function addReveal(sel, delay) {
        qsa(sel).forEach(function (el, i) {
            el.classList.add('reveal');
            if (!el.dataset.delay) el.dataset.delay = delay + i * 70;
            revealObs.observe(el);
        });
    }

    addReveal('.card', 0);
    addReveal('.exp-card', 0);
    addReveal('.project-card', 60);
    qsa('.section-label').forEach(function (el) {
        el.classList.add('reveal');
        revealObs.observe(el);
    });

    /* ── 7. Stat counters ───────────────────────────────────── */
    function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

    function animateCount(el) {
        const raw = el.textContent.trim();
        const suffix = raw.replace(/[\d.]+/, '');
        const target = parseFloat(raw);
        if (isNaN(target)) return;
        const dur = 1600;
        const start = performance.now();
        (function step(now) {
            const p = Math.min((now - start) / dur, 1);
            const v = target === Math.floor(target)
                ? Math.floor(easeOut(p) * target)
                : (easeOut(p) * target).toFixed(1);
            el.textContent = v + suffix;
            if (p < 1) requestAnimationFrame(step);
        })(start);
    }

    new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            qsa('.hero-stat-num', entry.target).forEach(animateCount);
            this.unobserve(entry.target);
        }.bind(this));
    }, { threshold: 0.5 }).observe(qs('.hero') || document.body);

    /* ── 8. Button ripple ───────────────────────────────────── */
    document.addEventListener('pointerdown', function (e) {
        const btn = e.target.closest('.btn');
        if (!btn) return;
        const r = document.createElement('span');
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        r.className = 'ripple';
        r.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - rect.left - size / 2) + 'px;top:' + (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(r);
        r.addEventListener('animationend', function () { r.remove(); });
    });

    /* ── 9. Cursor glow (desktop only) ─────────────────────── */
    const glow = qs('.cursor-glow');
    if (glow && window.matchMedia('(pointer:fine) and (min-width:768px)').matches) {
        let mx = innerWidth / 2, my = innerHeight / 2, cx = mx, cy = my;
        document.addEventListener('mousemove', function (e) { mx = e.clientX; my = e.clientY; });
        (function animate() {
            cx += (mx - cx) * 0.07;
            cy += (my - cy) * 0.07;
            glow.style.transform = 'translate(' + cx + 'px,' + cy + 'px) translate(-50%,-50%)';
            requestAnimationFrame(animate);
        })();
    } else if (glow) {
        glow.style.display = 'none';
    }

    /* ── 10. Auto-dismiss alerts ────────────────────────────── */
    qsa('.alert, .success-message, .error-message').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s, max-height 0.5s, margin 0.5s, padding 0.5s';
            el.style.opacity = '0'; el.style.maxHeight = '0'; el.style.overflow = 'hidden';
            el.style.margin = '0'; el.style.padding = '0';
            setTimeout(function () { el.remove(); }, 600);
        }, 4500);
    });

    /* ── 11. Password visibility toggle ────────────────────── */
    qsa('.pw-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const inp = btn.previousElementSibling || btn.parentElement.querySelector('input');
            if (!inp) return;
            inp.type = inp.type === 'text' ? 'password' : 'text';
            const icon = btn.querySelector('i');
            if (icon) icon.className = inp.type === 'text' ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    });

    /* ── 12. Admin sidebar (mobile) ────────────────────────── */
    const sidebar = qs('.admin-sidebar');
    const sidebarClose = qs('.sidebar-close');
    const mobileToggle = qs('.sidebar-toggle-mobile');

    if (mobileToggle) mobileToggle.addEventListener('click', function () { sidebar?.classList.add('open'); });
    if (sidebarClose) sidebarClose.addEventListener('click', function () { sidebar?.classList.remove('open'); });

    /* ── 13. Page fade-in ───────────────────────────────────── */
    document.body.classList.add('page-fade');

    /* ── 14. Floating label placeholder fix ─────────────────── */
    qsa('.field input, .field textarea').forEach(function (inp) {
        inp.placeholder = ' ';
    });

})();