<?php
// api/lib/security.php
require_once __DIR__ . '/../db.php';

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    $token = (string)$token;
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die(); // 403 sans détail
    }
}

function rate_limit_check($ip, $maxAttempts = 5, $lockoutTimeMinutes = 15) {
    global $pdo;
    
    // Purge de login_attempts (compatible MySQL et SQLite)
    $limitDate = date('Y-m-d H:i:s', time() - ($lockoutTimeMinutes * 60));
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE last_attempt < ?");
    $stmt->execute([$limitDate]);
    
    $stmt = $pdo->prepare("SELECT attempts FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $row = $stmt->fetch();
    
    if ($row && $row['attempts'] >= $maxAttempts) {
        http_response_code(429);
        die("Trop de tentatives. Veuillez réessayer plus tard.");
    }
}

function rate_limit_fail($ip) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE ip_address = ?")->execute([$ip]);
    } else {
        $pdo->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)")->execute([$ip]);
    }
}

function rate_limit_success($ip) {
    global $pdo;
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);
}

function escape_html($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}
