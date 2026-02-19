<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

$db = Database::getInstance();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM skills WHERE id = ?", [$id], 'i');
    header('Location: ' . BASE_URL . '/admin/skills.php?deleted=1');
    exit;
}

$skills = getSkills();

$pageTitle = 'Manage Skills';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-header">
            <h1>Skills</h1>
            <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php" class="btn primary">+ New Skill</a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="success-message">Skill deleted successfully</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['saved'])): ?>
        <div class="success-message">Skill saved successfully</div>
        <?php endif; ?>
        
        <div class="data-card bento-card">
            <?php if (empty($skills)): ?>
            <p class="empty-text">No skills yet. <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php">Add your first skill</a></p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($skills as $skill): ?>
                    <tr>
                        <td><strong><?php echo escape($skill['name']); ?></strong></td>
                        <td><span class="category-badge"><?php echo escape(ucfirst($skill['category'])); ?></span></td>
                        <td><?php echo $skill['display_order']; ?></td>
                        <td class="actions">
                            <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php?id=<?php echo $skill['id']; ?>" class="action-btn edit">Edit</a>
                            <a href="<?php echo BASE_URL; ?>/admin/skills.php?delete=<?php echo $skill['id']; ?>" 
                               class="action-btn delete"
                               onclick="return confirm('Delete this skill?')">Delete</a>
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
