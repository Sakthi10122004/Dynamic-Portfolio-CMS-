/* ============================================================
   main.js — Premium Portfolio Animations v2
   No external dependencies. GPU-accelerated transforms only.
   ============================================================ */

(function () {
    'use strict';

    /* ── 1. Scroll Progress Bar ─────────────────────────────── */
    const progressBar = document.getElementById('scrollProgress');
    function updateProgress() {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        const pct = max > 0 ? Math.round((window.scrollY / max) * 1000) / 10 : 0;
        document.documentElement.style.setProperty('--scroll-pct', pct + '%');
    }

    /* ── 2. Navbar Scroll Shrink ─────────────────────────────── */
    const navbar = document.querySelector('.navbar');
    function onScroll() {
        updateProgress();
        if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 40);
        updateActiveNav();
    }
    let ticking = false;
    window.addEventListener('scroll', function () {
        if (!ticking) { requestAnimationFrame(function () { onScroll(); ticking = false; }); ticking = true; }
    }, { passive: true });

    /* ── 3. Mobile hamburger ─────────────────────────────────── */
    const toggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (toggle && navLinks) {
        toggle.addEventListener('click', function () {
            const open = navLinks.classList.toggle('open');
            toggle.classList.toggle('open', open);
            toggle.setAttribute('aria-expanded', open);
            document.body.style.overflow = open ? 'hidden' : '';
        });
        navLinks.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                navLinks.classList.remove('open');
                toggle.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
        document.addEventListener('click', function (e) {
            if (!navbar.contains(e.target) && navLinks.classList.contains('open')) {
                navLinks.classList.remove('open');
                toggle.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    }

    /* ── 4. Scrollspy — active nav link ─────────────────────── */
    const sections = document.querySelectorAll('section[id]');
    const navAnchors = document.querySelectorAll('.nav-links a[href^="#"]');
    function updateActiveNav() {
        let current = '';
        sections.forEach(function (sec) {
            if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
        });
        navAnchors.forEach(function (a) {
            a.classList.toggle('active', a.getAttribute('href') === '#' + current);
        });
    }

    /* ── 5. Smooth scroll ───────────────────────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* ── 6. IntersectionObserver scroll reveal ──────────────── */
    const revealObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                const el = entry.target;
                const delay = el.dataset.delay || 0;
                setTimeout(function () { el.classList.add('visible'); }, +delay);
                revealObs.unobserve(el);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    function addReveal(selector, cls, baseDelay) {
        document.querySelectorAll(selector).forEach(function (el, i) {
            el.classList.add(cls);
            if (!el.dataset.delay) el.dataset.delay = baseDelay + i * 80;
            revealObs.observe(el);
        });
    }

    addReveal('.bento-card', 'reveal', 0);
    addReveal('.timeline-item', 'reveal', 0);
    addReveal('.section-title', 'reveal', 0);

    /* ── 7. Animated stat counters ──────────────────────────── */
    function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

    function animateCounter(el) {
        const raw = el.textContent.trim();
        const suffix = raw.replace(/[\d.]/g, '');
        const target = parseFloat(raw);
        if (isNaN(target)) return;
        const duration = 1800;
        const start = performance.now();
        function step(now) {
            const elapsed = Math.min(now - start, duration);
            const progress = easeOutCubic(elapsed / duration);
            const value = target === Math.floor(target)
                ? Math.floor(progress * target)
                : (progress * target).toFixed(1);
            el.textContent = value + suffix;
            if (elapsed < duration) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    const counterObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.querySelectorAll('.stat-number').forEach(animateCounter);
                counterObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stats-card').forEach(function (c) { counterObs.observe(c); });

    /* ── 8. Button ripple ───────────────────────────────────── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn');
        if (!btn) return;
        const r = document.createElement('span');
        r.className = 'ripple';
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        r.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - rect.left - size / 2) + 'px;top:' + (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(r);
        r.addEventListener('animationend', function () { r.remove(); });
    });

    /* ── 9. Floating labels — contact form ──────────────────── */
    document.querySelectorAll('.field input, .field textarea').forEach(function (inp) {
        inp.placeholder = ' '; // CSS float relies on :not(:placeholder-shown)
    });

    /* ── 10. Admin sidebar drawer (mobile) ──────────────────── */
    const sidebar = document.querySelector('.admin-sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebarClose = document.querySelector('.sidebar-close');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    function openSidebar() { if (sidebar) sidebar.classList.add('open'); }
    function closeSidebar() { if (sidebar) sidebar.classList.remove('open'); }

    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

    /* ── 11. Cursor glow (desktop only) ─────────────────────── */
    if (window.matchMedia('(pointer: fine) and (min-width: 769px)').matches) {
        const glow = document.querySelector('.cursor-glow');
        if (glow) {
            let mx = window.innerWidth / 2, my = window.innerHeight / 2;
            let cx = mx, cy = my;
            document.addEventListener('mousemove', function (e) { mx = e.clientX; my = e.clientY; });
            function animGlow() {
                cx += (mx - cx) * 0.08;
                cy += (my - cy) * 0.08;
                glow.style.transform = 'translate(' + cx + 'px,' + cy + 'px) translate(-50%,-50%)';
                requestAnimationFrame(animGlow);
            }
            animGlow();
        }
    }

    /* ── 12. Auto-dismiss alerts ─────────────────────────────── */
    document.querySelectorAll('.alert, .success-message, .error-message').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s, max-height 0.5s, margin 0.5s, padding 0.5s';
            el.style.opacity = '0';
            el.style.maxHeight = '0';
            el.style.overflow = 'hidden';
            el.style.margin = '0';
            el.style.padding = '0';
            setTimeout(function () { el.remove(); }, 600);
        }, 4500);
    });

    /* ── 13. File input preview ─────────────────────────────── */
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (inp) {
        inp.addEventListener('change', function () {
            const preview = document.getElementById(this.dataset.preview);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    /* ── 14. Page fade-in ───────────────────────────────────── */
    document.body.classList.add('page-fade');

    /* ── 15. Password visibility toggle ─────────────────────── */
    document.querySelectorAll('.pw-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const inp = document.getElementById(this.dataset.target) || this.previousElementSibling || this.parentElement.querySelector('input');
            if (!inp) return;
            const isText = inp.type === 'text';
            inp.type = isText ? 'password' : 'text';
            const icon = this.querySelector('i');
            if (icon) icon.className = isText ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
        });
    });

    /* ── 16. CSRF token for AJAX (future use) ───────────────── */
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    window.csrfToken = metaCsrf ? metaCsrf.content : '';

})();