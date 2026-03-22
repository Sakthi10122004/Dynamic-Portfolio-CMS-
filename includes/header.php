<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($isAdminPage)): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <meta name="description"
            content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Professional Portfolio — Full-Stack Developer'; ?>">
        <meta name="theme-color" content="#8b5cf6">
        <meta property="og:title"
            content="<?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?>">
        <meta property="og:description"
            content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Professional Developer Portfolio'; ?>">
        <meta property="og:type" content="website">
    <?php endif; ?>
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Fonts: Poppins (headings) + Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Font Awesome 6 — self-hosted -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css" onerror="this.remove()">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <?php if (!empty($isAdminPage)): ?>
        <link rel="stylesheet" href="../assets/css/style.css" onerror="this.remove()">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
        <link rel="stylesheet" href="../assets/css/admin.css" onerror="this.remove()">
    <?php endif; ?>

    <!-- Anti-flash theme script -->
    <script>
        (function () {
            var t = localStorage.getItem('portfolio-theme');
            document.documentElement.setAttribute('data-theme', t || 'dark');
        })();
    </script>
</head>
<body<?php echo !empty($isAdminPage) ? ' class="admin-body"' : ''; ?>>

    <?php if (empty($isAdminPage)): ?>
        <!-- ── Page Loader ── -->
        <div id="page-loader" aria-hidden="true">
            <div class="loader-blob"></div>
        </div>

        <!-- ── Navbar (Floating Glass) ── -->
        <header class="navbar" id="navbar" role="banner">
            <div class="navbar-inner">
                <a href="<?php echo BASE_URL; ?>/" class="navbar-brand" aria-label="Home">
                    <div class="brand-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="17" stroke="white" stroke-width="2.5"
                                fill="rgba(255,255,255,0.1)" />
                            <path d="M13 29 L20 11 L27 29" stroke="white" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <line x1="15.5" y1="23" x2="24.5" y2="23" stroke="white" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                    <span class="brand-name"><?php echo escape(SITE_NAME); ?></span>
                </a>

                <nav class="navbar-nav" id="navMenu" aria-label="Main navigation">
                    <a href="#hero" class="nav-link">Home</a>
                    <a href="#about" class="nav-link">About</a>
                    <a href="#skills" class="nav-link">Skills</a>
                    <a href="#projects" class="nav-link">Projects</a>
                </nav>

                <div class="navbar-actions">
                    <button class="theme-toggle" aria-label="Toggle dark/light mode" id="themeToggle">
                        <i class="fa-solid fa-moon  icon-dark" aria-hidden="true"></i>
                        <i class="fa-solid fa-sun   icon-light" aria-hidden="true"></i>
                    </button>

                    <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn-glass btn-sm">
                            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard
                        </a>
                    <?php endif; ?>

                    <a href="#contact" class="nav-cta">
                        <i class="fa-solid fa-rocket" aria-hidden="true"></i> Get Started
                    </a>

                    <!-- Mobile menu toggle -->
                    <button class="nav-hamburger" aria-label="Toggle mobile menu" id="navToggle" aria-expanded="false">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </header>

    <?php endif; ?>

    <main>