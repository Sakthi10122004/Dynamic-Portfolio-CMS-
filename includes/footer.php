</main>

<?php
$profile = $profile ?? getProfile();
if (empty($isAdminPage)):
    ?>
    <footer class="site-footer" role="contentinfo">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="footer-logo"><?php echo escape($profile['name'] ?? SITE_NAME); ?></span>
                <p class="footer-tagline"><?php echo escape($profile['headline'] ?? 'Full-Stack Developer'); ?></p>
            </div>

            <nav class="footer-nav" aria-label="Footer navigation">
                <a href="<?php echo BASE_URL; ?>/#hero"><?php echo escape(getSetting('nav_home', 'Home')); ?></a>
                <a href="<?php echo BASE_URL; ?>/#about"><?php echo escape(getSetting('nav_about', 'About')); ?></a>
                <a href="<?php echo BASE_URL; ?>/#skills"><?php echo escape(getSetting('nav_skills', 'Skills')); ?></a>
                <a href="<?php echo BASE_URL; ?>/#projects"><?php echo escape(getSetting('nav_projects', 'Projects')); ?></a>
                <a href="<?php echo BASE_URL; ?>/#blog"><?php echo escape(getSetting('nav_blog', 'Blog')); ?></a>
                <a href="<?php echo BASE_URL; ?>/#contact">Contact</a>
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
            <p style="margin-bottom:.5rem"><?php echo escape(getSetting('footer_text', 'Built with passion and lots of coffee.')); ?></p>
            <p style="font-size:.8rem;color:var(--ink3)"><?php echo escape(getSetting('footer_copyright', '© 2026 Sakthi. All rights reserved.')); ?></p>
        </div>
    </footer>

    <!-- Back to top button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>
<?php endif; ?>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js" defer></script>
</body>

</html>