<?php
require_once __DIR__ . '/api/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Requête refusée.');
    }
    logout();
}

header("Location: /");
exit;
