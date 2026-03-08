<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$note = $id ? getNote($id) : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        if ($id) {
            $db->query(
                "UPDATE notes SET title = ?, excerpt = ?, content = ?, published = ? WHERE id = ?",
                [$title, $excerpt, $content, $published, $id],
                'sssii'
            );
        } else {
            $db->insert(
                "INSERT INTO notes (title, excerpt, content, published) VALUES (?, ?, ?, ?)",
                [$title, $excerpt, $content, $published],
                'sssi'
            );
        }

        header('Location: ' . BASE_URL . '/admin/notes.php?saved=1');
        exit;
    }
}

$pageTitle = $id ? 'Edit Note' : 'New Note';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
                    <i class="fa-solid fa-<?php echo $id ? 'pen' : 'plus'; ?>" style="color:var(--accent)"></i>
                    <?php echo $id ? 'Edit Post' : 'New Post'; ?>
                </h1>
                <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Draft and publish articles to your
                    digital garden</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="btn-glass btn-sm">← Back to Posts</a>
        </div>

        <?php if ($error): ?>
            <div class="flash flash-error">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-file-signature"></i> Post Content</h2>
            </div>
            <div class="admin-card-body">
                <form method="POST" class="admin-form">
                    <?php echo csrfField(); ?>

                    <div class="field">
                        <label for="title">Post Title <span style="color:#f87171">*</span></label>
                        <input type="text" id="title" name="title"
                            value="<?php echo escape($note['title'] ?? $_POST['title'] ?? ''); ?>" required
                            placeholder="Enter a catchy title...">
                    </div>

                    <div class="field">
                        <label for="excerpt">Excerpt / Summary</label>
                        <textarea id="excerpt" name="excerpt" rows="2"
                            placeholder="Brief summary shown on the blog index..."><?php echo escape($note['excerpt'] ?? $_POST['excerpt'] ?? ''); ?></textarea>
                        <p class="form-hint">Shown in the project card or blog list.</p>
                    </div>

                    <div class="field">
                        <label for="content">Full Content <span style="color:#f87171">*</span></label>
                        <textarea id="content" name="content" rows="15" required
                            placeholder="Write your post content here..."><?php echo escape($note['content'] ?? $_POST['content'] ?? ''); ?></textarea>
                        <p class="form-hint">Markdown or plain text is supported via nl2br.</p>
                    </div>

                    <div class="field" style="display:flex; align-items:center; gap:0.6rem">
                        <input type="checkbox" id="published" name="published" <?php echo (($note['published'] ?? false) || isset($_POST['published'])) ? 'checked' : ''; ?>>
                        <label for="published" style="margin-bottom:0; cursor:pointer">
                            <i class="fa-solid fa-paper-plane" style="font-size:0.8rem; margin-right:0.3rem"></i>
                            Publish this post
                        </label>
                    </div>

                    <div class="form-actions" style="margin-top:1.5rem">
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save Post
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="btn-glass">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>