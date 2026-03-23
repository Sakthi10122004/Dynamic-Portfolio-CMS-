<?php
/* ── admin/_sidebar.php — Sidebar Navigation ── */
$currentPage = basename($_SERVER['PHP_SELF']);
$profile = $profile ?? getProfile();

// Count unread messages for badge
$db = Database::getInstance();
$unreadCount = $db->getRow("SELECT COUNT(*) as c FROM contact_messages WHERE read_status=0")['c'] ?? 0;
$currentUser = $auth->getCurrentUser();
?>
<aside class="admin-sidebar" aria-label="Admin navigation">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="sidebar-brand-icon">
      <svg width="22" height="22" viewBox="0 0 40 40" fill="none">
        <circle cx="20" cy="20" r="18" stroke="white" stroke-width="2" fill="rgba(255,255,255,0.1)" />
        <path d="M12 28 L20 12 L28 28" stroke="white" stroke-width="2.5" stroke-linecap="round"
          stroke-linejoin="round" />
        <line x1="15" y1="22" x2="25" y2="22" stroke="white" stroke-width="2" stroke-linecap="round" />
      </svg>
    </div>
    <div class="sidebar-brand-text">
      <h2><?php echo escape(SITE_NAME); ?></h2>
      <p>Admin Panel</p>
    </div>
  </div>

  <!-- Mobile Nav Toggle (hidden on desktop by default) -->
  <button class="sidebar-mobile-toggle" id="sidebarToggle" style="display:none" aria-expanded="false"
    aria-controls="sidebar-nav-content">
    <i class="fa-solid fa-bars" id="sidebarToggleIcon"></i>
    <span>Navigation</span>
  </button>

  <!-- Navigation -->
  <nav class="sidebar-nav" aria-label="Admin pages">

    <div class="sidebar-nav-label">Main</div>
    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php"
      class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>

    <div class="sidebar-nav-label">Content</div>
    <a href="<?php echo BASE_URL; ?>/admin/hero.php" class="<?php echo $currentPage === 'hero.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-star"></i> Hero Section
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/about.php"
      class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-user"></i> About Me
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/social.php"
      class="<?php echo $currentPage === 'social.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-share-nodes"></i> Social Links
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/profile.php"
      class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-id-card"></i> Profile
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/settings.php"
      class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-gear"></i> Settings
    </a>

    <div class="sidebar-nav-label">Portfolio</div>
    <a href="<?php echo BASE_URL; ?>/admin/projects.php"
      class="<?php echo in_array($currentPage, ['projects.php', 'project-edit.php']) ? 'active' : ''; ?>">
      <i class="fa-solid fa-rocket"></i> Projects
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/skills.php"
      class="<?php echo in_array($currentPage, ['skills.php', 'skill-edit.php']) ? 'active' : ''; ?>">
      <i class="fa-solid fa-code"></i> Skills
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/notes.php"
      class="<?php echo in_array($currentPage, ['notes.php', 'note-edit.php']) ? 'active' : ''; ?>">
      <i class="fa-solid fa-newspaper"></i> Blog Posts
    </a>

    <div class="sidebar-nav-label">Inbox</div>
    <a href="<?php echo BASE_URL; ?>/admin/messages.php"
      class="<?php echo $currentPage === 'messages.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-envelope"></i> Messages
      <?php if ($unreadCount > 0): ?>
        <span class="sidebar-badge"><?php echo $unreadCount; ?></span>
      <?php endif; ?>
    </a>

    <div class="sidebar-nav-label">Account</div>
    <a href="<?php echo BASE_URL; ?>/admin/security.php"
      class="<?php echo $currentPage === 'security.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-shield-halved"></i> Security
    </a>

  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar">
        <?php if (!empty($profile['avatar'])): ?>
          <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>" alt=""
            style="width:32px;height:32px;object-fit:cover;border-radius:50%">
        <?php else: ?>
          <i class="fa-solid fa-user" style="font-size:.85rem"></i>
        <?php endif; ?>
      </div>
      <div>
        <div class="sidebar-username"><?php echo escape($currentUser['username'] ?? 'Admin'); ?></div>
        <div class="sidebar-role">Administrator</div>
      </div>
    </div>
    <a href="<?php echo BASE_URL; ?>/" target="_blank">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> View Site
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/logout.php">
      <i class="fa-solid fa-right-from-bracket"></i> Sign Out
    </a>
  </div>

</aside>

<!-- Mobile sidebar backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>