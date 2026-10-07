<?php
// router.php pour le serveur de développement interne de PHP
if (php_sapi_name() !== 'cli-server') {
    die('Ce script est destiné uniquement au serveur interne PHP.');
}

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Fichiers statiques (CSS, images...)
if (is_file(__DIR__ . $path)) {
    return false;
}

// Règles de routage similaires au .htaccess
$routes = [
    '/' => '/index.php',
    '/track' => '/track.php',
    '/contact' => '/contact.php',
    '/login' => '/login.php',
    '/logout' => '/logout.php',
    '/dashboard' => '/dashboard.php',
    '/shipments' => '/shipments.php',
    '/shipments/new' => '/new_shipment.php',
    '/customers' => '/customers.php',
    '/settings' => '/settings.php',
];

if (array_key_exists($path, $routes)) {
    require __DIR__ . $routes[$path];
} elseif (preg_match('#^/shipments/([0-9]+)$#', $path, $matches)) {
    $_GET['id'] = $matches[1];
    require __DIR__ . '/shipment.php';
} else {
    // Page 404
    http_response_code(404);
    require __DIR__ . '/404.php';
}
