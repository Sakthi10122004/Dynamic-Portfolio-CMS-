<?php
/* ============================================================
   functions.php  — Helper & Data Retrieval Functions
   ============================================================ */

// ----------------------------------------------------------
// Output Sanitisation
// ----------------------------------------------------------

function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Truncate text at a word boundary.
 */
function truncate($text, $length = 150) {
    $text = strip_tags($text ?? '');
    if (mb_strlen($text) <= $length) return $text;
    $trimmed = mb_substr($text, 0, $length);
    $lastSpace = mb_strrpos($trimmed, ' ');
    return ($lastSpace !== false ? mb_substr($trimmed, 0, $lastSpace) : $trimmed) . '…';
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function timeAgo($date) {
    $diff = time() - strtotime($date);
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return floor($diff / 60)    . 'm ago';
    if ($diff < 86400)   return floor($diff / 3600)  . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return formatDate($date);
}

/**
 * Sanitise URLs for social links — only allow http/https.
 */
function sanitizeUrl($url) {
    $url = trim($url ?? '');
    if ($url === '') return '';
    if (!preg_match('#^https?://#i', $url)) return '';
    return filter_var($url, FILTER_SANITIZE_URL) ?: '';
}

// ----------------------------------------------------------
// CSRF Protection
// ----------------------------------------------------------

function csrfToken() {
    // Session must already be started by Auth::__construct()
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf() {
    $token   = $_POST['csrf_token'] ?? '';
    $session = $_SESSION['csrf_token'] ?? '';

    if (!$token || !$session || !hash_equals($session, $token)) {
        // Graceful redirect with error flag rather than die()
        $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';
        // Prevent open redirect
        $back = filter_var($back, FILTER_VALIDATE_URL) ? $back : BASE_URL . '/';
        header('Location: ' . $back . (strpos($back, '?') !== false ? '&' : '?') . 'csrf_error=1');
        exit();
    }
}

// ----------------------------------------------------------
// File Upload
// ----------------------------------------------------------

function uploadImage($file, $existingFile = null) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed (code ' . $file['error'] . ')'];
    }

    // Validate MIME type via finfo (not user-supplied type)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'];
    }

    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large. Maximum: 5MB'];
    }

    // Derive extension from MIME (not from filename — blocks evil.php.jpg)
    $mimeExtMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $extension = $mimeExtMap[$mimeType] ?? 'jpg';

    // Double-extension guard: reject filenames with multiple dots before final ext
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    if (strpos($originalName, '.') !== false) {
        return ['success' => false, 'error' => 'Invalid filename. Do not use dots in the file name.'];
    }

    // Generate a cryptographically random filename
    $filename   = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $filename;

    // Ensure upload directory exists
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // Delete old file
    if ($existingFile && file_exists(UPLOAD_DIR . $existingFile)) {
        unlink(UPLOAD_DIR . $existingFile);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'error' => 'Failed to save file. Check directory permissions.'];
}

// ----------------------------------------------------------
// Data Retrieval
// ----------------------------------------------------------

function getProfile() {
    $db      = Database::getInstance();
    $profile = $db->getRow("SELECT * FROM profile WHERE id = 1");
    return $profile ?: [
        'name'     => 'Sakthi',
        'headline' => 'Full-Stack Developer',
        'bio'      => 'Building digital experiences.',
        'email'    => 'hello@sakthi.dev',
        'avatar'   => null,
        'resume'   => null,
        'github'   => null,
        'linkedin' => null,
        'twitter'  => null,
    ];
}

function getProjects($limit = null, $featuredOnly = false) {
    $db  = Database::getInstance();
    $sql = $featuredOnly
        ? "SELECT * FROM projects WHERE featured = 1 ORDER BY created_at DESC"
        : "SELECT * FROM projects ORDER BY featured DESC, created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getProject($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM projects WHERE id = ?", [(int)$id], 'i');
}

function getSkills($category = null) {
    $db = Database::getInstance();
    if ($category) {
        return $db->getRows(
            "SELECT * FROM skills WHERE category = ? ORDER BY display_order",
            [$category], 's'
        );
    }
    return $db->getRows("SELECT * FROM skills ORDER BY category, display_order");
}

function getSkill($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM skills WHERE id = ?", [(int)$id], 'i');
}

function getNotes($limit = null) {
    $db  = Database::getInstance();
    $sql = "SELECT * FROM notes WHERE published = 1 ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getAllNotes($limit = null) {
    $db  = Database::getInstance();
    $sql = "SELECT * FROM notes ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getNote($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM notes WHERE id = ?", [(int)$id], 'i');
}

function getPublishedNote($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM notes WHERE id = ? AND published = 1", [(int)$id], 'i');
}

function saveContactMessage($name, $email, $message, $ip = '') {
    $db = Database::getInstance();
    return $db->insert(
        "INSERT INTO contact_messages (name, email, message, ip_address) VALUES (?, ?, ?, ?)",
        [$name, $email, $message, $ip],
        'ssss'
    );
}

function getMessages($limit = null) {
    $db  = Database::getInstance();
    $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getMessage($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM contact_messages WHERE id = ?", [(int)$id], 'i');
}