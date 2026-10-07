<?php
// api/lib/auth.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/security.php';

// Configure session cookies to be secure and httpOnly
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// NOTE: Custom session handler in DB is omitted for brevity in Phase 1, using native PHP sessions, 
// but we store session info if needed, or we implement SessionHandlerInterface.
// For now, native session is started.
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
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    
    rate_limit_fail($ip);
    return false;
}

function logout() {
    $_SESSION = [];
    session_destroy();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'ADMIN';
}

function require_admin() {
    if (!is_admin()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé"]);
        } else {
            header("Location: /login.php");
        }
        exit;
    }
}
