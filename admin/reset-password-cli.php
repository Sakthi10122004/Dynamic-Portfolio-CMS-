<?php
/**
 * reset-password-cli.php
 * ─────────────────────────────────────────────────────
 * ONE-TIME localhost-only tool to generate a bcrypt hash
 * and output the SQL UPDATE to copy into phpMyAdmin.
 *
 * HOW TO USE:
 *   1. Open http://localhost/protfolio/admin/reset-password-cli.php
 *   2. Copy the SQL shown on screen
 *   3. Run it in InfinityFree phpMyAdmin → SQL tab
 *   4. DELETE this file immediately after use
 *
 * SECURITY: Only accessible from 127.0.0.1 / ::1
 * ─────────────────────────────────────────────────────
 */

// ── Guard: localhost only ──────────────────────────────────
$allowedIPs = ['127.0.0.1', '::1', '::ffff:127.0.0.1'];
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs, true)) {
    http_response_code(403);
    exit('403 Forbidden — This script is localhost-only.');
}

// ── Configuration ─────────────────────────────────────────
$username    = 'sakthi';           // admin username to reset
$newPassword = 'Sakthi@2024!';     // ← CHANGE THIS before running

// ── Generate hash ─────────────────────────────────────────
$hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

// ── Build SQL ─────────────────────────────────────────────
$escapedUser = addslashes($username);
$escapedHash = addslashes($hash);

$sqlInsert = "INSERT INTO users (username, password_hash)\nVALUES ('{$escapedUser}', '{$escapedHash}')\nON DUPLICATE KEY UPDATE password_hash = '{$escapedHash}';";

$sqlUpdate = "UPDATE users SET password_hash = '{$escapedHash}' WHERE username = '{$escapedUser}' LIMIT 1;";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Reset Tool</title>
<style>
  body{font-family:monospace;background:#050508;color:#f0f0f8;padding:2rem;max-width:800px;margin:0 auto}
  h1{color:#7c6ef7;margin-bottom:1rem}
  .card{background:#1a1a28;border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:1.5rem;margin:1.5rem 0}
  pre{background:#050508;padding:1rem;border-radius:8px;overflow-x:auto;border:1px solid rgba(124,110,247,0.2);font-size:0.9rem;line-height:1.7;color:#06d6a0;white-space:pre-wrap;word-break:break-all}
  .warn{color:#f59e0b;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);padding:1rem;border-radius:8px;margin-top:1.5rem}
  label{font-size:0.8rem;color:#8888a8;margin-bottom:0.25rem;display:block;font-weight:600;text-transform:uppercase;letter-spacing:0.06em}
  .copy-btn{background:#7c6ef7;color:#fff;border:none;padding:0.4rem 0.9rem;border-radius:6px;font-size:0.82rem;cursor:pointer;float:right;margin-top:-2.5rem}
</style>
</head>
<body>
<h1>🔐 Admin Password Reset Tool</h1>

<div class="card">
  <label>Username</label>
  <pre><?php echo htmlspecialchars($username); ?></pre>

  <label>New Password (plaintext — do not share)</label>
  <pre><?php echo htmlspecialchars($newPassword); ?></pre>

  <label>Bcrypt Hash (cost=12) — generated at <?php echo date('Y-m-d H:i:s T'); ?></label>
  <pre><?php echo htmlspecialchars($hash); ?></pre>
</div>

<div class="card">
  <label>📋 SQL — Run in phpMyAdmin (INSERT or UPDATE)</label>
  <button class="copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('sql').innerText)">Copy</button>
  <pre id="sql"><?php echo htmlspecialchars($sqlInsert); ?></pre>
</div>

<div class="card">
  <label>📋 SQL — UPDATE only (if user already exists)</label>
  <button class="copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('sql2').innerText)">Copy</button>
  <pre id="sql2"><?php echo htmlspecialchars($sqlUpdate); ?></pre>
</div>

<div class="warn">
  ⚠️ <strong>IMPORTANT:</strong> Delete this file immediately after use.<br>
  Run: <code>rm admin/reset-password-cli.php</code> or delete via FTP/File Manager.
</div>
</body>
</html>
