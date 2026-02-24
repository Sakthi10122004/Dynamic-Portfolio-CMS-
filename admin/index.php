<?php
/* ── admin/index.php — Login Page ── */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $result = $auth->login($username, $password);
        if ($result === 'success') {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        } elseif ($result === 'rate_limited') {
            $error = 'Too many failed attempts. Please wait ' . LOGIN_LOCKOUT_MINUTES . ' minutes.';
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login | <?php echo SITE_NAME; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
<link rel="stylesheet" href="../assets/css/style.css" onerror="this.remove()">
<style>
  /* Override: login page is standalone, no main wrapper needed */
  body { margin: 0; }
  main { padding: 0; }
</style>
</head>
<body>
<main>

<div class="admin-login-page">

  <!-- Background decorative shapes -->
  <div class="login-bg-shapes" aria-hidden="true">
    <div class="login-shape login-shape-1"></div>
    <div class="login-shape login-shape-2"></div>
    <div class="login-shape login-shape-3"></div>
  </div>

  <div class="login-box">

    <!-- Logo / Brand -->
    <div class="login-logo">
      <div class="login-brand-icon">
        <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
          <circle cx="20" cy="20" r="18" stroke="url(#lg)" stroke-width="2.5" fill="rgba(255,255,255,0.05)"/>
          <path d="M12 28 L20 12 L28 28" stroke="url(#lg)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="15" y1="22" x2="25" y2="22" stroke="url(#lg)" stroke-width="2" stroke-linecap="round"/>
          <defs>
            <linearGradient id="lg" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
              <stop stop-color="#a78bfa"/>
              <stop offset="1" stop-color="#38bdf8"/>
            </linearGradient>
          </defs>
        </svg>
      </div>
      <h1><?php echo escape(SITE_NAME); ?></h1>
      <p>Sign in to your admin panel</p>
    </div>

    <!-- Error -->
    <?php if ($error): ?>
    <div class="flash flash-error" role="alert">
      <i class="fa-solid fa-circle-exclamation"></i>
      <?php echo escape($error); ?>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" class="login-form admin-form" novalidate>
      <?php echo csrfField(); ?>

      <div class="field">
        <label for="username">
          <i class="fa-solid fa-user"></i> Username
        </label>
        <input type="text" id="username" name="username"
               placeholder="Enter your username"
               value="<?php echo escape($username ?? ''); ?>"
               required autofocus autocomplete="username">
      </div>

      <div class="field">
        <label for="password">
          <i class="fa-solid fa-lock"></i> Password
        </label>
        <div class="pw-wrap">
          <input type="password" id="password" name="password"
                 placeholder="Enter your password"
                 required autocomplete="current-password">
          <button type="button" class="pw-toggle" aria-label="Show password">
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:.85rem;font-size:.95rem">
        <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
      </button>

      <div style="text-align:center;margin-top:1rem">
        <a href="<?php echo BASE_URL; ?>/admin/reset-password.php"
           style="font-size:.84rem;color:var(--text-muted)">
          Forgot password?
        </a>
      </div>
    </form>

    <div style="text-align:center;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--glass-border)">
      <a href="<?php echo BASE_URL; ?>/"
         style="font-size:.84rem;color:var(--text-muted);display:inline-flex;align-items:center;gap:.4rem">
        <i class="fa-solid fa-arrow-left"></i> Back to site
      </a>
    </div>

  </div><!-- /login-box -->
</div>

</main>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
<script src="../assets/js/main.js" onerror="this.remove()"></script>
</body>
</html>