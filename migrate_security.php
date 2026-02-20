<?php
/**
 * Quick one-time migration: adds security_question and security_answer to users table.
 * Visit this in browser once, then delete it.
 */
$conn = new mysqli('localhost', 'root', '', 'portfolio_db');
if ($conn->connect_error) die('DB error: ' . $conn->connect_error);

$sqls = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS security_question VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS security_answer   VARCHAR(255) DEFAULT NULL",
];

echo '<pre>';
foreach ($sqls as $sql) {
    if ($conn->query($sql)) {
        echo "✅ " . htmlspecialchars($sql) . "\n";
    } else {
        echo "❌ " . htmlspecialchars($conn->error) . "\n";
    }
}
echo '</pre>';
echo '<br><a href="/protfolio/admin/">→ Go to Admin</a>';
echo '<p style="color:orange;">⚠️ Delete this file (migrate_security.php) after running.</p>';
$conn->close();
