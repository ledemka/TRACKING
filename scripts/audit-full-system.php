<?php
// scripts/audit-full-system.php
// Audit de bout en bout de l'application TRACKING

require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../api/lib/shipments.php';
require_once __DIR__ . '/../api/lib/mailer.php';
require_once __DIR__ . '/../api/lib/status.php';

$baseUrl = 'http://127.0.0.1:8000';
$passed = 0;
$failed = 0;

function run_check(bool $cond, string $name, string $details = '') {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] $name\n";
        $passed++;
    } else {
        echo "  [FAIL] $name" . ($details ? " ($details)" : "") . "\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "       AUDIT GLOBAL DE BOUT EN BOUT : APPLICATION TRACKING       \n";
echo "=================================================================\n\n";

// ----------------------------------------------------------------------
// 1. CONTRÔLE DES PAGES PUBLIQUES ET URLS LOCALISÉES (4 LANGUES)
// ----------------------------------------------------------------------
echo "--- 1. Accessibilité des pages publiques dans les 4 langues ---\n";

$pages = [
    '/' => ['fr' => 'Suivre un colis', 'en' => 'Track', 'de' => 'Sendung verfolgen', 'nl' => 'Pakket volgen'],
    '/services' => ['fr' => 'services', 'en' => 'services', 'de' => 'Services', 'nl' => 'Diensten'],
    '/track' => ['fr' => 'Suivez votre expédition', 'en' => 'Track your shipment', 'de' => 'Sendung verfolgen', 'nl' => 'Volg uw zending'],
    '/contact' => ['fr' => 'Contactez', 'en' => 'Contact', 'de' => 'Kontaktieren', 'nl' => 'Contact'],
    '/login' => ['fr' => 'Connexion', 'en' => 'Login', 'de' => 'Anmeldung', 'nl' => 'Inloggen']
];

$langs = ['fr', 'en', 'de', 'nl'];

foreach ($pages as $route => $expectedSnippets) {
    foreach ($langs as $l) {
        $url = $baseUrl . '/' . $l . ($route === '/' ? '' : $route);
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 3.0,
                'ignore_errors' => true,
                'follow_location' => 0 // Ne pas suivre les redirections pour détecter les boucles
            ]
        ]);
        $response = @file_get_contents($url, false, $ctx);
        $statusLine = $http_response_header[0] ?? '';
        
        $is200 = (strpos($statusLine, '200 OK') !== false);
        $snippet = $expectedSnippets[$l] ?? '';
        $hasSnippet = ($response !== false && stripos($response, $snippet) !== false);

        run_check($is200 && $hasSnippet, "Page $url (200 OK, contient '$snippet')", "Status: $statusLine");
    }
}

// ----------------------------------------------------------------------
// 2. CONTRÔLE DE NON-RÉPÉTITION / PAS DE BOUCLE DE REDIRECTION
// ----------------------------------------------------------------------
echo "\n--- 2. Contrôle d'absence de boucles de redirection ---\n";

$urlsToTestRedirect = [
    $baseUrl . '/',
    $baseUrl . '/fr',
    $baseUrl . '/fr/',
    $baseUrl . '/en/services',
    $baseUrl . '/de/track?id=COLIS-2026-00125'
];

foreach ($urlsToTestRedirect as $u) {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 2.0,
            'ignore_errors' => true,
            'follow_location' => 0
        ]
    ]);
    @file_get_contents($u, false, $ctx);
    $statusLine = $http_response_header[0] ?? '';
    // Aucun code 301/302 ne doit boucler sur lui-même
    $isLoop = false;
    if (strpos($statusLine, '301') !== false || strpos($statusLine, '302') !== false) {
        foreach ($http_response_header as $hdr) {
            if (stripos($hdr, 'Location:') === 0) {
                $loc = trim(substr($hdr, 9));
                if ($loc === $u) {
                    $isLoop = true;
                }
            }
        }
    }
    run_check(!$isLoop && (strpos($statusLine, '200') !== false || strpos($statusLine, '30') !== false), "Pas de boucle sur $u ($statusLine)");
}

// ----------------------------------------------------------------------
// 3. CYCLE DE VIE EXPÉDITION (CRÉATION, MODIFICATION, CONSULTATION)
// ----------------------------------------------------------------------
echo "\n--- 3. Cycle de vie expédition (Création, Modes de transport, Modification) ---\n";

$modesToTest = [
    TRANSPORT_MODE_LAND => ['lat' => 48.8566, 'lng' => 2.3522, 'city' => 'Paris'],
    TRANSPORT_MODE_AIR  => ['lat' => 50.1109, 'lng' => 8.6821, 'city' => 'Francfort'],
    TRANSPORT_MODE_SEA  => ['lat' => 51.9244, 'lng' => 4.4777, 'city' => 'Rotterdam'],
];

foreach ($modesToTest as $mode => $coords) {
    $trackingNum = 'PS-AUDIT-' . strtoupper(substr(md5(uniqid()), 0, 7));
    $shipmentData = [
        'tracking_number' => $trackingNum,
        'carrier_id' => null,
        'recipient_name' => 'Auditeur ' . ucfirst($mode),
        'recipient_phone' => '+33100000000',
        'recipient_email' => 'audit.' . $mode . '@example.com',
        'recipient_lang' => ($mode === 'sea' ? 'nl' : ($mode === 'air' ? 'de' : 'fr')),
        'address' => '1 Rue du Port',
        'zip_code' => '75001',
        'city' => $coords['city'],
        'country' => 'France',
        'origin_address' => '10 Avenue Logistique',
        'origin_city' => 'Marseille',
        'shipped_at' => gmdate('Y-m-d H:i:s'),
        'estimated_delivery_at' => gmdate('Y-m-d H:i:s', time() + 86400 * 3),
        'status' => STATUS_SHIPPED,
        'transport_mode' => $mode,
        'description' => 'Colis de test audit mode ' . $mode,
        'destination_lat' => $coords['lat'],
        'destination_lng' => $coords['lng'],
        'is_company' => 0
    ];

    $shipmentId = insert_shipment_and_event($shipmentData, [
        'label' => 'Prise en charge',
        'location' => 'Marseille',
        'latitude' => 43.2965,
        'longitude' => 5.3698
    ]);

    run_check($shipmentId > 0, "Création expédition mode '$mode' avec coordonnées (ID: $shipmentId)");

    // Vérification en lecture directe
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
    $stmt->execute([$shipmentId]);
    $created = $stmt->fetch();

    run_check($created['transport_mode'] === $mode, "Mode '$mode' correctement persisté");
    run_check((float)$created['destination_lat'] === (float)$coords['lat'], "Latitude destination persistée");
    run_check($created['recipient_lang'] === $shipmentData['recipient_lang'], "Langue destinataire persistée ('{$shipmentData['recipient_lang']}')");

    // Modification de l'expédition
    $stmtUp = $pdo->prepare("UPDATE shipments SET recipient_name = ?, transport_mode = ?, updated_at = NOW() WHERE id = ?");
    $stmtUp->execute(['Auditeur Modifié', TRANSPORT_MODE_AIR, $shipmentId]);

    $stmt->execute([$shipmentId]);
    $updated = $stmt->fetch();
    run_check($updated['recipient_name'] === 'Auditeur Modifié' && $updated['transport_mode'] === TRANSPORT_MODE_AIR, "Modification expédition (changement mode -> air)");

    // Nettoyage après test
    $pdo->prepare("DELETE FROM tracking_events WHERE shipment_id = ?")->execute([$shipmentId]);
    $pdo->prepare("DELETE FROM shipments WHERE id = ?")->execute([$shipmentId]);
}

// ----------------------------------------------------------------------
// 4. COMPATIBILITÉ AVEC LES DONNÉES HISTORIQUES / EXISTANTES
// ----------------------------------------------------------------------
echo "\n--- 4. Intégrité et compatibilité des données existantes ---\n";

$stmtAll = $pdo->query("SELECT id, tracking_number, recipient_email, recipient_lang, transport_mode, country FROM shipments LIMIT 10");
$existingShipments = $stmtAll->fetchAll();

run_check(!empty($existingShipments), "Expéditions existantes présentes dans la base (" . count($existingShipments) . " analysées)");

foreach ($existingShipments as $s) {
    // Résolution linguistique sans erreur
    $resolved = resolve_recipient_language($s);
    $validLang = in_array($resolved['lang'], ['fr', 'en', 'de', 'nl'], true);
    
    // Libellé mode de transport sans erreur
    $modeLabel = get_transport_mode_label($s['transport_mode']);
    $validMode = !empty($modeLabel);

    run_check($validLang && $validMode, "Expédition {$s['tracking_number']} : langue résolue '{$resolved['lang']}', mode '$modeLabel'");
}

// ----------------------------------------------------------------------
// 5. RÉSILIENCE FACE AUX PANNES GÉO / EXTÉRIEURES
// ----------------------------------------------------------------------
echo "\n--- 5. Résilience en cas d'erreur de géolocalisation ou d'indisponibilité ---\n";

// Simulation d'une IP injoignable ou en-tête absent
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
$_SERVER['REMOTE_ADDR'] = '10.0.0.1'; // IP privée : ne doit pas appeler l'API externe et replier immédiatement sur FR
$fallbackLang = detect_browser_language();
run_check($fallbackLang === null, "Accept-Language vide retourne null sans erreur");

$geoLang = detect_ip_geolocation_language();
run_check($geoLang === null, "IP privée ignorée et protégée contre timeout");

$resolvedDefault = resolve_recipient_language(['recipient_lang' => null, 'recipient_email' => ''], null);
run_check($resolvedDefault['lang'] === 'fr', "Repli résilient sur 'fr' en cas d'absence complète de données");

// ----------------------------------------------------------------------
// 6. SÉCURITÉ & NON-EXPOSITION DES SECRETS
// ----------------------------------------------------------------------
echo "\n--- 6. Sécurité : vérification de la non-exposition des secrets ---\n";

$resendApiKey = getenv('RESEND_API_KEY');
$dbPass = getenv('DB_PASS');

// Test : l'API key ne doit jamais apparaître dans le code source HTML public
$publicHtml = @file_get_contents($baseUrl . '/fr');
run_check($resendApiKey && strpos($publicHtml, $resendApiKey) === false, "Clé API Resend absente de la page d'accueil HTML");

$trackHtml = @file_get_contents($baseUrl . '/fr/track');
run_check($resendApiKey && strpos($trackHtml, $resendApiKey) === false, "Clé API Resend absente de la page de suivi HTML");

// Test accès direct au fichier .env
$envAccessCtx = stream_context_create([
    'http' => [
        'timeout' => 2.0,
        'ignore_errors' => true
    ]
]);
$envRes = @file_get_contents($baseUrl . '/.env', false, $envAccessCtx);
$envStatus = $http_response_header[0] ?? '';
run_check(strpos($envStatus, '403') !== false || strpos($envStatus, '404') !== false || empty($envRes), "Fichier .env protégé contre l'accès HTTP direct ($envStatus)");

echo "\n=================================================================\n";
echo "   RÉSULTAT DE L'AUDIT DE BOUT EN BOUT : $passed PASS / $failed FAIL\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
