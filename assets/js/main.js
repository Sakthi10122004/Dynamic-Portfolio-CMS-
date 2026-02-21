/**
 * Sakthi Portfolio — main.js
 * Lightweight animations & interactions (no dependencies)
 * GPU-accelerated where possible, requestAnimationFrame-based
 */

/* ============================================================
   UTILITY
   ============================================================ */
const $ = (s, ctx = document) => ctx.querySelector(s);
const $$ = (s, ctx = document) => [...ctx.querySelectorAll(s)];

/* ============================================================
   1. NAVBAR — Scroll shrink & transparency
   ============================================================ */
(function initNavbar() {
    const nav = $('#navbar');
    if (!nav) return;

    let ticking = false;
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(() => {
                nav.classList.toggle('scrolled', window.scrollY > 40);
                ticking = false;
            });
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
})();

/* ============================================================
   2. MOBILE MENU TOGGLE
   ============================================================ */
(function initMobileMenu() {
    const toggle = $('#menuToggle');
    const links = $('#navLinks');
    if (!toggle || !links) return;

    function close() {
        toggle.classList.remove('open');
        links.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', () => {
        const open = toggle.classList.toggle('open');
        links.classList.toggle('open', open);
        toggle.setAttribute('aria-expanded', String(open));
    });

    // Close on link click or outside click
    links.addEventListener('click', close);
    document.addEventListener('click', e => {
        if (!toggle.contains(e.target) && !links.contains(e.target)) close();
    });
})();

/* ============================================================
   3. ADMIN SIDEBAR — Mobile drawer
   ============================================================ */
(function initAdminSidebar() {
    const sidebar = $('#adminSidebar');
    const closeBtn = $('#sidebarClose');
    if (!sidebar) return;

    // Create overlay
    const overlay = document.createElement('div');
    overlay.style.cssText =
        'display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1999;backdrop-filter:blur(4px);transition:opacity 0.25s';
    document.body.appendChild(overlay);

    // Create hamburger trigger for mobile
    const menuBtn = document.createElement('button');
    menuBtn.innerHTML = '☰';
    menuBtn.setAttribute('aria-label', 'Open sidebar');
    menuBtn.style.cssText =
        'display:none;position:fixed;top:1rem;left:1rem;z-index:3000;background:var(--surface);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:0.5rem 0.75rem;font-size:1.25rem;cursor:pointer;';
    document.body.appendChild(menuBtn);

    function open() {
        sidebar.classList.add('open');
        overlay.style.display = 'block';
        requestAnimationFrame(() => { overlay.style.opacity = '1'; });
    }

    function close() {
        sidebar.classList.remove('open');
        overlay.style.opacity = '0';
        setTimeout(() => { overlay.style.display = 'none'; }, 250);
    }

    menuBtn.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', close);

    // Show/hide based on viewport
    function checkViewport() {
        const isMobile = window.innerWidth <= 768;
        menuBtn.style.display = isMobile ? 'block' : 'none';
    }

    window.addEventListener('resize', checkViewport, { passive: true });
    checkViewport();
})();

/* ============================================================
   4. SMOOTH SCROLL for anchor links
   ============================================================ */
document.addEventListener('click', e => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const target = document.getElementById(a.getAttribute('href').slice(1));
    if (!target) return;
    e.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

/* ============================================================
   5. INTERSECTION OBSERVER — Scroll reveal
   ============================================================ */
(function initReveal() {
    const items = $$('.reveal, .bento-card, .note-card, .project-card, .skills-card, .stat-card');

    if (!items.length || !('IntersectionObserver' in window)) {
        items.forEach(el => el.classList.add('visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    items.forEach((el, i) => {
        // Apply reveal class to enable the transition
        if (!el.classList.contains('reveal')) {
            el.classList.add('reveal');
            // Stagger siblings inside the same parent grid
            const siblings = el.parentElement
                ? [...el.parentElement.children].filter(c => c !== el && c.classList.contains(el.classList[0]))
                : [];
            el.style.transitionDelay = (Math.min(i % 4, 3) * 80) + 'ms';
        }
        observer.observe(el);
    });
})();

/* ============================================================
   6. ANIMATED STAT COUNTERS
   ============================================================ */
(function initCounters() {
    const stats = $$('.stat-number');
    if (!stats.length) return;

    function animateCounter(el) {
        const raw = el.textContent.trim();
        const suffix = raw.replace(/[\d.]/g, '');
        const end = parseFloat(raw) || 0;
        if (!end) return; // Skip if ∞ or non-numeric

        let start = 0;
        const duration = 1400;
        const startTime = performance.now();

        const step = (now) => {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * end);
            el.textContent = current + suffix;
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = raw; // Restore original text exactly
        };

        requestAnimationFrame(step);
    }

    if (!('IntersectionObserver' in window)) {
        stats.forEach(animateCounter);
        return;
    }

    const obs = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(el => obs.observe(el));
})();

/* ============================================================
   7. BUTTON RIPPLE EFFECT
   ============================================================ */
document.addEventListener('click', e => {
    const btn = e.target.closest('.btn');
    if (!btn) return;

    const circle = document.createElement('span');
    const diameter = Math.max(btn.clientWidth, btn.clientHeight);
    const rect = btn.getBoundingClientRect();

    circle.className = 'ripple';
    circle.style.cssText = `
    width: ${diameter}px;
    height: ${diameter}px;
    left: ${e.clientX - rect.left - diameter / 2}px;
    top: ${e.clientY - rect.top - diameter / 2}px;
  `;

    btn.appendChild(circle);
    setTimeout(() => circle.remove(), 700);
}, true);

/* ============================================================
   8. SKILL TAG STAGGER ANIMATION
   ============================================================ */
(function initSkillTags() {
    const tags = $$('.skill-tag');
    tags.forEach((tag, i) => {
        tag.style.animationDelay = (i * 40) + 'ms';
        tag.style.opacity = '0';
        tag.style.animation = `tagAppear 0.4s ease-out ${i * 40}ms both`;
    });

    if (!document.getElementById('tag-style')) {
        const style = document.createElement('style');
        style.id = 'tag-style';
        style.textContent = `
      @keyframes tagAppear {
        from { opacity: 0; transform: scale(0.8) translateY(4px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
      }
    `;
        document.head.appendChild(style);
    }
})();

/* ============================================================
   9. AUTO-DISMISS ALERTS
   ============================================================ */
(function initAlerts() {
    $$('.alert, .success-message, .error-message').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease, margin 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => {
                alert.style.maxHeight = '0';
                alert.style.margin = '0';
                alert.style.padding = '0';
                alert.style.overflow = 'hidden';
            }, 500);
        }, 4500);
    });
})();

/* ============================================================
   10. PAGE TRANSITION FADE-IN
   ============================================================ */
(function initPageFade() {
    document.body.classList.add('page-fade');
})();

/* ============================================================
   11. FILE INPUT PREVIEW (future-proof helper)
   ============================================================ */
(function initFilePreview() {
    $$('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function () {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            if (!preview || !this.files[0]) return;

            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; };
            reader.readAsDataURL(this.files[0]);
        });
    });
})();

/* ============================================================
   12. ACTIVE NAV LINK highlighting (based on scroll position)
   ============================================================ */
(function initActiveNav() {
    const sections = $$('section[id]');
    const navLinks = $$('.nav-links a[href*="#"]');
    if (!sections.length || !navLinks.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const id = entry.target.id;
            navLinks.forEach(a => {
                a.classList.toggle('active', a.getAttribute('href').includes('#' + id));
            });
        });
    }, { threshold: 0.4 });

    sections.forEach(s => observer.observe(s));
})();

/* ============================================================
   13. CSRF TOKEN for fetch() calls (if any future AJAX is added)
   ============================================================ */
window.csrfToken = document.querySelector('input[name="csrf_token"]')?.value ?? '';