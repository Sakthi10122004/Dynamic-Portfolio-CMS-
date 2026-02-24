<?php
/* ── admin/about.php — Manage About Section ── */
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
    $content = trim($_POST['content'] ?? '');
    if (!$content) {
        $flash = 'Content cannot be empty.';
        $flashType = 'error';
    } else {
        $exists = $db->getRow("SELECT id FROM about WHERE id = 1");
        if ($exists) {
            $db->query("UPDATE about SET content=? WHERE id=1", [$content], 's');
        } else {
            $db->insert("INSERT INTO about (id, content) VALUES (1, ?)", [$content], 's');
        }
        $flash = 'About section updated successfully!';
    }
}

$about = getAbout();
$pageTitle = 'About Section';
require_once '../includes/header.php';
?>
<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
            <i class="fa-solid fa-user" style="color:var(--accent)"></i> About Section
        </h1>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Edit the professional biography shown on your homepage</p>
      </div>
      <a href="<?php echo BASE_URL; ?>/#about" target="_blank" class="btn-glass btn-sm">Preview →</a>
    </div>
    <?php if ($flash): ?>
    <div class="flash flash-<?php echo $flashType; ?>">
      <i class="fa-solid fa-<?php echo $flashType === 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
      <?php echo escape($flash); ?>
    </div>
    <?php endif; ?>
    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fa-solid fa-pen-to-square"></i> Edit Biography</h2>
      </div>
      <div class="admin-card-body">
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem">Write about yourself — your background, skills, and what drives you. New lines are preserved as paragraphs.</p>
      <form method="POST" class="admin-form">
        <?php echo csrfField(); ?>
        <div class="field">
          <label for="content">About Content <span style="color:#f87171">*</span></label>
          <textarea id="content" name="content" rows="12" required
                    placeholder="Write about yourself here..."><?php echo escape($about['content'] ?? ''); ?></textarea>
          <p class="form-hint">Use new lines to separate paragraphs. Plain text only — no HTML needed.</p>
        </div>
        <button type="submit" class="btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Save Changes
        </button>
      </form>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
