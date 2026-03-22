-- ============================================================
-- Portfolio Database Schema v2 — Glassmorphism CMS Edition
-- Run this file or use setup.php for automatic installation
-- Default admin: admin / admin123
-- ============================================================

CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- ── Users (admin authentication) ────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    security_question VARCHAR(255) DEFAULT NULL,
    security_answer_hash VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Login rate limiting ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB;

-- ── Profile (single-row, portfolio owner info) ───────────────
CREATE TABLE IF NOT EXISTS profile (
    id INT PRIMARY KEY DEFAULT 1,
    name VARCHAR(100) NOT NULL DEFAULT 'Developer',
    headline VARCHAR(255) NOT NULL DEFAULT 'Full-Stack Developer',
    bio TEXT,
    email VARCHAR(100) NOT NULL DEFAULT 'hello@example.com',
    avatar VARCHAR(255) DEFAULT NULL,
    resume VARCHAR(255) DEFAULT NULL,
    github VARCHAR(255) DEFAULT NULL,
    linkedin VARCHAR(255) DEFAULT NULL,
    twitter VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Hero section (single-row) ────────────────────────────────
CREATE TABLE IF NOT EXISTS hero (
    id INT PRIMARY KEY DEFAULT 1,
    title VARCHAR(255) NOT NULL DEFAULT 'Building Digital Experiences',
    subtitle VARCHAR(500) DEFAULT 'Full-Stack Developer & Creative Technologist',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── About section (single-row) ───────────────────────────────
CREATE TABLE IF NOT EXISTS about (
    id INT PRIMARY KEY DEFAULT 1,
    content TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Social Links ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(500) NOT NULL,
    icon_class VARCHAR(100) DEFAULT 'fa-solid fa-link',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Projects ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    tech_stack VARCHAR(500) DEFAULT NULL,
    github_link VARCHAR(500) DEFAULT NULL,
    demo_link VARCHAR(500) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Skills ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('frontend', 'backend', 'devops', 'other') NOT NULL DEFAULT 'other',
    percentage INT NOT NULL DEFAULT 80,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Notes / Blog / Experience ────────────────────────────────
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    excerpt TEXT,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Contact messages ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    read_status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Default Data
-- ============================================================

-- Admin user (password: admin123)
INSERT INTO users (username, password_hash) VALUES
('admin', '$2y$12$QGR.F90V4yhkUUp30utEqOXZsQfy9bSz.l7OCXm.C5pUOuRGLHD.i')
ON DUPLICATE KEY UPDATE username = username;

-- Default profile
INSERT INTO profile (id, name, headline, bio, email) VALUES
(1, 'Sakthi', 'Full-Stack Developer & Creative Technologist',
 'I craft modern web experiences where clean code meets creative design. Passionate about turning ideas into reality through technology and innovation.',
 'hello@sakthi.dev')
ON DUPLICATE KEY UPDATE id = id;

-- Default hero section
INSERT INTO hero (id, title, subtitle) VALUES
(1, 'Building Digital Experiences', 'Full-Stack Developer & Creative Technologist — crafting modern web solutions with clean code and elegant design.')
ON DUPLICATE KEY UPDATE id = id;

-- Default about section
INSERT INTO about (id, content) VALUES
(1, 'I am a passionate full-stack developer with expertise in building modern, scalable web applications. I love working at the intersection of design and engineering, creating experiences that are both beautiful and functional.\n\nWith a strong foundation in PHP, JavaScript, MySQL, and modern frontend technologies, I bring ideas to life through clean, maintainable code. I am always exploring new technologies and pushing the boundaries of what is possible on the web.\n\nWhen I am not coding, you can find me contributing to open-source projects, writing technical articles, or exploring the latest trends in web development.')
ON DUPLICATE KEY UPDATE id = id;

-- Default social links
INSERT INTO social_links (platform, url, icon_class, display_order) VALUES
('GitHub', 'https://github.com/Sakthi10122004', 'fab fa-github', 1),
('LinkedIn', 'https://linkedin.com/in/sakthi', 'fab fa-linkedin', 2),
('Twitter', 'https://twitter.com/sakthi', 'fab fa-x-twitter', 3)
ON DUPLICATE KEY UPDATE platform = platform;

-- Default skills
INSERT INTO skills (name, category, percentage, display_order) VALUES
('HTML/CSS', 'frontend', 95, 1),
('JavaScript', 'frontend', 90, 2),
('React', 'frontend', 85, 3),
('TypeScript', 'frontend', 80, 4),
('Tailwind CSS', 'frontend', 88, 5),
('PHP', 'backend', 92, 1),
('Node.js', 'backend', 82, 2),
('Python', 'backend', 75, 3),
('MySQL', 'backend', 88, 4),
('REST APIs', 'backend', 90, 5),
('Git', 'devops', 90, 1),
('Docker', 'devops', 70, 2),
('Linux', 'devops', 80, 3),
('CI/CD', 'devops', 72, 4),
('AWS', 'devops', 68, 5)
ON DUPLICATE KEY UPDATE name = name;

-- Default project
INSERT INTO projects (title, description, tech_stack, github_link, demo_link, featured) VALUES
('Portfolio Website', 'A modern glassmorphism portfolio with full CMS admin panel, built with core PHP and MySQL. Features smooth parallax scrolling, animated skill bars, glass card UI, and a complete admin dashboard for managing all content dynamically.', 'PHP, MySQL, JavaScript, CSS3', 'https://github.com/Sakthi10122004/Dynamic-Portfolio-CMS-', '', 1)
ON DUPLICATE KEY UPDATE title = title;

-- Default blog note
INSERT INTO notes (title, excerpt, content, published) VALUES
('Getting Started with Web Development',
 'My journey into web development and the resources that helped me along the way.',
 'Web development is an exciting field that combines creativity with technical problem-solving.\n\nStart with the fundamentals: HTML, CSS, and JavaScript. Master these before jumping into frameworks.\n\nPractice by building real projects. The best way to learn is by doing.\n\nJoin developer communities — surround yourself with people who share your passion.',
 1)
ON DUPLICATE KEY UPDATE title = title;
