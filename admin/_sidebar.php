<aside class="admin-sidebar">
    <div class="sidebar-header">
        <span class="sidebar-title">Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="sidebar-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'dashboard.php' ? 'active' : ''; ?>">
            <span class="sidebar-icon">📊</span> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="sidebar-link <?php echo in_array(basename($_SERVER['SCRIPT_NAME']), ['projects.php','project-edit.php']) ? 'active' : ''; ?>">
            <span class="sidebar-icon">🚀</span> Projects
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/skills.php" class="sidebar-link <?php echo in_array(basename($_SERVER['SCRIPT_NAME']), ['skills.php','skill-edit.php']) ? 'active' : ''; ?>">
            <span class="sidebar-icon">💡</span> Skills
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="sidebar-link <?php echo in_array(basename($_SERVER['SCRIPT_NAME']), ['notes.php','note-edit.php']) ? 'active' : ''; ?>">
            <span class="sidebar-icon">📝</span> Notes
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/profile.php" class="sidebar-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'profile.php' ? 'active' : ''; ?>">
            <span class="sidebar-icon">👤</span> Profile
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/messages.php" class="sidebar-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'messages.php' ? 'active' : ''; ?>">
            <span class="sidebar-icon">📬</span> Messages
            <?php 
            $unreadMessages = $unreadMessages ?? ($db ?? Database::getInstance())->getRow("SELECT COUNT(*) as count FROM contact_messages WHERE read_status = 0")['count'] ?? 0;
            if ($unreadMessages > 0): ?>
            <span class="badge"><?php echo $unreadMessages; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/security.php" class="sidebar-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'security.php' ? 'active' : ''; ?>">
            <span class="sidebar-icon">🔐</span> Security
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="sidebar-link logout">
            <span class="sidebar-icon">🚪</span> Logout
        </a>
    </nav>
</aside>
