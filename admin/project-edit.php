<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project = $id ? getProject($id) : null;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $github_link = trim($_POST['github_link'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if (empty($title)) {
        $error = 'Project title is required';
    } else {
        $image = $project['image'] ?? null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['image'], $image);
            if ($upload['success']) {
                $image = $upload['filename'];
            } else {
                $error = $upload['error'];
            }
        }
        
        if (empty($error)) {
            if ($id) {
                $sql = "UPDATE projects SET title = ?, description = ?, github_link = ?, image = ?, featured = ? WHERE id = ?";
                $db->query($sql, [$title, $description, $github_link, $image, $featured, $id], 'ssssii');
            } else {
                $sql = "INSERT INTO projects (title, description, github_link, image, featured) VALUES (?, ?, ?, ?, ?)";
                $id = $db->insert($sql, [$title, $description, $github_link, $image, $featured], 'ssssi');
            }
            
            header('Location: ' . BASE_URL . '/admin/projects.php?saved=1');
            exit;
        }
    }
}

$unreadMessages = $db->getRow("SELECT COUNT(*) as count FROM contact_messages WHERE read_status = 0")['count'] ?? 0;

$pageTitle = $id ? 'Edit Project' : 'New Project';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-header">
            <h1><?php echo $id ? 'Edit Project' : 'New Project'; ?></h1>
            <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="btn secondary">← Back</a>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="edit-form bento-card">
            <?php echo csrfField(); ?>
            
            <div class="form-group">
                <label for="title">Project Title</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo escape($project['title'] ?? $_POST['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6" required><?php echo escape($project['description'] ?? $_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="github_link">GitHub Link (optional)</label>
                <input type="url" id="github_link" name="github_link" 
                       value="<?php echo escape($project['github_link'] ?? $_POST['github_link'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="image">Project Image</label>
                <?php if ($project && !empty($project['image'])): ?>
                <div class="current-image">
                    <img src="<?php echo UPLOAD_URL . escape($project['image']); ?>" alt="Current image">
                    <p>Current image. Upload new to replace.</p>
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                <small>Max: 5MB. Formats: JPG, PNG, WebP, GIF</small>
            </div>
            
            <div class="form-group checkbox">
                <label>
                    <input type="checkbox" name="featured" 
                           <?php echo (($project['featured'] ?? false) || isset($_POST['featured'])) ? 'checked' : ''; ?>>
                    Featured Project
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn primary">Save Project</button>
                <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>