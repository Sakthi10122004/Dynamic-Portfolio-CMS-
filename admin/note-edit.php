<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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
        <div class="admin-header">
            <h1><?php echo $id ? 'Edit Note' : 'New Note'; ?></h1>
            <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="btn secondary">← Back</a>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="edit-form bento-card">
            <?php echo csrfField(); ?>
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo escape($note['title'] ?? $_POST['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="excerpt">Excerpt (shown on homepage)</label>
                <textarea id="excerpt" name="excerpt" rows="2"><?php echo escape($note['excerpt'] ?? $_POST['excerpt'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="12" required><?php echo escape($note['content'] ?? $_POST['content'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group checkbox">
                <label>
                    <input type="checkbox" name="published" 
                           <?php echo (($note['published'] ?? false) || isset($_POST['published'])) ? 'checked' : ''; ?>>
                    Published
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn primary">Save Note</button>
                <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
