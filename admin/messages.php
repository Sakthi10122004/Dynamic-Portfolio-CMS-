<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();

// Handle POST actions (CSRF protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $db->query("DELETE FROM contact_messages WHERE id = ?", [$id], 'i');
        header('Location: ' . BASE_URL . '/admin/messages.php?deleted=1');
        exit;
    }

    if (isset($_POST['read_id'])) {
        $id = (int)$_POST['read_id'];
        $db->query("UPDATE contact_messages SET read_status = 1 WHERE id = ?", [$id], 'i');
        header('Location: ' . BASE_URL . '/admin/messages.php');
        exit;
    }
}

$messages = getMessages();

$pageTitle = 'Contact Messages';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-header">
            <h1>Messages</h1>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Message deleted.</div>
        <?php endif; ?>

        <div class="data-card bento-card">
            <?php if (empty($messages)): ?>
            <div class="empty-state">
                <div class="empty-icon">📬</div>
                <h3>No messages yet</h3>
                <p>They'll appear here when visitors use the contact form.</p>
            </div>
            <?php else: ?>
            <div class="messages-list">
                <?php foreach ($messages as $msg): ?>
                <div class="message-item <?php echo ($msg['read_status'] ?? 0) ? 'read' : 'unread'; ?>">
                    <div class="message-header">
                        <div class="message-meta">
                            <strong class="message-name"><?php echo escape($msg['name']); ?></strong>
                            <a href="mailto:<?php echo escape($msg['email']); ?>" class="message-email"><?php echo escape($msg['email']); ?></a>
                        </div>
                        <span class="message-date"><?php echo timeAgo($msg['created_at']); ?></span>
                    </div>
                    <p class="message-body"><?php echo nl2br(escape($msg['message'])); ?></p>
                    <div class="message-actions">
                        <?php if (!($msg['read_status'] ?? 0)): ?>
                        <form method="POST" class="inline-form">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="read_id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="action-btn edit">Mark Read</button>
                        </form>
                        <?php endif; ?>
                        <a href="mailto:<?php echo escape($msg['email']); ?>" class="action-btn edit">Reply</a>
                        <form method="POST" class="inline-form" onsubmit="return confirm('Delete this message?')">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="delete_id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="action-btn delete">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
