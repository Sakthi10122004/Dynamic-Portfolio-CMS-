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
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
                    <i class="fa-solid fa-code" style="color:var(--accent)"></i> Skills
                </h1>
                <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Manage your technical expertise and proficiency levels</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php" class="btn-primary">
                <i class="fa-solid fa-plus"></i> New Skill
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-check-circle"></i> Skill deleted successfully.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-check-circle"></i> Skill saved successfully.
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-list-check"></i> All Skills</h2>
            </div>
            <div class="admin-card-body" style="padding:0">
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
                        <td><span class="badge badge-featured"><?php echo escape(ucfirst($skill['category'])); ?></span></td>
                        <td><?php echo (int)$skill['display_order']; ?></td>
                        <td class="actions-cell">
                            <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php?id=<?php echo $skill['id']; ?>" 
                               class="btn-icon" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this skill?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $skill['id']; ?>">
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
