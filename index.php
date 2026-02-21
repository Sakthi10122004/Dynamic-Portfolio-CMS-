<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$profile = getProfile();
$featuredProjects = getProjects(3, false); // top 3 ordered by featured
$skills = getSkills();
$recentNotes = getNotes(3);

$pageTitle = escape($profile['name']) . ' — ' . escape($profile['headline']);
$pageDescription = escape($profile['headline']);

require_once 'includes/header.php';
?>

<section class="hero bento-grid">
    <div class="bento-card hero-card">
        <div class="hero-top">
            <?php if (!empty($profile['avatar'])): ?>
            <div class="hero-avatar">
                <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>" 
                     alt="<?php echo escape($profile['name']); ?>" class="avatar-ring">
            </div>
            <?php endif; ?>
            <div class="hero-text">
                <div class="hero-badge">Available for work</div>
                <h1 class="glitch" data-text="<?php echo escape($profile['name']); ?>">
                    <?php echo escape($profile['name']); ?>
                </h1>
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
            <a href="<?php echo UPLOAD_URL . escape($profile['resume']); ?>" class="btn secondary" download>
                📄 Download Resume
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="bento-card stats-card">
        <div class="stat-item">
            <span class="stat-number">5+</span>
            <span class="stat-label">Years Experience</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">20+</span>
            <span class="stat-label">Projects</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">∞</span>
            <span class="stat-label">Innovation</span>
        </div>
    </div>
</section>

<section id="work" class="work-section">
    <h2 class="section-title">Featured Work</h2>
    <div class="projects-grid bento-grid">
        <?php if (empty($featuredProjects)): ?>
        <div class="bento-card empty-state">
            <p>No projects yet. Add some from the <a href="<?php echo BASE_URL; ?>/admin/">admin panel</a>.</p>
        </div>
        <?php else: ?>
        <?php foreach ($featuredProjects as $project): ?>
        <a href="<?php echo BASE_URL; ?>/project.php?id=<?php echo $project['id']; ?>" class="project-card bento-card">
            <?php if (!empty($project['image'])): ?>
            <div class="project-image">
                <img src="<?php echo UPLOAD_URL . escape($project['image']); ?>" 
                     alt="<?php echo escape($project['title']); ?>"
                     loading="lazy">
            </div>
            <?php endif; ?>
            <h3><?php echo escape($project['title']); ?></h3>
            <p><?php echo escape(truncate($project['description'], 100)); ?></p>
            <span class="project-link">View Project →</span>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section id="skills" class="skills-section">
    <h2 class="section-title">Skills & Expertise</h2>
    <div class="skills-bento bento-grid">
        <?php 
        $categories = ['frontend', 'backend', 'devops'];
        $categoryIcons = ['frontend' => '🎨', 'backend' => '⚙️', 'devops' => '☁️'];
        foreach ($categories as $category): 
        ?>
        <div class="skills-card bento-card">
            <div class="skills-card-header">
                <span class="category-icon"><?php echo $categoryIcons[$category]; ?></span>
                <h3><?php echo ucfirst($category); ?></h3>
            </div>
            <div class="skills-list">
                <?php 
                $categorySkills = array_filter($skills, function($s) use ($category) {
                    return $s['category'] === $category;
                });
                if (empty($categorySkills)): ?>
                <span class="skill-tag empty">Add skills in admin</span>
                <?php else: ?>
                <?php foreach ($categorySkills as $skill): ?>
                <span class="skill-tag"><?php echo escape($skill['name']); ?></span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="garden" class="garden-section">
    <h2 class="section-title">Digital Garden</h2>
    <div class="notes-grid bento-grid">
        <?php if (empty($recentNotes)): ?>
        <div class="bento-card empty-state">
            <p>No notes published yet. Start writing from the <a href="<?php echo BASE_URL; ?>/admin/">admin panel</a>.</p>
        </div>
        <?php else: ?>
        <?php foreach ($recentNotes as $note): ?>
        <article class="note-card bento-card">
            <h3><?php echo escape($note['title']); ?></h3>
            <p class="note-excerpt"><?php echo escape($note['excerpt'] ?? truncate($note['content'] ?? '', 120)); ?></p>
            <div class="note-meta">
                <time datetime="<?php echo $note['created_at']; ?>">
                    <?php echo formatDate($note['created_at']); ?>
                </time>
                <a href="<?php echo BASE_URL; ?>/note.php?id=<?php echo $note['id']; ?>" class="read-more">Read →</a>
            </div>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section id="contact" class="contact-section">
    <h2 class="section-title">Get in Touch</h2>
    <div class="contact-bento bento-grid">
        <div class="contact-info bento-card">
            <h3>Let's create something amazing</h3>
            <p><?php echo escape($profile['bio']); ?></p>
            <div class="contact-details">
                <a href="mailto:<?php echo escape($profile['email']); ?>" class="contact-email">
                    📧 <?php echo escape($profile['email']); ?>
                </a>
                <?php if (!empty($profile['github']) || !empty($profile['linkedin']) || !empty($profile['twitter'])): ?>
                <div class="contact-socials">
                    <?php if (!empty($profile['github'])): ?>
                    <a href="<?php echo escape($profile['github']); ?>" target="_blank" rel="noopener" class="social-chip">GitHub →</a>
                    <?php endif; ?>
                    <?php if (!empty($profile['linkedin'])): ?>
                    <a href="<?php echo escape($profile['linkedin']); ?>" target="_blank" rel="noopener" class="social-chip">LinkedIn →</a>
                    <?php endif; ?>
                    <?php if (!empty($profile['twitter'])): ?>
                    <a href="<?php echo escape($profile['twitter']); ?>" target="_blank" rel="noopener" class="social-chip">Twitter →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($_GET['sent'])): ?>
        <div class="bento-card">
            <div class="alert alert-success">✅ Message sent successfully! I'll get back to you soon.</div>
        </div>
        <?php elseif (isset($_GET['limited'])): ?>
        <div class="bento-card">
            <div class="alert alert-error">⏱ Too many messages. Please wait an hour before sending another.</div>
        </div>
        <?php else: ?>
        <form class="contact-form bento-card" method="POST" action="<?php echo BASE_URL; ?>/contact.php">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <input type="text" name="name" placeholder="Your Name" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Your Email" required>
            </div>
            <div class="form-group">
                <textarea name="message" placeholder="Your Message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn primary full-width">Send Message</button>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>