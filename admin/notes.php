<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

$db = Database::getInstance();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
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
        <div class="admin-header">
            <h1>Notes</h1>
            <a href="<?php echo BASE_URL; ?>/admin/note-edit.php" class="btn primary">+ New Note</a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="success-message">Note deleted successfully</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['saved'])): ?>
        <div class="success-message">Note saved successfully</div>
        <?php endif; ?>
        
        <div class="data-card bento-card">
            <?php if (empty($notes)): ?>
            <p class="empty-text">No notes yet. <a href="<?php echo BASE_URL; ?>/admin/note-edit.php">Write your first note</a></p>
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
                            <span class="status-badge <?php echo $note['published'] ? 'published' : 'draft'; ?>">
                                <?php echo $note['published'] ? 'Published' : 'Draft'; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($note['created_at']); ?></td>
                        <td class="actions">
                            <a href="<?php echo BASE_URL; ?>/admin/note-edit.php?id=<?php echo $note['id']; ?>" class="action-btn edit">Edit</a>
                            <a href="<?php echo BASE_URL; ?>/admin/notes.php?delete=<?php echo $note['id']; ?>" 
                               class="action-btn delete"
                               onclick="return confirm('Delete this note?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
