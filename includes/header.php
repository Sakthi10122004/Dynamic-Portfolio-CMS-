<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Full-Stack Developer — Modern portfolio with bento grid design'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <meta name="theme-color" content="#050508">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>/" class="logo">✦ Sakthi</a>
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="<?php echo BASE_URL; ?>/#work">Work</a>
                <a href="<?php echo BASE_URL; ?>/#skills">Skills</a>
                <a href="<?php echo BASE_URL; ?>/#garden">Garden</a>
                <a href="<?php echo BASE_URL; ?>/#contact">Contact</a>
                <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="admin-link">Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main>