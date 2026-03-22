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
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Login | <?php echo SITE_NAME; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css" onerror="this.remove()">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #f8f9fc;
      --card: #ffffff;
      --stroke: #e2e6ef;
      --ink: #1a1a2e;
      --ink2: #4a5568;
      --ink3: #8896ab;
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --primary-glow: rgba(37, 99, 235, 0.15);
      --green: #22c55e;
      --coral: #ef4444;
      --font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      --font-head: 'Playfair Display', Georgia, serif;
    }

    body {
      background: var(--bg);
      color: var(--ink);
      font-family: var(--font);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      -webkit-font-smoothing: antialiased;
    }

    /* Subtle bg gradient */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(ellipse 60% 50% at 50% 0%, var(--primary-glow) 0%, transparent 60%);
      pointer-events: none;
    }

    .login-wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 420px;
    }

    .login-box {
      background: var(--card);
      border: 1px solid var(--stroke);
      border-radius: 16px;
      padding: 2.5rem 2rem;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
      position: relative;
      overflow: hidden;
    }

    .login-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: var(--primary);
    }

    .login-logo { text-align: center; margin-bottom: 2rem; }

    .login-brand-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      color: white;
      margin: 0 auto 1rem;
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
    }

    .login-logo h1 {
      font-family: var(--font-head);
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: .25rem;
    }

    .login-logo p {
      font-size: .84rem;
      color: var(--ink2);
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: rgba(34, 197, 94, 0.08);
      border: 1px solid rgba(34, 197, 94, 0.2);
      color: #16a34a;
      border-radius: 100px;
      padding: .3rem .8rem;
      font-size: .72rem;
      font-weight: 600;
      letter-spacing: .04em;
      margin-bottom: 1.5rem;
    }

    .status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--green);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.5); opacity: .5; }
    }

    .fg { margin-bottom: 1.1rem; }

    .fg label {
      display: flex;
      align-items: center;
      gap: .35rem;
      font-size: .73rem;
      font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--ink2);
      margin-bottom: .45rem;
    }

    .fg label i {
      color: var(--primary);
      font-size: .78rem;
    }

    .fg input {
      width: 100%;
      padding: .85rem 1rem;
      background: var(--bg);
      border: 1px solid var(--stroke);
      border-radius: 8px;
      font-family: var(--font);
      font-size: .9rem;
      color: var(--ink);
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .fg input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-glow);
    }

    .fg input::placeholder { color: var(--ink3); }

    .pw-wrap { position: relative; }
    .pw-wrap input { padding-right: 2.8rem; }

    .pw-toggle {
      position: absolute;
      right: .8rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--ink3);
      cursor: pointer;
      font-size: .9rem;
    }

    .error-box {
      background: rgba(239, 68, 68, 0.06);
      border: 1px solid rgba(239, 68, 68, 0.2);
      color: var(--coral);
      border-radius: 8px;
      padding: .8rem 1rem;
      font-size: .85rem;
      margin-bottom: 1.1rem;
      display: flex;
      align-items: center;
      gap: .5rem;
      animation: shake .4s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .btn-submit {
      width: 100%;
      padding: .9rem;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      font-family: var(--font);
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all .25s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      box-shadow: 0 2px 12px rgba(37, 99, 235, 0.25);
    }

    .btn-submit:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
    }

    .btn-submit:disabled {
      opacity: .65;
      transform: none;
      cursor: not-allowed;
    }

    .login-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 1.4rem;
      padding-top: 1.2rem;
      border-top: 1px solid var(--stroke);
    }

    .login-footer a {
      font-size: .8rem;
      color: var(--ink2);
      display: flex;
      align-items: center;
      gap: .35rem;
      transition: color .2s;
      text-decoration: none;
    }

    .login-footer a:hover { color: var(--primary); }

    .deco-version {
      text-align: center;
      margin-top: 1rem;
      font-size: .72rem;
      color: var(--ink3);
    }
  </style>
</head>

<body>
  <div class="login-wrap">
    <div class="login-box">
      <div class="login-logo">
        <div class="login-brand-icon">
          <svg width="24" height="24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" viewBox="0 0 24 24">
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
        </div>
        <h1>Admin Portal</h1>
        <p><?php echo SITE_NAME; ?> — Control Center</p>
      </div>

      <div style="text-align:center">
        <span class="status-pill"><span class="status-dot"></span> SYSTEM ONLINE</span>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-box">
          <i class="fa-solid fa-circle-exclamation" style="font-size:.85rem;"></i>
          <?php echo escape($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="login-form">
        <?php echo csrfField(); ?>

        <div class="fg">
          <label for="username">
            <i class="fa-solid fa-user"></i> Username
          </label>
          <input type="text" id="username" name="username" placeholder="your_handle"
            value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>" autocomplete="username"
            required>
        </div>

        <div class="fg">
          <label for="password">
            <i class="fa-solid fa-lock"></i> Password
          </label>
          <div class="pw-wrap">
            <input type="password" id="password" name="password" placeholder="••••••••••"
              autocomplete="current-password" required>
            <button type="button" class="pw-toggle" onclick="togglePw()" aria-label="Toggle password">
              <i class="fa fa-eye" id="eye-icon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <polyline points="10 17 15 12 10 7" />
            <line x1="15" y1="12" x2="3" y2="12" />
          </svg>
          <span id="btnText">Sign In</span>
        </button>
      </form>

      <div class="login-footer">
        <a href="<?php echo BASE_URL; ?>/">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M19 12H5M12 19l-7-7 7-7" />
          </svg>
          Back to site
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/reset-password.php">Forgot password?</a>
      </div>
      <div class="deco-version">v3.0 · secured</div>
    </div>
  </div>

  <script>
    function togglePw() {
      const pw = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
      if (pw.type === 'password') {
        pw.type = 'text';
        icon.className = 'fa fa-eye-slash';
      } else {
        pw.type = 'password';
        icon.className = 'fa fa-eye';
      }
    }
    document.querySelector('.login-form').addEventListener('submit', function () {
      const btn = document.getElementById('submitBtn');
      const text = document.getElementById('btnText');
      btn.disabled = true;
      text.textContent = 'Authenticating...';
    });
  </script>
</body>

</html>