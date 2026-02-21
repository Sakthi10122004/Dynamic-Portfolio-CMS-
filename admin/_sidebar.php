<?php
/* _sidebar.php — Shared admin sidebar (included by all admin pages) */
$currentPage = basename($_SERVER['SCRIPT_NAME']);

function sidebarLink($href, $icon, $label, $current, $match) {
    $active = is_array($match) ? in_array($current, $match) : $current === $match;
    $cls = $active ? 'sidebar-link active' : 'sidebar-link';
    return '<a href="' . $href . '" class="' . $cls . '"><span class="sidebar-icon">' . $icon . '</span>' . $label . '</a>';
}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>/" class="sidebar-logo">✦ Sakthi</a>
        <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">×</button>
    </div>
    <nav class="sidebar-nav">
        <?php echo sidebarLink(BASE_URL . '/admin/dashboard.php',  '📊', 'Dashboard', $currentPage, 'dashboard.php'); ?>
        <?php echo sidebarLink(BASE_URL . '/admin/projects.php',   '🚀', 'Projects',  $currentPage, ['projects.php','project-edit.php']); ?>
        <?php echo sidebarLink(BASE_URL . '/admin/skills.php',     '💡', 'Skills',    $currentPage, ['skills.php','skill-edit.php']); ?>
        <?php echo sidebarLink(BASE_URL . '/admin/notes.php',      '📝', 'Notes',     $currentPage, ['notes.php','note-edit.php']); ?>
        <?php echo sidebarLink(BASE_URL . '/admin/profile.php',    '👤', 'Profile',   $currentPage, 'profile.php'); ?>
        <a href="<?php echo BASE_URL; ?>/admin/messages.php"
           class="sidebar-link <?php echo $currentPage === 'messages.php' ? 'active' : ''; ?>">
            <span class="sidebar-icon">📬</span> Messages
            <?php
            $unreadMessages = $unreadMessages ?? (Database::getInstance()->getRow("SELECT COUNT(*) as count FROM contact_messages WHERE read_status = 0")['count'] ?? 0);
            if ($unreadMessages > 0):
            ?>
            <span class="badge"><?php echo $unreadMessages; ?></span>
            <?php endif; ?>
        </a>
        <?php echo sidebarLink(BASE_URL . '/admin/security.php',   '🔐', 'Security',  $currentPage, 'security.php'); ?>

        <!-- CSRF-protected logout button -->
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/logout.php" class="logout-form">
            <?php echo csrfField(); ?>
            <button type="submit" class="sidebar-link logout" aria-label="Logout">
                <span class="sidebar-icon">🚪</span> Logout
            </button>
        </form>
    </nav>
    <div class="sidebar-footer">
        <a href="<?php echo BASE_URL; ?>/" target="_blank" class="view-site-link">View Site →</a>
    </div>
</aside>
