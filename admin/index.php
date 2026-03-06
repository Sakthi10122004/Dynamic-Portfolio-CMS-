<?php
/* â”€â”€ admin/index.php â€” Login Page â”€â”€ */
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
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
    rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }

    :root {
      --bg: #f4f1ec; --bg2: #eceae3;
      --card: #ffffff; --stroke: #ddd8cf;
      --ink: #18140f; --ink2: #5a5248; --ink3: #9a9088;
      --orange: #f04a00; --lime: #c5f000; --sky: #0050f0;
      --pink: #f03680; --mint: #00d49e;
      --font: 'DM Sans', sans-serif;
      --font-head: 'Syne', sans-serif;
    }

    body {
      background: var(--bg); color: var(--ink);
      font-family: var(--font); min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 2rem; position: relative; overflow: hidden;
    }

    /* Warm mesh bg */
    body::before {
      content: ''; position: fixed; inset: 0; z-index: 0;
      background:
        radial-gradient(ellipse 600px 500px at 10% 15%, rgba(197,240,0,.25) 0%, transparent 68%),
        radial-gradient(ellipse 500px 400px at 90% 10%, rgba(0,80,240,.10) 0%, transparent 62%),
        radial-gradient(ellipse 400px 350px at 60% 90%, rgba(240,54,128,.08) 0%, transparent 65%),
        radial-gradient(ellipse 350px 350px at 20% 75%, rgba(0,212,158,.08) 0%, transparent 58%);
      animation: meshMove 16s ease-in-out infinite alternate;
      pointer-events: none;
    }
    @keyframes meshMove { 0% { transform: scale(1) } 100% { transform: scale(1.07) translate(1%,1%) } }

    .login-wrap { position: relative; z-index: 1; width: 100%; max-width: 440px }

    .login-box {
      background: var(--card);
      border: 1.5px solid var(--stroke);
      border-radius: 24px;
      padding: 2.5rem 2.25rem;
      box-shadow: 0 20px 60px rgba(0,0,0,.10);
      position: relative; overflow: hidden;
    }
    /* Gradient top bar */
    .login-box::before {
      content: ''; position: absolute;
      top: 0; left: 0; right: 0; height: 4px;
      background: linear-gradient(90deg, var(--orange), var(--sky), var(--lime));
    }

    .login-logo { text-align: center; margin-bottom: 2rem }
    .login-brand-icon {
      width: 58px; height: 58px; border-radius: 16px;
      background: var(--orange); display: flex; align-items: center;
      justify-content: center; font-size: 1.5rem; color: white;
      margin: 0 auto 1rem; box-shadow: 0 8px 24px rgba(240,74,0,.4);
    }
    .login-logo h1 { font-family: var(--font-head); font-size: 1.5rem; font-weight: 800; color: var(--ink); margin-bottom: .3rem }
    .login-logo p { font-size: .84rem; color: var(--ink2) }

    .status-pill {
      display: inline-flex; align-items: center; gap: .4rem;
      background: rgba(0,212,158,.1); border: 1px solid rgba(0,212,158,.3);
      color: #00a87e; border-radius: 100px;
      padding: .28rem .8rem; font-size: .72rem; font-weight: 600;
      letter-spacing: .04em; margin-bottom: 1.8rem;
    }
    .status-dot { width: 7px; height: 7px; border-radius: 50%; background: #00d49e; animation: pulse 2s infinite }
    @keyframes pulse { 0%,100% { transform: scale(1); opacity: 1 } 50% { transform: scale(1.5); opacity: .5 } }

    .fg { margin-bottom: 1.1rem }
    .fg label {
      display: flex; align-items: center; gap: .4rem;
      font-size: .73rem; font-weight: 600; letter-spacing: .08em;
      text-transform: uppercase; color: var(--ink2); margin-bottom: .45rem;
    }
    .fg label i { color: var(--orange); font-size: .78rem }
    .fg input {
      width: 100%; padding: .88rem 1rem;
      background: var(--bg); border: 1.5px solid var(--stroke);
      border-radius: 10px; font-family: var(--font);
      font-size: .9rem; color: var(--ink); outline: none;
      transition: border-color .2s, background .2s;
    }
    .fg input:focus { border-color: var(--orange); background: white }
    .fg input::placeholder { color: var(--ink3) }

    .pw-wrap { position: relative }
    .pw-wrap input { padding-right: 2.8rem }
    .pw-toggle {
      position: absolute; right: .8rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: var(--ink3); cursor: pointer; font-size: .9rem;
    }

    .error-box {
      background: rgba(240,74,0,.07);
      border: 1.5px solid rgba(240,74,0,.25);
      color: var(--orange);
      border-radius: 10px; padding: .8rem 1rem;
      font-size: .86rem; margin-bottom: 1.1rem;
      display: flex; align-items: center; gap: .5rem;
      animation: shake .4s ease;
    }
    @keyframes shake { 0%,100% { transform: translateX(0) } 25% { transform: translateX(-7px) } 75% { transform: translateX(7px) } }

    .btn-submit {
      width: 100%; padding: .95rem;
      background: var(--ink); color: white;
      border: none; border-radius: 100px;
      font-family: var(--font-head); font-size: .9rem; font-weight: 700;
      cursor: pointer; transition: all .25s; letter-spacing: .03em;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
      box-shadow: 0 4px 20px rgba(0,0,0,.18);
    }
    .btn-submit:hover { background: var(--orange); transform: translateY(-1px); box-shadow: 0 8px 28px rgba(240,74,0,.35) }
    .btn-submit:disabled { opacity: .65; transform: none; cursor: not-allowed }

    .login-footer {
      display: flex; justify-content: space-between;
      margin-top: 1.4rem; padding-top: 1.2rem;
      border-top: 1.5px solid var(--stroke);
    }
    .login-footer a {
      font-size: .8rem; color: var(--ink2);
      display: flex; align-items: center; gap: .35rem;
      transition: color .2s; text-decoration: none;
    }
    .login-footer a:hover { color: var(--orange) }
    .deco-version { text-align: center; margin-top: 1.2rem; font-size: .72rem; color: var(--ink3) }
  </style>
</head>

<body>
  <div class="login-wrap">
    <div class="login-box">
      <div class="login-logo">
        <div class="login-brand-icon">
          <svg width="26" height="26" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h1>Admin Portal</h1>
        <p><?php echo SITE_NAME; ?> â€” Control Center</p>
      </div>

      <div style="text-align:center">
        <span class="status-pill"><span class="status-dot"></span> SYSTEM ONLINE</span>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-box">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php echo escape($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="login-form">
        <?php echo csrfField(); ?>

        <div class="fg">
          <label for="username">
            <i class="fa fa-user"></i> Username
          </label>
          <input type="text" id="username" name="username"
            placeholder="your_handle"
            value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>"
            autocomplete="username" required>
        </div>

        <div class="fg">
          <label for="password">
            <i class="fa fa-lock"></i> Password
          </label>
          <div class="pw-wrap">
            <input type="password" id="password" name="password"
              placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
              autocomplete="current-password" required>
            <button type="button" class="pw-toggle" onclick="togglePw()" aria-label="Toggle password">
              <i class="fa fa-eye" id="eye-icon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          <span id="btnText">Sign In</span>
        </button>
      </form>

      <div class="login-footer">
        <a href="<?php echo BASE_URL; ?>/">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Back to site
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/reset-password.php">Forgot password?</a>
      </div>
      <div class="deco-version">v2.4.1 Â· secured</div>
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
