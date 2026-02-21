<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();
$profile = getProfile();

// Get statistics
$stats = [
    'projects' => $db->getRow("SELECT COUNT(*) as count FROM projects")['count'] ?? 0,
    'skills'   => $db->getRow("SELECT COUNT(*) as count FROM skills")['count'] ?? 0,
    'notes'    => $db->getRow("SELECT COUNT(*) as count FROM notes")['count'] ?? 0,
    'messages' => $db->getRow("SELECT COUNT(*) as count FROM contact_messages WHERE read_status = 0")['count'] ?? 0,
];

$recentProjects = $db->getRows("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
$recentMessages = $db->getRows("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$unreadMessages = $stats['messages'];

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-header">
            <div class="admin-welcome">
                <?php if (!empty($profile['avatar'])): ?>
                <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>" alt="Avatar" class="admin-avatar">
                <?php endif; ?>
                <div>
                    <h1>Dashboard</h1>
                    <span class="welcome-text">Welcome back, <?php echo escape($auth->getCurrentUser()['username']); ?></span>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>/" class="btn secondary btn-sm" target="_blank">View Site →</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card bento-card">
                <div class="stat-value"><?php echo $stats['projects']; ?></div>
                <div class="stat-label">Projects</div>
            </div>
            <div class="stat-card bento-card">
                <div class="stat-value"><?php echo $stats['skills']; ?></div>
                <div class="stat-label">Skills</div>
            </div>
            <div class="stat-card bento-card">
                <div class="stat-value"><?php echo $stats['notes']; ?></div>
                <div class="stat-label">Notes</div>
            </div>
            <div class="stat-card bento-card">
                <div class="stat-value"><?php echo $stats['messages']; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="recent-projects bento-card">
                <div class="card-header">
                    <h2>Recent Projects</h2>
                    <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="view-all">View All →</a>
                </div>
                <div class="recent-list">
                    <?php if (empty($recentProjects)): ?>
                    <p class="empty-text">No projects yet. <a href="<?php echo BASE_URL; ?>/admin/project-edit.php">Create one</a></p>
                    <?php else: ?>
                    <?php foreach ($recentProjects as $project): ?>
                    <div class="recent-item">
                        <div class="item-info">
                            <span class="item-title"><?php echo escape($project['title']); ?></span>
                            <span class="item-date"><?php echo timeAgo($project['created_at']); ?></span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/admin/project-edit.php?id=<?php echo $project['id']; ?>" class="edit-link">Edit</a>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="recent-messages bento-card">
                <div class="card-header">
                    <h2>Recent Messages</h2>
                    <a href="<?php echo BASE_URL; ?>/admin/messages.php" class="view-all">View All →</a>
                </div>
                <div class="recent-list">
                    <?php if (empty($recentMessages)): ?>
                    <p class="empty-text">No messages yet.</p>
                    <?php else: ?>
                    <?php foreach ($recentMessages as $message): ?>
                    <div class="recent-item <?php echo ($message['read_status'] ?? 0) ? '' : 'unread'; ?>">
                        <div class="item-info">
                            <span class="message-sender"><?php echo escape($message['name']); ?></span>
                            <span class="message-preview"><?php echo escape(truncate($message['message'], 50)); ?></span>
                        </div>
                        <span class="item-date"><?php echo timeAgo($message['created_at']); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>