-- ============================================================
-- Portfolio Database Upgrade v4 — Site Settings (Dynamic Labels)
-- Run this migration to add settings table for fully dynamic text
-- ============================================================

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    setting_label VARCHAR(100),
    setting_group VARCHAR(50),
    setting_type ENUM('text', 'textarea', 'url') DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_label, setting_group, setting_type) VALUES
-- Navbar
('nav_home', 'Home', 'Nav Link: Home', 'navbar', 'text'),
('nav_about', 'About', 'Nav Link: About', 'navbar', 'text'),
('nav_skills', 'Skills', 'Nav Link: Skills', 'navbar', 'text'),
('nav_projects', 'Projects', 'Nav Link: Projects', 'navbar', 'text'),
('nav_blog', 'Blog', 'Nav Link: Blog', 'navbar', 'text'),
('nav_cta', 'Get Started', 'Navbar CTA Button', 'navbar', 'text'),

-- Hero (Buttons)
('hero_cta_1', 'View Projects', 'Hero Button 1 Text', 'hero', 'text'),
('hero_cta_2', 'Get in Touch', 'Hero Button 2 Text', 'hero', 'text'),
('hero_cta_3', 'Resume', 'Hero Button 3 Text', 'hero', 'text'),

-- About
('about_label', 'Who I Am', 'Section Label', 'about', 'text'),
('about_title_1', 'About', 'Section Title (Part 1)', 'about', 'text'),
('about_title_2', 'Me', 'Section Title (Part 2 - Colored)', 'about', 'text'),

-- Skills
('skills_label', 'What I Know', 'Section Label', 'skills', 'text'),
('skills_title_1', 'Technical', 'Section Title (Part 1)', 'skills', 'text'),
('skills_title_2', 'Skills', 'Section Title (Part 2 - Colored)', 'skills', 'text'),
('skills_subtitle', 'A curated set of tools and technologies I use to build great products.', 'Section Subtitle', 'skills', 'textarea'),

-- Projects
('projects_label', 'What I\'ve Built', 'Section Label', 'projects', 'text'),
('projects_title_1', 'Featured', 'Section Title (Part 1)', 'projects', 'text'),
('projects_title_2', 'Projects', 'Section Title (Part 2 - Colored)', 'projects', 'text'),
('projects_subtitle', 'A selection of projects that showcase my skills and passion for building.', 'Section Subtitle', 'projects', 'textarea'),

-- Blog
('blog_label', 'Thoughts & Learnings', 'Section Label', 'blog', 'text'),
('blog_title_1', 'Latest', 'Section Title (Part 1)', 'blog', 'text'),
('blog_title_2', 'Articles', 'Section Title (Part 2 - Colored)', 'blog', 'text'),
('blog_subtitle', 'Insights, tutorials, and lessons from my development journey.', 'Section Subtitle', 'blog', 'textarea'),

-- Contact
('contact_label', 'Say Hello', 'Section Label', 'contact', 'text'),
('contact_title_1', 'Get In', 'Section Title (Part 1)', 'contact', 'text'),
('contact_title_2', 'Touch', 'Section Title (Part 2 - Colored)', 'contact', 'text'),
('contact_subtitle', 'Have a project in mind? Let\'s build something great together.', 'Section Subtitle', 'contact', 'textarea'),
('contact_button', 'Send Message', 'Submit Button Text', 'contact', 'text'),

-- Footer
('footer_text', 'Built with passion and lots of coffee.', 'Footer Slogan', 'footer', 'text'),
('footer_copyright', '© 2026 Sakthi. All rights reserved.', 'Footer Copyright', 'footer', 'text');
