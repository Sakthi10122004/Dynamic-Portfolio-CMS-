<?php
/* ── admin/_sidebar.php — Admin Navigation Sidebar ── */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <h2>⬡ Admin Panel</h2>
    <p><?php echo escape($auth->getCurrentUser()['username'] ?? 'Admin'); ?></p>
  </div>

  <nav class="sidebar-nav">
    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php"
       class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>

    <div class="sidebar-nav-label">Portfolio</div>
    <a href="<?php echo BASE_URL; ?>/admin/hero.php"
       class="<?php echo $currentPage === 'hero.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-star"></i> Hero Section
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/about.php"
       class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-user"></i> About Me
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/skills.php"
       class="<?php echo $currentPage === 'skills.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-code"></i> Skills
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/projects.php"
       class="<?php echo $currentPage === 'projects.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-rocket"></i> Projects
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/social.php"
       class="<?php echo $currentPage === 'social.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-share-nodes"></i> Social Links
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/notes.php"
       class="<?php echo $currentPage === 'notes.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-newspaper"></i> Blog / Notes
    </a>

    <div class="sidebar-nav-label">Inbox</div>
    <a href="<?php echo BASE_URL; ?>/admin/messages.php"
       class="<?php echo $currentPage === 'messages.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-envelope"></i> Messages
    </a>

    <div class="sidebar-nav-label">Settings</div>
    <a href="<?php echo BASE_URL; ?>/admin/profile.php"
       class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-id-card"></i> Profile
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/security.php"
       class="<?php echo $currentPage === 'security.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-shield-halved"></i> Security
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn-glass btn-sm" style="width:100%;justify-content:center;margin-bottom:.5rem">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> View Site
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="btn-glass btn-sm" style="width:100%;justify-content:center;color:#f87171;border-color:rgba(248,113,113,.3)">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
    </a>
  </div>
</aside>
