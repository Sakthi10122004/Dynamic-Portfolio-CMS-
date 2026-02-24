-- ============================================================
-- InfinityFree / cPanel MySQL Upgrade Script
-- Portfolio CMS v2 — Run this in phpMyAdmin SQL tab
-- ============================================================
-- Select your database first (top-left dropdown in phpMyAdmin)
-- Then paste ALL of this into the SQL tab and click "Go"
-- ============================================================

-- ── 1. Add new columns to existing tables ────────────────────
-- (Safe: errors silently if column already exists — just re-run)

ALTER TABLE `projects`
  ADD COLUMN IF NOT EXISTS `tech_stack` VARCHAR(500) DEFAULT NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `demo_link`  VARCHAR(500) DEFAULT NULL AFTER `github_link`;

ALTER TABLE `skills`
  ADD COLUMN IF NOT EXISTS `percentage` INT NOT NULL DEFAULT 80 AFTER `category`;

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `security_question`    VARCHAR(255) DEFAULT NULL AFTER `password_hash`,
  ADD COLUMN IF NOT EXISTS `security_answer_hash` VARCHAR(255) DEFAULT NULL AFTER `security_question`;

-- ── 2. Create new tables ──────────────────────────────────────

CREATE TABLE IF NOT EXISTS `hero` (
    `id`         INT PRIMARY KEY DEFAULT 1,
    `title`      VARCHAR(255) NOT NULL DEFAULT 'Building Digital Experiences',
    `subtitle`   VARCHAR(500) DEFAULT 'Full-Stack Developer & Creative Technologist',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `about` (
    `id`         INT PRIMARY KEY DEFAULT 1,
    `content`    TEXT NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `social_links` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `platform`      VARCHAR(50)  NOT NULL,
    `url`           VARCHAR(500) NOT NULL,
    `icon_class`    VARCHAR(100) DEFAULT 'fa-solid fa-link',
    `display_order` INT DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Seed default data (INSERT IGNORE = skips if row exists) ──

INSERT IGNORE INTO `hero` (`id`, `title`, `subtitle`) VALUES
  (1, 'Building Digital Experiences',
      'Full-Stack Developer & Creative Technologist — crafting modern web solutions.');

INSERT IGNORE INTO `about` (`id`, `content`) VALUES
  (1, 'I am a passionate full-stack developer with expertise in building modern, scalable web applications.\n\nWith a strong foundation in PHP, JavaScript, and MySQL, I bring ideas to life through clean, maintainable code. Always exploring new technologies and pushing the boundaries of what is possible on the web.');

-- Add your social links below (remove or adjust as needed):
INSERT IGNORE INTO `social_links` (`id`, `platform`, `url`, `icon_class`, `display_order`) VALUES
  (1, 'GitHub',   'https://github.com/Sakthi10122004',    'fab fa-github',   1),
  (2, 'LinkedIn', 'https://linkedin.com/in/yourprofile',  'fab fa-linkedin', 2);

-- ── Done! ──────────────────────────────────────────────────────
-- After running:
--   1. Log in to your admin panel
--   2. Go to Hero, About, Social Links and fill in your data
--   3. Upload your avatar and resume via Profile
-- ── ──────────────────────────────────────────────────────────
