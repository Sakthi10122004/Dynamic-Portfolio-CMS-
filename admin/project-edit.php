<?php
/* ── admin/project-edit.php — Add / Edit Project ── */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$project = $id ? getProject($id) : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf();
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $tech_stack = trim($_POST['tech_stack'] ?? '');
  $github_link = trim($_POST['github_link'] ?? '');
  $demo_link = trim($_POST['demo_link'] ?? '');
  $featured = isset($_POST['featured']) ? 1 : 0;

  if (empty($title)) {
    $error = 'Project title is required.';
  } else {
    $image = $project['image'] ?? null;
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
        $db->query(
          "UPDATE projects SET title=?, description=?, tech_stack=?, github_link=?, demo_link=?, image=?, featured=? WHERE id=?",
          [$title, $description, $tech_stack, $github_link, $demo_link, $image, $featured, $id],
          'ssssssii'
        );
      } else {
        $id = $db->insert(
          "INSERT INTO projects (title, description, tech_stack, github_link, demo_link, image, featured) VALUES (?,?,?,?,?,?,?)",
          [$title, $description, $tech_stack, $github_link, $demo_link, $image, $featured],
          'ssssssi'
        );
      }
      header('Location: ' . BASE_URL . '/admin/projects.php?saved=1');
      exit;
    }
  }
}

$pageTitle = $id ? 'Edit Project' : 'New Project';
require_once '../includes/header.php';
?>
<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
          <i class="fa-solid fa-<?php echo $id ? 'pen' : 'plus'; ?>" style="color:var(--accent)"></i>
          <?php echo $id ? 'Edit Project' : 'New Project'; ?>
        </h1>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Showcase your best work with images and
          live demos</p>
      </div>
      <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="btn-glass btn-sm">← Back to Projects</a>
    </div>

    <?php if ($error): ?>
      <div class="flash flash-error">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo escape($error); ?>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fa-solid fa-rocket"></i> Project Details</h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data" class="admin-form">
          <?php echo csrfField(); ?>

          <div class="form-row">
            <div class="field">
              <label for="title">Project Title <span style="color:#f87171">*</span></label>
              <input type="text" id="title" name="title" required
                value="<?php echo escape($project['title'] ?? $_POST['title'] ?? ''); ?>">
            </div>
            <div class="field">
              <label for="tech_stack">Tech Stack</label>
              <input type="text" id="tech_stack" name="tech_stack" placeholder="PHP, MySQL, JavaScript, React"
                value="<?php echo escape($project['tech_stack'] ?? $_POST['tech_stack'] ?? ''); ?>">
              <p class="form-hint">Comma-separated list of technologies.</p>
            </div>
          </div>

          <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description"
              rows="5"><?php echo escape($project['description'] ?? $_POST['description'] ?? ''); ?></textarea>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="github_link">GitHub Link</label>
              <input type="url" id="github_link" name="github_link" placeholder="https://github.com/..."
                value="<?php echo escape($project['github_link'] ?? $_POST['github_link'] ?? ''); ?>">
            </div>
            <div class="field">
              <label for="demo_link">Live Demo Link</label>
              <input type="url" id="demo_link" name="demo_link" placeholder="https://yourproject.com"
                value="<?php echo escape($project['demo_link'] ?? $_POST['demo_link'] ?? ''); ?>">
            </div>
          </div>

          <div class="field">
            <label for="image">Project Image</label>
            <?php if ($project && !empty($project['image'])): ?>
              <div class="current-image-wrap">
                <img src="<?php echo UPLOAD_URL . escape($project['image']); ?>" alt="Current">
                <p class="form-hint">Current image. Upload a new one to replace it.</p>
              </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
              data-preview="img-preview">
            <img id="img-preview" style="display:none;margin-top:.5rem" class="img-preview" alt="Preview">
            <p class="form-hint">Max 5MB. Formats: JPG, PNG, WebP, GIF.</p>
          </div>

          <div class="field" style="display:flex;align-items:center;gap:.6rem">
            <input type="checkbox" id="featured" name="featured" <?php echo ($project['featured'] ?? false) || isset($_POST['featured']) ? 'checked' : ''; ?>>
            <label for="featured" style="margin-bottom:0;cursor:pointer">⭐ Mark as Featured Project</label>
          </div>

          <div class="form-actions" style="margin-top:1.5rem">
            <button type="submit" class="btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> <?php echo $id ? 'Update Project' : 'Create Project'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="btn-glass">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>