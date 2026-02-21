<?php
/* ============================================================
   index.php — Portfolio Front Page (v3 Bento + Command Palette)
   ============================================================ */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$auth    = new Auth(Database::getInstance());
$profile = getProfile();
$skills  = getSkills();
$projects= getProjects();
$notes   = getNotes(3);

// Group skills by category
$skillsByCategory = [];
foreach ($skills as $s) {
    $skillsByCategory[$s['category']][] = $s;
}

// Handle contact form submission
$contactSuccess = false;
$contactError   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    verifyCsrf();
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!$name || !$email || !$message) {
        $contactError = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please enter a valid email address.';
    } else {
        saveContactMessage($name, $email, $message, $ip);
        $contactSuccess = true;
    }
}

// Tech icon map — emoji fallback (works without any CDN)
function techIcon(string $name): string {
    static $map = [
        'html/css' => '🌐', 'html'     => '🌐', 'css'   => '🎨',
        'javascript' => '🟨', 'js'     => '🟨', 'typescript' => '🔷',
        'react'    => '⚛️',  'vue'     => '💚', 'angular' => '🔺',
        'svelte'   => '🧡',  'nextjs'  => '▲',  'next.js' => '▲',
        'tailwind' => '💨',  'tailwind css' => '💨', 'bootstrap' => '🅱️',
        'php'      => '🐘',  'laravel' => '🔴', 'node.js' => '💚',
        'nodejs'   => '💚',  'python'  => '🐍', 'django'  => '🌿',
        'flask'    => '🍶',  'java'    => '☕',  'go'      => '🔵',
        'rust'     => '🦀',  'ruby'    => '💎', 'mysql'   => '🐬',
        'postgresql' => '🐘','mongodb' => '🍃', 'redis'   => '🔴',
        'sqlite'   => '🗃️',  'rest apis' => '🔌','graphql' => '⬡',
        'docker'   => '🐳',  'kubernetes' => '☸️','aws'    => '☁️',
        'gcp'      => '☁️',  'azure'   => '☁️', 'linux'   => '🐧',
        'git'      => '🌿',  'github'  => '🐙', 'gitlab'  => '🦊',
        'ci/cd'    => '🔄',  'nginx'   => '⚡',  'figma'   => '🎭',
        'photoshop' => '🖌️', 'vscode'  => '💙', 'vim'     => '📝',
        'bash'     => '💻',  'linux'   => '🐧',
    ];
    $key = strtolower(trim($name));
    return $map[$key] ?? '🔧';
}

$pageTitle       = null; // use SITE_NAME as title
$pageDescription = escape($profile['name']) . ' — ' . escape($profile['headline'])
                 . '. Full-stack developer portfolio.';
include __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════
     HERO
     ══════════════════════════════════════════════════════════ -->
<section id="home" class="hero">
    <div class="bento hero-grid">

        <!-- Main bio card — spans 8 cols -->
        <div class="col-8 card hero-main reveal">
            <div>
                <div class="hero-eyebrow">
                    <?php if (!empty($profile['avatar'])): ?>
                    <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>"
                         alt="<?php echo escape($profile['name']); ?>"
                         class="avatar-sm" width="56" height="56" loading="eager">
                    <?php else: ?>
                    <div class="avatar-sm" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                        🧑‍💻
                    </div>
                    <?php endif; ?>
                    <span class="status-pill">Open to work</span>
                </div>

                <h1 class="hero-name"><?php echo escape($profile['name']); ?></h1>
                <p class="hero-headline"><?php echo escape($profile['headline']); ?></p>
                <p class="hero-bio"><?php echo escape($profile['bio'] ?? 'Building digital experiences with clean code and thoughtful design.'); ?></p>
            </div>

            <div class="hero-cta">
                <a href="#projects" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M2 9h20M9 9V4M15 9V4M3 20h18a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1z"/>
                    </svg>
                    View Projects
                </a>
                <?php if (!empty($profile['resume'])): ?>
                <a href="<?php echo UPLOAD_URL . escape($profile['resume']); ?>"
                   class="btn btn-ghost" download target="_blank" rel="noopener">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Résumé
                </a>
                <?php endif; ?>
                <a href="#contact" class="btn btn-ghost">Let's talk →</a>
            </div>
        </div>

        <!-- Stats column — 4 cols, 2 rows -->
        <div class="col-4" style="display:flex;flex-direction:column;gap:1rem;">

            <div class="card hero-stat reveal" data-delay="80">
                <span class="hero-stat-num"><?php echo count($projects); ?>+</span>
                <span class="hero-stat-label">Projects built</span>
            </div>

            <div class="card hero-stat reveal" data-delay="160">
                <span class="hero-stat-num"><?php echo count($skills); ?>+</span>
                <span class="hero-stat-label">Technologies</span>
            </div>

            <!-- Social links card -->
            <div class="card hero-social reveal" data-delay="240">
                <?php if (!empty($profile['github'])): ?>
                <a href="<?php echo escape(sanitizeUrl($profile['github'])); ?>"
                   class="social-row" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.21 11.39.6.11.79-.26.79-.58v-2.23c-3.34.73-4.03-1.42-4.03-1.42-.55-1.39-1.33-1.76-1.33-1.76-1.09-.75.08-.73.08-.73 1.2.08 1.84 1.24 1.84 1.24 1.07 1.83 2.81 1.3 3.49 1 .11-.78.42-1.3.76-1.6-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 3-.4c1.02.005 2.05.14 3 .4 2.28-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.82 1.1.82 2.22v3.29c0 .32.19.7.8.58A12 12 0 0 0 24 12C24 5.37 18.63 0 12 0z"/>
                    </svg>
                    GitHub
                </a>
                <?php endif; ?>
                <?php if (!empty($profile['linkedin'])): ?>
                <a href="<?php echo escape(sanitizeUrl($profile['linkedin'])); ?>"
                   class="social-row" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.44-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43c-1.14 0-2.06-.93-2.06-2.06a2.06 2.06 0 1 1 2.06 2.06zm1.78 13.02H3.55V9h3.57v11.45zM22.23 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.46C23.2 24 24 23.23 24 22.27V1.73C24 .77 23.2 0 22.22 0h.01z"/>
                    </svg>
                    LinkedIn
                </a>
                <?php endif; ?>
                <?php if (!empty($profile['twitter'])): ?>
                <a href="<?php echo escape(sanitizeUrl($profile['twitter'])); ?>"
                   class="social-row" target="_blank" rel="noopener noreferrer" aria-label="X / Twitter">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24H16.17l-5.21-6.82-5.97 6.82H1.68l7.73-8.84L1.25 2.25H8.08l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.12z"/>
                    </svg>
                    Twitter / X
                </a>
                <?php endif; ?>
                <?php if (!empty($profile['email'])): ?>
                <a href="mailto:<?php echo escape($profile['email']); ?>"
                   class="social-row" aria-label="Email">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <?php echo escape($profile['email']); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     TECH STACK
     ══════════════════════════════════════════════════════════ -->
<section id="tech">
    <span class="section-label">Toolbox</span>
    <h2 style="margin-bottom:2rem">Tech Stack</h2>

    <?php
    $categories = [
        'frontend' => 'Frontend',
        'backend'  => 'Backend',
        'devops'   => 'DevOps & Tools',
    ];
    foreach ($categories as $cat => $catLabel):
        $catSkills = $skillsByCategory[$cat] ?? [];
        if (empty($catSkills)) continue;
    ?>
    <div class="card card--flat reveal" style="margin-bottom:1rem;padding:1.5rem 1.75rem;">
        <div class="tech-category-label"><?php echo $catLabel; ?></div>
        <div class="tech-grid">
            <?php foreach ($catSkills as $skill): ?>
            <div class="tech-icon" data-tooltip="<?php echo escape($skill['name']); ?>"
                 role="img" aria-label="<?php echo escape($skill['name']); ?>">
                <?php echo techIcon($skill['name']); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<!-- ══════════════════════════════════════════════════════════
     EXPERIENCE
     ══════════════════════════════════════════════════════════ -->
<section id="experience">
    <span class="section-label">Journey</span>
    <h2 style="margin-bottom:2rem">Experience</h2>

    <div class="bento exp-grid">
        <!-- Experience entries — add/edit here -->
        <article class="col-12 card exp-card reveal">
            <div class="exp-header">
                <div>
                    <div class="exp-role">Full-Stack Developer</div>
                    <div class="exp-org">Freelance</div>
                </div>
                <span class="exp-period">2022 — Present</span>
            </div>
            <ul class="exp-bullets">
                <li>Designed and shipped full-stack web applications using PHP, React, and MySQL for clients across multiple industries.</li>
                <li>Built reusable component libraries and design systems that cut frontend development time by 40%.</li>
                <li>Implemented REST APIs, authentication systems, and admin dashboards for 10+ client projects.</li>
            </ul>
            <div class="exp-tech">
                <?php foreach (['PHP','React','MySQL','Docker','REST APIs'] as $t): ?>
                <span class="badge badge-accent"><?php echo $t; ?></span>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="col-12 card exp-card reveal" data-delay="80">
            <div class="exp-header">
                <div>
                    <div class="exp-role">Web Development Intern</div>
                    <div class="exp-org">Tech Startup (Remote)</div>
                </div>
                <span class="exp-period">2021 — 2022</span>
            </div>
            <ul class="exp-bullets">
                <li>Contributed to building a SaaS product dashboard; wrote modular CSS and vanilla JS components.</li>
                <li>Developed automated CI/CD pipelines using GitHub Actions, reducing deployment errors by 60%.</li>
                <li>Collaborated with senior engineers in code reviews and agile sprint cycles.</li>
            </ul>
            <div class="exp-tech">
                <?php foreach (['JavaScript','Node.js','GitHub Actions','Linux'] as $t): ?>
                <span class="badge badge-neutral"><?php echo $t; ?></span>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="col-12 card exp-card reveal" data-delay="160">
            <div class="exp-header">
                <div>
                    <div class="exp-role">Computer Science (B.E.)</div>
                    <div class="exp-org">College / University</div>
                </div>
                <span class="exp-period">2020 — 2024</span>
            </div>
            <ul class="exp-bullets">
                <li>Specialised in software engineering, data structures, and database systems.</li>
                <li>Built award-winning capstone project — end-to-end web platform with 500 + users.</li>
            </ul>
            <div class="exp-tech">
                <?php foreach (['Python','Java','SQL','Algorithms'] as $t): ?>
                <span class="badge badge-green"><?php echo $t; ?></span>
                <?php endforeach; ?>
            </div>
        </article>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     PROJECTS
     ══════════════════════════════════════════════════════════ -->
<section id="projects">
    <span class="section-label">Portfolio</span>
    <h2 style="margin-bottom:2rem">Projects</h2>

    <?php if ($projects): ?>
    <div class="bento">
        <?php
        foreach ($projects as $i => $p):
            // Alternate span for bento feel: featured gets 8 cols, rest get 4 or 6
            if ($p['featured']) $span = 'col-8';
            elseif ($i % 3 === 0) $span = 'col-6';
            else $span = 'col-4';

            // Simple tag extraction from description (first 3 words after "built with")
            $descLower = strtolower($p['description'] ?? '');
            $tags = [];
            if (preg_match('/built (with|using)\s+([^.]+)/i', $descLower, $m)) {
                $raw = preg_split('/[,&]+/', $m[2]);
                foreach (array_slice($raw, 0, 4) as $t) {
                    $t = trim($t);
                    if ($t) $tags[] = ucfirst($t);
                }
            }
        ?>
        <div class="<?php echo $span; ?> card project-card reveal" data-delay="<?php echo $i * 55; ?>">
            <?php if ($p['featured']): ?>
            <div class="project-featured-tag">⭐ Featured</div>
            <?php endif; ?>
            <!-- Image / placeholder -->
            <?php if (!empty($p['image'])): ?>
            <img src="<?php echo UPLOAD_URL . escape($p['image']); ?>"
                 alt="<?php echo escape($p['title']); ?>"
                 class="project-img" loading="lazy">
            <?php else: ?>
            <div class="project-img-placeholder" aria-hidden="true">🚀</div>
            <?php endif; ?>

            <div class="project-body">
                <div class="project-name"><?php echo escape($p['title']); ?></div>
                <p class="project-desc"><?php echo escape(truncate($p['description'] ?? '', 120)); ?></p>
                <div class="project-footer">
                    <div class="project-tags">
                        <?php foreach ($tags as $tag): ?>
                        <span class="badge badge-accent"><?php echo escape($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="project-links">
                        <?php if (!empty($p['github_link'])): ?>
                        <a href="<?php echo escape(sanitizeUrl($p['github_link'])); ?>"
                           class="project-link-btn" target="_blank" rel="noopener noreferrer"
                           aria-label="GitHub repo for <?php echo escape($p['title']); ?>">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.21 11.39.6.11.79-.26.79-.58v-2.23c-3.34.73-4.03-1.42-4.03-1.42-.55-1.39-1.34-1.75-1.34-1.75-1.09-.74.08-.73.08-.73 1.2.08 1.84 1.24 1.84 1.24 1.07 1.83 2.8 1.3 3.49.99.11-.77.42-1.3.76-1.6-2.66-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.12-3.18 0 0 1-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.28-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.29c0 .32.19.7.8.58A12 12 0 0 0 24 12C24 5.37 18.63 0 12 0z"/>
                            </svg>
                            GitHub
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/project.php?id=<?php echo (int)$p['id']; ?>"
                           class="project-link-btn">Details →</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">⚡</div>
        <h3>Projects coming soon</h3>
        <p style="font-size:0.875rem">Check back — exciting work is on its way.</p>
    </div>
    <?php endif; ?>
</section>

<?php if ($notes): ?>
<!-- ══════════════════════════════════════════════════════════
     DIGITAL GARDEN (Notes)
     ══════════════════════════════════════════════════════════ -->
<section id="garden">
    <span class="section-label">Writing</span>
    <h2 style="margin-bottom:2rem">Digital Garden</h2>
    <div class="bento">
        <?php foreach ($notes as $j => $note): ?>
        <a href="<?php echo BASE_URL; ?>/note.php?id=<?php echo (int)$note['id']; ?>"
           class="col-4 card note-card reveal" data-delay="<?php echo $j * 80; ?>">
            <h3><?php echo escape($note['title']); ?></h3>
            <p class="note-excerpt"><?php echo escape(truncate($note['excerpt'] ?? '', 100)); ?></p>
            <div class="note-meta">
                <time datetime="<?php echo $note['created_at']; ?>">
                    <?php echo formatDate($note['created_at']); ?>
                </time>
                <span class="read-more">Read →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     CONTACT
     ══════════════════════════════════════════════════════════ -->
<section id="contact">
    <span class="section-label">Say Hello</span>
    <h2 style="margin-bottom:2rem">Get in Touch</h2>

    <div class="bento" style="align-items:start;">

        <!-- Info col -->
        <div class="col-4 reveal" style="display:flex;flex-direction:column;gap:1rem;">
            <div class="card card--flat" style="padding:1.75rem;">
                <div class="contact-info-title">Let's work<br>together.</div>
                <p class="contact-info-text">Open to freelance projects, collaborations, and full-time roles. If you've got something interesting — reach out.</p>
                <div class="contact-meta-row">
                    <?php if (!empty($profile['email'])): ?>
                    <div class="contact-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <a href="mailto:<?php echo escape($profile['email']); ?>">
                            <?php echo escape($profile['email']); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['github'])): ?>
                    <div class="contact-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.21 11.39.6.11.79-.26.79-.58v-2.23c-3.34.73-4.03-1.42-4.03-1.42-.55-1.39-1.34-1.75-1.34-1.75-1.09-.74.08-.73.08-.73 1.2.08 1.84 1.24 1.84 1.24 1.07 1.83 2.8 1.3 3.49.99.11-.77.42-1.3.76-1.6-2.66-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.12-3.18 0 0 1-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.28-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.29c0 .32.19.7.8.58A12 12 0 0 0 24 12C24 5.37 18.63 0 12 0z"/>
                        </svg>
                        <a href="<?php echo escape(sanitizeUrl($profile['github'])); ?>" target="_blank" rel="noopener">GitHub</a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['linkedin'])): ?>
                    <div class="contact-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.44-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.55V9h3.57v11.45zM22.23 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.46C23.2 24 24 23.23 24 22.27V1.73C24 .77 23.2 0 22.22 0h.01z"/></svg>
                        <a href="<?php echo escape(sanitizeUrl($profile['linkedin'])); ?>" target="_blank" rel="noopener">LinkedIn</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contact form col -->
        <div class="col-8 card contact-card reveal" data-delay="100">
            <?php if ($contactSuccess): ?>
            <div class="alert alert-success" role="alert">
                🎉 Message sent! I'll get back to you soon.
            </div>
            <?php endif; ?>
            <?php if ($contactError): ?>
            <div class="alert alert-error" role="alert">
                <?php echo escape($contactError); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="contact-form-wrap" novalidate>
                <?php echo csrfField(); ?>
                <div class="bento" style="grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="field">
                        <input type="text" id="contact-name" name="name"
                               placeholder=" " required autocomplete="name"
                               value="<?php echo !$contactSuccess ? escape($_POST['name'] ?? '') : ''; ?>">
                        <label for="contact-name">Your name</label>
                    </div>
                    <div class="field">
                        <input type="email" id="contact-email" name="email"
                               placeholder=" " required autocomplete="email"
                               value="<?php echo !$contactSuccess ? escape($_POST['email'] ?? '') : ''; ?>">
                        <label for="contact-email">Email address</label>
                    </div>
                </div>
                <div class="field">
                    <textarea id="contact-message" name="message"
                              placeholder=" " required rows="5"><?php echo !$contactSuccess ? escape($_POST['message'] ?? '') : ''; ?></textarea>
                    <label for="contact-message">Your message</label>
                </div>
                <p style="font-size:0.75rem;color:var(--text-muted);">
                    ⏱ Typically responds within 24 hours.
                </p>
                <button type="submit" name="contact_submit" class="btn btn-primary" style="align-self:flex-start;">
                    Send Message
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>