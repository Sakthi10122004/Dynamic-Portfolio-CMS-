<?php
/**
 * One-Click Database Setup
 * 
 * Visit this file in your browser to automatically create the database and tables.
 * DELETE THIS FILE after setup is complete for security.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Detect environment
$isLocalhost = (
    strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false
);

if ($isLocalhost) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'portfolio_db';
} else {
    // Update these for InfinityFree
    $host = 'sql300.infinityfree.com';
    $user = 'if0_your_username';
    $pass = 'your_password_here';
    $dbname = 'if0_your_dbname';
}

$steps = [];
$hasError = false;

// Step 1: Connect to MySQL (without database)
try {
    $conn = new mysqli($host, $user, $pass);
    $conn->set_charset('utf8mb4');
    $steps[] = ['✅', 'Connected to MySQL server'];
} catch (Exception $e) {
    $steps[] = ['❌', 'Cannot connect to MySQL: ' . $e->getMessage()];
    $hasError = true;
    goto output;
}

// Step 2: Create database
try {
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($dbname);
    $steps[] = ['✅', "Database '$dbname' ready"];
} catch (Exception $e) {
    $steps[] = ['❌', 'Cannot create database: ' . $e->getMessage()];
    $hasError = true;
    goto output;
}

// Step 3: Create tables
$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        security_question VARCHAR(255) DEFAULT NULL,
        security_answer VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    'profile' => "CREATE TABLE IF NOT EXISTS profile (
        id INT PRIMARY KEY DEFAULT 1,
        name VARCHAR(100) NOT NULL DEFAULT 'Sakthi',
        headline VARCHAR(255) NOT NULL DEFAULT 'Full-Stack Developer',
        bio TEXT,
        email VARCHAR(100) NOT NULL DEFAULT 'hello@sakthi.dev',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    'projects' => "CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        github_link VARCHAR(500),
        image VARCHAR(255),
        featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    'skills' => "CREATE TABLE IF NOT EXISTS skills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category ENUM('frontend', 'backend', 'devops') NOT NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    'notes' => "CREATE TABLE IF NOT EXISTS notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        excerpt TEXT,
        content TEXT NOT NULL,
        published TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    'contact_messages' => "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        ip_address VARCHAR(45),
        read_status TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB"
];

foreach ($tables as $name => $sql) {
    try {
        $conn->query($sql);
        $steps[] = ['✅', "Table '$name' created"];
    } catch (Exception $e) {
        $steps[] = ['❌', "Table '$name' failed: " . $e->getMessage()];
        $hasError = true;
    }
}

// Step 3b: Migrate existing users table (add security columns if missing)
$migrations = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS security_question VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS security_answer   VARCHAR(255) DEFAULT NULL",
];
foreach ($migrations as $sql) {
    try {
        $conn->query($sql);
    } catch (Exception $e) {
        // Ignore — column may already exist on older MySQL without IF NOT EXISTS
    }
}
$steps[] = ['✅', 'Users table migration applied (security question columns)'];

// Step 4: Insert default data
// Generate proper password hash
$passwordHash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);

$defaults = [
    ["INSERT IGNORE INTO users (username, password_hash) VALUES ('admin', '$passwordHash')", 'Default admin user created (admin / admin123)'],
    ["INSERT IGNORE INTO profile (id, name, headline, bio, email) VALUES (1, 'Sakthi', 'Full-Stack Developer & Creative Technologist', 'I build modern web experiences with clean code and creative design.', 'hello@sakthi.dev')", 'Default profile created'],
];

// Sample skills
$skills = [
    ['HTML/CSS', 'frontend', 1], ['JavaScript', 'frontend', 2], ['React', 'frontend', 3],
    ['TypeScript', 'frontend', 4], ['Tailwind CSS', 'frontend', 5],
    ['PHP', 'backend', 1], ['Node.js', 'backend', 2], ['Python', 'backend', 3],
    ['MySQL', 'backend', 4], ['REST APIs', 'backend', 5],
    ['Git', 'devops', 1], ['Docker', 'devops', 2], ['Linux', 'devops', 3],
    ['CI/CD', 'devops', 4], ['AWS', 'devops', 5]
];

foreach ($defaults as [$sql, $msg]) {
    try {
        $conn->query($sql);
        $steps[] = ['✅', $msg];
    } catch (Exception $e) {
        $steps[] = ['⚠️', $msg . ' (may already exist)'];
    }
}

// Insert skills
$skillCount = $conn->query("SELECT COUNT(*) as c FROM skills")->fetch_assoc()['c'];
if ($skillCount == 0) {
    foreach ($skills as [$name, $cat, $order]) {
        $stmt = $conn->prepare("INSERT INTO skills (name, category, display_order) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $name, $cat, $order);
        $stmt->execute();
    }
    $steps[] = ['✅', '15 sample skills inserted'];
} else {
    $steps[] = ['⚠️', "Skills table already has data ($skillCount skills)"];
}

// Insert sample project
$projCount = $conn->query("SELECT COUNT(*) as c FROM projects")->fetch_assoc()['c'];
if ($projCount == 0) {
    $conn->query("INSERT INTO projects (title, description, github_link, featured) VALUES ('Portfolio Website', 'A modern dark-themed portfolio with bento grid layout, built with core PHP and MySQL.', 'https://github.com/sakthi/portfolio', 1)");
    $steps[] = ['✅', 'Sample project inserted'];
}

// Insert sample note
$noteCount = $conn->query("SELECT COUNT(*) as c FROM notes")->fetch_assoc()['c'];
if ($noteCount == 0) {
    $conn->query("INSERT INTO notes (title, excerpt, content, published) VALUES ('Getting Started with Web Development', 'My journey into web development and the resources that helped me.', 'Web development is an exciting field...', 1)");
    $steps[] = ['✅', 'Sample note inserted'];
}

// Create uploads directory
$uploadsDir = __DIR__ . '/assets/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
    $steps[] = ['✅', 'Uploads directory created'];
} else {
    $steps[] = ['✅', 'Uploads directory exists'];
}

$conn->close();

output:

$baseUrl = $isLocalhost ? '/protfolio' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup | Portfolio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0a0a0f; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .setup-card { background: #111118; border: 1px solid #2a2a35; border-radius: 24px; padding: 3rem; max-width: 600px; width: 90%; }
        h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .subtitle { color: #a0a0b0; margin-bottom: 2rem; }
        .step { padding: 0.75rem 0; border-bottom: 1px solid #1a1a24; display: flex; gap: 0.75rem; align-items: center; }
        .step:last-child { border-bottom: none; }
        .step-icon { font-size: 1.2rem; }
        .step-text { color: #a0a0b0; }
        .result { margin-top: 2rem; padding: 1.5rem; border-radius: 12px; }
        .result.success { background: rgba(0, 255, 100, 0.1); border: 1px solid #00ff64; }
        .result.error { background: rgba(255, 0, 0, 0.1); border: 1px solid #ff4444; }
        .result h2 { margin-bottom: 0.5rem; }
        .result a { color: #6c5ce7; text-decoration: none; font-weight: 600; }
        .result a:hover { text-decoration: underline; }
        .creds { background: #1a1a24; padding: 1rem; border-radius: 8px; margin-top: 1rem; font-family: monospace; }
        .warn { color: #ffaa00; margin-top: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h1>⚡ Database Setup</h1>
        <p class="subtitle">Setting up your portfolio database...</p>
        
        <?php foreach ($steps as [$icon, $text]): ?>
        <div class="step">
            <span class="step-icon"><?php echo $icon; ?></span>
            <span class="step-text"><?php echo htmlspecialchars($text); ?></span>
        </div>
        <?php endforeach; ?>
        
        <?php if (!$hasError): ?>
        <div class="result success">
            <h2>🎉 Setup Complete!</h2>
            <p>Your portfolio is ready.</p>
            <div class="creds">
                <strong>Admin Login:</strong><br>
                Username: admin<br>
                Password: admin123
            </div>
            <p style="margin-top: 1rem;">
                <a href="<?php echo $baseUrl; ?>/">→ View Portfolio</a> &nbsp;|&nbsp;
                <a href="<?php echo $baseUrl; ?>/admin/">→ Admin Login</a>
            </p>
            <p class="warn">⚠️ Delete this file (setup.php) after setup for security.</p>
        </div>
        <?php else: ?>
        <div class="result error">
            <h2>Setup encountered errors</h2>
            <p>Please fix the errors above and try again. Make sure MySQL is running.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
