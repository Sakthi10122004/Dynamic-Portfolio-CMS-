<?php
/* ============================================================
   index.php — Glassmorphism Portfolio (Dynamic CMS)
   ============================================================ */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth(Database::getInstance());
$profile = getProfile();
$hero = getHero();
$about = getAbout();
$skills = getSkills();
$projects = getProjects();
$socials = getSocialLinks();
$notes = getNotes(3);

// Group skills by category
$skillsByCategory = [];
foreach ($skills as $s) {
  $skillsByCategory[$s['category']][] = $s;
}

// Contact form
$contactSuccess = false;
$contactError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
  verifyCsrf();
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $message = trim($_POST['message'] ?? '');
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  if (!$name || !$email || !$message) {
    $contactError = 'Please fill in all fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $contactError = 'Please enter a valid email address.';
  } else {
    saveContactMessage($name, $email, $message, $ip);
    $contactSuccess = true;
  }
}

$pageTitle = null;
$pageDescription = escape($profile['name']) . ' — ' . escape($hero['title']);
include __DIR__ . '/includes/header.php';

// Category display config
$catConfig = [
  'frontend' => ['label' => 'Frontend', 'icon' => 'fa-solid fa-palette'],
  'backend' => ['label' => 'Backend', 'icon' => 'fa-solid fa-server'],
  'devops' => ['label' => 'DevOps', 'icon' => 'fa-brands fa-docker'],
  'other' => ['label' => 'Other', 'icon' => 'fa-solid fa-wrench'],
];
?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<section id="hero">
  <div class="container">
    <div class="hero-grid">

      <!-- Left: Content -->
      <div class="hero-content reveal">
        <div class="hero-badge">
          <i class="fa-solid fa-circle" style="color:#4ade80;font-size:.5rem"></i>
          Available for work
        </div>
        <h1 class="hero-title">
          <?php echo escape($profile['name']); ?>
          <span class="grad-text"><?php echo escape($hero['title']); ?></span>
        </h1>
        <p class="hero-subtitle"><?php echo escape($hero['subtitle']); ?></p>

        <div class="hero-cta">
          <a href="#projects" class="btn-primary">
            <i class="fa-solid fa-rocket" aria-hidden="true"></i> View Projects
          </a>
          <a href="#contact" class="btn-glass">
            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Get in Touch
          </a>
          <?php if (!empty($profile['resume'])): ?>
            <a href="<?php echo UPLOAD_URL . escape($profile['resume']); ?>" class="btn-outline" download target="_blank"
              rel="noopener">
              <i class="fa-solid fa-download" aria-hidden="true"></i> Résumé
            </a>
          <?php endif; ?>
        </div>

        <div class="hero-stats">
          <div class="hero-stat">
            <span class="hero-stat-num"><?php echo count($projects); ?>+</span>
            <span class="hero-stat-label">Projects</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-num"><?php echo count($skills); ?>+</span>
            <span class="hero-stat-label">Skills</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-num">3+</span>
            <span class="hero-stat-label">Years Exp.</span>
          </div>
        </div>
      </div>

      <!-- Right: Visual Card -->
      <div class="hero-visual reveal" data-delay="200">
        <div class="glass-card hero-avatar-card">
          <?php if (!empty($profile['avatar'])): ?>
            <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>"
              alt="<?php echo escape($profile['name']); ?>" class="hero-avatar" loading="eager">
          <?php else: ?>
            <div class="hero-avatar-placeholder"><i class="fa-solid fa-user-tie"></i></div>
          <?php endif; ?>
          <div class="hero-name"><?php echo escape($profile['name']); ?></div>
          <div class="hero-role"><?php echo escape($profile['headline']); ?></div>
          <?php if (!empty($profile['email'])): ?>
            <p style="font-size:.8rem;color:var(--text-muted);margin-top:.4rem">
              <i class="fa-solid fa-envelope" aria-hidden="true"></i>
              <?php echo escape($profile['email']); ?>
            </p>
          <?php endif; ?>
        </div>

        <?php if ($socials): ?>
          <div class="glass-card hero-social-card">
            <h4><i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Find me on</h4>
            <div class="social-links-grid">
              <?php foreach ($socials as $sl):
                $url = sanitizeUrl($sl['url']);
                if (!$url)
                  continue; ?>
                <a href="<?php echo escape($url); ?>" target="_blank" rel="noopener noreferrer" class="social-link-pill">
                  <i class="<?php echo escape($sl['icon_class']); ?>" aria-hidden="true"></i>
                  <?php echo escape($sl['platform']); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<!-- ══ ABOUT ══════════════════════════════════════════════════ -->
<section id="about">
  <div class="container">
    <div class="text-center" style="margin-bottom:3rem">
      <span class="section-label">Who I Am</span>
      <h2 class="section-title">About <span>Me</span></h2>
    </div>
    <div class="about-grid">
      <div class="reveal">
        <div class="glass-card about-illustration"><i class="fa-solid fa-user-tie"></i></div>
      </div>
      <div class="about-content reveal" data-delay="150">
        <p class="about-text"><?php echo escape($about['content']); ?></p>
        <div class="about-tags">
          <?php $tags = ['Problem Solver', 'Team Player', 'Fast Learner', 'Open Source', 'Creative Thinker', 'Full-Stack'];
          foreach ($tags as $t): ?>
            <span class="about-tag"><?php echo $t; ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ SKILLS ═════════════════════════════════════════════════ -->
<section id="skills">
  <div class="container">
    <div class="text-center" style="margin-bottom:3rem">
      <span class="section-label">What I Know</span>
      <h2 class="section-title">Technical <span>Skills</span></h2>
      <p class="section-subtitle">A curated set of tools and technologies I use to build great products.</p>
    </div>

    <?php if ($skillsByCategory): ?>
      <div class="skills-grid">
        <?php foreach ($catConfig as $catKey => $catMeta):
          $catSkills = $skillsByCategory[$catKey] ?? [];
          if (empty($catSkills))
            continue; ?>
          <div class="glass-card skill-category-card reveal">
            <div class="skill-category-title">
              <i class="<?php echo $catMeta['icon']; ?>" aria-hidden="true"></i>
              <?php echo $catMeta['label']; ?>
            </div>
            <?php foreach ($catSkills as $skill): ?>
              <div class="skill-item">
                <div class="skill-header">
                  <span class="skill-name"><?php echo escape($skill['name']); ?></span>
                  <span class="skill-pct"><?php echo (int) ($skill['percentage'] ?? 80); ?>%</span>
                </div>
                <div class="skill-bar-track">
                  <div class="skill-bar-fill" data-width="<?php echo (int) ($skill['percentage'] ?? 80); ?>"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state"><i class="fa-solid fa-code" aria-hidden="true"></i>
        <p>Skills coming soon.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ══ PROJECTS ════════════════════════════════════════════════ -->
<section id="projects">
  <div class="container">
    <div class="text-center" style="margin-bottom:3rem">
      <span class="section-label">What I've Built</span>
      <h2 class="section-title">Featured <span>Projects</span></h2>
      <p class="section-subtitle">A selection of projects that showcase my skills and passion for building.</p>
    </div>

    <?php if ($projects): ?>
      <div class="projects-grid">
        <?php foreach ($projects as $i => $p):
          $tags = [];
          if (!empty($p['tech_stack'])) {
            foreach (array_slice(preg_split('/[,;]+/', $p['tech_stack']), 0, 5) as $t) {
              $t = trim($t);
              if ($t)
                $tags[] = $t;
            }
          }
          ?>
          <div class="glass-card project-card reveal" data-delay="<?php echo $i * 60; ?>">
            <?php if ($p['featured']): ?>
              <div class="project-featured-tag"><i class="fa-solid fa-star" aria-hidden="true"></i> Featured</div>
            <?php endif; ?>

            <div class="project-img-wrap">
              <?php if (!empty($p['image'])): ?>
                <img src="<?php echo UPLOAD_URL . escape($p['image']); ?>" alt="<?php echo escape($p['title']); ?>"
                  class="project-img" loading="lazy">
              <?php else: ?>
                <div class="project-img-placeholder"><i class="fa-solid fa-rocket" aria-hidden="true"></i></div>
              <?php endif; ?>
            </div>

            <div class="project-body">
              <h3 class="project-title"><?php echo escape($p['title']); ?></h3>
              <p class="project-desc"><?php echo escape(truncate($p['description'] ?? '', 110)); ?></p>
              <?php if ($tags): ?>
                <div class="project-tags">
                  <?php foreach ($tags as $tag): ?>
                    <span class="tag"><?php echo escape($tag); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <div class="project-links">
                <?php if (!empty($p['github_link'])): ?>
                  <a href="<?php echo escape(sanitizeUrl($p['github_link'])); ?>" class="project-link" target="_blank"
                    rel="noopener noreferrer">
                    <i class="fab fa-github" aria-hidden="true"></i> GitHub
                  </a>
                <?php endif; ?>
                <?php if (!empty($p['demo_link'])): ?>
                  <a href="<?php echo escape(sanitizeUrl($p['demo_link'])); ?>" class="project-link primary" target="_blank"
                    rel="noopener noreferrer">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Live Demo
                  </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/project.php?id=<?php echo (int) $p['id']; ?>" class="project-link">Details
                  <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
        <p>Projects coming soon. Check back later!</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ══ BLOG / NOTES ══════════════════════════════════════════ -->
<?php if ($notes): ?>
  <section id="blog">
    <div class="container">
      <div class="text-center" style="margin-bottom:3rem">
        <span class="section-label">Thoughts & Learnings</span>
        <h2 class="section-title">Digital <span>Garden</span></h2>
      </div>
      <div class="projects-grid">
        <?php foreach ($notes as $j => $note): ?>
          <a href="<?php echo BASE_URL; ?>/note.php?id=<?php echo (int) $note['id']; ?>"
            class="glass-card project-card reveal" data-delay="<?php echo $j * 80; ?>"
            style="text-decoration:none;color:inherit">
            <div class="project-body" style="padding:1.75rem">
              <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.6rem">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <?php echo formatDate($note['created_at']); ?>
              </div>
              <h3 class="project-title"><?php echo escape($note['title']); ?></h3>
              <p class="project-desc"><?php echo escape(truncate($note['excerpt'] ?? '', 100)); ?></p>
              <span style="color:var(--accent);font-size:.85rem;font-weight:600">Read more <i
                  class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<!-- ══ CONTACT ════════════════════════════════════════════════ -->
<section id="contact">
  <div class="container">
    <div class="text-center" style="margin-bottom:3rem">
      <span class="section-label">Say Hello</span>
      <h2 class="section-title">Get In <span>Touch</span></h2>
      <p class="section-subtitle">Have a project in mind? Let's build something great together.</p>
    </div>

    <div class="contact-grid">
      <div class="glass-card contact-info reveal">
        <h3>Let's connect</h3>
        <p>Open to freelance projects, collaborations, and full-time opportunities. I typically respond within 24 hours.
        </p>
        <?php if (!empty($profile['email'])): ?>
          <div class="contact-detail">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
            <a href="mailto:<?php echo escape($profile['email']); ?>"><?php echo escape($profile['email']); ?></a>
          </div>
        <?php endif; ?>
        <?php if (!empty($profile['github'])): ?>
          <div class="contact-detail">
            <i class="fab fa-github" aria-hidden="true"></i>
            <a href="<?php echo escape(sanitizeUrl($profile['github'])); ?>" target="_blank" rel="noopener">GitHub
              Profile</a>
          </div>
        <?php endif; ?>
        <?php if (!empty($profile['linkedin'])): ?>
          <div class="contact-detail">
            <i class="fab fa-linkedin" aria-hidden="true"></i>
            <a href="<?php echo escape(sanitizeUrl($profile['linkedin'])); ?>" target="_blank" rel="noopener">LinkedIn</a>
          </div>
        <?php endif; ?>
        <div style="margin-top:1.5rem">
          <div class="social-links-grid">
            <?php foreach ($socials as $sl):
              $url = sanitizeUrl($sl['url']);
              if (!$url)
                continue; ?>
              <a href="<?php echo escape($url); ?>" target="_blank" rel="noopener" class="social-icon-btn"
                aria-label="<?php echo escape($sl['platform']); ?>">
                <i class="<?php echo escape($sl['icon_class']); ?>" aria-hidden="true"></i>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="glass-card contact-form-card reveal" data-delay="120">
        <?php if ($contactSuccess): ?>
          <div class="alert alert-success">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            Message sent! I'll get back to you within 24 hours.
          </div>
        <?php endif; ?>
        <?php if ($contactError): ?>
          <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <?php echo escape($contactError); ?>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <?php echo csrfField(); ?>
          <div class="form-row">
            <div class="field">
              <label for="c-name">Your Name</label>
              <input type="text" id="c-name" name="name" required autocomplete="name" placeholder="John Doe"
                value="<?php echo !$contactSuccess ? escape($_POST['name'] ?? '') : ''; ?>">
            </div>
            <div class="field">
              <label for="c-email">Email Address</label>
              <input type="email" id="c-email" name="email" required autocomplete="email" placeholder="john@example.com"
                value="<?php echo !$contactSuccess ? escape($_POST['email'] ?? '') : ''; ?>">
            </div>
          </div>
          <div class="field">
            <label for="c-msg">Message</label>
            <textarea id="c-msg" name="message" required rows="5"
              placeholder="Tell me about your project..."><?php echo !$contactSuccess ? escape($_POST['message'] ?? '') : ''; ?></textarea>
          </div>
          <button type="submit" name="contact_submit" class="btn-primary">
            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>