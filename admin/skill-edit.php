<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$skill = $id ? getSkill($id) : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    if (empty($name) || empty($category)) {
        $error = 'Name and category are required';
    } else {
        if ($id) {
            $db->query(
                "UPDATE skills SET name = ?, category = ?, display_order = ? WHERE id = ?",
                [$name, $category, $display_order, $id],
                'ssii'
            );
        } else {
            $db->insert(
                "INSERT INTO skills (name, category, display_order) VALUES (?, ?, ?)",
                [$name, $category, $display_order],
                'ssi'
            );
        }
        
        header('Location: ' . BASE_URL . '/admin/skills.php?saved=1');
        exit;
    }
}

$pageTitle = $id ? 'Edit Skill' : 'New Skill';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-header">
            <h1><?php echo $id ? 'Edit Skill' : 'New Skill'; ?></h1>
            <a href="<?php echo BASE_URL; ?>/admin/skills.php" class="btn secondary">← Back</a>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="edit-form bento-card">
            <?php echo csrfField(); ?>
            
            <div class="form-group">
                <label for="name">Skill Name</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo escape($skill['name'] ?? $_POST['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select category</option>
                    <?php 
                    $cats = ['frontend' => 'Frontend', 'backend' => 'Backend', 'devops' => 'DevOps'];
                    $currentCat = $skill['category'] ?? $_POST['category'] ?? '';
                    foreach ($cats as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php echo $currentCat === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="display_order">Display Order</label>
                <input type="number" id="display_order" name="display_order" 
                       value="<?php echo $skill['display_order'] ?? $_POST['display_order'] ?? 0; ?>" min="0">
                <small>Lower numbers appear first</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn primary">Save Skill</button>
                <a href="<?php echo BASE_URL; ?>/admin/skills.php" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
