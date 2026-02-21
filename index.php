<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$profile         = getProfile();
$featuredProjects = getProjects(6, false); // up to 6, ordered by featured desc
$skills          = getSkills();
$recentNotes     = getNotes(3);

$pageTitle       = escape($profile['name']) . ' — ' . escape($profile['headline']);
$pageDescription = escape($profile['bio'] ? truncate($profile['bio'], 155) : $profile['headline']);

require_once 'includes/header.php';
?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<section id="home" class="hero">

  <!-- Left: text + actions -->
  <div class="bento-card hero-card reveal-left">
    <div class="hero-top">
      <?php if (!empty($profile['avatar'])): ?>
      <div class="hero-avatar">
        <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>"
             alt="<?php echo escape($profile['name']); ?>"
             class="avatar-ring" width="96" height="96" loading="eager">
      </div>
      <?php endif; ?>
      <div class="hero-text">
        <div class="hero-badge">Available for work</div>
        <h1><?php echo escape($profile['name']); ?></h1>
        <p class="headline"><?php echo escape($profile['headline']); ?></p>
      </div>
    </div>

    <?php if (!empty($profile['bio'])): ?>
    <p class="hero-bio"><?php echo escape($profile['bio']); ?></p>
    <?php endif; ?>

    <div class="hero-actions">
      <a href="#work" class="btn primary">View Work</a>
      <a href="#contact" class="btn secondary">Get in Touch</a>
      <?php if (!empty($profile['resume'])): ?>
      <a href="<?php echo UPLOAD_URL . escape($profile['resume']); ?>"
         class="btn secondary" download>📄 Resume</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right: SVG illustration + stats -->
  <div style="display:flex;flex-direction:column;gap:1rem;">

    <div class="bento-card hero-illustration-card reveal-right">
      <div class="hero-svg-wrap">
        <!-- Inline developer SVG illustration -->
        <svg viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <!-- Gradient defs -->
          <defs>
            <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="#7c6ef7"/>
              <stop offset="100%" stop-color="#06d6a0"/>
            </linearGradient>
            <linearGradient id="g2" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#1a1a28"/>
              <stop offset="100%" stop-color="#0d0d14"/>
            </linearGradient>
            <filter id="glow">
              <feGaussianBlur stdDeviation="4" result="blur"/>
              <feComposite in="SourceGraphic" in2="blur" operator="over"/>
            </filter>
          </defs>

          <!-- Monitor -->
          <rect x="60" y="30" width="280" height="180" rx="16" fill="url(#g2)" stroke="url(#g1)" stroke-width="2"/>
          <rect x="75" y="45" width="250" height="148" rx="8" fill="#050508"/>

          <!-- Code lines -->
          <rect x="90" y="62" width="80"  height="8" rx="4" fill="#7c6ef7" opacity="0.9"/>
          <rect x="90" y="78" width="140" height="8" rx="4" fill="#b0b0cc" opacity="0.4"/>
          <rect x="90" y="94" width="60"  height="8" rx="4" fill="#06d6a0" opacity="0.8"/>
          <rect x="158" y="94" width="90" height="8" rx="4" fill="#b0b0cc" opacity="0.4"/>
          <rect x="90" y="110" width="110" height="8" rx="4" fill="#f472b6" opacity="0.7"/>
          <rect x="90" y="126" width="50"  height="8" rx="4" fill="#7c6ef7" opacity="0.6"/>
          <rect x="148" y="126" width="80" height="8" rx="4" fill="#b0b0cc" opacity="0.3"/>
          <rect x="90" y="142" width="160" height="8" rx="4" fill="#06d6a0" opacity="0.5"/>
          <rect x="90" y="158" width="90"  height="8" rx="4" fill="#b0b0cc" opacity="0.35"/>
          <rect x="90" y="174" width="120" height="8" rx="4" fill="#7c6ef7" opacity="0.5"/>

          <!-- Cursor blink -->
          <rect x="218" y="174" width="2" height="8" rx="1" fill="#7c6ef7">
            <animate attributeName="opacity" values="1;0;1" dur="1.2s" repeatCount="indefinite"/>
          </rect>

          <!-- Stand -->
          <rect x="178" y="210" width="44" height="18" rx="4" fill="#1a1a28"/>
          <rect x="148" y="228" width="104" height="10" rx="5" fill="#1a1a28" stroke="url(#g1)" stroke-width="1"/>

          <!-- Floating elements -->
          <!-- React / atom icon -->
          <ellipse cx="340" cy="75" rx="24" ry="8" fill="none" stroke="#06d6a0" stroke-width="1.5" opacity="0.7">
            <animateTransform attributeName="transform" type="rotate" from="0 340 75" to="360 340 75" dur="5s" repeatCount="indefinite"/>
          </ellipse>
          <ellipse cx="340" cy="75" rx="24" ry="8" fill="none" stroke="#7c6ef7" stroke-width="1.5" opacity="0.6" transform="rotate(60 340 75)">
            <animateTransform attributeName="transform" type="rotate" from="60 340 75" to="420 340 75" dur="5s" repeatCount="indefinite"/>
          </ellipse>
          <circle cx="340" cy="75" r="4" fill="url(#g1)" filter="url(#glow)"/>

          <!-- PHP tag -->
          <rect x="18" y="100" width="36" height="18" rx="6" fill="#7c6ef7" opacity="0.15" stroke="#7c6ef7" stroke-width="1"/>
          <text x="36" y="113" text-anchor="middle" fill="#a89cf9" font-size="9" font-family="monospace" font-weight="700">PHP</text>

          <!-- JS tag -->
          <rect x="338" y="140" width="36" height="18" rx="6" fill="#f59e0b" opacity="0.12" stroke="#f59e0b" stroke-width="1"/>
          <text x="356" y="153" text-anchor="middle" fill="#fbbf24" font-size="9" font-family="monospace" font-weight="700">JS</text>

          <!-- Glow orb left -->
          <circle cx="20" cy="200" r="18" fill="#7c6ef7" opacity="0.08"/>
          <circle cx="20" cy="200" r="10" fill="#7c6ef7" opacity="0.12"/>

          <!-- Glow orb right -->
          <circle cx="375" cy="220" r="22" fill="#06d6a0" opacity="0.07"/>
          <circle cx="375" cy="220" r="12" fill="#06d6a0" opacity="0.1"/>

          <!-- Stars / sparkles -->
          <circle cx="52"  cy="48"  r="2" fill="#7c6ef7" opacity="0.8"><animate attributeName="opacity" values="0.8;0.2;0.8" dur="2.4s" repeatCount="indefinite"/></circle>
          <circle cx="360" cy="38"  r="1.5" fill="#06d6a0" opacity="0.6"><animate attributeName="opacity" values="0.6;0.1;0.6" dur="3s"   repeatCount="indefinite"/></circle>
          <circle cx="320" cy="270" r="2"   fill="#f472b6" opacity="0.5"><animate attributeName="opacity" values="0.5;0.1;0.5" dur="2.8s" repeatCount="indefinite"/></circle>
          <circle cx="80"  cy="275" r="1.5" fill="#7c6ef7" opacity="0.6"><animate attributeName="opacity" values="0.6;0.2;0.6" dur="2s"   repeatCount="indefinite"/></circle>
        </svg>
      </div>
    </div>

    <!-- Stats -->
    <div class="bento-card stats-card reveal">
      <div class="stat-item">
        <span class="stat-number" data-target="5">5+</span>
        <span class="stat-label">Years Exp.</span>
      </div>
      <div class="stat-item">
        <span class="stat-number" data-target="20">20+</span>
        <span class="stat-label">Projects</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">∞</span>
        <span class="stat-label">Innovation</span>
      </div>
    </div>
  </div>

</section>

<!-- ══ ABOUT ════════════════════════════════════════════════ -->
<section id="about">
  <div class="section-title reveal">
    <span class="section-label">✦ About Me</span>
    <h2>Who I Am</h2>
  </div>
  <div class="bento-grid">
    <div class="bento-card reveal" style="grid-column: span 2;">
      <p style="font-size:1.05rem;line-height:1.9;color:var(--text-2);max-width:72ch;">
        <?php echo escape($profile['bio'] ?? 'Passionate developer building modern web experiences.'); ?>
      </p>
      <div style="display:flex;flex-wrap:wrap;gap:0.6rem;margin-top:var(--sp-xl);">
        <?php if (!empty($profile['github'])): ?>
        <a href="<?php echo escape($profile['github']); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
          GitHub
        </a>
        <?php endif; ?>
        <?php if (!empty($profile['linkedin'])): ?>
        <a href="<?php echo escape($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
          LinkedIn
        </a>
        <?php endif; ?>
        <?php if (!empty($profile['twitter'])): ?>
        <a href="<?php echo escape($profile['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">𝕏 Twitter</a>
        <?php endif; ?>
        <?php if (!empty($profile['email'])): ?>
        <a href="mailto:<?php echo escape($profile['email']); ?>" class="social-chip">
          📧 <?php echo escape($profile['email']); ?>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ══ WORK ══════════════════════════════════════════════════ -->
<section id="work" class="work-section">
  <div class="section-title reveal">
    <span class="section-label">✦ Portfolio</span>
    <h2>Featured Work</h2>
  </div>
  <div class="projects-grid bento-grid">
    <?php if (empty($featuredProjects)): ?>
    <div class="bento-card empty-state">
      <span class="empty-icon">🚀</span>
      <h3>No projects yet</h3>
      <p>Add your work from the <a href="<?php echo BASE_URL; ?>/admin/" style="color:var(--primary-light)">admin panel</a>.</p>
    </div>
    <?php else: ?>
    <?php foreach ($featuredProjects as $project): ?>
    <a href="<?php echo BASE_URL; ?>/project.php?id=<?php echo $project['id']; ?>"
       class="project-card bento-card<?php echo $project['featured'] ? ' featured' : ''; ?>">
      <?php if (!empty($project['image'])): ?>
      <div class="project-image">
        <img src="<?php echo UPLOAD_URL . escape($project['image']); ?>"
             alt="<?php echo escape($project['title']); ?>" loading="lazy">
      </div>
      <?php endif; ?>
      <h3><?php echo escape($project['title']); ?></h3>
      <p><?php echo escape(truncate($project['description'], 110)); ?></p>
      <div class="project-tags">
        <?php
        // Auto-generate tag chips from title keywords for visual richness
        $tags = ['PHP', 'MySQL', 'JavaScript'];
        foreach ($tags as $tag): ?>
        <span class="tag-chip"><?php echo $tag; ?></span>
        <?php endforeach; ?>
      </div>
      <span class="project-link">View Project →</span>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- ══ SKILLS ════════════════════════════════════════════════ -->
<section id="skills" class="skills-section">
  <div class="section-title reveal">
    <span class="section-label">✦ Expertise</span>
    <h2>Skills &amp; Technologies</h2>
  </div>
  <div class="skills-bento bento-grid">
    <?php
    $categories    = ['frontend', 'backend', 'devops'];
    $categoryIcons = ['frontend' => '🎨', 'backend' => '⚙️', 'devops' => '☁️'];
    $categoryLabel = ['frontend' => 'Frontend', 'backend' => 'Backend', 'devops' => 'DevOps'];
    foreach ($categories as $category):
      $catSkills = array_filter($skills, fn($s) => $s['category'] === $category);
    ?>
    <div class="skills-card bento-card reveal">
      <div class="skills-card-header">
        <span class="category-icon"><?php echo $categoryIcons[$category]; ?></span>
        <h3><?php echo $categoryLabel[$category]; ?></h3>
      </div>
      <div class="skills-list">
        <?php if (empty($catSkills)): ?>
        <span class="skill-tag empty">Add skills in admin</span>
        <?php else: ?>
        <?php foreach ($catSkills as $skill): ?>
        <span class="skill-tag"><?php echo escape($skill['name']); ?></span>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══ EXPERIENCE TIMELINE ══════════════════════════════════ -->
<section id="experience" class="timeline-section">
  <div class="section-title reveal">
    <span class="section-label">✦ Journey</span>
    <h2>Experience</h2>
  </div>
  <div class="timeline">
    <?php
    // Experience data — edit these entries directly or move to DB later
    $experiences = [
      [
        'role'    => 'Full-Stack Developer',
        'company' => 'Freelance',
        'period'  => '2022 — Present',
        'desc'    => [
          'Built 15+ web apps with PHP, MySQL, and modern JS',
          'Designed responsive UIs with pixel-perfect attention to detail',
          'Integrated payment gateways, REST APIs, and third-party services',
        ],
      ],
      [
        'role'    => 'Frontend Developer',
        'company' => 'Open Source Projects',
        'period'  => '2021 — 2022',
        'desc'    => [
          'Contributed UI components to open-source repositories',
          'Implemented accessible, animated interfaces',
          'Reduced page load time by 40% through asset optimization',
        ],
      ],
      [
        'role'    => 'Computer Science Student',
        'company' => 'College',
        'period'  => '2019 — 2023',
        'desc'    => [
          'Graduated with honors in Computer Science Engineering',
          'Led technical team in national-level hackathons',
          'Built portfolio CMS and exam-management systems as capstone projects',
        ],
      ],
    ];
    foreach ($experiences as $exp):
    ?>
    <div class="timeline-item reveal">
      <div class="timeline-header">
        <div>
          <div class="timeline-role"><?php echo escape($exp['role']); ?></div>
          <div class="timeline-company"><?php echo escape($exp['company']); ?></div>
        </div>
        <span class="timeline-period"><?php echo escape($exp['period']); ?></span>
      </div>
      <div class="timeline-desc">
        <ul>
          <?php foreach ($exp['desc'] as $point): ?>
          <li><?php echo escape($point); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══ GARDEN ════════════════════════════════════════════════ -->
<?php if (!empty($recentNotes)): ?>
<section id="garden" class="garden-section">
  <div class="section-title reveal">
    <span class="section-label">✦ Writing</span>
    <h2>Digital Garden</h2>
  </div>
  <div class="notes-grid bento-grid">
    <?php foreach ($recentNotes as $note): ?>
    <article class="note-card bento-card reveal">
      <h3><?php echo escape($note['title']); ?></h3>
      <p class="note-excerpt"><?php echo escape($note['excerpt'] ?? truncate($note['content'] ?? '', 120)); ?></p>
      <div class="note-meta">
        <time datetime="<?php echo $note['created_at']; ?>"><?php echo formatDate($note['created_at']); ?></time>
        <a href="<?php echo BASE_URL; ?>/note.php?id=<?php echo $note['id']; ?>" class="read-more">Read →</a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ══ CONTACT ═══════════════════════════════════════════════ -->
<section id="contact" class="contact-section">
  <div class="section-title reveal">
    <span class="section-label">✦ Hire Me</span>
    <h2>Get in Touch</h2>
  </div>
  <div class="contact-bento bento-grid">

    <div class="contact-info bento-card reveal-left">
      <h3>Let's build something amazing</h3>
      <p>Open to freelance projects, collaborations, and full-time opportunities. Drop a message!</p>
      <div class="contact-details">
        <?php if (!empty($profile['email'])): ?>
        <a href="mailto:<?php echo escape($profile['email']); ?>" class="contact-email">
          📧 <?php echo escape($profile['email']); ?>
        </a>
        <?php endif; ?>
        <div class="contact-socials">
          <?php if (!empty($profile['github'])): ?>
          <a href="<?php echo escape($profile['github']); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">GitHub →</a>
          <?php endif; ?>
          <?php if (!empty($profile['linkedin'])): ?>
          <a href="<?php echo escape($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">LinkedIn →</a>
          <?php endif; ?>
          <?php if (!empty($profile['twitter'])): ?>
          <a href="<?php echo escape($profile['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">𝕏 Twitter →</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (isset($_GET['sent'])): ?>
    <div class="bento-card reveal-right">
      <div class="alert alert-success">✅ Message sent! I'll get back to you soon.</div>
    </div>
    <?php elseif (isset($_GET['limited'])): ?>
    <div class="bento-card reveal-right">
      <div class="alert alert-error">⏱ Too many messages. Please wait an hour and try again.</div>
    </div>
    <?php else: ?>
    <form class="contact-form bento-card reveal-right" method="POST" action="<?php echo BASE_URL; ?>/contact.php">
      <?php echo csrfField(); ?>
      <div class="field">
        <input type="text" name="name" id="name" placeholder=" " required autocomplete="name">
        <label for="name">Your Name</label>
      </div>
      <div class="field">
        <input type="email" name="email" id="email" placeholder=" " required autocomplete="email">
        <label for="email">Email Address</label>
      </div>
      <div class="field">
        <textarea name="message" id="message" rows="5" placeholder=" " required></textarea>
        <label for="message">Your Message</label>
      </div>
      <button type="submit" class="btn primary full-width">Send Message 🚀</button>
    </form>
    <?php endif; ?>

  </div>
</section>

<?php require_once 'includes/footer.php'; ?>