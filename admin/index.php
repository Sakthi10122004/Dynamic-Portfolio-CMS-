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
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Mono:wght@300;400;500&display=swap"
    rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg: #08090c;
      --surface: #0e1017;
      --panel: #12151e;
      --border: rgba(255, 255, 255, 0.07);
      --border-hover: rgba(139, 92, 246, 0.4);
      --accent: #7c3aed;
      --accent-2: #06b6d4;
      --accent-glow: rgba(124, 58, 237, 0.35);
      --text: #f0f0f5;
      --muted: #5a5e72;
      --muted-2: #3a3e52;
      --error: #f87171;
      --error-bg: rgba(248, 113, 113, 0.08);
      --success: #34d399;
      --font-display: 'Syne', sans-serif;
      --font-mono: 'DM Mono', monospace;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-display);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    /* ── Animated grid background ── */
    .grid-bg {
      position: fixed;
      inset: 0;
      background-image:
        linear-gradient(rgba(124, 58, 237, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(124, 58, 237, 0.04) 1px, transparent 1px);
      background-size: 48px 48px;
      animation: gridDrift 20s linear infinite;
      pointer-events: none;
    }

    @keyframes gridDrift {
      0% {
        transform: translate(0, 0);
      }

      100% {
        transform: translate(48px, 48px);
      }
    }

    /* ── Noise overlay ── */
    .noise {
      position: fixed;
      inset: 0;
      opacity: 0.025;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 1;
    }

    /* ── Blobs ── */
    .blob {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.18;
      pointer-events: none;
    }

    .blob-1 {
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, #7c3aed, transparent 70%);
      top: -120px;
      left: -120px;
      animation: blobFloat1 12s ease-in-out infinite;
    }

    .blob-2 {
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, #06b6d4, transparent 70%);
      bottom: -100px;
      right: -80px;
      animation: blobFloat2 15s ease-in-out infinite;
    }

    @keyframes blobFloat1 {

      0%,
      100% {
        transform: translate(0, 0) scale(1);
      }

      50% {
        transform: translate(40px, 30px) scale(1.1);
      }
    }

    @keyframes blobFloat2 {

      0%,
      100% {
        transform: translate(0, 0) scale(1);
      }

      50% {
        transform: translate(-30px, -40px) scale(1.08);
      }
    }

    /* ── Main card ── */
    .login-wrap {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 460px;
      padding: 20px;
      animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes cardIn {
      from {
        opacity: 0;
        transform: translateY(24px) scale(0.97);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .login-card {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 48px 44px;
      position: relative;
      overflow: hidden;
      box-shadow:
        0 0 0 1px var(--border),
        0 32px 64px rgba(0, 0, 0, 0.6),
        0 0 80px var(--accent-glow);
    }

    /* Top accent line */
    .login-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 16px;
      right: 16px;
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--accent), var(--accent-2), transparent);
      opacity: 0.7;
    }

    /* Corner decoration */
    .login-card::after {
      content: '';
      position: absolute;
      top: -1px;
      right: -1px;
      width: 80px;
      height: 80px;
      border-top: 1px solid rgba(124, 58, 237, 0.5);
      border-right: 1px solid rgba(6, 182, 212, 0.4);
      border-radius: 0 20px 0 0;
    }

    /* ── Header ── */
    .login-header {
      margin-bottom: 36px;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(124, 58, 237, 0.12);
      border: 1px solid rgba(124, 58, 237, 0.25);
      border-radius: 100px;
      padding: 5px 12px;
      font-family: var(--font-mono);
      font-size: 11px;
      color: #a78bfa;
      letter-spacing: 0.08em;
      margin-bottom: 20px;
      animation: cardIn 0.6s 0.15s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .status-dot {
      width: 6px;
      height: 6px;
      background: #34d399;
      border-radius: 50%;
      box-shadow: 0 0 6px #34d399;
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.5;
        transform: scale(0.8);
      }
    }

    .login-title {
      font-size: 30px;
      font-weight: 800;
      letter-spacing: -0.02em;
      line-height: 1.1;
      background: linear-gradient(135deg, #f0f0f5 30%, #a78bfa 70%, #67e8f9 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 8px;
      animation: cardIn 0.6s 0.2s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .login-sub {
      font-family: var(--font-mono);
      font-size: 12.5px;
      color: var(--muted);
      letter-spacing: 0.03em;
      animation: cardIn 0.6s 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* ── Error flash ── */
    .flash-error {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--error-bg);
      border: 1px solid rgba(248, 113, 113, 0.2);
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 13.5px;
      color: var(--error);
      margin-bottom: 24px;
      animation: shakeIn 0.4s ease both;
    }

    @keyframes shakeIn {
      0% {
        transform: translateX(-6px);
        opacity: 0;
      }

      40% {
        transform: translateX(4px);
      }

      70% {
        transform: translateX(-2px);
      }

      100% {
        transform: translateX(0);
        opacity: 1;
      }
    }

    /* ── Form ── */
    .login-form {
      animation: cardIn 0.6s 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .field {
      margin-bottom: 20px;
    }

    label {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 9px;
    }

    label svg {
      width: 13px;
      height: 13px;
      opacity: 0.8;
    }

    .input-wrap {
      position: relative;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 13px 16px;
      font-family: var(--font-mono);
      font-size: 14px;
      color: var(--text);
      outline: none;
      transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
      letter-spacing: 0.02em;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      border-color: rgba(124, 58, 237, 0.6);
      background: rgba(124, 58, 237, 0.04);
      box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }

    input::placeholder {
      color: var(--muted-2);
    }

    .pw-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 4px;
      display: flex;
      transition: color 0.2s;
    }

    .pw-toggle:hover {
      color: var(--text);
    }

    /* ── Submit button ── */
    .btn-submit {
      width: 100%;
      margin-top: 28px;
      padding: 14px;
      background: linear-gradient(135deg, var(--accent), #5b21b6);
      border: none;
      border-radius: 11px;
      color: white;
      font-family: var(--font-display);
      font-size: 14.5px;
      font-weight: 700;
      letter-spacing: 0.04em;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 9px;
      position: relative;
      overflow: hidden;
      transition: transform 0.15s, box-shadow 0.2s, filter 0.2s;
      box-shadow: 0 4px 20px rgba(124, 58, 237, 0.4);
    }

    .btn-submit::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.12), transparent);
      transition: left 0.5s ease;
    }

    .btn-submit:hover::before {
      left: 100%;
    }

    .btn-submit:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 32px rgba(124, 58, 237, 0.55);
      filter: brightness(1.05);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    .btn-submit:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }

    /* ── Footer links ── */
    .form-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 22px;
      padding-top: 22px;
      border-top: 1px solid var(--border);
    }

    .form-footer a {
      font-family: var(--font-mono);
      font-size: 12px;
      color: var(--muted);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: color 0.2s;
    }

    .form-footer a:hover {
      color: #a78bfa;
    }

    /* ── Decorative code stamp ── */
    .deco-code {
      position: absolute;
      bottom: 18px;
      right: 20px;
      font-family: var(--font-mono);
      font-size: 9px;
      color: var(--muted-2);
      letter-spacing: 0.05em;
      opacity: 0.5;
      user-select: none;
      pointer-events: none;
    }

    @media (max-width: 500px) {
      .login-card {
        padding: 36px 28px;
      }

      .login-title {
        font-size: 26px;
      }
    }
  </style>
</head>

<body>

  <div class="grid-bg" aria-hidden="true"></div>
  <div class="noise" aria-hidden="true"></div>
  <div class="blob blob-1" aria-hidden="true"></div>
  <div class="blob blob-2" aria-hidden="true"></div>

  <div class="login-wrap">
    <div class="login-card">

      <div class="login-header">
        <div class="status-badge">
          <span class="status-dot"></span>
          SYSTEM ONLINE
        </div>
        <h1 class="login-title">Admin<br>Portal</h1>
        <p class="login-sub">// authenticate to continue</p>
      </div>

      <?php if ($error): ?>
        <div class="flash-error" role="alert">
          <svg viewBox="0 0 20 20" fill="currentColor" width="16" flex-shrink="0">
            <path fill-rule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
              clip-rule="evenodd" />
          </svg>
          <?php echo escape($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="login-form" novalidate>
        <?php echo csrfField(); ?>

        <div class="field">
          <label for="username">
            <svg viewBox="0 0 20 20" fill="currentColor">
              <path d="M10 8a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
            </svg>
            Username
          </label>
          <input type="text" id="username" name="username" placeholder="your_handle"
            value="<?php echo escape($username ?? ''); ?>" required autofocus autocomplete="username">
        </div>

        <div class="field">
          <label for="password">
            <svg viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z"
                clip-rule="evenodd" />
            </svg>
            Password
          </label>
          <div class="input-wrap">
            <input type="password" id="password" name="password" placeholder="••••••••••" required
              autocomplete="current-password">
            <button type="button" class="pw-toggle" aria-label="Toggle password visibility" onclick="togglePw()">
              <svg id="eye-icon" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                <path fill-rule="evenodd"
                  d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                  clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <svg viewBox="0 0 20 20" fill="currentColor" width="16">
            <path fill-rule="evenodd"
              d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25zM19 10a.75.75 0 00-.75-.75H8.704l1.048-.943a.75.75 0 10-1.004-1.114l-2.5 2.25a.75.75 0 000 1.114l2.5 2.25a.75.75 0 101.004-1.114l-1.048-.943h9.546A.75.75 0 0019 10z"
              clip-rule="evenodd" />
          </svg>
          <span id="btnText">Sign In</span>
        </button>

        <div class="form-footer">
          <a href="<?php echo BASE_URL; ?>/">
            <svg viewBox="0 0 20 20" fill="currentColor" width="12">
              <path fill-rule="evenodd"
                d="M7.793 2.232a.75.75 0 01-.025 1.06L3.622 7.25h10.003a5.375 5.375 0 010 10.75H10.75a.75.75 0 010-1.5h2.875a3.875 3.875 0 000-7.75H3.622l4.146 3.957a.75.75 0 01-1.036 1.085l-5.5-5.25a.75.75 0 010-1.085l5.5-5.25a.75.75 0 011.061.025z"
                clip-rule="evenodd" />
            </svg>
            Back to site
          </a>
          <a href="<?php echo BASE_URL; ?>/admin/reset-password.php">Forgot password?</a>
        </div>
      </form>

      <div class="deco-code">v2.4.1 · secured</div>
    </div>
  </div>

  <script>
    function togglePw() {
      const pw = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
      if (pw.type === 'password') {
        pw.type = 'text';
        icon.innerHTML = `<path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/><path d="M10.748 13.93l2.523 2.524a10.065 10.065 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z"/>`;
      } else {
        pw.type = 'password';
        icon.innerHTML = `<path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>`;
      }
    }

    // Loading state on form submit
    document.querySelector('.login-form').addEventListener('submit', function () {
      const btn = document.getElementById('submitBtn');
      const text = document.getElementById('btnText');
      btn.disabled = true;
      text.textContent = 'Authenticating...';
    });
  </script>
</body>

</html>