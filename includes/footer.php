</main>

<?php if (empty($isAdminPage)): ?>
    <footer class="site-footer" role="contentinfo">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="footer-logo"><?php echo escape($profile['name'] ?? SITE_NAME); ?></span>
                <p class="footer-tagline">Crafting digital experiences with passion.</p>
            </div>

            <nav class="footer-nav" aria-label="Footer navigation">
                <a href="#hero">Home</a>
                <a href="#about">About</a>
                <a href="#skills">Skills</a>
                <a href="#projects">Projects</a>
                <a href="#contact">Contact</a>
            </nav>

            <div class="footer-social">
                <?php
                $footerSocials = getSocialLinks();
                if ($footerSocials):
                    foreach ($footerSocials as $sl):
                        $url = sanitizeUrl($sl['url']);
                        if (!$url)
                            continue;
                        ?>
                        <a href="<?php echo escape($url); ?>" target="_blank" rel="noopener noreferrer"
                            aria-label="<?php echo escape($sl['platform']); ?>" class="social-icon-btn">
                            <i class="<?php echo escape($sl['icon_class']); ?>" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; else: ?>
                    <?php if (!empty($profile['github'])): ?>
                        <a href="<?php echo escape(sanitizeUrl($profile['github'])); ?>" target="_blank" rel="noopener noreferrer"
                            aria-label="GitHub" class="social-icon-btn">
                            <i class="fa-brands fa-github" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($profile['linkedin'])): ?>
                        <a href="<?php echo escape(sanitizeUrl($profile['linkedin'])); ?>" target="_blank" rel="noopener noreferrer"
                            aria-label="LinkedIn" class="social-icon-btn">
                            <i class="fa-brands fa-linkedin" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> &nbsp;
                <?php echo escape($profile['name'] ?? SITE_NAME); ?>.
                Built with <i class="fa-solid fa-heart"></i> using PHP &amp; MySQL.
            </p>
        </div>
    </footer>
<?php endif; ?>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js" defer></script>
</body>

</html>