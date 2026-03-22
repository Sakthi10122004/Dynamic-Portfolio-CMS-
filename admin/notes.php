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
    // Delete image file if exists
    $note = getNote($id);
    if ($note && !empty($note['image'])) {
        $imgPath = UPLOAD_DIR . $note['image'];
        if (file_exists($imgPath)) @unlink($imgPath);
    }
    $db->query("DELETE FROM notes WHERE id = ?", [$id], 'i');
    header('Location: ' . BASE_URL . '/admin/notes.php?deleted=1');
    exit;
}

$notes = getAllNotes();

$pageTitle = 'Manage Posts';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700">
                    <i class="fa-solid fa-newspaper" style="color:#a78bfa"></i> Blog Posts
                </h1>
                <p style="font-size:.83rem;color:#6e6590;margin-top:.1rem">Manage your digital garden and articles</p>
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
                <span style="font-size:.78rem;color:#6e6590"><?php echo count($notes); ?> post<?php echo count($notes) !== 1 ? 's' : ''; ?></span>
            </div>
            <div class="admin-card-body" style="padding:0">
            <?php if (empty($notes)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-pen-nib" style="font-size:2rem;opacity:.2;display:block;margin-bottom:.5rem"></i>
                <p>No blog posts yet.</p>
                <a href="<?php echo BASE_URL; ?>/admin/note-edit.php" class="btn-primary" style="margin-top:1rem;display:inline-flex">
                    <i class="fa-solid fa-plus"></i> Write First Post
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $note): ?>
                    <tr>
                        <td>
                            <?php if (!empty($note['image'])): ?>
                                <img src="<?php echo UPLOAD_URL . escape($note['image']); ?>"
                                     alt="" class="img-preview">
                            <?php else: ?>
                                <div style="width:80px;height:55px;border-radius:10px;background:rgba(139,92,246,0.06);display:flex;align-items:center;justify-content:center;color:#6e6590;font-size:.8rem">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo escape($note['title']); ?></strong></td>
                        <td>
                            <span class="badge-<?php echo $note['published'] ? 'published' : 'unpublished'; ?>">
                                <i class="fa-solid fa-<?php echo $note['published'] ? 'check-circle' : 'circle'; ?>" style="font-size:0.65rem;margin-right:0.2rem"></i>
                                <?php echo $note['published'] ? 'Published' : 'Draft'; ?>
                            </span>
                        </td>
                        <td style="white-space:nowrap"><?php echo formatDate($note['created_at']); ?></td>
                        <td class="actions-cell">
                            <?php if ($note['published']): ?>
                                <a href="<?php echo BASE_URL; ?>/note.php?id=<?php echo $note['id']; ?>"
                                   class="btn-icon" title="View" target="_blank">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/admin/note-edit.php?id=<?php echo $note['id']; ?>"
                               class="btn-icon" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this post?')">
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
            </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
