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
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } elseif ($auth->login($username, $password)) {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            <div class="error-message"><?php echo escape($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" 
                           id="username"
                           name="username" 
                           placeholder="Enter username" 
                           value="<?php echo escape($username ?? ''); ?>"
                           required 
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           placeholder="Enter password" 
                           required>
                </div>
                
                <button type="submit" class="btn primary full-width">Sign In</button>
            </form>
            
            <div class="login-footer">
                <a href="<?php echo BASE_URL; ?>/">← Back to site</a>
            </div>
        </div>
    </div>
</body>
</html>