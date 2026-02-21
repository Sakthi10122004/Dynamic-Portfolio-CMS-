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
        <div class="alert alert-success">Skill deleted successfully.</div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Skill saved successfully.</div>
        <?php endif; ?>

        <div class="data-card bento-card">
            <?php if (empty($skills)): ?>
            <div class="empty-state">
                <div class="empty-icon">💡</div>
                <h3>No skills yet</h3>
                <p>Add your technical skills to showcase expertise.</p>
                <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php" class="btn primary">Add First Skill</a>
            </div>
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
                        <td><span class="category-badge <?php echo escape($skill['category']); ?>"><?php echo escape(ucfirst($skill['category'])); ?></span></td>
                        <td><?php echo (int)$skill['display_order']; ?></td>
                        <td class="actions">
                            <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php?id=<?php echo $skill['id']; ?>" class="action-btn edit">Edit</a>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this skill?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $skill['id']; ?>">
                                <button type="submit" class="action-btn delete">Delete</button>
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

<?php require_once '../includes/footer.php'; ?>
