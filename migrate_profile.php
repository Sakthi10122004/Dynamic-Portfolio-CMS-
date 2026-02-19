<?php
/**
 * Database Migration: Add avatar and resume columns to profile table
 * Run this once, then DELETE this file.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'portfolio_db');
if ($conn->connect_error) {
    die('DB error: ' . $conn->connect_error);
}

$steps = [];

// Add avatar column
$result = $conn->query("SHOW COLUMNS FROM profile LIKE 'avatar'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE profile ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER email");
    $steps[] = '✅ Added avatar column';
} else {
    $steps[] = '⚠️ avatar column already exists';
}

// Add resume column
$result = $conn->query("SHOW COLUMNS FROM profile LIKE 'resume'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE profile ADD COLUMN resume VARCHAR(255) DEFAULT NULL AFTER avatar");
    $steps[] = '✅ Added resume column';
} else {
    $steps[] = '⚠️ resume column already exists';
}

// Add github, linkedin, twitter columns
$socials = ['github' => 'resume', 'linkedin' => 'github', 'twitter' => 'linkedin'];
foreach ($socials as $col => $after) {
    $result = $conn->query("SHOW COLUMNS FROM profile LIKE '$col'");
    if ($result->num_rows === 0) {
        $conn->query("ALTER TABLE profile ADD COLUMN $col VARCHAR(255) DEFAULT NULL AFTER $after");
        $steps[] = "✅ Added $col column";
    } else {
        $steps[] = "⚠️ $col column already exists";
    }
}

$conn->close();

echo "<h2>Migration Complete</h2>";
foreach ($steps as $s) echo "<p>$s</p>";
echo "<p style='color:red;'>⚠️ DELETE this file now!</p>";
echo "<p><a href='/protfolio/admin/profile.php'>→ Go to Profile</a></p>";
