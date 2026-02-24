<?php
/* migrate_v2.php — CLI-safe DB migration for Portfolio CMS v2 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'portfolio_db');

// Connect WITHOUT selecting a database first
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
if (!$conn) {
    die("MySQL connection failed: " . mysqli_connect_error() . "\n");
}
echo "Connected to MySQL server.\n";

// Create DB if missing
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
mysqli_select_db($conn, DB_NAME);
echo "Using database: " . DB_NAME . "\n\n";

$statements = [
    // Core tables
    "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, security_question VARCHAR(255) DEFAULT NULL, security_answer_hash VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS login_attempts (id INT AUTO_INCREMENT PRIMARY KEY, ip_address VARCHAR(45) NOT NULL, attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_ip_time (ip_address, attempted_at)) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS profile (id INT PRIMARY KEY DEFAULT 1, name VARCHAR(100) NOT NULL DEFAULT 'Sakthi', headline VARCHAR(255) NOT NULL DEFAULT 'Full-Stack Developer', bio TEXT, email VARCHAR(100) NOT NULL DEFAULT 'hello@sakthi.dev', avatar VARCHAR(255) DEFAULT NULL, resume VARCHAR(255) DEFAULT NULL, github VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS hero (id INT PRIMARY KEY DEFAULT 1, title VARCHAR(255) NOT NULL DEFAULT 'Building Digital Experiences', subtitle VARCHAR(500) DEFAULT 'Full-Stack Developer & Creative Technologist', updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS about (id INT PRIMARY KEY DEFAULT 1, content TEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS social_links (id INT AUTO_INCREMENT PRIMARY KEY, platform VARCHAR(50) NOT NULL, url VARCHAR(500) NOT NULL, icon_class VARCHAR(100) DEFAULT 'fa-solid fa-link', display_order INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS projects (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, description TEXT, tech_stack VARCHAR(500) DEFAULT NULL, github_link VARCHAR(500) DEFAULT NULL, demo_link VARCHAR(500) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, featured TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS skills (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, category ENUM('frontend','backend','devops','other') NOT NULL DEFAULT 'other', percentage INT NOT NULL DEFAULT 80, display_order INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS notes (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, excerpt TEXT, content TEXT NOT NULL, published TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS contact_messages (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, message TEXT NOT NULL, ip_address VARCHAR(45), read_status TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB",

    // Column additions (will fail silently if already exist)
    "ALTER TABLE projects ADD COLUMN tech_stack VARCHAR(500) DEFAULT NULL",
    "ALTER TABLE projects ADD COLUMN demo_link   VARCHAR(500) DEFAULT NULL",
    "ALTER TABLE skills   ADD COLUMN percentage  INT NOT NULL DEFAULT 80",

    // Seed admin user (password: admin123)
    "INSERT IGNORE INTO users (username, password_hash) VALUES ('admin', '\$2y\$12\$QGR.F90V4yhkUUp30utEqOXZsQfy9bSz.l7OCXm.C5pUOuRGLHD.i')",

    // Seed profile
    "INSERT IGNORE INTO profile (id, name, headline, bio, email) VALUES (1, 'Sakthi', 'Full-Stack Developer & Creative Technologist', 'Building modern web experiences with clean code and creative design.', 'hello@sakthi.dev')",

    // Seed hero
    "INSERT IGNORE INTO hero (id, title, subtitle) VALUES (1, 'Building Digital Experiences', 'Full-Stack Developer & Creative Technologist — crafting modern web solutions.')",

    // Seed about
    "INSERT IGNORE INTO about (id, content) VALUES (1, 'I am a passionate full-stack developer with expertise in building modern, scalable web applications.\n\nWith a strong foundation in PHP, JavaScript, and MySQL, I bring ideas to life through clean, maintainable code.')",

    // Seed social links
    "INSERT IGNORE INTO social_links (id, platform, url, icon_class, display_order) VALUES (1, 'GitHub', 'https://github.com/Sakthi10122004', 'fab fa-github', 1),(2, 'LinkedIn', 'https://linkedin.com', 'fab fa-linkedin', 2)",

    // Seed skills
    "INSERT IGNORE INTO skills (name, category, percentage, display_order) VALUES ('HTML/CSS','frontend',95,1),('JavaScript','frontend',90,2),('React','frontend',85,3),('PHP','backend',92,1),('MySQL','backend',88,2),('Node.js','backend',82,3),('Git','devops',90,1),('Docker','devops',70,2)",

    // Seed project
    "INSERT IGNORE INTO projects (title, description, tech_stack, github_link, featured) VALUES ('Portfolio CMS', 'Modern glassmorphism portfolio with full admin panel built with PHP & MySQL.', 'PHP, MySQL, JavaScript, CSS3', 'https://github.com/Sakthi10122004/Dynamic-Portfolio-CMS-', 1)",

    // Seed note
    "INSERT IGNORE INTO notes (title, excerpt, content, published) VALUES ('Getting Started with Web Development', 'My journey into web development.', 'Start with fundamentals: HTML, CSS, JavaScript. Master these before frameworks.', 1)",
];

foreach ($statements as $sql) {
    $result = mysqli_query($conn, $sql);
    $short  = substr(trim(preg_replace('/\s+/', ' ', $sql)), 0, 80);
    if ($result) {
        echo "[OK]   $short\n";
    } else {
        $err = mysqli_error($conn);
        if (preg_match('/Duplicate column|already exists/i', $err)) {
            echo "[SKIP] $short\n";
        } else {
            echo "[ERR]  $err\n";
        }
    }
}

mysqli_close($conn);
echo "\n=== Migration complete! ===\n";
