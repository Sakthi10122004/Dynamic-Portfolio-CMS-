<?php
/* ─────────────────────────────────────────────────
   migrate_v4.php — Add dynamic settings
   Run once, then delete this file.
   ───────────────────────────────────────────────── */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db  = Database::getInstance();
$conn = $db->getConnection();
$out = [];

// 1. Create settings table
try {
    $conn->query("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT,
        setting_label VARCHAR(100),
        setting_group VARCHAR(50),
        setting_type ENUM('text', 'textarea', 'url') DEFAULT 'text'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $out[] = "✅ settings table ensured.";
} catch (Exception $e) {
    $out[] = "❌ Error creating settings: " . $e->getMessage();
}

$settings = [
    // Navbar
    ['nav_home', 'Home', 'Nav Link: Home', 'navbar', 'text'],
    ['nav_about', 'About', 'Nav Link: About', 'navbar', 'text'],
    ['nav_skills', 'Skills', 'Nav Link: Skills', 'navbar', 'text'],
    ['nav_projects', 'Projects', 'Nav Link: Projects', 'navbar', 'text'],
    ['nav_blog', 'Blog', 'Nav Link: Blog', 'navbar', 'text'],
    ['nav_cta', 'Get Started', 'Navbar CTA Button', 'navbar', 'text'],

    // Hero
    ['hero_badge', 'Available for work', 'Hero Top Badge', 'hero', 'text'],
    ['hero_cta_1', 'View Projects', 'Hero Button 1 Text', 'hero', 'text'],
    ['hero_cta_2', 'Get in Touch', 'Hero Button 2 Text', 'hero', 'text'],
    ['hero_cta_3', 'Resume', 'Hero Button 3 Text', 'hero', 'text'],

    // About
    ['about_label', 'Who I Am', 'Section Label', 'about', 'text'],
    ['about_title_1', 'About', 'Section Title (Part 1)', 'about', 'text'],
    ['about_title_2', 'Me', 'Section Title (Part 2)', 'about', 'text'],

    // Skills
    ['skills_label', 'What I Know', 'Section Label', 'skills', 'text'],
    ['skills_title_1', 'Technical', 'Section Title (Part 1)', 'skills', 'text'],
    ['skills_title_2', 'Skills', 'Section Title (Part 2)', 'skills', 'text'],
    ['skills_subtitle', 'A curated set of tools and technologies I use to build great products.', 'Section Subtitle', 'skills', 'textarea'],

    // Projects
    ['projects_label', 'What I\'ve Built', 'Section Label', 'projects', 'text'],
    ['projects_title_1', 'Featured', 'Section Title (Part 1)', 'projects', 'text'],
    ['projects_title_2', 'Projects', 'Section Title (Part 2)', 'projects', 'text'],
    ['projects_subtitle', 'A selection of projects that showcase my skills and passion for building.', 'Section Subtitle', 'projects', 'textarea'],

    // Blog
    ['blog_label', 'Thoughts & Learnings', 'Section Label', 'blog', 'text'],
    ['blog_title_1', 'Latest', 'Section Title (Part 1)', 'blog', 'text'],
    ['blog_title_2', 'Articles', 'Section Title (Part 2)', 'blog', 'text'],
    ['blog_subtitle', 'Insights, tutorials, and lessons from my development journey.', 'Section Subtitle', 'blog', 'textarea'],

    // Contact
    ['contact_label', 'Say Hello', 'Section Label', 'contact', 'text'],
    ['contact_title_1', 'Get In', 'Section Title (Part 1)', 'contact', 'text'],
    ['contact_title_2', 'Touch', 'Section Title (Part 2)', 'contact', 'text'],
    ['contact_subtitle', 'Have a project in mind? Let\'s build something great together.', 'Section Subtitle', 'contact', 'textarea'],
    ['contact_button', 'Send Message', 'Submit Button Text', 'contact', 'text'],

    // Footer
    ['footer_text', 'Built with passion and lots of coffee.', 'Footer Slogan', 'footer', 'textarea'],
    ['footer_copyright', '© 2026 Sakthi. All rights reserved.', 'Footer Copyright', 'footer', 'text']
];

foreach ($settings as $s) {
    try {
        $db->query(
            "INSERT IGNORE INTO settings (setting_key, setting_value, setting_label, setting_group, setting_type) VALUES (?, ?, ?, ?, ?)",
            [$s[0], $s[1], $s[2], $s[3], $s[4]],
            'sssss'
        );
    } catch (Exception $e) { }
}
$out[] = "✅ Inserted default settings.";

// Output results
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Migration v4</title>
<style>body{font-family:system-ui;background:#0a0818;color:#f1eeff;padding:2rem;max-width:600px;margin:2rem auto}
.card{background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.2);border-radius:16px;padding:1.5rem;margin-bottom:1rem}
h1{font-size:1.3rem;margin-bottom:1rem}p{margin:.4rem 0;font-size:.9rem;line-height:1.6}
a{color:#a78bfa;text-decoration:none}a:hover{color:#f472b6}
</style></head><body>
<div class='card'><h1>🔧 Migration v4 — Dynamic Labels (Settings Table)</h1>";
foreach ($out as $line) {
    echo "<p>{$line}</p>";
}
echo "</div><p style='margin-top:1rem;font-size:.85rem;color:#6e6590'>
⚠️ Delete this file after running: <code>migrate_v4.php</code><br>
<a href='" . BASE_URL . "/admin/dashboard.php'>← Back to Admin</a></p>
</body></html>";
