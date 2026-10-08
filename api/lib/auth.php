<?php
// api/lib/auth.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/security.php';

class DBSessionHandler implements SessionHandlerInterface {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }
    
    public function read(string $id): string|false {
        $stmt = $this->pdo->prepare("SELECT payload FROM sessions WHERE id = ? AND last_activity > ?");
        $stmt->execute([$id, time() - (8 * 3600)]);
        $row = $stmt->fetch();
        return $row ? $row['payload'] : '';
    }
    
    public function write(string $id, string $data): bool {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $this->pdo->prepare("SELECT id FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->fetch()) {
            $stmt = $this->pdo->prepare("UPDATE sessions SET payload = ?, last_activity = ?, user_id = ? WHERE id = ?");
            $stmt->execute([$data, time(), $userId, $id]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $userId, $ip, $ua, $data, time()]);
        }
        return true;
    }
    
    public function destroy(string $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
    
    public function gc(int $max_lifetime): int|false {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_activity < ?");
        $stmt->execute([time() - min($max_lifetime, 30 * 60)]);
        return $stmt->rowCount();
    }
}

$handler = new DBSessionHandler($pdo);
session_set_save_handler($handler, true);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);

$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $isSecure = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $isSecure = true;
}
if ($isSecure) {
    ini_set('session.cookie_secure', 1);
}

session_start();

function login($email, $password) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    rate_limit_check($ip, 5, 15);
    
    $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        rate_limit_success($ip);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['created_at'] = time(); // Origine de la session
        return true;
    }
    
    rate_limit_fail($ip);
    return false;
}

function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function is_logged_in() {
    // Session max 8 heures ou sans origine (considérée expirée)
    if (!isset($_SESSION['created_at']) || (time() - $_SESSION['created_at'] > 8 * 3600)) {
        if (isset($_SESSION['user_id'])) {
            logout();
        }
        return false;
    }
    // Inactivité 30 min max
    if (isset($_SESSION['last_action']) && (time() - $_SESSION['last_action'] > 1800)) {
        logout();
        return false;
    }
    $_SESSION['last_action'] = time();
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'ADMIN';
}

function require_admin() {
    if (!is_admin()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Non autorisé"]);
        } else {
            header("Location: /login");
        }
        exit;
    }
}
