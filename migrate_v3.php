<?php
/* ─────────────────────────────────────────────────
   migrate_v3.php — Add blog images + fix messages
   Run once, then delete this file.
   ───────────────────────────────────────────────── */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db  = Database::getInstance();
$conn = $db->getConnection();
$out = [];

// 1. Add image column to notes table
try {
    $check = $conn->query("SHOW COLUMNS FROM notes LIKE 'image'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE notes ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER content");
        $out[] = "✅ Added 'image' column to notes table.";
    } else {
        $out[] = "⏭️ 'image' column already exists in notes.";
    }
} catch (Exception $e) {
    $out[] = "❌ Error adding image column: " . $e->getMessage();
}

// 2. Ensure contact_messages table exists
try {
    $conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        read_status TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $out[] = "✅ contact_messages table ensured.";
} catch (Exception $e) {
    $out[] = "❌ Error with contact_messages: " . $e->getMessage();
}

// 3. Ensure read_status column exists
try {
    $check = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'read_status'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE contact_messages ADD COLUMN read_status TINYINT(1) DEFAULT 0");
        $out[] = "✅ Added 'read_status' column to contact_messages.";
    } else {
        $out[] = "⏭️ 'read_status' column already exists in contact_messages.";
    }
} catch (Exception $e) {
    $out[] = "❌ Error adding read_status: " . $e->getMessage();
}

// 4. Ensure ip_address column exists
try {
    $check = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'ip_address'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE contact_messages ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL");
        $out[] = "✅ Added 'ip_address' column to contact_messages.";
    } else {
        $out[] = "⏭️ 'ip_address' column already exists in contact_messages.";
    }
} catch (Exception $e) {
    $out[] = "❌ Error adding ip_address: " . $e->getMessage();
}

// Output results
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Migration v3</title>
<style>body{font-family:system-ui;background:#0a0818;color:#f1eeff;padding:2rem;max-width:600px;margin:2rem auto}
.card{background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.2);border-radius:16px;padding:1.5rem;margin-bottom:1rem}
h1{font-size:1.3rem;margin-bottom:1rem}p{margin:.4rem 0;font-size:.9rem;line-height:1.6}
a{color:#a78bfa;text-decoration:none}a:hover{color:#f472b6}
</style></head><body>
<div class='card'><h1>🔧 Migration v3 — Blog Images + Messages Fix</h1>";
foreach ($out as $line) {
    echo "<p>{$line}</p>";
}
echo "</div><p style='margin-top:1rem;font-size:.85rem;color:#6e6590'>
⚠️ Delete this file after running: <code>migrate_v3.php</code><br>
<a href='" . BASE_URL . "/admin/dashboard.php'>← Back to Admin</a></p>
</body></html>";
