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

// Ensure the HTTP header correctly matches the error (only if headers not yet sent)
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

<section class="error-page"
    style="min-height: 80vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem; position: relative; z-index: 10;">
    <!-- Background Orbs purely for error page to match theme -->
    <div class="orb-field" style="z-index: -1;">
        <div class="orb orb-1"
            style="background: var(--coral); opacity: 0.1; width: 400px; height: 400px; top: 10%; right: 20%;"></div>
        <div class="orb orb-4"
            style="background: var(--violet); opacity: 0.15; width: 500px; height: 500px; bottom: -10%; left: -10%;">
        </div>
    </div>

    <div class="glass-card reveal visible"
        style="max-width: 600px; padding: 4rem 2rem; width: 100%; border-top: 2px solid var(--violet);">
        <!-- Glowing Number -->
        <div class="error-number glow-text stat-number"
            style="font-size: clamp(5rem, 15vw, 8rem); margin-bottom: 0.5rem; line-height: 1; letter-spacing: -0.05em;">
            <?php echo escape((string) $code); ?>
        </div>

        <h1 class="section-title" style="margin-bottom: 1.5rem; font-size: clamp(1.5rem, 5vw, 2.2rem);">
            <span>
                <?php echo escape($title); ?>
            </span>
        </h1>

        <p class="section-subtitle" style="margin: 0 auto 2.5rem; font-size: 1.1rem; color: var(--ink2);">
            <?php echo escape($message); ?>
        </p>

        <a href="<?php echo BASE_URL; ?>/" class="btn-primary" style="font-size: 1rem; padding: 1rem 2.5rem;">
            <i class="fa-solid fa-house" aria-hidden="true" style="margin-right: 0.5rem;"></i> Return to Homepage
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>