<?php
require_once __DIR__ . '/api/lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    logout();
}

header("Location: /");
exit;
