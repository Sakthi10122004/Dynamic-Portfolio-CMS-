<?php
/**
 * Custom Error Page
 * Handles 400, 401, 403, 404, 500, 503
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Determine the error code from server or GET params
$code = $_SERVER['REDIRECT_STATUS'] ?? $_GET['code'] ?? 404;
$code = (int) $code;

// Set valid HTTP code
if ($code < 400 || $code > 599) {
    if (isset($_GET['code']) && is_numeric($_GET['code'])) {
        $code = (int) $_GET['code'];
    } else {
        $code = 404;
    }
}

// Ensure the HTTP header correctly matches the error
if (!headers_sent() && isset($_SERVER['REDIRECT_STATUS'])) {
    http_response_code($code);
}

$titles = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Page Not Found',
    500 => 'Internal Server Error',
    503 => 'Service Unavailable'
];

$messages = [
    400 => 'The server could not understand the request due to invalid syntax.',
    401 => 'You must authenticate to access this resource.',
    403 => 'You do not have permission to access this directory or page.',
    404 => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
    500 => 'The server encountered an unexpected condition that prevented it from fulfilling the request.',
    503 => 'The server is currently unable to handle the request due to temporary overloading or maintenance.'
];

$title = $titles[$code] ?? 'Unknown Error';
$message = $messages[$code] ?? 'Something went wrong processing your request.';

$pageTitle = "Error $code - $title";
$pageDescription = $message;

require_once __DIR__ . '/includes/header.php';
?>

<section class="error-page">
    <div class="glass-card" style="max-width:520px;padding:3.5rem 2.5rem;width:100%;text-align:center;">
        <!-- Error number -->
        <div style="font-family:var(--font-head);font-size:clamp(4rem,12vw,6.5rem);font-weight:700;color:var(--primary);line-height:1;margin-bottom:.5rem;letter-spacing:-.04em;">
            <?php echo escape((string) $code); ?>
        </div>

        <h1 style="font-family:var(--font-head);font-size:clamp(1.3rem,3.5vw,1.8rem);font-weight:700;color:var(--ink);margin-bottom:1.25rem;">
            <?php echo escape($title); ?>
        </h1>

        <p style="color:var(--ink2);font-size:.95rem;line-height:1.7;margin-bottom:2rem;max-width:400px;margin-left:auto;margin-right:auto;">
            <?php echo escape($message); ?>
        </p>

        <a href="<?php echo BASE_URL; ?>/" class="btn-primary" style="font-size:.9rem;padding:.9rem 2rem;">
            <i class="fa-solid fa-house" aria-hidden="true"></i> Return to Homepage
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>