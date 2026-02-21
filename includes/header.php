<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($isAdminPage)): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
    <meta name="description" content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Full-Stack Developer — Premium dark portfolio'; ?>">
    <meta name="theme-color" content="#050508">
    <?php endif; ?>
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap"
          rel="stylesheet">

    <!-- Font Awesome (icons for admin + social) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Main CSS (preload) -->
    <link rel="preload" as="style" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet"         href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body<?php echo !empty($isAdminPage) ? ' class="admin-body"' : ''; ?>>

<!-- Scroll progress indicator -->
<div id="scrollProgress" aria-hidden="true"></div>

<!-- Cursor glow (hidden on mobile via CSS) -->
<div class="cursor-glow" aria-hidden="true"></div>

<?php if (empty($isAdminPage)): ?>
<nav class="navbar" id="navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>/" class="logo">✦ <?php echo escape(SITE_NAME); ?></a>

        <button class="menu-toggle" id="menuToggle"
                aria-label="Toggle navigation menu" aria-expanded="false"
                aria-controls="navLinks">
            <span></span><span></span><span></span>
        </button>

        <div class="nav-links" id="navLinks">
            <a href="<?php echo BASE_URL; ?>/#about">About</a>
            <a href="<?php echo BASE_URL; ?>/#work">Work</a>
            <a href="<?php echo BASE_URL; ?>/#skills">Skills</a>
            <a href="<?php echo BASE_URL; ?>/#experience">Journey</a>
            <a href="<?php echo BASE_URL; ?>/#contact">Contact</a>
            <?php if (isset($auth) && $auth->isLoggedIn()): ?>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="admin-link">
                <i class="fa-solid fa-gauge-simple-high" style="font-size:0.8rem"></i> Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<main>