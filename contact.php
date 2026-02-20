<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate
    if (empty($name) || empty($email) || empty($message)) {
        header('Location: ' . BASE_URL . '/#contact');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . BASE_URL . '/#contact');
        exit;
    }
    
    // Rate limiting: max 3 messages per IP per hour
    $db = Database::getInstance();
    $ip = $_SERVER['REMOTE_ADDR'];
    $recentCount = $db->getRow(
        "SELECT COUNT(*) as cnt FROM contact_messages WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
        [$ip], 's'
    );
    
    if ($recentCount && $recentCount['cnt'] >= 3) {
        header('Location: ' . BASE_URL . '/?sent=1#contact');
        exit;
    }
    
    // Save message
    $db->insert(
        "INSERT INTO contact_messages (name, email, message, ip_address) VALUES (?, ?, ?, ?)",
        [$name, $email, $message, $ip],
        'ssss'
    );

    // Email notification to admin
    $adminEmail = 'sakthikaribeeran@gmail.com';
    $subject    = "New Contact Message from {$name}";
    $body       = "You have a new message from your portfolio contact form.\n\n"
                . "Name   : {$name}\n"
                . "Email  : {$email}\n"
                . "Message:\n{$message}\n\n"
                . "---\nView in admin: " . BASE_URL . "/admin/messages.php";
    $headers    = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'portfolio') . "\r\n"
                . "Reply-To: {$email}\r\n"
                . "X-Mailer: PHP/" . phpversion();
    @mail($adminEmail, $subject, $body, $headers);

    header('Location: ' . BASE_URL . '/?sent=1#contact');
    exit;
}

// If someone navigates here directly via GET
header('Location: ' . BASE_URL . '/#contact');
exit;
