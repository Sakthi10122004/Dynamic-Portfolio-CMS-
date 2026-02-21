    </main>

    <footer role="contentinfo">
        <div class="footer-inner">
            <?php $profile = $profile ?? []; ?>
            <p>© <?php echo date('Y'); ?> <?php echo escape($profile['name'] ?? SITE_NAME); ?></p>

            <nav class="footer-nav" aria-label="Footer navigation">
                <a href="#home">Home</a>
                <a href="#tech">Stack</a>
                <a href="#experience">Journey</a>
                <a href="#projects">Projects</a>
                <a href="#contact">Contact</a>
            </nav>

            <div class="footer-social">
                <?php if (!empty($profile['github'])): ?>
                <a href="<?php echo escape(sanitizeUrl($profile['github'])); ?>"
                   target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.21 11.39.6.11.79-.26.79-.58v-2.23c-3.34.73-4.03-1.42-4.03-1.42-.55-1.39-1.34-1.75-1.34-1.75-1.09-.74.08-.73.08-.73 1.2.08 1.84 1.24 1.84 1.24 1.07 1.83 2.8 1.3 3.49.99.11-.77.42-1.3.76-1.6-2.66-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.12-3.18 0 0 1-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.28-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.29c0 .32.19.7.8.58A12 12 0 0 0 24 12C24 5.37 18.63 0 12 0z"/>
                    </svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($profile['linkedin'])): ?>
                <a href="<?php echo escape(sanitizeUrl($profile['linkedin'])); ?>"
                   target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.44-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45C23.2 24 24 23.23 24 22.27V1.73C24 .77 23.2 0 22.22 0z"/>
                    </svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($profile['twitter'])): ?>
                <a href="<?php echo escape(sanitizeUrl($profile['twitter'])); ?>"
                   target="_blank" rel="noopener noreferrer" aria-label="X / Twitter">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24H16.17l-5.21-6.82-5.97 6.82H1.68l7.73-8.84L1.25 2.25H8.08l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.12z"/>
                    </svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/assets/js/main.js" defer></script>
</body>
</html>
