/* ============================================================
   main.js — Portfolio Interactions & Animations
   ============================================================ */

(function () {
    'use strict';

    // ── Page Loader ───────────────────────────────────────────
    const loader = document.getElementById('page-loader');
    if (loader) {
        window.addEventListener('load', () => {
            setTimeout(() => loader.classList.add('hidden'), 300);
        });
        // Failsafe
        setTimeout(() => loader && loader.classList.add('hidden'), 3000);
    }

    // ── Scroll Progress Bar ───────────────────────────────────
    const progressBar = document.getElementById('scroll-progress');
    function updateProgress() {
        if (!progressBar) return;
        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        const scrollH = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        progressBar.style.width = scrollH > 0 ? (scrollTop / scrollH * 100) + '%' : '0%';
    }

    // ── Sticky Navbar ─────────────────────────────────────────
    const navbar = document.getElementById('navbar');
    function handleScroll() {
        updateProgress();
        if (navbar) {
            navbar.classList.toggle('scrolled', window.scrollY > 60);
        }
        revealElements();
        animateSkillBars();
    }
    window.addEventListener('scroll', handleScroll, { passive: true });

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

        // Close on nav link click
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.classList.remove('active');
            });
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target)) {
                navMenu.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.classList.remove('active');
            }
        });
    }

    // ── Hamburger animation ───────────────────────────────────
    const style = document.createElement('style');
    style.textContent = `
    .Nav-hamburger.active span:nth-child(1){transform:translateY(7px) rotate(45deg)}
    .Nav-hamburger.active span:nth-child(2){opacity:0;transform:scaleX(0)}
    .Nav-hamburger.active span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
  `;
    document.head.appendChild(style);

    // ── Active Nav Link Highlight ─────────────────────────────
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    function updateActiveNav() {
        let current = '';
        sections.forEach(s => {
            if (window.scrollY >= s.offsetTop - 120) current = s.id;
        });
        navLinks.forEach(l => {
            l.classList.toggle('active', l.getAttribute('href') === '#' + current);
        });
    }
    window.addEventListener('scroll', updateActiveNav, { passive: true });

    // ── Reveal on Scroll (Intersection Observer) ───────────────
    const revealItems = document.querySelectorAll('.reveal');
    function revealElements() {
        revealItems.forEach(el => {
            const rect = el.getBoundingClientRect();
            const delay = parseInt(el.dataset.delay || 0);
            if (rect.top < window.innerHeight - 80) {
                setTimeout(() => el.classList.add('visible'), delay);
            }
        });
    }

    // Use IntersectionObserver if available
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = parseInt(entry.target.dataset.delay || 0);
                    setTimeout(() => entry.target.classList.add('visible'), delay);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });
        revealItems.forEach(el => observer.observe(el));
    } else {
        // Fallback
        revealElements();
        window.addEventListener('scroll', revealElements, { passive: true });
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
                // Trigger reflow then animate
                bar.style.width = '0%';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        bar.style.width = w + '%';
                    });
                });
            });
        }
    }

    // ── Lightweight Parallax ──────────────────────────────────
    let ticking = false;
    function onScrollParallax() {
        if (!ticking) {
            requestAnimationFrame(() => {
                const y = window.scrollY;
                // Parallax on background shapes
                document.querySelectorAll('.bg-shape').forEach((s, i) => {
                    const speed = (i + 1) * 0.08;
                    s.style.transform = `translateY(${y * speed}px)`;
                });
                // Hero parallax
                const heroContent = document.querySelector('.hero-content');
                if (heroContent) heroContent.style.transform = `translateY(${y * 0.12}px)`;
                ticking = false;
            });
            ticking = true;
        }
    }
    window.addEventListener('scroll', onScrollParallax, { passive: true });

    // ── Smooth Scroll for anchor links ───────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href').slice(1);
            const target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                const offset = document.querySelector('.navbar')?.offsetHeight || 70;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        });
    });

    // ── Password toggle (admin login) ───────────────────────
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

    // ── Skill percentage live preview (admin) ───────────────
    const pctRange = document.getElementById('percentage-range');
    const pctShow = document.getElementById('percentage-display');
    if (pctRange && pctShow) {
        pctRange.addEventListener('input', () => {
            pctShow.textContent = pctRange.value + '%';
        });
    }

    // ── Image preview for file inputs (admin) ───────────────
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

    // ── Auto-dismiss flash messages ──────────────────────────
    document.querySelectorAll('.flash, .alert').forEach(flash => {
        setTimeout(() => {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 600);
        }, 5000);
    });

    // ── Initial triggers ──────────────────────────────────────
    updateProgress();
    updateActiveNav();
    setTimeout(animateSkillBars, 600);

})();