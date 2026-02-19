<?php
// Quick fix: Reset admin password
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'portfolio_db');
if ($conn->connect_error) {
    die('DB error: ' . $conn->connect_error);
}

$hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);

// Delete old admin and re-insert with correct hash
$conn->query("DELETE FROM users WHERE username = 'admin'");
$stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
$username = 'admin';
$stmt->bind_param('ss', $username, $hash);
$stmt->execute();

echo "<h2>Admin password reset!</h2>";
echo "<p>Username: <b>admin</b></p>";
echo "<p>Password: <b>admin123</b></p>";
echo "<p>Hash: <code>$hash</code></p>";
echo "<p><a href='/protfolio/admin/'>Go to Login →</a></p>";
echo "<p style='color:red;'>⚠️ DELETE this file (fix_admin.php) now!</p>";

$conn->close();
