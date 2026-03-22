/* ============================================================
   main.js — Vibrant Gradient Portfolio v5
   Futuristic · Smooth · Premium Interactions
   ============================================================ */

(function () {
    'use strict';

    // ── Page Loader ───────────────────────────────────────────
    const loader = document.getElementById('page-loader');
    if (loader) {
        window.addEventListener('load', () => {
            setTimeout(() => loader.classList.add('hidden'), 350);
        });
        setTimeout(() => loader && loader.classList.add('hidden'), 2500);
    }

    // ── Sticky Navbar ─────────────────────────────────────────
    const navbar = document.getElementById('navbar');
    function handleScroll() {
        if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 50);
        animateSkillBars();
        handleBackToTop();
    }
    window.addEventListener('scroll', handleScroll, { passive: true });

    // ── Restore saved theme ───────────────────────────────────
    const savedTheme = localStorage.getItem('portfolio-theme');
    if (savedTheme) document.documentElement.setAttribute('data-theme', savedTheme);

    // ── Dark / Light Mode Toggle ──────────────────────────────
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('portfolio-theme', next);
        });
    }

    // ── Mobile Navigation ─────────────────────────────────────
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.querySelector('.navbar-nav');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            const open = navMenu.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', open);
            navToggle.classList.toggle('active', open);
        });
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.classList.remove('active');
            });
        });
        document.addEventListener('click', (e) => {
            if (navbar && !navbar.contains(e.target)) {
                navMenu.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.classList.remove('active');
            }
        });
    }

    // ── Hamburger animation + hide-mobile ─────────────────────
    const injectStyle = document.createElement('style');
    injectStyle.textContent = `
    .Nav-hamburger.active span:nth-child(1){transform:translateY(7px) rotate(45deg)}
    .Nav-hamburger.active span:nth-child(2){opacity:0;transform:scaleX(0)}
    .Nav-hamburger.active span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
    .hide-mobile{display:inline-flex}
    @media(max-width:768px){.hide-mobile{display:none!important}}
    `;
    document.head.appendChild(injectStyle);

    // ── Active Nav Link Highlight ─────────────────────────────
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    function updateActiveNav() {
        let current = '';
        sections.forEach(s => {
            if (window.scrollY >= s.offsetTop - 130) current = s.id;
        });
        navLinks.forEach(l => {
            l.classList.toggle('active', l.getAttribute('href') === '#' + current);
        });
    }
    window.addEventListener('scroll', updateActiveNav, { passive: true });

    // ── Reveal on Scroll ──────────────────────────────────────
    const revealItems = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = parseInt(entry.target.dataset.delay || 0);
                    setTimeout(() => entry.target.classList.add('visible'), delay);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.06, rootMargin: '0px 0px -30px 0px' });
        revealItems.forEach(el => observer.observe(el));
    } else {
        revealItems.forEach(el => el.classList.add('visible'));
    }

    // ── Skill Bar Animation ───────────────────────────────────
    let skillsBarsAnimated = false;
    function animateSkillBars() {
        if (skillsBarsAnimated) return;
        const skillsSection = document.getElementById('skills');
        if (!skillsSection) return;
        const rect = skillsSection.getBoundingClientRect();
        if (rect.top < window.innerHeight - 100) {
            skillsBarsAnimated = true;
            document.querySelectorAll('.skill-bar-fill').forEach(bar => {
                const w = bar.dataset.width || 80;
                bar.style.width = '0%';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => { bar.style.width = w + '%'; });
                });
            });
        }
    }

    // ── Smooth Scroll ─────────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href').slice(1);
            const target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                const offset = 90;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        });
    });

    // ── Back to Top ──────────────────────────────────────────
    const backToTop = document.getElementById('backToTop');
    function handleBackToTop() {
        if (!backToTop) return;
        backToTop.classList.toggle('visible', window.scrollY > 500);
    }
    if (backToTop) {
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ── Custom Cursor (desktop fine pointers only) ────────────
    const cursorDot = document.getElementById('cursorDot');
    const cursorRing = document.getElementById('cursorRing');

    if (cursorDot && cursorRing && window.matchMedia('(pointer: fine)').matches && window.innerWidth > 768) {
        let mouseX = 0, mouseY = 0, ringX = 0, ringY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            cursorDot.style.left = mouseX + 'px';
            cursorDot.style.top = mouseY + 'px';
        });

        function animateRing() {
            ringX += (mouseX - ringX) * 0.12;
            ringY += (mouseY - ringY) * 0.12;
            cursorRing.style.left = ringX + 'px';
            cursorRing.style.top = ringY + 'px';
            requestAnimationFrame(animateRing);
        }
        animateRing();

        const interactiveEls = 'a, button, .project-card, .skill-category-card, .hero-tech-pill, .about-tag, .social-icon-btn';
        document.querySelectorAll(interactiveEls).forEach(el => {
            el.addEventListener('mouseenter', () => cursorRing.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursorRing.classList.remove('hover'));
        });
    } else {
        if (cursorDot) cursorDot.style.display = 'none';
        if (cursorRing) cursorRing.style.display = 'none';
    }

    // ── 3D Tilt on Cards ─────────────────────────────────────
    if (window.innerWidth > 768) {
        document.querySelectorAll('.project-card, .skill-category-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `perspective(900px) rotateX(${-y * 5}deg) rotateY(${x * 5}deg) translateY(-6px)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.transition = 'all .5s cubic-bezier(0.4, 0, 0.2, 1)';
            });
            card.addEventListener('mouseenter', () => {
                card.style.transition = 'box-shadow .3s, border-color .3s';
            });
        });
    }

    // ── Floating Particles (hero only) ───────────────────────
    const hero = document.getElementById('hero');
    if (hero && window.innerWidth > 768) {
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            const size = Math.random() * 3 + 1;
            particle.style.cssText = `
                position:absolute;width:${size}px;height:${size}px;
                border-radius:50%;pointer-events:none;z-index:0;
                background:${['rgba(139,92,246,0.3)', 'rgba(236,72,153,0.25)', 'rgba(6,182,212,0.3)', 'rgba(59,130,246,0.25)'][Math.floor(Math.random() * 4)]};
                left:${Math.random() * 100}%;top:${Math.random() * 100}%;
                animation:floatParticle ${8 + Math.random() * 12}s ease-in-out infinite;
                animation-delay:${Math.random() * 5}s;
                box-shadow:0 0 ${size * 3}px currentColor;
            `;
            hero.appendChild(particle);
        }
        const pStyle = document.createElement('style');
        pStyle.textContent = `
        @keyframes floatParticle{
            0%,100%{transform:translate(0,0) scale(1);opacity:0.4}
            25%{transform:translate(${Math.random() > 0.5 ? '' : '-'}30px,-40px) scale(1.2);opacity:0.7}
            50%{transform:translate(${Math.random() > 0.5 ? '' : '-'}20px,30px) scale(0.8);opacity:0.3}
            75%{transform:translate(${Math.random() > 0.5 ? '' : '-'}40px,-20px) scale(1.1);opacity:0.6}
        }`;
        document.head.appendChild(pStyle);
    }

    // ── Password toggle (admin) ───────────────────────────────
    document.querySelectorAll('.pw-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.pw-wrap')?.querySelector('input');
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
                btn.innerHTML = input.type === 'password'
                    ? '<i class="fa-regular fa-eye"></i>'
                    : '<i class="fa-regular fa-eye-slash"></i>';
            }
        });
    });

    // ── Admin Sidebar Mobile Toggle ───────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    if (sidebarToggle && adminSidebar) {
        function handleSidebarResize() {
            sidebarToggle.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
        }
        handleSidebarResize();
        window.addEventListener('resize', handleSidebarResize);
        sidebarToggle.addEventListener('click', () => {
            const isOpen = adminSidebar.classList.toggle('sidebar-open');
            sidebarToggle.setAttribute('aria-expanded', isOpen);
            const icon = document.getElementById('sidebarToggleIcon');
            if (icon) icon.className = isOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        });
    }

    // ── Skill % preview (admin) ───────────────────────────────
    const pctRange = document.getElementById('percentage-range');
    const pctShow = document.getElementById('percentage-display');
    if (pctRange && pctShow) {
        pctRange.addEventListener('input', () => { pctShow.textContent = pctRange.value + '%'; });
    }

    // ── Image preview (admin) ─────────────────────────────────
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', () => {
            const previewId = input.dataset.preview;
            const preview = document.getElementById(previewId);
            if (preview && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });

    // ── Auto-dismiss flash messages ───────────────────────────
    document.querySelectorAll('.flash, .alert').forEach(flash => {
        setTimeout(() => {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 600);
        }, 5000);
    });

    // ── Counter animation ─────────────────────────────────────
    function initCounters() {
        document.querySelectorAll('[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count);
            const duration = 1800;
            const step = target / (duration / 16);
            let current = 0;
            const timer = setInterval(() => {
                current += step;
                if (current >= target) { current = target; clearInterval(timer); }
                el.textContent = Math.floor(current) + (el.dataset.suffix || '');
            }, 16);
        });
    }
    if ('IntersectionObserver' in window) {
        const counterObs = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) { initCounters(); counterObs.disconnect(); } });
        }, { threshold: 0.3 });
        document.querySelectorAll('[data-count]').forEach(el => counterObs.observe(el));
    }

    // ── Initial triggers ──────────────────────────────────────
    updateActiveNav();
    handleBackToTop();
    setTimeout(animateSkillBars, 600);

})();