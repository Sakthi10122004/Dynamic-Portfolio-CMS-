<?php
/**
 * fix_avatar.php — Fix avatar not showing on homepage
 * Visit: https://sakthi.page.gd/fix_avatar.php
 * DELETE THIS FILE after fixing.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo '<style>
body{font-family:monospace;background:#0a0a0f;color:#e8e8f0;padding:2rem;max-width:900px;margin:0 auto}
h2{color:#6c63ff;margin:1.5rem 0 .8rem}
.ok{color:#39d98a}.err{color:#ff6b6b}.warn{color:#ffd166}
code{background:#1e1e2e;padding:.2rem .5rem;border-radius:4px;color:#00d4ff}
table{border-collapse:collapse;width:100%;margin:1rem 0}
td,th{border:1px solid #333;padding:.5rem .8rem;vertical-align:middle}
th{background:#1e1e2e;color:#9898b8}
.img-thumb{max-height:50px;border-radius:4px;border:1px solid #333}
button,input[type=submit]{background:#6c63ff;color:#fff;border:0;padding:.5rem 1.2rem;border-radius:6px;cursor:pointer;font-size:.9rem}
button:hover{background:#8b83ff}
.info-box{background:#1e1e2e;border:1px solid #333;border-radius:8px;padding:1rem;margin:1rem 0}
hr{border:0;border-top:1px solid #222;margin:2rem 0}
</style>';

echo '<h2>🔧 Avatar Fix Tool</h2>';

$db = Database::getInstance();

// --- ACTION: Set avatar in DB ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['set_avatar'])) {
    $filename = basename($_POST['set_avatar']); // sanitize
    // Verify file exists
    if (file_exists(UPLOAD_DIR . $filename)) {
        $existing = $db->getRow("SELECT id, avatar FROM profile WHERE id = 1");
        if ($existing) {
            $db->query("UPDATE profile SET avatar = ? WHERE id = 1", [$filename], 's');
            echo '<div class="ok" style="padding:1rem;background:#0d2e1a;border-radius:8px;margin:1rem 0">
                ✅ <strong>Avatar updated!</strong> Set to: <code>' . htmlspecialchars($filename) . '</code><br>
                <a href="https://sakthi.page.gd/" target="_blank" style="color:#00d4ff">→ Check homepage now</a>
            </div>';
        } else {
            echo '<div class="err">✗ No profile row found (id=1). Create the profile first via admin panel.</div>';
        }
    } else {
        echo '<div class="err">✗ File not found: ' . htmlspecialchars($filename) . '</div>';
    }
}

// --- ACTION: Upload new avatar directly ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['new_avatar']['name'])) {
    require_once __DIR__ . '/includes/functions.php';
    $r = uploadImage($_FILES['new_avatar']);
    if ($r['success']) {
        $filename = $r['filename'];
        $existing = $db->getRow("SELECT id FROM profile WHERE id = 1");
        if ($existing) {
            $db->query("UPDATE profile SET avatar = ? WHERE id = 1", [$filename], 's');
            echo '<div class="ok" style="padding:1rem;background:#0d2e1a;border-radius:8px;margin:1rem 0">
                ✅ <strong>Uploaded & saved!</strong> Filename: <code>' . htmlspecialchars($filename) . '</code><br>
                <img src="/assets/uploads/' . htmlspecialchars($filename) . '" style="max-height:100px;margin-top:.5rem;border-radius:8px"><br>
                <a href="https://sakthi.page.gd/" target="_blank" style="color:#00d4ff;margin-top:.5rem;display:inline-block">→ Check homepage now</a>
            </div>';
        } else {
            echo '<div class="err">✗ File uploaded but no profile row in DB. Create profile via admin first.</div>';
        }
    } else {
        echo '<div class="err">✗ Upload failed: ' . htmlspecialchars($r['error']) . '</div>';
    }
}

// --- Show current DB state ---
$profile = $db->getRow("SELECT id, name, avatar FROM profile WHERE id = 1");
echo '<h2>📊 Current Database State</h2>';
echo '<div class="info-box">';
if ($profile) {
    echo '<p>Profile row found: ✅</p>';
    echo '<p>Name: <code>' . htmlspecialchars($profile['name'] ?? 'NULL') . '</code></p>';
    echo '<p>Avatar in DB: ';
    if ($profile['avatar']) {
        $avatarFile = $profile['avatar'];
        $avatarPath = UPLOAD_DIR . $avatarFile;
        $avatarUrl = '/assets/uploads/' . $avatarFile;
        echo '<code>' . htmlspecialchars($avatarFile) . '</code>';
        if (file_exists($avatarPath)) {
            echo ' <span class="ok">✓ file exists</span>';
            echo '<br><img src="' . htmlspecialchars($avatarUrl) . '" class="img-thumb" style="max-height:80px;margin-top:.5rem"
                onerror="this.outerHTML=\'<span class=err>❌ File exists on disk but URL not serving (htaccess issue?)</span>\'">';
        } else {
            echo ' <span class="err">✗ file MISSING from disk!</span>';
        }
    } else {
        echo '<span class="err">NULL — this is why the placeholder shows!</span>';
    }
    echo '</p>';
} else {
    echo '<span class="err">No profile row with id=1 found in database!</span>';
}
echo '</div>';

// --- Show all images in uploads ---
echo '<h2>📁 Images in uploads/ directory (' . UPLOAD_DIR . ')</h2>';
$allFiles = glob(UPLOAD_DIR . '*');
$imgFiles = array_filter($allFiles ?? [], fn($f) => preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f));

if ($imgFiles) {
    echo '<p style="color:#9898b8;margin-bottom:.5rem">Click "Use This" to set any image as the profile avatar:</p>';
    echo '<table><tr><th>Preview</th><th>Filename</th><th>Size</th><th>Action</th></tr>';
    foreach ($imgFiles as $f) {
        $fname = basename($f);
        $url = '/assets/uploads/' . $fname;
        $size = round(filesize($f) / 1024) . ' KB';
        $isCurrent = ($profile['avatar'] ?? '') === $fname;
        echo '<tr>';
        echo '<td><img src="' . htmlspecialchars($url) . '" class="img-thumb" onerror="this.src=\'\'"></td>';
        echo '<td><code>' . htmlspecialchars($fname) . '</code>' . ($isCurrent ? ' <span class="ok">← current</span>' : '') . '</td>';
        echo '<td>' . $size . '</td>';
        echo '<td>';
        if (!$isCurrent) {
            echo '<form method="POST" style="display:inline">
                <input type="hidden" name="set_avatar" value="' . htmlspecialchars($fname) . '">
                <button type="submit">Use This</button>
            </form>';
        } else {
            echo '<span class="ok">✓ Active</span>';
        }
        echo '</td></tr>';
    }
    echo '</table>';
} else {
    echo '<p class="err">No image files found in uploads directory.</p>';
}

// --- Upload fresh image ---
echo '<hr>';
echo '<h2>⬆️ Upload a Fresh Avatar</h2>';
echo '<form method="POST" enctype="multipart/form-data">
  <input type="file" name="new_avatar" accept="image/*" style="color:#e8e8f0">
  <input type="submit" value="Upload & Set as Avatar" style="margin-left:.5rem">
</form>';
