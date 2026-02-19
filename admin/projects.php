<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

$db = Database::getInstance();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $project = getProject($id);
    if ($project && !empty($project['image'])) {
        $imagePath = UPLOAD_DIR . $project['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $db->query("DELETE FROM projects WHERE id = ?", [$id], 'i');
    header('Location: ' . BASE_URL . '/admin/projects.php?deleted=1');
    exit;
}

$projects = getProjects();
$unreadMessages = $db->getRow("SELECT COUNT(*) as count FROM contact_messages WHERE read_status = 0")['count'] ?? 0;

$pageTitle = 'Manage Projects';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-header">
            <h1>Projects</h1>
            <a href="<?php echo BASE_URL; ?>/admin/project-edit.php" class="btn primary">+ New Project</a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="success-message">Project deleted successfully</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['saved'])): ?>
        <div class="success-message">Project saved successfully</div>
        <?php endif; ?>
        
        <div class="data-card bento-card">
            <?php if (empty($projects)): ?>
            <p class="empty-text">No projects yet. <a href="<?php echo BASE_URL; ?>/admin/project-edit.php">Create your first project</a></p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Featured</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <strong><?php echo escape($project['title']); ?></strong>
                            <?php if (!empty($project['image'])): ?>
                            <span class="has-image">📸</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $project['featured'] ? '⭐' : '—'; ?></td>
                        <td><?php echo formatDate($project['created_at']); ?></td>
                        <td class="actions">
                            <a href="<?php echo BASE_URL; ?>/admin/project-edit.php?id=<?php echo $project['id']; ?>" class="action-btn edit">Edit</a>
                            <a href="<?php echo BASE_URL; ?>/admin/projects.php?delete=<?php echo $project['id']; ?>" 
                               class="action-btn delete" 
                               onclick="return confirm('Delete this project?')">Delete</a>
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