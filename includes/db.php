<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $this->connection->set_charset('utf8mb4');
            
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            if (defined('DB_HOST') && DB_HOST === 'localhost') {
                die('<div style="font-family:sans-serif;padding:2rem;background:#1a1a24;color:#ff4444;border:1px solid #ff4444;border-radius:12px;margin:2rem;max-width:600px;">
                    <h2>⚠️ Database Connection Failed</h2>
                    <p>' . htmlspecialchars($e->getMessage()) . '</p>
                    <p style="color:#a0a0b0;margin-top:1rem;">Run <a href="' . BASE_URL . '/setup.php" style="color:#6c5ce7;">setup.php</a> to create the database.</p>
                </div>');
            } else {
                die("Database connection error. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $stmt;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Query failed: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }
    
    public function getRow($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        if ($stmt) {
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return null;
    }
    
    public function getRows($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        if ($stmt) {
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
    public function insert($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        if ($stmt) {
            return $this->connection->insert_id;
        }
        return false;
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Global database instance
$db = Database::getInstance();