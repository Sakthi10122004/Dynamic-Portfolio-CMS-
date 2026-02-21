<?php
require_once __DIR__ . '/db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->initSession();
    }
    
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            session_set_cookie_params([
                'lifetime' => SESSION_TIMEOUT,
                'path'     => '/',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => $secure ? 'Strict' : 'Lax' // Strict on HTTPS
            ]);
            session_start();
        }
        
        // Regenerate session ID periodically (every 30 min)
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    // ----------------------------------------------------------
    // Rate Limiting
    // ----------------------------------------------------------
    
    public function isRateLimited($ip) {
        $window = date('Y-m-d H:i:s', time() - (LOGIN_LOCKOUT_MINUTES * 60));
        $row = $this->db->getRow(
            "SELECT COUNT(*) AS cnt FROM login_attempts WHERE ip_address = ? AND attempted_at > ?",
            [$ip, $window],
            'ss'
        );
        return ($row['cnt'] ?? 0) >= LOGIN_MAX_ATTEMPTS;
    }
    
    public function recordFailedAttempt($ip) {
        $this->db->query(
            "INSERT INTO login_attempts (ip_address) VALUES (?)",
            [$ip],
            's'
        );
        // Clean up old entries (older than 1 hour) to keep table small
        $this->db->query(
            "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
    }
    
    public function clearAttempts($ip) {
        $this->db->query(
            "DELETE FROM login_attempts WHERE ip_address = ?",
            [$ip],
            's'
        );
    }
    
    // ----------------------------------------------------------
    // Authentication
    // ----------------------------------------------------------
    
    public function login($username, $password) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        if ($this->isRateLimited($ip)) {
            return 'rate_limited';
        }
        
        $user = $this->db->getRow(
            "SELECT * FROM users WHERE username = ?",
            [$username],
            's'
        );
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $this->clearAttempts($ip);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['login_time'] = time();
            session_regenerate_id(true);
            return 'success';
        }
        
        $this->recordFailedAttempt($ip);
        return 'invalid';
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time']) &&
            (time() - $_SESSION['login_time'] > SESSION_TIMEOUT)) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/admin/');
            exit();
        }
    }
    
    public function logout() {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id'       => $_SESSION['user_id'],
                'username' => $_SESSION['username']
            ];
        }
        return null;
    }
}

// Global auth instance
$auth = new Auth();