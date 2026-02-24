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
    $id      = (int)$_POST['delete_id'];
    $project = getProject($id);
    if ($project && !empty($project['image'])) {
        $imagePath = UPLOAD_DIR . $project['image'];
        if (file_exists($imagePath)) unlink($imagePath);
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
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
                    <i class="fa-solid fa-rocket" style="color:var(--accent)"></i> Projects
                </h1>
                <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Manage your portfolio projects and case studies</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/project-edit.php" class="btn-primary">
                <i class="fa-solid fa-plus"></i> New Project
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-check-circle"></i> Project deleted successfully.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-check-circle"></i> Project saved successfully.
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-list"></i> All Projects</h2>
            </div>
            <div class="admin-card-body" style="padding:0">
            <?php if (empty($projects)): ?>
            <div class="empty-state">
                <div class="empty-icon">🚀</div>
                <h3>No projects yet</h3>
                <p>Share your work with the world.</p>
                <a href="<?php echo BASE_URL; ?>/admin/project-edit.php" class="btn primary">Create First Project</a>
            </div>
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
                            <span class="has-image" title="Has image">📸</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $project['featured'] ? '<span class="badge badge-featured">⭐ Featured</span>' : '<span class="text-muted">—</span>'; ?></td>
                        <td><?php echo formatDate($project['created_at']); ?></td>
                        <td class="actions-cell">
                            <a href="<?php echo BASE_URL; ?>/admin/project-edit.php?id=<?php echo $project['id']; ?>" 
                               class="btn-icon" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this project? This cannot be undone.')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $project['id']; ?>">
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