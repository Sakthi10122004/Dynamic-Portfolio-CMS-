// ============================================================
// SAKTHI PORTFOLIO — 2050 Interactive Engine
// Quantum Particles · 3D NeuroTilt · Plasma Interactions
// ============================================================

// ---- Quantum Particle System ----
(function initParticles() {
    const canvas = document.createElement('canvas');
    canvas.id = 'particles-canvas';
    document.body.prepend(canvas);
    const ctx = canvas.getContext('2d');
    let w, h, particles = [], mouse = { x: -1000, y: -1000 };
    const COUNT = 55;
    const COLORS = [
        { r: 0, g: 229, b: 255 },   // cyan
        { r: 83, g: 109, b: 254 },   // blue
        { r: 124, g: 77, b: 255 },   // violet
        { r: 224, g: 64, b: 251 },   // magenta
        { r: 0, g: 230, b: 118 },    // green
    ];

    function resize() {
        w = canvas.width = window.innerWidth;
        h = canvas.height = window.innerHeight;
    }

    function spawn() {
        particles = [];
        for (let i = 0; i < COUNT; i++) {
            const c = COLORS[Math.floor(Math.random() * COLORS.length)];
            particles.push({
                x: Math.random() * w,
                y: Math.random() * h,
                vx: (Math.random() - 0.5) * 0.25,
                vy: (Math.random() - 0.5) * 0.25,
                size: Math.random() * 1.8 + 0.5,
                color: c,
                baseOpacity: Math.random() * 0.4 + 0.15,
                phase: Math.random() * Math.PI * 2
            });
        }
    }

    document.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    function draw() {
        ctx.clearRect(0, 0, w, h);

        particles.forEach(p => {
            // Mouse repulsion
            const dx = p.x - mouse.x;
            const dy = p.y - mouse.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 120 && dist > 0) {
                const force = (120 - dist) / 120 * 0.8;
                p.vx += (dx / dist) * force * 0.02;
                p.vy += (dy / dist) * force * 0.02;
            }

            // Damping
            p.vx *= 0.99;
            p.vy *= 0.99;

            p.x += p.vx;
            p.y += p.vy;
            p.phase += 0.015;

            // Wrap
            if (p.x < -20) p.x = w + 20;
            if (p.x > w + 20) p.x = -20;
            if (p.y < -20) p.y = h + 20;
            if (p.y > h + 20) p.y = -20;

            const osc = 0.5 + 0.5 * Math.sin(p.phase);
            const opacity = p.baseOpacity * (0.5 + 0.5 * osc);
            const sz = p.size * (0.8 + 0.4 * osc);
            const { r, g, b } = p.color;

            // Core
            ctx.beginPath();
            ctx.arc(p.x, p.y, sz, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${r},${g},${b},${opacity})`;
            ctx.fill();

            // Glow ring
            ctx.beginPath();
            ctx.arc(p.x, p.y, sz * 4, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${r},${g},${b},${opacity * 0.08})`;
            ctx.fill();
        });

        // Connection lines
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const d = Math.sqrt(dx * dx + dy * dy);
                if (d < 130) {
                    const alpha = (1 - d / 130) * 0.06;
                    const ci = particles[i].color;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(${ci.r},${ci.g},${ci.b},${alpha})`;
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(draw);
    }

    resize();
    spawn();
    draw();
    window.addEventListener('resize', () => { resize(); spawn(); });
})();

// ---- 3D NeuroTilt on Cards ----
function init3DTilt() {
    const cards = document.querySelectorAll('.bento-card');
    const MAX_TILT = 6;

    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const cx = rect.width / 2;
            const cy = rect.height / 2;
            const rx = ((y - cy) / cy) * -MAX_TILT;
            const ry = ((x - cx) / cx) * MAX_TILT;

            card.style.transform = `perspective(1000px) rotateX(${rx}deg) rotateY(${ry}deg) translateZ(8px) scale(1.01)`;

            // Dynamic spotlight
            card.style.backgroundImage = `radial-gradient(350px circle at ${x}px ${y}px, rgba(0,229,255,0.06), transparent 60%)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.backgroundImage = '';
        });
    });
}

// ---- Scroll Reveal ----
function initScrollReveal() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.04, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.bento-card, .section-title').forEach(el => observer.observe(el));
}

// ---- Smooth Anchor Scroll ----
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"], a[href*="/#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            let hash;
            const href = this.getAttribute('href');
            if (href.startsWith('#')) hash = href.substring(1);
            else if (href.includes('/#')) hash = href.split('#')[1];
            if (hash) {
                const target = document.getElementById(hash);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
}

// ---- Mobile Menu ----
function initMobileMenu() {
    const toggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    if (!toggle || !navLinks) return;

    toggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
        toggle.classList.toggle('active');
    });

    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('open');
            toggle.classList.remove('active');
        });
    });

    document.addEventListener('click', (e) => {
        if (navLinks.classList.contains('open') &&
            !e.target.closest('.nav-links') &&
            !e.target.closest('.menu-toggle')) {
            navLinks.classList.remove('open');
            toggle.classList.remove('active');
        }
    });
}

// ---- Navbar Scroll ----
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                navbar.classList.toggle('scrolled', window.scrollY > 20);
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
}

// ---- Auto-dismiss Messages ----
function initMessages() {
    document.querySelectorAll('.success-message').forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-15px) scale(0.98)';
            setTimeout(() => msg.remove(), 400);
        }, 4000);
    });
}

// ---- Delete Confirm ----
function initDeleteConfirm() {
    document.querySelectorAll('.action-btn.delete, a.delete').forEach(btn => {
        if (!btn.getAttribute('onclick')) {
            btn.addEventListener('click', function (e) {
                if (!confirm('Are you sure you want to delete this?')) e.preventDefault();
            });
        }
    });
}

// ---- Magnetic Hover on Primary Buttons ----
function initMagneticButtons() {
    document.querySelectorAll('.btn.primary').forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            btn.style.transform = `translate(${x * 0.12}px, ${y * 0.12}px) scale(1.03)`;
        });
        btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
    });
}

// ---- Animated Stat Counter ----
function initCountUp() {
    const statNumbers = document.querySelectorAll('.stat-number');
    if (!statNumbers.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const text = el.textContent.trim();
                const match = text.match(/(\d+)/);
                if (match) {
                    const target = parseInt(match[1]);
                    const suffix = text.replace(match[1], '');
                    const start = performance.now();
                    const dur = 1800;
                    function tick(now) {
                        const progress = Math.min((now - start) / dur, 1);
                        const eased = 1 - Math.pow(1 - progress, 4);
                        el.textContent = Math.round(eased * target) + suffix;
                        if (progress < 1) requestAnimationFrame(tick);
                    }
                    requestAnimationFrame(tick);
                }
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    statNumbers.forEach(el => observer.observe(el));
}

// ---- Admin Sidebar Toggle (mobile) ----
function initAdminSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    if (!sidebar || window.innerWidth > 768) return;

    const toggle = document.createElement('button');
    toggle.className = 'sidebar-toggle-btn';
    toggle.innerHTML = '☰';
    toggle.style.cssText = 'position:fixed;top:64px;left:10px;z-index:1000;background:rgba(10,10,22,0.9);color:#eaeaf4;border:1px solid rgba(0,229,255,0.15);padding:0.5rem 0.75rem;border-radius:10px;font-size:1.1rem;cursor:pointer;backdrop-filter:blur(12px);';
    document.body.appendChild(toggle);

    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}

// ---- Init Everything ----
document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
    initSmoothScroll();
    initMobileMenu();
    initNavbarScroll();
    initMessages();
    initDeleteConfirm();
    init3DTilt();
    initMagneticButtons();
    initCountUp();
    initAdminSidebar();
});