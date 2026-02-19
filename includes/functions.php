<?php
function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function truncate($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function timeAgo($date) {
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return formatDate($date);
}

function uploadImage($file, $existingFile = null) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed with error code: ' . $file['error']];
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'];
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large. Maximum: 5MB'];
    }
    
    // Generate secure filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $filename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Delete old file if exists
    if ($existingFile && file_exists(UPLOAD_DIR . $existingFile)) {
        unlink(UPLOAD_DIR . $existingFile);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'error' => 'Failed to save file'];
}

// ---- Data Retrieval Functions ----

function getProfile() {
    $db = Database::getInstance();
    $profile = $db->getRow("SELECT * FROM profile WHERE id = 1");
    if (!$profile) {
        return [
            'name' => 'Sakthi',
            'headline' => 'Full-Stack Developer',
            'bio' => 'Building digital experiences.',
            'email' => 'hello@sakthi.dev',
            'avatar' => null,
            'resume' => null,
            'github' => null,
            'linkedin' => null,
            'twitter' => null
        ];
    }
    return $profile;
}

function getProjects($limit = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM projects ORDER BY featured DESC, created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getProject($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM projects WHERE id = ?", [$id], 'i');
}

function getSkills($category = null) {
    $db = Database::getInstance();
    if ($category) {
        return $db->getRows(
            "SELECT * FROM skills WHERE category = ? ORDER BY display_order",
            [$category],
            's'
        );
    }
    return $db->getRows("SELECT * FROM skills ORDER BY category, display_order");
}

function getSkill($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM skills WHERE id = ?", [$id], 'i');
}

function getNotes($limit = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM notes WHERE published = 1 ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getAllNotes($limit = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM notes ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getNote($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM notes WHERE id = ?", [$id], 'i');
}

function getPublishedNote($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM notes WHERE id = ? AND published = 1", [$id], 'i');
}

function saveContactMessage($name, $email, $message) {
    $db = Database::getInstance();
    return $db->insert(
        "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)",
        [$name, $email, $message],
        'sss'
    );
}

function getMessages($limit = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        return $db->getRows($sql, [$limit], 'i');
    }
    return $db->getRows($sql);
}

function getMessage($id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM contact_messages WHERE id = ?", [$id], 'i');
}

function csrfToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die('Invalid security token. Please go back and try again.');
    }
}