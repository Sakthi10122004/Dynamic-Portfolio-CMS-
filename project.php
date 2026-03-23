<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$auth = new Auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project = getProject($id);

if (!$project) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Project Not Found';
    require_once 'includes/header.php';
    echo '<div class="project-detail" style="text-align:center;padding:8rem 2rem;">
        <div class="glass-card" style="max-width:500px;margin:0 auto;padding:3rem 2rem;">
            <i class="fa-solid fa-folder-open" style="font-size:2.5rem;color:var(--ink3);opacity:.3;display:block;margin-bottom:1rem;"></i>
            <h1 style="font-family:var(--font-head);font-size:1.5rem;margin-bottom:.75rem;">Project Not Found</h1>
            <p style="color:var(--ink2);margin-bottom:1.5rem;">The project you\'re looking for doesn\'t exist or has been removed.</p>
            <a href="' . BASE_URL . '/#projects" class="btn-primary">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Projects
            </a>
        </div>
    </div>';
    require_once 'includes/footer.php';
    exit;
}

$pageTitle = escape($project['title']);
$pageDescription = escape(truncate($project['description'], 160));

// Parse tech stack tags
$tags = [];
if (!empty($project['tech_stack'])) {
    foreach (array_slice(preg_split('/[,;]+/', $project['tech_stack']), 0, 8) as $t) {
        $t = trim($t);
        if ($t) $tags[] = $t;
    }
}

require_once 'includes/header.php';
?>

<article class="project-detail">
    <!-- Header -->
    <div class="glass-card" style="padding:2rem;margin-bottom:1.5rem;">
        <a href="<?php echo BASE_URL; ?>/#projects" class="back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Projects
        </a>
        <h1><?php echo escape($project['title']); ?></h1>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-top:.5rem;">
            <time datetime="<?php echo $project['created_at']; ?>" style="font-size:.8rem;color:var(--ink3);">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <?php echo formatDate($project['created_at']); ?>
            </time>
            <?php if ($project['featured']): ?>
                <span class="tag" style="background:rgba(201,163,79,0.1);color:#c9a34f;border-color:rgba(201,163,79,0.2);">
                    <i class="fa-solid fa-star" aria-hidden="true"></i> Featured
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($project['image'])): ?>
    <!-- Image -->
    <div class="glass-card" style="padding:1rem;margin-bottom:1.5rem;">
        <img src="<?php echo UPLOAD_URL . escape($project['image']); ?>"
             alt="<?php echo escape($project['title']); ?>"
             loading="lazy"
             style="border-radius:var(--radius-sm);width:100%;">
    </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="glass-card" style="padding:2rem;margin-bottom:1.5rem;">
        <?php if ($tags): ?>
            <div class="project-tags" style="margin-bottom:1.5rem;">
                <?php foreach ($tags as $tag): ?>
                    <span class="tag"><?php echo escape($tag); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="font-size:1rem;line-height:1.85;color:var(--ink2);">
            <?php echo nl2br(escape($project['description'])); ?>
        </div>

        <!-- Links -->
        <div class="project-links" style="margin-top:2rem;">
            <?php if (!empty($project['github_link'])): ?>
                <a href="<?php echo escape(sanitizeUrl($project['github_link'])); ?>"
                   target="_blank" rel="noopener noreferrer" class="btn-primary">
                    <i class="fa-brands fa-github" aria-hidden="true"></i> View on GitHub
                </a>
            <?php endif; ?>
            <?php if (!empty($project['demo_link'])): ?>
                <a href="<?php echo escape(sanitizeUrl($project['demo_link'])); ?>"
                   target="_blank" rel="noopener noreferrer" class="btn-glass">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Live Demo
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>