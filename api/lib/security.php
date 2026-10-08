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
        error_log("CSRF FAILED! Session token: " . ($_SESSION['csrf_token'] ?? 'EMPTY') . " POST token: " . $token);
        return false;
    }
    return true;
}

// L'ancien système de login reste intact mais utilise UTC
function rate_limit_check($ip, $maxAttempts = 5, $lockoutTimeMinutes = 15) {
    global $pdo;
    
    // Test obligatoire du blocage avec Europe/Paris forcé :
    // Même si date_default_timezone_set() est changé à la volée, time() renvoie toujours
    // le timestamp Unix UTC universel. date('Y-m-d H:i:s') va utiliser le timezone actif.
    // Pour assurer que la comparaison SQL avec datetime('now') (SQLite) ou CURRENT_TIMESTAMP (MySQL) 
    // soit toujours UTC, nous calculons la date limite en utilisant gmdate() au lieu de date().
    $limitDate = gmdate('Y-m-d H:i:s', time() - ($lockoutTimeMinutes * 60));
    
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
    $now = gmdate('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = ? WHERE ip_address = ?")->execute([$now, $ip]);
    } else {
        $pdo->prepare("INSERT INTO login_attempts (ip_address, last_attempt) VALUES (?, ?)")->execute([$ip, $now]);
    }
}

function rate_limit_success($ip) {
    global $pdo;
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);
}

// Nouveau système de rate_limits pour /track et /contact
function rate_limit_hit($action, $ip, $max, $windowSeconds) {
    global $pdo;
    
    // Purge des expirés (toujours en UTC)
    $limitDate = gmdate('Y-m-d H:i:s', time() - $windowSeconds);
    $pdo->prepare("DELETE FROM rate_limits WHERE action = ? AND window_started_at < ?")->execute([$action, $limitDate]);
    
    $stmt = $pdo->prepare("SELECT id, hits FROM rate_limits WHERE action = ? AND ip_address = ?");
    $stmt->execute([$action, $ip]);
    $row = $stmt->fetch();
    
    if ($row) {
        if ($row['hits'] >= $max) {
            http_response_code(429);
            die("Trop de requêtes. Veuillez patienter.");
        }
        $pdo->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE id = ?")->execute([$row['id']]);
    } else {
        $now = gmdate('Y-m-d H:i:s');
        $pdo->prepare("INSERT INTO rate_limits (action, ip_address, hits, window_started_at) VALUES (?, ?, 1, ?)")->execute([$action, $ip, $now]);
    }
}

function escape_html($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}
