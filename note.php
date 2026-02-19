<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$note = getPublishedNote($id);

if (!$note) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Note Not Found';
    require_once 'includes/header.php';
    echo '<div class="error-page bento-card" style="max-width:600px;margin:4rem auto;text-align:center;">
        <h1>Note Not Found</h1>
        <p style="margin:1rem 0;color:var(--text-secondary);">This note doesn\'t exist or isn\'t published yet.</p>
        <a href="' . BASE_URL . '/#garden" class="btn primary">Back to Garden</a>
    </div>';
    require_once 'includes/footer.php';
    exit;
}

$pageTitle = escape($note['title']);
$pageDescription = escape(truncate($note['excerpt'] ?? $note['content'] ?? '', 160));

require_once 'includes/header.php';
?>

<article class="note-detail bento-grid">
    <div class="note-header bento-card">
        <a href="<?php echo BASE_URL; ?>/#garden" class="back-link">← Back to Garden</a>
        <h1><?php echo escape($note['title']); ?></h1>
        <time datetime="<?php echo $note['created_at']; ?>">
            <?php echo formatDate($note['created_at']); ?>
        </time>
    </div>
    
    <div class="note-content bento-card">
        <?php echo nl2br(escape($note['content'])); ?>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>
