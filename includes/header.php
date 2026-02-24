<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($isAdminPage)): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
    <meta name="description" content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Full-Stack Developer — modern glassmorphism portfolio'; ?>">
    <meta name="theme-color" content="#0f0c29">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Full-Stack Developer Portfolio'; ?>">
    <meta property="og:type" content="website">
    <?php endif; ?>
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Fonts: Poppins + JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 (jsdelivr — reliable on InfinityFree) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <!-- FA fallback via cdnjs -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          onerror="this.remove()">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <?php if (!empty($isAdminPage)): ?>
    <!-- Admin extra: ensure CSS loads even if BASE_URL is misconfigured -->
    <link rel="stylesheet" href="../assets/css/style.css" onerror="this.remove()">
    <?php endif; ?>

    <!-- Anti-flash theme script -->
    <script>
      (function(){
        var t = localStorage.getItem('portfolio-theme');
        var d = window.matchMedia('(prefers-color-scheme:dark)').matches;
        document.documentElement.setAttribute('data-theme', t || (d ? 'dark' : 'light'));
      })();
    </script>
</head>
<body<?php echo !empty($isAdminPage) ? ' class="admin-body"' : ''; ?>>

<!-- ── Page Loader ─────────────────────────────────────── -->
<?php if (empty($isAdminPage)): ?>
<div id="page-loader" aria-hidden="true">
    <div class="loader-blob"></div>
</div>

<!-- ── Scroll Progress ─────────────────────────────────── -->
<div id="scroll-progress" aria-hidden="true"></div>

<!-- ── Floating Background Shapes ─────────────────────── -->
<div class="bg-shapes" aria-hidden="true">
    <div class="bg-shape bg-shape-1"></div>
    <div class="bg-shape bg-shape-2"></div>
    <div class="bg-shape bg-shape-3"></div>
    <div class="bg-shape bg-shape-4"></div>
</div>

<!-- ── Glassmorphism Navbar ────────────────────────────── -->
<header class="navbar" id="navbar" role="banner">
    <div class="navbar-inner">
        <a href="<?php echo BASE_URL; ?>/" class="navbar-brand" aria-label="Home">
            <div class="brand-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 40 40" fill="none">
                    <circle cx="20" cy="20" r="18" stroke="url(#ng)" stroke-width="2.5" fill="rgba(255,255,255,0.05)"/>
                    <path d="M12 28 L20 12 L28 28" stroke="url(#ng)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="15" y1="22" x2="25" y2="22" stroke="url(#ng)" stroke-width="2" stroke-linecap="round"/>
                    <defs><linearGradient id="ng" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse"><stop stop-color="#a78bfa"/><stop offset="1" stop-color="#38bdf8"/></linearGradient></defs>
                </svg>
            </div>
            <span class="brand-name"><?php echo escape(SITE_NAME); ?></span>
        </a>

        <nav class="navbar-nav" aria-label="Main navigation">
            <a href="#hero" class="nav-link">Home</a>
            <a href="#about" class="nav-link">About</a>
            <a href="#skills" class="nav-link">Skills</a>
            <a href="#projects" class="nav-link">Projects</a>
            <a href="#contact" class="nav-link">Contact</a>
        </nav>

        <div class="navbar-actions">
            <!-- Dark/Light toggle -->
            <button class="theme-toggle" aria-label="Toggle dark/light mode" id="themeToggle">
                <i class="fa-solid fa-moon icon-dark" aria-hidden="true"></i>
                <i class="fa-solid fa-sun icon-light" aria-hidden="true"></i>
            </button>

            <?php if (isset($auth) && $auth->isLoggedIn()): ?>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn-glass btn-sm">
                <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard
            </a>
            <?php endif; ?>

            <!-- Mobile menu toggle -->
            <button class="Nav-hamburger" aria-label="Toggle mobile menu" id="navToggle" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<?php endif; /* end non-admin navbar */ ?>

<main<?php echo !empty($isAdminPage) ? '' : ''; ?>>