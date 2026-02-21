<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Redirect if already logged in
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
            $error = 'Too many failed attempts. Please wait ' . LOGIN_LOCKOUT_MINUTES . ' minutes and try again.';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <a href="<?php echo BASE_URL; ?>/" class="login-logo">✦ Sakthi</a>
                <h1>Admin Access</h1>
                <p>Enter your credentials to continue</p>
            </div>

            <?php if ($error): ?>
            <div class="error-message" role="alert"><?php echo escape($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form" autocomplete="off" novalidate>
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           placeholder="Enter username"
                           value="<?php echo escape($username ?? ''); ?>"
                           required
                           autofocus
                           autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Enter password"
                               required
                               autocomplete="current-password">
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password visibility">
                            <i class="fa-regular fa-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn primary full-width">Sign In</button>
                <div class="forgot-link">
                    <a href="<?php echo BASE_URL; ?>/admin/reset-password.php">Forgot password?</a>
                </div>
            </form>

            <div class="login-footer">
                <a href="<?php echo BASE_URL; ?>/">← Back to site</a>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('pwToggle').addEventListener('click', function () {
        var inp = document.getElementById('password');
        var icn = document.getElementById('pwIcon');
        if (inp.type === 'password') {
            inp.type = 'text';
            icn.className = 'fa-regular fa-eye-slash';
        } else {
            inp.type = 'password';
            icn.className = 'fa-regular fa-eye';
        }
    });
    </script>
</body>
</html>