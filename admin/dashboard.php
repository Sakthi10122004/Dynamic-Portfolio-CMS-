<?php
/* ── admin/dashboard.php — Admin Dashboard ── */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
$auth->requireLogin();
$isAdminPage = true;
$db = Database::getInstance();
$profile = getProfile();

$stats = [
    'projects' => $db->getRow("SELECT COUNT(*) as c FROM projects")['c'] ?? 0,
    'skills'   => $db->getRow("SELECT COUNT(*) as c FROM skills")['c']   ?? 0,
    'notes'    => $db->getRow("SELECT COUNT(*) as c FROM notes")['c']    ?? 0,
    'messages' => $db->getRow("SELECT COUNT(*) as c FROM contact_messages WHERE read_status=0")['c'] ?? 0,
];
$recentProjects = $db->getRows("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5") ?: [];
$recentMessages = $db->getRows("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5") ?: [];

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
?>

<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>

  <div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
      <div>
        <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
          <i class="fa-solid fa-gauge-high" style="color:var(--accent)"></i> Dashboard
        </h1>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">
          Welcome back, <strong><?php echo escape($auth->getCurrentUser()['username']); ?></strong> 👋
        </p>
      </div>
      <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn-glass btn-sm">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Site
      </a>
    </div>

    <!-- Stats -->
    <div class="dashboard-stats">

      <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="glass-card stat-card">
        <div class="stat-icon" style="background:rgba(167,139,250,.15);color:#a78bfa">
          <i class="fa-solid fa-rocket"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?php echo $stats['projects']; ?></div>
          <div class="stat-label">Projects</div>
        </div>
        <i class="fa-solid fa-arrow-right stat-arrow"></i>
      </a>

      <a href="<?php echo BASE_URL; ?>/admin/skills.php" class="glass-card stat-card">
        <div class="stat-icon" style="background:rgba(56,189,248,.15);color:#38bdf8">
          <i class="fa-solid fa-code"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?php echo $stats['skills']; ?></div>
          <div class="stat-label">Skills</div>
        </div>
        <i class="fa-solid fa-arrow-right stat-arrow"></i>
      </a>

      <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="glass-card stat-card">
        <div class="stat-icon" style="background:rgba(244,114,182,.15);color:#f472b6">
          <i class="fa-solid fa-newspaper"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?php echo $stats['notes']; ?></div>
          <div class="stat-label">Blog Posts</div>
        </div>
        <i class="fa-solid fa-arrow-right stat-arrow"></i>
      </a>

      <a href="<?php echo BASE_URL; ?>/admin/messages.php" class="glass-card stat-card <?php echo $stats['messages'] > 0 ? 'stat-card-alert' : ''; ?>">
        <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24">
          <i class="fa-solid fa-envelope"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?php echo $stats['messages']; ?></div>
          <div class="stat-label">Unread Messages <?php if ($stats['messages'] > 0): ?><span class="unread-badge"><?php echo $stats['messages']; ?></span><?php endif; ?></div>
        </div>
        <i class="fa-solid fa-arrow-right stat-arrow"></i>
      </a>

    </div><!-- /stats -->

    <!-- Quick Actions -->
    <div class="admin-card" style="margin-bottom:1.5rem">
      <div class="admin-card-header">
        <h2><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
      </div>
      <div class="quick-actions">
        <a href="<?php echo BASE_URL; ?>/admin/project-edit.php" class="quick-action">
          <i class="fa-solid fa-plus"></i> New Project
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/skill-edit.php" class="quick-action">
          <i class="fa-solid fa-plus"></i> Add Skill
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/note-edit.php" class="quick-action">
          <i class="fa-solid fa-plus"></i> New Post
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/hero.php" class="quick-action">
          <i class="fa-solid fa-star"></i> Edit Hero
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/about.php" class="quick-action">
          <i class="fa-solid fa-user"></i> Edit About
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/social.php" class="quick-action">
          <i class="fa-solid fa-share-nodes"></i> Social Links
        </a>
      </div>
    </div>

    <!-- Recent Activity Grid -->
    <div class="dashboard-grid">

      <!-- Recent Projects -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2><i class="fa-solid fa-rocket"></i> Recent Projects</h2>
          <a href="<?php echo BASE_URL; ?>/admin/projects.php" class="card-link">View all →</a>
        </div>
        <?php if (empty($recentProjects)): ?>
        <div class="empty-state">
          <i class="fa-solid fa-rocket"></i>
          <p>No projects yet. <a href="<?php echo BASE_URL; ?>/admin/project-edit.php">Add one →</a></p>
        </div>
        <?php else: ?>
        <div class="activity-list">
          <?php foreach ($recentProjects as $p): ?>
          <div class="activity-item">
            <div class="activity-icon" style="background:rgba(167,139,250,.1)">
              <i class="fa-solid fa-rocket" style="color:var(--accent)"></i>
            </div>
            <div class="activity-info">
              <span class="activity-title"><?php echo escape($p['title']); ?></span>
              <span class="activity-meta">
                <?php if ($p['featured']): ?><span class="badge-featured">⭐ Featured</span><?php endif; ?>
                <?php echo timeAgo($p['created_at']); ?>
              </span>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/project-edit.php?id=<?php echo $p['id']; ?>"
               class="btn-icon" title="Edit">
              <i class="fa-solid fa-pen"></i>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Recent Messages -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2><i class="fa-solid fa-envelope"></i> Recent Messages</h2>
          <a href="<?php echo BASE_URL; ?>/admin/messages.php" class="card-link">View all →</a>
        </div>
        <?php if (empty($recentMessages)): ?>
        <div class="empty-state">
          <i class="fa-solid fa-envelope-open"></i>
          <p>No messages yet.</p>
        </div>
        <?php else: ?>
        <div class="activity-list">
          <?php foreach ($recentMessages as $msg): ?>
          <div class="activity-item <?php echo !$msg['read_status'] ? 'activity-unread' : ''; ?>">
            <div class="activity-icon" style="background:rgba(251,191,36,.1)">
              <i class="fa-solid fa-envelope<?php echo !$msg['read_status'] ? '' : '-open'; ?>"
                 style="color:#fbbf24"></i>
            </div>
            <div class="activity-info">
              <span class="activity-title"><?php echo escape($msg['name']); ?></span>
              <span class="activity-meta"><?php echo escape(truncate($msg['message'], 45)); ?></span>
              <span class="activity-time"><?php echo timeAgo($msg['created_at']); ?></span>
            </div>
            <?php if (!$msg['read_status']): ?>
            <span class="unread-dot" title="Unread"></span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div><!-- /dashboard-grid -->

  </div><!-- /admin-main -->
</div>

<?php require_once '../includes/footer.php'; ?>