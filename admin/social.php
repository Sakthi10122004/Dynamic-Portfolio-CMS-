<?php
/* ── admin/social.php — Manage Social Links ── */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
$auth->requireLogin();
$isAdminPage = true;
$db = Database::getInstance();

$flash = '';
$flashType = 'success';
$editItem = null;

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    verifyCsrf();
    $db->query("DELETE FROM social_links WHERE id=?", [(int)$_GET['delete']], 'i');
    header('Location: ' . BASE_URL . '/admin/social.php?deleted=1');
    exit();
}

// Edit load
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editItem = $db->getRow("SELECT * FROM social_links WHERE id=?", [(int)$_GET['edit']], 'i');
}

// Save (add or update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $platform = trim($_POST['platform']   ?? '');
    $url      = trim($_POST['url']        ?? '');
    $icon     = trim($_POST['icon_class'] ?? 'fa-solid fa-link');
    $order    = (int)($_POST['display_order'] ?? 0);
    $editId   = (int)($_POST['edit_id']   ?? 0);

    if (!$platform || !$url) {
        $flash = 'Platform and URL are required.';
        $flashType = 'error';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $flash = 'Please enter a valid URL (include https://).';
        $flashType = 'error';
    } else {
        if ($editId > 0) {
            $db->query(
                "UPDATE social_links SET platform=?, url=?, icon_class=?, display_order=? WHERE id=?",
                [$platform, $url, $icon, $order, $editId], 'sssii'
            );
            $flash = 'Social link updated!';
        } else {
            $db->insert(
                "INSERT INTO social_links (platform, url, icon_class, display_order) VALUES (?,?,?,?)",
                [$platform, $url, $icon, $order], 'sssi'
            );
            $flash = 'Social link added!';
        }
        header('Location: ' . BASE_URL . '/admin/social.php?saved=1');
        exit();
    }
}

if (isset($_GET['saved'])) $flash = 'Saved successfully!';
if (isset($_GET['deleted'])) { $flash = 'Link deleted.'; $flashType = 'info'; }

$links = $db->getRows("SELECT * FROM social_links ORDER BY display_order, id") ?: [];
$pageTitle = 'Social Links';
require_once '../includes/header.php';

// Common FA icon suggestions
$iconSuggestions = [
    'fab fa-github' => 'GitHub',
    'fab fa-linkedin' => 'LinkedIn',
    'fab fa-x-twitter' => 'X / Twitter',
    'fab fa-instagram' => 'Instagram',
    'fab fa-youtube' => 'YouTube',
    'fab fa-dribbble' => 'Dribbble',
    'fab fa-behance' => 'Behance',
    'fab fa-dev' => 'Dev.to',
    'fab fa-stack-overflow' => 'Stack Overflow',
    'fa-solid fa-globe' => 'Website',
    'fa-solid fa-envelope' => 'Email',
];
?>
<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
            <i class="fa-solid fa-share-nodes" style="color:var(--accent)"></i> Social Links
        </h1>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Manage social media profiles and external links</p>
      </div>
      <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn-glass btn-sm">View Site →</a>
    </div>
    <?php if ($flash): ?>
    <div class="flash flash-<?php echo $flashType; ?>">
      <i class="fa-solid fa-circle-check"></i> <?php echo escape($flash); ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:1.5rem;align-items:start">

      <!-- Add / Edit Form -->
      <div class="admin-card">
        <div class="admin-card-header">
            <h2><i class="fa-solid fa-<?php echo $editItem ? 'pen' : 'plus'; ?>"></i> <?php echo $editItem ? 'Edit Link' : 'Add New Link'; ?></h2>
        </div>
        <div class="admin-card-body">
        <form method="POST" class="admin-form">
          <?php echo csrfField(); ?>
          <?php if ($editItem): ?>
          <input type="hidden" name="edit_id" value="<?php echo (int)$editItem['id']; ?>">
          <?php endif; ?>

          <div class="field">
            <label for="platform">Platform Name <span style="color:#f87171">*</span></label>
            <input type="text" id="platform" name="platform" required
                   value="<?php echo escape($editItem['platform'] ?? ''); ?>"
                   placeholder="e.g. GitHub">
          </div>

          <div class="field">
            <label for="url">Profile URL <span style="color:#f87171">*</span></label>
            <input type="url" id="url" name="url" required
                   value="<?php echo escape($editItem['url'] ?? ''); ?>"
                   placeholder="https://github.com/yourusername">
          </div>

          <div class="field">
            <label for="icon_class">Font Awesome Icon Class</label>
            <input type="text" id="icon_class" name="icon_class"
                   value="<?php echo escape($editItem['icon_class'] ?? 'fab fa-github'); ?>"
                   placeholder="fab fa-github">
            <p class="form-hint" style="font-size:0.7rem">
              Quick picks:
              <?php foreach($iconSuggestions as $cls => $lbl): ?>
              <button type="button" onclick="document.getElementById('icon_class').value='<?php echo $cls; ?>'"
                style="background:none;border:none;color:var(--accent);cursor:pointer;text-decoration:underline;padding:0 .15rem"><?php echo $lbl; ?></button>
              <?php endforeach; ?>
            </p>
          </div>

          <div class="field">
            <label for="display_order">Display Order</label>
            <input type="number" id="display_order" name="display_order" min="0"
                   value="<?php echo (int)($editItem['display_order'] ?? 0); ?>" placeholder="0">
            <p class="form-hint">Lower numbers appear first.</p>
          </div>

          <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            <button type="submit" class="btn-primary">
              <i class="fa-solid fa-floppy-disk"></i>
              <?php echo $editItem ? 'Update Link' : 'Add Link'; ?>
            </button>
            <?php if ($editItem): ?>
            <a href="<?php echo BASE_URL; ?>/admin/social.php" class="btn-glass">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
        </div>
      </div>

      <!-- List -->
      <div class="admin-card">
        <div class="admin-card-header">
            <h2><i class="fa-solid fa-list-nodes"></i> Links (<?php echo count($links); ?>)</h2>
        </div>
        <div class="admin-card-body" style="padding:0">
        <?php if ($links): ?>
        <table class="admin-table">
          <thead><tr><th>Platform</th><th>Icon</th><th>Order</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($links as $l): ?>
          <tr>
            <td>
              <strong><?php echo escape($l['platform']); ?></strong><br>
              <span style="font-size:.75rem;color:var(--text-muted)"><?php echo escape(substr($l['url'],0,35)).'...'; ?></span>
            </td>
            <td><i class="<?php echo escape($l['icon_class']); ?>"></i></td>
            <td><?php echo (int)$l['display_order']; ?></td>
            <td>
              <div class="actions-cell">
                <a href="?edit=<?php echo $l['id']; ?>" class="btn-icon" title="Edit">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <a href="?delete=<?php echo $l['id']; ?>&csrf_token=<?php echo csrfToken(); ?>"
                   class="btn-icon danger" title="Delete"
                   onclick="return confirm('Delete this social link?')">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-share-nodes"></i>
          <p>No social links yet. Add one using the form.</p>
        </div>
        <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
