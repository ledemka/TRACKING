<?php
// router.php pour le serveur de développement interne de PHP
if (php_sapi_name() !== 'cli-server') {
    die('Ce script est destiné uniquement au serveur interne PHP.');
}

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Interdiction stricte des fichiers et dossiers sensibles (aligné avec .htaccess)
if (preg_match('#^/(\.env|\.git|api/migrate\.php|scripts/|templates/|lang/|\.sqlite|docs/|node_modules/)#i', $path) || 
    preg_match('#\.(sql|log|bak|env|sqlite|md)$#i', $path)) {
    http_response_code(403);
    die('403 Forbidden');
}

// Fichiers statiques autorisés uniquement (CSS, JS, images, polices, favicons)
if (is_file(__DIR__ . $path)) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowedStaticExts = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'txt'];
    if (in_array($ext, $allowedStaticExts, true)) {
        return false;
    }
}

// Support transparent des URL localisées (/fr/, /en/, /de/, /nl/) sans redirection
$supportedPrefixes = ['fr', 'en', 'de', 'nl'];
if (preg_match('#^/(' . implode('|', $supportedPrefixes) . ')(/.*)?$#i', $path, $langMatches)) {
    $_GET['lang'] = strtolower($langMatches[1]);
    $subPath = $langMatches[2] ?? '/';
    $path = ($subPath === '' || $subPath === false) ? '/' : $subPath;
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
    '/shipments/new' => '/shipment_new.php',
    '/customers' => '/customers.php',
    '/services' => '/services.php',
    '/services/' => '/services.php',
    '/services/expedition' => '/service_expedition.php',
    '/services/expedition/' => '/service_expedition.php',
    '/services/suivi' => '/service_suivi.php',
    '/services/suivi/' => '/service_suivi.php',
    '/services/livraison' => '/service_livraison.php',
    '/services/livraison/' => '/service_livraison.php',
    '/services/gestion' => '/service_gestion.php',
    '/services/gestion/' => '/service_gestion.php',
    '/services/gestion-expeditions' => '/service_gestion.php',
    '/services/gestion-expeditions/' => '/service_gestion.php',
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
