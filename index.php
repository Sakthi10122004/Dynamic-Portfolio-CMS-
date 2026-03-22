<?php
/* ============================================================
   index.php — Professional Portfolio (Dynamic CMS)
   ============================================================ */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
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
    // 1. Save to database
    saveContactMessage($name, $email, $message, $ip);

    // 2. Send Email Notification
    $to = !empty($profile['email']) ? $profile['email'] : 'hello@example.com';
    $subject = "New Contact Message from " . $name;

    $body = "You have received a new message from your portfolio website.\n\n";
    $body .= "Name: " . $name . "\n";
    $body .= "Email: " . $email . "\n";
    $body .= "IP Address: " . $ip . "\n\n";
    $body .= "Message:\n" . $message . "\n";

    // Attempt PHPMailer first if SMTP is configured
    if (!empty($profile['smtp_host']) && !empty($profile['smtp_user'])) {
      require_once __DIR__ . '/includes/PHPMailer/Exception.php';
      require_once __DIR__ . '/includes/PHPMailer/PHPMailer.php';
      require_once __DIR__ . '/includes/PHPMailer/SMTP.php';

      $mail = new PHPMailer\PHPMailer\PHPMailer(true);
      try {
        $mail->isSMTP();
        $mail->Host = $profile['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $profile['smtp_user'];
        $mail->Password = $profile['smtp_pass'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = !empty($profile['smtp_port']) ? (int) $profile['smtp_port'] : 587;

        $mail->setFrom($profile['smtp_user'], escape($profile['name']) . ' Portfolio');
        $mail->addAddress($to);
        $mail->addReplyTo($email, $name);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
      } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
      }
    } else {
      $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
      $headers .= "Reply-To: " . $email . "\r\n";
      $headers .= "X-Mailer: PHP/" . phpversion();
      @mail($to, $subject, $body, $headers);
    }

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
  <div class="hero-center">

    <!-- Available badge -->
    <div class="hero-badge reveal">
      <i class="fa-solid fa-circle" style="font-size:.5rem"></i>
      Available for work
    </div>

    <!-- Avatar -->
    <div class="hero-avatar-wrap reveal" data-delay="80">
      <div class="hero-avatar-glow"></div>
      <?php if (!empty($profile['avatar'])): ?>
        <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>" alt="<?php echo escape($profile['name']); ?>"
          class="hero-avatar" loading="eager">
      <?php else: ?>
        <div class="hero-avatar-placeholder">
          <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
        </div>
      <?php endif; ?>
    </div>

    <!-- Name & title -->
    <h1 class="hero-title reveal" data-delay="160">
      <?php echo escape($profile['name']); ?>
      <span class="grad-text"><?php echo escape($hero['title']); ?></span>
    </h1>
    <p class="hero-subtitle reveal" data-delay="220"><?php echo escape($hero['subtitle']); ?></p>

    <!-- CTA buttons -->
    <div class="hero-cta reveal" data-delay="300">
      <a href="#projects" class="btn-primary">
        <i class="fa-solid fa-briefcase" aria-hidden="true"></i> View Projects
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

    <!-- Social pills -->
    <?php if ($socials): ?>
      <div class="hero-tech-pills reveal" data-delay="380">
        <?php foreach ($socials as $sl):
          $url = sanitizeUrl($sl['url']);
          if (!$url)
            continue; ?>
          <a href="<?php echo escape($url); ?>" target="_blank" rel="noopener noreferrer" class="hero-tech-pill">
            <i class="<?php echo escape($sl['icon_class']); ?>" aria-hidden="true"></i>
            <?php echo escape($sl['platform']); ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="hero-stats reveal" data-delay="440">
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
</section>

<!-- ══ ABOUT ═════════════════════════════════════════════════ -->
<section id="about">
  <div class="container">
    <div class="text-center" style="margin-bottom:3rem">
      <span class="section-label">Who I Am</span>
      <h2 class="section-title">About <span>Me</span></h2>
    </div>
    <div class="about-grid">
      <div class="reveal">
        <div class="glass-card about-illustration">
          <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
        </div>
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

<!-- ══ SKILLS ════════════════════════════════════════════════ -->
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
          <div class="skill-category-card reveal">
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
      <div class="empty-state">
        <i class="fa-solid fa-code" aria-hidden="true"></i>
        <p>Skills coming soon.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ══ PROJECTS ══════════════════════════════════════════════ -->
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
              <div class="project-featured-tag">
                <i class="fa-solid fa-star" aria-hidden="true"></i> Featured
              </div>
            <?php endif; ?>

            <div class="project-img-wrap">
              <?php if (!empty($p['image'])): ?>
                <img src="<?php echo UPLOAD_URL . escape($p['image']); ?>" alt="<?php echo escape($p['title']); ?>"
                  class="project-img" loading="lazy">
              <?php else: ?>
                <div class="project-img-placeholder">
                  <i class="fa-solid fa-code" aria-hidden="true"></i>
                </div>
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
                    <i class="fa-brands fa-github" aria-hidden="true"></i> GitHub
                  </a>
                <?php endif; ?>
                <?php if (!empty($p['demo_link'])): ?>
                  <a href="<?php echo escape(sanitizeUrl($p['demo_link'])); ?>" class="project-link primary" target="_blank"
                    rel="noopener noreferrer">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Live Demo
                  </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/project.php?id=<?php echo (int) $p['id']; ?>" class="project-link">
                  Details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
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
        <h2 class="section-title">Latest <span>Articles</span></h2>
      </div>
      <div class="projects-grid">
        <?php foreach ($notes as $j => $note): ?>
          <a href="<?php echo BASE_URL; ?>/note.php?id=<?php echo (int) $note['id']; ?>"
            class="glass-card project-card reveal" data-delay="<?php echo $j * 80; ?>"
            style="text-decoration:none;color:inherit">
            <div class="project-body" style="padding:1.75rem">
              <div style="font-size:.76rem;color:var(--ink3);margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <?php echo formatDate($note['created_at']); ?>
              </div>
              <h3 class="project-title"><?php echo escape($note['title']); ?></h3>
              <p class="project-desc"><?php echo escape(truncate($note['excerpt'] ?? '', 100)); ?></p>
              <span
                style="color:var(--primary);font-size:.84rem;font-weight:600;display:inline-flex;align-items:center;gap:.3rem">
                Read more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </span>
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
      <!-- Info side -->
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
            <i class="fa-brands fa-github" aria-hidden="true"></i>
            <a href="<?php echo escape(sanitizeUrl($profile['github'])); ?>" target="_blank" rel="noopener">GitHub
              Profile</a>
          </div>
        <?php endif; ?>
        <?php if (!empty($profile['linkedin'])): ?>
          <div class="contact-detail">
            <i class="fa-brands fa-linkedin" aria-hidden="true"></i>
            <a href="<?php echo escape(sanitizeUrl($profile['linkedin'])); ?>" target="_blank" rel="noopener">LinkedIn</a>
          </div>
        <?php endif; ?>
        <?php if ($socials): ?>
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
        <?php endif; ?>
      </div>

      <!-- Form side -->
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
          <button type="submit" name="contact_submit" class="btn-primary" style="width:100%;justify-content:center">
            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>