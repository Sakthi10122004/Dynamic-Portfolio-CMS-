<?php
/**
 * upload_check.php — Diagnostic to verify InfinityFree upload setup.
 * REMOVE THIS FILE after confirming uploads work on production.
 * Access: https://sakthi.page.gd/upload_check.php
 */
require_once __DIR__ . '/includes/config.php';

echo '<style>body{font-family:monospace;background:#0a0a0f;color:#e8e8f0;padding:2rem;max-width:800px}
h2{color:#6c63ff}code{background:#1e1e2e;padding:.2rem .5rem;border-radius:4px;color:#00d4ff}
.ok{color:#39d98a}.err{color:#ff6b6b}.warn{color:#ffd166}
table{border-collapse:collapse;width:100%;margin:1rem 0}
td,th{border:1px solid #333;padding:.4rem .8rem;text-align:left}
</style>';

echo '<h2>🔍 Upload Diagnostic — sakthi.page.gd</h2>';
echo '<table>';
echo '<tr><th>Check</th><th>Value</th><th>Status</th></tr>';

// 1. UPLOAD_DIR path
echo '<tr><td>UPLOAD_DIR</td><td><code>' . htmlspecialchars(UPLOAD_DIR) . '</code></td>';
echo '<td>' . (is_dir(UPLOAD_DIR) ? '<span class="ok">✓ exists</span>' : '<span class="err">✗ missing</span>') . '</td></tr>';

// 2. Writable
echo '<tr><td>Writable?</td><td></td>';
echo '<td>' . (is_writable(UPLOAD_DIR) ? '<span class="ok">✓ writable</span>' : '<span class="err">✗ NOT writable — set 755 via file manager</span>') . '</td></tr>';

// 3. PHP upload settings
$uMax = ini_get('upload_max_filesize');
$pMax = ini_get('post_max_size');
echo "<tr><td>upload_max_filesize</td><td><code>$uMax</code></td><td class='ok'>ℹ️</td></tr>";
echo "<tr><td>post_max_size</td><td><code>$pMax</code></td><td class='ok'>ℹ️</td></tr>";

// 4. finfo available
echo '<tr><td>finfo extension</td><td></td>';
echo '<td>' . (function_exists('finfo_open') ? '<span class="ok">✓ available</span>' : '<span class="warn">⚠ not available — using fallback</span>') . '</td></tr>';

// 5. mime_content_type
echo '<tr><td>mime_content_type()</td><td></td>';
echo '<td>' . (function_exists('mime_content_type') ? '<span class="ok">✓ available</span>' : '<span class="warn">⚠ not available — using extension fallback</span>') . '</td></tr>';

// 6. UPLOAD_URL
echo '<tr><td>UPLOAD_URL</td><td><code>' . htmlspecialchars(UPLOAD_URL) . '</code></td><td class="ok">ℹ️</td></tr>';

// 7. Try to write a test file
$testFile = UPLOAD_DIR . '_test_' . time() . '.txt';
$written = @file_put_contents($testFile, 'test');
if ($written !== false) {
    @unlink($testFile);
    echo '<tr><td>Write test</td><td></td><td><span class="ok">✓ can write files</span></td></tr>';
} else {
    echo '<tr><td>Write test</td><td></td><td><span class="err">✗ Cannot write — permissions issue</span></td></tr>';
}

// 8. Existing uploads
$files = glob(UPLOAD_DIR . '*');
$imgFiles = array_filter($files ?? [], fn($f) => preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f));
echo '<tr><td>Images in uploads/</td><td>' . count($imgFiles) . ' files</td><td class="ok">ℹ️</td></tr>';

echo '</table>';

// Show images if any
if ($imgFiles) {
    echo '<h2>📁 Uploaded images</h2>';
    foreach (array_slice($imgFiles, 0, 5) as $f) {
        $fname = basename($f);
        $url = UPLOAD_URL . $fname;
        echo '<div style="margin:.5rem 0"><a href="' . htmlspecialchars($url) . '" target="_blank" style="color:#00d4ff">' . htmlspecialchars($fname) . '</a>';
        echo ' — <img src="' . htmlspecialchars($url) . '" style="max-height:60px;vertical-align:middle;margin-left:1rem;border:1px solid #333;border-radius:4px" onerror="this.outerHTML=\'<span class=err>❌ Could not load image from URL</span>\'"></div>';
    }
}

// 9. Quick upload form to test
echo '<h2>🧪 Test Upload</h2>';
echo '<p style="color:#9898b8;margin-bottom:1rem">Upload a test image to verify the full pipeline:</p>';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['test_img'])) {
    require_once __DIR__ . '/includes/functions.php';
    $r = uploadImage($_FILES['test_img']);
    if ($r['success']) {
        $url = UPLOAD_URL . $r['filename'];
        echo '<div class="ok">✓ Uploaded: <a href="' . htmlspecialchars($url) . '" target="_blank" style="color:#00d4ff">' . htmlspecialchars($r['filename']) . '</a></div>';
        echo '<img src="' . htmlspecialchars($url) . '" style="max-height:200px;margin-top:1rem;border:1px solid #6c63ff;border-radius:8px" onerror="this.outerHTML=\'<div class=err>✗ Image URL not loading — likely htaccess issue</div>\'">';
    } else {
        echo '<div class="err">✗ ' . htmlspecialchars($r['error']) . '</div>';
    }
}
echo '<form method="POST" enctype="multipart/form-data" style="margin-top:1rem">
  <input type="file" name="test_img" accept="image/*" style="color:#e8e8f0">
  <button type="submit" style="background:#6c63ff;color:#fff;border:0;padding:.4rem 1rem;border-radius:6px;cursor:pointer;margin-left:.5rem">Test Upload</button>
</form>';
