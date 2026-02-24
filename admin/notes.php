<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();

// Handle POST delete (CSRF protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verifyCsrf();
    $id = (int)$_POST['delete_id'];
    $db->query("DELETE FROM notes WHERE id = ?", [$id], 'i');
    header('Location: ' . BASE_URL . '/admin/notes.php?deleted=1');
    exit;
}

$notes = getAllNotes();

$pageTitle = 'Manage Notes';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
                    <i class="fa-solid fa-newspaper" style="color:var(--accent)"></i> Blog Posts
                </h1>
                <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Manage your digital garden and articles</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/note-edit.php" class="btn-primary">
                <i class="fa-solid fa-plus"></i> New Post
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-check-circle"></i> Post deleted successfully.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-check-circle"></i> Post saved successfully.
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-list-ul"></i> All Posts</h2>
            </div>
            <div class="admin-card-body" style="padding:0">
            <?php if (empty($notes)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>No notes yet</h3>
                <p>Start your digital garden.</p>
                <a href="<?php echo BASE_URL; ?>/admin/note-edit.php" class="btn primary">Write First Note</a>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $note): ?>
                    <tr>
                        <td><strong><?php echo escape($note['title']); ?></strong></td>
                        <td>
                            <span class="badge-<?php echo $note['published'] ? 'published' : 'unpublished'; ?>">
                                <i class="fa-solid fa-<?php echo $note['published'] ? 'check-circle' : 'circle'; ?>" style="font-size:0.7rem; margin-right:0.3rem"></i>
                                <?php echo $note['published'] ? 'Published' : 'Draft'; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($note['created_at']); ?></td>
                        <td class="actions-cell">
                            <a href="<?php echo BASE_URL; ?>/admin/note-edit.php?id=<?php echo $note['id']; ?>" 
                               class="btn-icon" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this note?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $note['id']; ?>">
                                <button type="submit" class="btn-icon danger" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
