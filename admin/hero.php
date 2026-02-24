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
    $title    = trim($_POST['title']    ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    if (!$title) {
        $flash = 'Title is required.';
        $flashType = 'error';
    } else {
        $exists = $db->getRow("SELECT id FROM hero WHERE id = 1");
        if ($exists) {
            $db->query(
                "UPDATE hero SET title=?, subtitle=? WHERE id=1",
                [$title, $subtitle], 'ss'
            );
        } else {
            $db->insert(
                "INSERT INTO hero (id, title, subtitle) VALUES (1, ?, ?)",
                [$title, $subtitle], 'ss'
            );
        }
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
      <h1><i class="fa-solid fa-star" aria-hidden="true"></i> Hero Section</h1>
      <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn-glass btn-sm">View Site →</a>
    </div>
    <?php if ($flash): ?>
    <div class="flash flash-<?php echo $flashType; ?>">
      <i class="fa-solid fa-<?php echo $flashType === 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
      <?php echo escape($flash); ?>
    </div>
    <?php endif; ?>
    <div class="admin-card">
      <h2>Edit Hero Section</h2>
      <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem">This content appears in the main hero area at the top of your portfolio.</p>
      <form method="POST" class="admin-form">
        <?php echo csrfField(); ?>
        <div class="field">
          <label for="title">Hero Title <span style="color:#f87171">*</span></label>
          <input type="text" id="title" name="title" required
                 value="<?php echo escape($hero['title'] ?? ''); ?>"
                 placeholder="e.g. Building Digital Experiences">
          <p class="form-hint">Main headline shown in the hero section.</p>
        </div>
        <div class="field">
          <label for="subtitle">Subtitle / Tagline</label>
          <textarea id="subtitle" name="subtitle" rows="3"
                    placeholder="e.g. Full-Stack Developer crafting modern web solutions..."><?php echo escape($hero['subtitle'] ?? ''); ?></textarea>
          <p class="form-hint">A short description shown below the title.</p>
        </div>
        <button type="submit" class="btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Save Changes
        </button>
      </form>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
