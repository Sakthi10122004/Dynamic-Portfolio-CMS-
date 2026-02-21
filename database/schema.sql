-- ============================================================
-- Portfolio Database Schema
-- Run this file or use setup.php for automatic installation
-- Default admin: admin / admin123
-- ============================================================

CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- Users table (admin authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    security_question VARCHAR(255) DEFAULT NULL,
    security_answer_hash VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Login rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB;

-- Profile table (single-row, portfolio owner info)
CREATE TABLE IF NOT EXISTS profile (
    id INT PRIMARY KEY DEFAULT 1,
    name VARCHAR(100) NOT NULL DEFAULT 'Sakthi',
    headline VARCHAR(255) NOT NULL DEFAULT 'Full-Stack Developer',
    bio TEXT,
    email VARCHAR(100) NOT NULL DEFAULT 'hello@sakthi.dev',
    avatar VARCHAR(255) DEFAULT NULL,
    resume VARCHAR(255) DEFAULT NULL,
    github VARCHAR(255) DEFAULT NULL,
    linkedin VARCHAR(255) DEFAULT NULL,
    twitter VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    github_link VARCHAR(500),
    image VARCHAR(255),
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Skills table
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('frontend', 'backend', 'devops') NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Notes table (digital garden)
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    excerpt TEXT,
    content TEXT NOT NULL,
    published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Contact messages
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
 'I build modern web experiences with clean code and creative design. Passionate about turning ideas into reality through technology.',
 'hello@sakthi.dev')
ON DUPLICATE KEY UPDATE id = id;

-- Sample skills
INSERT INTO skills (name, category, display_order) VALUES 
('HTML/CSS', 'frontend', 1),
('JavaScript', 'frontend', 2),
('React', 'frontend', 3),
('TypeScript', 'frontend', 4),
('Tailwind CSS', 'frontend', 5),
('PHP', 'backend', 1),
('Node.js', 'backend', 2),
('Python', 'backend', 3),
('MySQL', 'backend', 4),
('REST APIs', 'backend', 5),
('Git', 'devops', 1),
('Docker', 'devops', 2),
('Linux', 'devops', 3),
('CI/CD', 'devops', 4),
('AWS', 'devops', 5)
ON DUPLICATE KEY UPDATE name = name;

-- Sample project
INSERT INTO projects (title, description, github_link, featured) VALUES 
('Portfolio Website', 'A modern, dark-themed portfolio with bento grid layout, built with core PHP and MySQL. Features an admin panel for managing projects, skills, notes, and contact messages.', 'https://github.com/sakthi/portfolio', 1)
ON DUPLICATE KEY UPDATE title = title;

-- Sample note
INSERT INTO notes (title, excerpt, content, published) VALUES 
('Getting Started with Web Development', 
 'My journey into web development and the resources that helped me along the way.',
 'Web development is an exciting field that combines creativity with technical problem-solving. Here are some resources and tips that helped me on my journey...\n\nStart with the fundamentals: HTML, CSS, and JavaScript. Master these before jumping into frameworks.\n\nPractice by building real projects. The best way to learn is by doing.\n\nJoin developer communities. Surround yourself with people who share your passion.',
 1)
ON DUPLICATE KEY UPDATE title = title;
