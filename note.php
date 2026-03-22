<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$note = getPublishedNote($id);

if (!$note) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Article Not Found';
    require_once 'includes/header.php';
    echo '<div class="project-detail" style="text-align:center;padding:8rem 2rem;">
        <div class="glass-card" style="max-width:500px;margin:0 auto;padding:3rem 2rem;">
            <i class="fa-solid fa-file-lines" style="font-size:2.5rem;color:var(--ink3);opacity:.3;display:block;margin-bottom:1rem;"></i>
            <h1 style="font-family:var(--font-head);font-size:1.5rem;margin-bottom:.75rem;">Article Not Found</h1>
            <p style="color:var(--ink2);margin-bottom:1.5rem;">This article doesn\'t exist or isn\'t published yet.</p>
            <a href="' . BASE_URL . '/#blog" class="btn-primary">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Articles
            </a>
        </div>
    </div>';
    require_once 'includes/footer.php';
    exit;
}

$pageTitle = escape($note['title']);
$pageDescription = escape(truncate($note['excerpt'] ?? $note['content'] ?? '', 160));

require_once 'includes/header.php';
?>

<article class="project-detail">
    <!-- Header -->
    <div class="glass-card" style="padding:2rem;margin-bottom:1.5rem;">
        <a href="<?php echo BASE_URL; ?>/#blog" class="back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Articles
        </a>
        <h1><?php echo escape($note['title']); ?></h1>
        <time datetime="<?php echo $note['created_at']; ?>" style="font-size:.8rem;color:var(--ink3);display:inline-flex;align-items:center;gap:.4rem;">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <?php echo formatDate($note['created_at']); ?>
        </time>
    </div>

    <!-- Content -->
    <div class="glass-card" style="padding:2rem;">
        <div style="font-size:1rem;line-height:1.85;color:var(--ink2);">
            <?php echo nl2br(escape($note['content'])); ?>
        </div>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>
