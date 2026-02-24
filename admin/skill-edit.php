<?php
/* ── admin/skill-edit.php — Add / Edit Skill ── */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
$auth->requireLogin();
$isAdminPage = true;

$db    = Database::getInstance();
$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$skill = $id ? getSkill($id) : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name          = trim($_POST['name']          ?? '');
    $category      = trim($_POST['category']      ?? '');
    $percentage    = min(100, max(0, (int)($_POST['percentage'] ?? 80)));
    $display_order = (int)($_POST['display_order'] ?? 0);

    if (empty($name) || empty($category)) {
        $error = 'Skill name and category are required.';
    } else {
        if ($id) {
            $db->query(
                "UPDATE skills SET name=?, category=?, percentage=?, display_order=? WHERE id=?",
                [$name, $category, $percentage, $display_order, $id], 'ssiii'
            );
        } else {
            $db->insert(
                "INSERT INTO skills (name, category, percentage, display_order) VALUES (?,?,?,?)",
                [$name, $category, $percentage, $display_order], 'ssii'
            );
        }
        header('Location: ' . BASE_URL . '/admin/skills.php?saved=1');
        exit;
    }
}

$pageTitle = $id ? 'Edit Skill' : 'New Skill';
require_once '../includes/header.php';

$currentCat = $skill['category'] ?? $_POST['category'] ?? '';
$currentPct = $skill['percentage'] ?? $_POST['percentage'] ?? 80;
$cats = ['frontend' => 'Frontend', 'backend' => 'Backend', 'devops' => 'DevOps', 'other' => 'Other'];
?>
<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
            <i class="fa-solid fa-<?php echo $id ? 'pen' : 'plus'; ?>" style="color:var(--accent)"></i> <?php echo $id ? 'Edit Skill' : 'New Skill'; ?>
        </h1>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Define your proficiency levels and expertise areas</p>
      </div>
      <a href="<?php echo BASE_URL; ?>/admin/skills.php" class="btn-glass btn-sm">← Back</a>
    </div>
    
    <?php if ($error): ?>
    <div class="flash flash-error">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo escape($error); ?>
    </div>
    <?php endif; ?>

    <div class="admin-card" style="max-width:520px">
      <div class="admin-card-header">
        <h2><i class="fa-solid fa-bolt"></i> Skill Configuration</h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" class="admin-form">
        <?php echo csrfField(); ?>

        <div class="field">
          <label for="name">Skill Name <span style="color:#f87171">*</span></label>
          <input type="text" id="name" name="name" required
                 placeholder="e.g. JavaScript"
                 value="<?php echo escape($skill['name'] ?? $_POST['name'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="category">Category <span style="color:#f87171">*</span></label>
          <select id="category" name="category" required>
            <option value="">Select category</option>
            <?php foreach ($cats as $val => $label): ?>
            <option value="<?php echo $val; ?>" <?php echo $currentCat === $val ? 'selected' : ''; ?>>
              <?php echo $label; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="percentage-range">
            Proficiency: <span class="pct-display" id="percentage-display"><?php echo $currentPct; ?>%</span>
          </label>
          <input type="range" id="percentage-range" name="percentage"
                 min="0" max="100" step="5"
                 value="<?php echo $currentPct; ?>">
          <p class="form-hint">Drag to set your skill proficiency level (0–100%).</p>
        </div>

        <div class="field">
          <label for="display_order">Display Order</label>
          <input type="number" id="display_order" name="display_order" min="0"
                 value="<?php echo $skill['display_order'] ?? $_POST['display_order'] ?? 0; ?>">
          <p class="form-hint">Lower numbers appear first within the category.</p>
        </div>

        <div class="form-actions" style="margin-top:1.5rem">
          <button type="submit" class="btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> <?php echo $id ? 'Update Skill' : 'Create Skill'; ?>
          </button>
          <a href="<?php echo BASE_URL; ?>/admin/skills.php" class="btn-glass">Cancel</a>
        </div>
      </form>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
