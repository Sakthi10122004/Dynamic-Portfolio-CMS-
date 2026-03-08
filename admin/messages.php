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
        $id = (int) $_POST['delete_id'];
        $db->query("DELETE FROM contact_messages WHERE id = ?", [$id], 'i');
        header('Location: ' . BASE_URL . '/admin/messages.php?deleted=1');
        exit;
    }

    if (isset($_POST['read_id'])) {
        $id = (int) $_POST['read_id'];
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
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
                    <i class="fa-solid fa-envelope" style="color:var(--accent)"></i> Messages
                </h1>
                <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">View and manage inquiries from your
                    portfolio visitors</p>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="flash flash-success">
                <i class="fa-solid fa-check-circle"></i> Message deleted successfully.
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-inbox"></i> Inbox</h2>
            </div>

            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-envelope-open"></i>
                    <p>No messages yet. They'll appear here when someone contacts you.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto">
                    <div class="activity-list">
                        <?php foreach ($messages as $msg): ?>
                            <div class="activity-item <?php echo ($msg['read_status'] ?? 0) ? '' : 'activity-unread'; ?>"
                                style="flex-direction:column; align-items:flex-start; gap:0.5rem; padding:1.25rem 1.5rem">
                                <div
                                    style="display:flex; justify-content:space-between; width:100%; align-items:center; margin-bottom:0.5rem">
                                    <div style="display:flex; align-items:center; gap:0.8rem">
                                        <div class="activity-icon"
                                            style="background:rgba(<?php echo ($msg['read_status'] ?? 0) ? '255,255,255,0.05' : '251,191,36,0.1'; ?>); color:<?php echo ($msg['read_status'] ?? 0) ? 'var(--text-muted)' : '#fbbf24'; ?>">
                                            <i
                                                class="fa-solid fa-envelope<?php echo ($msg['read_status'] ?? 0) ? '-open' : ''; ?>"></i>
                                        </div>
                                        <div>
                                            <div class="activity-title" style="font-size:0.95rem">
                                                <?php echo escape($msg['name']); ?></div>
                                            <div class="activity-meta"><a href="mailto:<?php echo escape($msg['email']); ?>"
                                                    style="color:var(--accent)"><?php echo escape($msg['email']); ?></a></div>
                                        </div>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:0.8rem">
                                        <span class="activity-time"><?php echo timeAgo($msg['created_at']); ?></span>
                                        <?php if (!($msg['read_status'] ?? 0)): ?>
                                            <span class="unread-dot"></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div
                                    style="padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border-radius:8px; border:1px solid var(--glass-border); width:100%; font-size:0.88rem; line-height:1.6; color:var(--text); margin-bottom:1rem">
                                    <?php echo nl2br(escape($msg['message'])); ?>
                                </div>

                                <div style="display:flex; gap:0.6rem; align-self:flex-end">
                                    <?php if (!($msg['read_status'] ?? 0)): ?>
                                        <form method="POST" class="inline-form">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="read_id" value="<?php echo $msg['id']; ?>">
                                            <button type="submit" class="btn-icon" title="Mark Read">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="mailto:<?php echo escape($msg['email']); ?>" class="btn-icon" title="Reply">
                                        <i class="fa-solid fa-reply"></i>
                                    </a>
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Delete this message?')">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="delete_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="btn-icon danger" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
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