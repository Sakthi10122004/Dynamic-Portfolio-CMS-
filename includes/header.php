<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($isAdminPage)): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
    <meta name="description" content="<?php echo isset($pageDescription) ? escape($pageDescription) : 'Full-Stack Developer portfolio — bento grid, dark design'; ?>">
    <meta name="theme-color" content="#0a0a0f">
    <?php endif; ?>
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Preload critical font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap"
          rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Main stylesheet -->
    <link rel="preload" as="style" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet"         href="<?php echo BASE_URL; ?>/assets/css/style.css">

    <!-- Apply stored theme immediately to avoid flash -->
    <script>
      (function(){
        var t = localStorage.getItem('portfolio-theme');
        var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', t || (d ? 'dark' : 'light'));
      })();
    </script>
</head>
<body<?php echo !empty($isAdminPage) ? ' class="admin-body"' : ''; ?>>

<!-- Scroll progress bar -->
<div id="scroll-progress" aria-hidden="true"></div>

<!-- Cursor glow (hidden on touch devices via CSS/JS) -->
<div class="cursor-glow" aria-hidden="true"></div>

<?php if (empty($isAdminPage)): ?>

<!-- ══ COMMAND PALETTE OVERLAY ════════════════════════════ -->
<div id="palette-backdrop" class="palette-backdrop" role="dialog"
     aria-modal="true" aria-label="Navigation search">
    <div class="palette" role="combobox" aria-expanded="true">
        <div class="palette-header">
            <span class="palette-icon">⌘</span>
            <input id="palette-input" class="palette-input"
                   type="search" placeholder="Search or jump to…"
                   autocomplete="off" spellcheck="false" aria-autocomplete="list"
                   aria-controls="palette-list">
            <kbd class="palette-esc">ESC</kbd>
        </div>
        <ul id="palette-list" class="palette-list" role="listbox"></ul>
    </div>
</div>

<!-- ══ TOP BAR ════════════════════════════════════════════ -->
<header class="topbar" role="banner">
    <div class="topbar-inner">
        <a href="<?php echo BASE_URL; ?>/" class="topbar-logo" aria-label="Home">
            <span class="topbar-logo-dot" aria-hidden="true"></span>
            <?php echo escape(SITE_NAME); ?>
        </a>

        <div class="topbar-actions">
            <!-- Command palette trigger -->
            <button class="cmd-trigger" aria-label="Open navigation (Ctrl+K)"
                    aria-haspopup="dialog" aria-controls="palette-backdrop">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                Search &amp; Navigate
                <kbd>⌘K</kbd>
            </button>

            <!-- Theme toggle -->
            <button class="theme-toggle" aria-label="Toggle colour theme">
                <!-- Dark mode icon (shown by default) -->
                <svg class="icon-dark" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <!-- Light mode icon (shown in light theme) -->
                <svg class="icon-light" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </button>

            <?php if (isset($auth) && $auth->isLoggedIn()): ?>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php"
               class="btn btn-ghost btn-sm" style="border-color:var(--border)">
                Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php endif; ?>

<main>