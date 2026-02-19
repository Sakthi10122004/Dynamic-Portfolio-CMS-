<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project = getProject($id);

if (!$project) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Project Not Found';
    require_once 'includes/header.php';
    echo '<div class="error-page bento-card" style="max-width:600px;margin:4rem auto;text-align:center;">
        <h1>Project Not Found</h1>
        <p style="margin:1rem 0;color:var(--text-secondary);">The project you\'re looking for doesn\'t exist.</p>
        <a href="' . BASE_URL . '/" class="btn primary">Go Home</a>
    </div>';
    require_once 'includes/footer.php';
    exit;
}

$pageTitle = escape($project['title']);
$pageDescription = escape(truncate($project['description'], 160));

require_once 'includes/header.php';
?>

<article class="project-detail bento-grid">
    <div class="project-header bento-card">
        <a href="<?php echo BASE_URL; ?>/#work" class="back-link">← Back to Work</a>
        <h1><?php echo escape($project['title']); ?></h1>
        <time datetime="<?php echo $project['created_at']; ?>">
            Created <?php echo formatDate($project['created_at']); ?>
        </time>
    </div>
    
    <?php if (!empty($project['image'])): ?>
    <div class="project-image-full bento-card">
        <img src="<?php echo UPLOAD_URL . escape($project['image']); ?>" 
             alt="<?php echo escape($project['title']); ?>"
             loading="lazy">
    </div>
    <?php endif; ?>
    
    <div class="project-content bento-card">
        <div class="project-description">
            <?php echo nl2br(escape($project['description'])); ?>
        </div>
        
        <?php if (!empty($project['github_link'])): ?>
        <div class="project-links">
            <a href="<?php echo escape($project['github_link']); ?>" 
               target="_blank" 
               rel="noopener"
               class="btn primary">
                View on GitHub →
            </a>
        </div>
        <?php endif; ?>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>