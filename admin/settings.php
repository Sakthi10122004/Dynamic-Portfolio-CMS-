<?php
/* ── admin/settings.php — Manage Dynamic Labels ── */
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
    
    // Check if migration has run
    $tableExists = $db->query("SHOW TABLES LIKE 'settings'");
    if (!$tableExists || $tableExists->num_rows === 0) {
        $flash = 'Settings table not found. Please run the migration script first.';
        $flashType = 'error';
    } else {
        $settingsInput = $_POST['settings'] ?? [];
        $success = true;
        
        foreach ($settingsInput as $key => $value) {
            $val = trim($value);
            $result = $db->query(
                "UPDATE settings SET setting_value = ? WHERE setting_key = ?",
                [$val, $key],
                'ss'
            );
            if ($result === false) {
                $success = false;
            }
        }
        
        if ($success) {
            $flash = 'Settings updated successfully.';
        } else {
            $flash = 'Some settings failed to update.';
            $flashType = 'error';
        }
    }
}

// Fetch settings assuming migration ran
$settingsData = [];
$tableExists = $db->query("SHOW TABLES LIKE 'settings'");
if ($tableExists && $tableExists->num_rows > 0) {
    $rows = $db->getRows("SELECT * FROM settings ORDER BY setting_group, setting_key");
    if ($rows) {
        foreach ($rows as $row) {
            $settingsData[$row['setting_group']][] = $row;
        }
    }
}

$pageTitle = 'Site Settings';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700">
                    <i class="fa-solid fa-gear" style="color:#a78bfa"></i> Site Settings
                </h1>
                <p style="font-size:.83rem;color:#6e6590;margin-top:.1rem">Manage all text labels and dynamic content across your portfolio</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn-glass btn-sm">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View Site
            </a>
        </div>

        <?php if (empty($settingsData)): ?>
            <div class="flash flash-error" style="margin-bottom:1.5rem">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <strong>Migration required:</strong> The settings table is missing or empty. Please visit <a href="<?php echo BASE_URL; ?>/migrate_v4.php" style="color:white;text-decoration:underline;font-weight:600">migrate_v4.php</a> to set it up.
            </div>
        <?php else: ?>

            <?php if ($flash): ?>
                <div class="flash flash-<?php echo $flashType; ?>">
                    <i class="fa-solid fa-<?php echo $flashType === 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
                    <?php echo escape($flash); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="admin-form">
                <?php echo csrfField(); ?>

                <div class="dashboard-grid" style="grid-template-columns:1fr; gap:1.5rem;">
                
                <?php foreach ($settingsData as $group => $items): 
                    $icons = [
                        'navbar' => 'fa-bars',
                        'hero' => 'fa-star',
                        'about' => 'fa-user',
                        'skills' => 'fa-code',
                        'projects' => 'fa-rocket',
                        'blog' => 'fa-newspaper',
                        'contact' => 'fa-envelope',
                        'footer' => 'fa-shoe-prints'
                    ];
                    $icon = $icons[$group] ?? 'fa-gear';
                    $groupName = ucfirst($group);
                ?>
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2><i class="fa-solid <?php echo $icon; ?>"></i> <?php echo escape($groupName); ?> Settings</h2>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-row">
                                <?php foreach ($items as $item): ?>
                                    <div class="field <?php echo $item['setting_type'] === 'textarea' ? 'full-width' : ''; ?>" <?php echo $item['setting_type'] === 'textarea' ? 'style="grid-column: 1 / -1;"' : ''; ?>>
                                        <label for="<?php echo escape($item['setting_key']); ?>">
                                            <?php echo escape($item['setting_label']); ?>
                                        </label>
                                        
                                        <?php if ($item['setting_type'] === 'textarea'): ?>
                                            <textarea id="<?php echo escape($item['setting_key']); ?>" 
                                                      name="settings[<?php echo escape($item['setting_key']); ?>]" 
                                                      rows="2"><?php echo escape($item['setting_value']); ?></textarea>
                                        <?php else: ?>
                                            <input type="text" 
                                                   id="<?php echo escape($item['setting_key']); ?>" 
                                                   name="settings[<?php echo escape($item['setting_key']); ?>]" 
                                                   value="<?php echo escape($item['setting_value']); ?>">
                                        <?php endif; ?>
                                        <p class="form-hint" style="font-size:0.65rem; opacity:0.6">Key: <?php echo escape($item['setting_key']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                </div>

                <div class="form-actions" style="margin-top:1.5rem; position:sticky; bottom:20px; z-index:100; background:rgba(20,14,40,0.8); padding:1rem; border-radius:16px; backdrop-filter:blur(10px); border:1px solid rgba(139,92,246,0.3); box-shadow:0 10px 30px rgba(0,0,0,0.5)">
                    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:1rem;">
                        <i class="fa-solid fa-floppy-disk"></i> Save All Settings
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
