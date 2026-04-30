<?php
/* ── admin/hero.php — Manage Hero Section ── */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
$auth->requireLogin();
$isAdminPage = true;
$db = Database::getInstance();

$flash = '';
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf();
  $title = trim($_POST['title'] ?? '');
  $subtitle = trim($_POST['subtitle'] ?? '');
  $stat_1 = trim($_POST['stat_1'] ?? 'Projects');
  $stat_2 = trim($_POST['stat_2'] ?? 'Skills');
  $stat_3 = trim($_POST['stat_3'] ?? '3+');
  if (!$title) {
    $flash = 'Title is required.';
    $flashType = 'error';
  } else {
    $exists = $db->getRow("SELECT id FROM hero WHERE id = 1");
    if ($exists) {
      $db->query(
        "UPDATE hero SET title=?, subtitle=? WHERE id=1",
        [$title, $subtitle],
        'ss'
      );
    } else {
      $db->insert(
        "INSERT INTO hero (id, title, subtitle) VALUES (1, ?, ?)",
        [$title, $subtitle],
        'ss'
      );
    }
    
    // Update the dynamic stats in the settings table
    $db->query("UPDATE settings SET setting_value=? WHERE setting_key='stat_1_label'", [$stat_1], 's');
    $db->query("UPDATE settings SET setting_value=? WHERE setting_key='stat_2_label'", [$stat_2], 's');
    $db->query("UPDATE settings SET setting_value=? WHERE setting_key='stat_3_value'", [$stat_3], 's');
    
    $flash = 'Hero section updated successfully!';
  }
}

$hero = getHero();
$pageTitle = 'Hero Section';
require_once '../includes/header.php';
?>
<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
          <i class="fa-solid fa-star" style="color:var(--accent)" aria-hidden="true"></i> Hero Section
        </h1>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Manage the main title and tagline shown at
          the top of your portfolio</p>
      </div>
      <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn-glass btn-sm">View Site →</a>
    </div>
    <?php if ($flash): ?>
      <div class="flash flash-<?php echo $flashType; ?>">
        <i class="fa-solid fa-<?php echo $flashType === 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
        <?php echo escape($flash); ?>
      </div>
    <?php endif; ?>
    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fa-solid fa-star"></i> Hero Settings</h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" class="admin-form">
          <?php echo csrfField(); ?>
          <div class="field">
            <label for="title">Hero Title <span style="color:#f87171">*</span></label>
            <input type="text" id="title" name="title" required value="<?php echo escape($hero['title'] ?? ''); ?>"
              placeholder="e.g. Building Digital Experiences">
            <p class="form-hint">Main headline shown in the hero section.</p>
          </div>
          <div class="field">
            <label for="subtitle">Subtitle / Tagline</label>
            <textarea id="subtitle" name="subtitle" rows="3"
              placeholder="e.g. Full-Stack Developer crafting modern web solutions..."><?php echo escape($hero['subtitle'] ?? ''); ?></textarea>
            <p class="form-hint">A short description shown below the title.</p>
          </div>
          
          <div class="form-row">
            <div class="field">
              <label for="stat_3">Experience Value</label>
              <input type="text" id="stat_3" name="stat_3" value="<?php echo escape(getSetting('stat_3_value', '3+')); ?>" placeholder="e.g. 3+">
              <p class="form-hint">Shown as Years Exp.</p>
            </div>
            <div class="field">
              <label for="stat_1">Projects Label</label>
              <input type="text" id="stat_1" name="stat_1" value="<?php echo escape(getSetting('stat_1_label', 'Projects')); ?>" placeholder="e.g. Projects">
            </div>
            <div class="field">
              <label for="stat_2">Skills Label</label>
              <input type="text" id="stat_2" name="stat_2" value="<?php echo escape(getSetting('stat_2_label', 'Skills')); ?>" placeholder="e.g. Skills">
            </div>
          </div>
          <div class="form-actions" style="margin-top:1.5rem">
            <button type="submit" class="btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>