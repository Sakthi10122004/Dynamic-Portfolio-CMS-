<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

$db = Database::getInstance();

// Handle mark as read
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $db->query("UPDATE contact_messages SET read_status = 1 WHERE id = ?", [$id], 'i');
    header('Location: ' . BASE_URL . '/admin/messages.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM contact_messages WHERE id = ?", [$id], 'i');
    header('Location: ' . BASE_URL . '/admin/messages.php?deleted=1');
    exit;
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
        <div class="success-message">Message deleted</div>
        <?php endif; ?>
        
        <div class="data-card bento-card">
            <?php if (empty($messages)): ?>
            <p class="empty-text">No messages yet. They'll appear here when visitors use the contact form.</p>
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
                        <a href="<?php echo BASE_URL; ?>/admin/messages.php?read=<?php echo $msg['id']; ?>" class="action-btn edit">Mark Read</a>
                        <?php endif; ?>
                        <a href="mailto:<?php echo escape($msg['email']); ?>" class="action-btn edit">Reply</a>
                        <a href="<?php echo BASE_URL; ?>/admin/messages.php?delete=<?php echo $msg['id']; ?>" 
                           class="action-btn delete"
                           onclick="return confirm('Delete this message?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
