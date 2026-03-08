/* ============================================================
   main.js — Portfolio Interactions & Animations
   Deep Space Neon — v2 (3D tilt, particles, no scroll bar)
   ============================================================ */

(function () {
    'use strict';

    // ── Page Loader ───────────────────────────────────────────
    const loader = document.getElementById('page-loader');
    if (loader) {
        window.addEventListener('load', () => {
            setTimeout(() => loader.classList.add('hidden'), 300);
        });
        setTimeout(() => loader && loader.classList.add('hidden'), 3000);
    }

    // ── Sticky Navbar ─────────────────────────────────────────
    const navbar = document.getElementById('navbar');
    function handleScroll() {
        if (navbar) {
            navbar.classList.toggle('scrolled', window.scrollY > 60);
        }
        revealElements();
        animateSkillBars();
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
                bar.style.width = '0%';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        bar.style.width = w + '%';
                    });
                });
            });
        }
    }

    // ── 3D Card Tilt Effect ───────────────────────────────────
    function init3DTilt() {
        document.querySelectorAll('.project-card, .skill-card, .about-card').forEach(card => {
            card.addEventListener('mousemove', function (e) {
                const rect = this.getBoundingClientRect();
                const cx = rect.left + rect.width / 2;
                const cy = rect.top + rect.height / 2;
                const dx = (e.clientX - cx) / (rect.width / 2);
                const dy = (e.clientY - cy) / (rect.height / 2);
                const rotX = -dy * 8;
                const rotY = dx * 8;
                this.style.transform = `perspective(800px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(8px)`;
                // Move the glow overlay
                const glow = this.querySelector('.card-glow');
                if (glow) {
                    glow.style.background = `radial-gradient(circle at ${(dx + 1) * 50}% ${(dy + 1) * 50}%, rgba(108,99,255,0.18) 0%, transparent 70%)`;
                }
            });
            card.addEventListener('mouseleave', function () {
                this.style.transform = '';
                const glow = this.querySelector('.card-glow');
                if (glow) glow.style.background = '';
            });
        });
    }
    init3DTilt();

    // ── Floating Particle System ──────────────────────────────
    function initParticles() {
        const canvas = document.getElementById('particle-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        const COLORS = ['#6c63ff', '#00d4ff', '#ff6b6b', '#39d98a'];
        const COUNT = window.innerWidth < 768 ? 30 : 60;
        const particles = Array.from({ length: COUNT }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: Math.random() * 2 + 0.5,
            vx: (Math.random() - 0.5) * 0.4,
            vy: (Math.random() - 0.5) * 0.4,
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
            alpha: Math.random() * 0.5 + 0.1,
        }));

        let mouse = { x: -9999, y: -9999 };
        window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });

        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => {
                // Mouse repulsion
                const dx = p.x - mouse.x;
                const dy = p.y - mouse.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 100) {
                    p.vx += dx / dist * 0.15;
                    p.vy += dy / dist * 0.15;
                }
                // Speed clamp
                const speed = Math.sqrt(p.vx * p.vx + p.vy * p.vy);
                if (speed > 1.2) { p.vx *= 0.9; p.vy *= 0.9; }

                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0) p.x = canvas.width;
                if (p.x > canvas.width) p.x = 0;
                if (p.y < 0) p.y = canvas.height;
                if (p.y > canvas.height) p.y = 0;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = p.alpha;
                ctx.fill();
            });
            // Draw connecting lines between nearby particles
            ctx.globalAlpha = 1;
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const d = Math.sqrt(dx * dx + dy * dy);
                    if (d < 120) {
                        ctx.beginPath();
                        ctx.strokeStyle = particles[i].color;
                        ctx.globalAlpha = (1 - d / 120) * 0.12;
                        ctx.lineWidth = 0.5;
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(draw);
        }
        draw();
    }
    initParticles();

    // ── Typewriter Effect ─────────────────────────────────────
    function initTypewriter() {
        const el = document.getElementById('typewriter');
        if (!el) return;
        const words = el.dataset.words ? JSON.parse(el.dataset.words) : [el.textContent];
        let wi = 0, ci = 0, deleting = false;
        function tick() {
            const word = words[wi];
            if (!deleting) {
                el.textContent = word.slice(0, ++ci);
                if (ci === word.length) { deleting = true; setTimeout(tick, 1800); return; }
            } else {
                el.textContent = word.slice(0, --ci);
                if (ci === 0) { deleting = false; wi = (wi + 1) % words.length; }
            }
            setTimeout(tick, deleting ? 55 : 90);
        }
        el.textContent = '';
        tick();
    }
    initTypewriter();

    // ── Smooth Scroll ─────────────────────────────────────────
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
        // Show toggle only on mobile
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
        pctRange.addEventListener('input', () => {
            pctShow.textContent = pctRange.value + '%';
        });
    }

    // ── Image preview for file inputs (admin) ────────────────
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

    // ── Parallax bg shapes ────────────────────────────────────
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                const y = window.scrollY;
                document.querySelectorAll('.bg-shape').forEach((s, i) => {
                    s.style.transform = `translateY(${y * (i + 1) * 0.06}px)`;
                });
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });

    // ── Counters (number roll-up) ─────────────────────────────
    function initCounters() {
        document.querySelectorAll('[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count);
            const duration = 1600;
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
    setTimeout(animateSkillBars, 600);
    setTimeout(revealElements, 100);

})();