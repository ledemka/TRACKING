<?php
// scripts/test-email-i18n.php
// Suite complète de tests automatisés pour l'envoi d'emails multilingues (FR, EN, DE, NL)

require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../api/lib/mailer.php';
require_once __DIR__ . '/../api/lib/status.php';
require_once __DIR__ . '/../api/lib/shipments.php';
require_once __DIR__ . '/../api/lib/notifications.php';
require_once __DIR__ . '/../templates/emails/tracking_email.php';

echo "=================================================================\n";
echo "    TESTS DU SYSTÈME D'ENVOI D'EMAILS MULTILINGUES (TRACKING)    \n";
echo "=================================================================\n\n";

$passedCount = 0;
$failedCount = 0;

function assert_test(bool $condition, string $title, string $details = '') {
    global $passedCount, $failedCount;
    if ($condition) {
        echo "  [PASS] $title\n";
        $passedCount++;
    } else {
        echo "  [FAIL] $title" . ($details ? " ($details)" : "") . "\n";
        $failedCount++;
    }
}

// --------------------------------------------------------------------------
// SUITE 1 : RÈGLES DE RÉSOLUTION DE LA LANGUE DU DESTINATAIRE (6 PALIERS)
// --------------------------------------------------------------------------
echo "--- 1. Hiérarchie de résolution de la langue du destinataire ---\n";

// Tier 1 : Surcharge ponctuelle
$res1 = resolve_recipient_language([
    'recipient_lang' => 'fr',
    'recipient_email' => 'client@example.com',
    'country' => 'France'
], 'nl');
assert_test($res1['lang'] === 'nl' && $res1['source'] === 'manual_override', "Palier 1 : La surcharge ponctuelle prime ('nl')");

// Tier 2 : Préférence explicite sur la fiche expédition
$res2 = resolve_recipient_language([
    'recipient_lang' => 'de',
    'recipient_email' => 'kunde@example.com',
    'country' => 'France'
]);
assert_test($res2['lang'] === 'de' && $res2['source'] === 'shipment_profile', "Palier 2 : Préférence explicite de l'expédition ('de')");

// Tier 3 : Préférence du compte utilisateur existant en BDD
// Création temporaire ou test avec compte existant
$testUserEmail = 'test-i18n-account-' . time() . '@example.com';
$stmtUser = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, lang, created_at) VALUES ('Test User', ?, 'dummy_hash', 'user', 'en', NOW())");
$stmtUser->execute([$testUserEmail]);
$createdUserId = $pdo->lastInsertId();

$res3 = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => $testUserEmail,
    'country' => 'France'
]);
assert_test($res3['lang'] === 'en' && $res3['source'] === 'user_account', "Palier 3 : Préférence du compte client ('en')", "Obtenu: {$res3['lang']}/{$res3['source']}");

// Nettoyage utilisateur temporaire
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$createdUserId]);

// Tier 4 : Historique d'une expédition précédente pour le même email
$historyEmail = 'test-history-' . time() . '@example.com';
$stmtHist = $pdo->prepare("INSERT INTO shipments (tracking_number, recipient_name, recipient_phone, recipient_email, recipient_lang, address, city, country, origin_address, origin_city, shipped_at, status, transport_mode, created_at, updated_at) VALUES (?, 'Test Hist', '0102030405', ?, 'nl', 'Keizersgracht 1', 'Amsterdam', 'Pays-Bas', 'Rue de Paris', 'Paris', NOW(), 'shipped', 'road', NOW(), NOW())");
$histNum = 'PS-HIST-' . substr(md5(uniqid()), 0, 7);
$stmtHist->execute([$histNum, $historyEmail]);
$histShipmentId = $pdo->lastInsertId();

$res4 = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => $historyEmail,
    'country' => 'France' // même si le pays est France, l'historique explicite 'nl' prime
]);
assert_test($res4['lang'] === 'nl' && $res4['source'] === 'previous_shipment', "Palier 4 : Reprise de l'historique client ('nl')", "Obtenu: {$res4['lang']}/{$res4['source']}");

// Nettoyage expédition historique
$pdo->prepare("DELETE FROM shipments WHERE id = ?")->execute([$histShipmentId]);

// Tier 5 : Pays de destination
$res5de = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => 'new-client-de@example.com',
    'country' => 'Allemagne'
]);
assert_test($res5de['lang'] === 'de' && $res5de['source'] === 'country', "Palier 5 : Déduction selon pays Allemagne ('de')");

$res5nl = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => 'new-client-nl@example.com',
    'country' => 'Netherlands'
]);
assert_test($res5nl['lang'] === 'nl' && $res5nl['source'] === 'country', "Palier 5 : Déduction selon pays Pays-Bas ('nl')");

$res5en = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => 'new-client-en@example.com',
    'country' => 'United Kingdom'
]);
assert_test($res5en['lang'] === 'en' && $res5en['source'] === 'country', "Palier 5 : Déduction selon pays UK ('en')");

// Tier 6 : Règle de secours par défaut documentée ('fr')
$res6 = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => 'unknown-country@example.com',
    'country' => 'Wakanda'
]);
assert_test($res6['lang'] === 'fr' && $res6['source'] === 'default', "Palier 6 : Règle de secours par défaut documentée ('fr')");

// --------------------------------------------------------------------------
// SUITE 2 : INTERDICTION STRICTE DE DÉDUIRE LA LANGUE DU SEUL DOMAINE EMAIL
// --------------------------------------------------------------------------
echo "\n--- 2. Interdiction stricte de déduction depuis le domaine email ---\n";

$resDomainDe = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => 'johann@firma.de',
    'country' => 'France'
]);
assert_test($resDomainDe['lang'] === 'fr', "Un email @firma.de vers la France n'est PAS forcé en 'de' (Obtenu: '{$resDomainDe['lang']}')");

$resDomainNl = resolve_recipient_language([
    'recipient_lang' => null,
    'recipient_email' => 'pieter@bedrijf.nl',
    'country' => 'Espagne' // Inconnu dans la cartographie directe -> fallback 'fr'
]);
assert_test($resDomainNl['lang'] === 'fr', "Un email @bedrijf.nl vers un pays neutre n'est PAS forcé en 'nl' (Obtenu: '{$resDomainNl['lang']}')");

// --------------------------------------------------------------------------
// SUITE 3 : INDÉPENDANCE ABSOLUE VIS-À-VIS DE LA LANGUE DE L'ADMINISTRATEUR
// --------------------------------------------------------------------------
echo "\n--- 3. Indépendance absolue de la session administrateur ---\n";

// Simuler un admin connecté en Allemand (de)
$_SESSION['lang'] = 'de';
$shipmentFrench = [
    'recipient_lang' => 'fr',
    'recipient_name' => 'François Martin',
    'recipient_email' => 'francois@example.fr',
    'tracking_number' => 'PS-2610-FR0001',
    'city' => 'Lyon',
    'origin_city' => 'Paris',
    'transport_mode' => 'road',
    'status' => 'shipped'
];
$settings = ['nom' => 'PAVA SHIPPING', 'email' => 'support@pava.fr', 'telephone' => '+33 1 00 00 00 00'];
$emailFr = render_tracking_email($shipmentFrench, $settings, 'http://127.0.0.1:8000');
assert_test($emailFr['lang'] === 'fr', "Email pour client FR généré en Français malgré admin en 'de'");
assert_test(strpos($emailFr['subject'], 'Suivi de votre expédition') !== false, "Sujet en Français : '{$emailFr['subject']}'");
assert_test(strpos($emailFr['html'], 'Suivre mon colis') !== false, "Bouton en Français dans l'HTML");

// Simuler un admin connecté en Français (fr) avec un client en Néerlandais (nl)
$_SESSION['lang'] = 'fr';
$shipmentDutch = [
    'recipient_lang' => 'nl',
    'recipient_name' => 'Lars van der Beek',
    'recipient_email' => 'lars@example.nl',
    'tracking_number' => 'PS-2610-NL0001',
    'city' => 'Rotterdam',
    'origin_city' => 'Paris',
    'transport_mode' => 'maritime',
    'status' => 'shipped'
];
$emailNl = render_tracking_email($shipmentDutch, $settings, 'http://127.0.0.1:8000');
assert_test($emailNl['lang'] === 'nl', "Email pour client NL généré en Néerlandais malgré admin en 'fr'");
assert_test(strpos($emailNl['subject'], 'Volg uw zending') !== false, "Sujet en Néerlandais : '{$emailNl['subject']}'");
assert_test(strpos($emailNl['html'], 'Mijn pakket volgen') !== false, "Bouton en Néerlandais dans l'HTML");

// --------------------------------------------------------------------------
// SUITE 4 : RENDU DES MODÈLES DANS LES 4 LANGUES & VARIABLES DYNAMIQUES
// --------------------------------------------------------------------------
echo "\n--- 4. Rendu des modèles dans les 4 langues (FR, EN, DE, NL) ---\n";

$testShipmentTemplate = [
    'tracking_number' => 'PS-2610-TESTVAR',
    'recipient_name' => 'Marie & <script>alert("xss")</script>',
    'recipient_email' => 'marie@example.com',
    'city' => 'München',
    'origin_city' => 'Bordeaux',
    'transport_mode' => 'air',
    'carrier_name' => 'Express Airway',
    'status' => 'out_for_delivery'
];

$languagesToTest = [
    'fr' => ['subject_kw' => 'Suivi de votre expédition', 'btn' => 'Suivre mon colis', 'track_link' => '/fr/track?id='],
    'en' => ['subject_kw' => 'Tracking your shipment', 'btn' => 'Track my parcel', 'track_link' => '/en/track?id='],
    'de' => ['subject_kw' => 'Sendungsverfolgung', 'btn' => 'Mein Paket verfolgen', 'track_link' => '/de/track?id='],
    'nl' => ['subject_kw' => 'Volg uw zending', 'btn' => 'Mijn pakket volgen', 'track_link' => '/nl/track?id=']
];

foreach ($languagesToTest as $langCode => $expect) {
    $rendered = render_tracking_email($testShipmentTemplate, $settings, 'https://pava-shipping.fr', $langCode);
    
    // Sujet
    assert_test(strpos($rendered['subject'], $expect['subject_kw']) !== false, "[$langCode] Sujet traduit contenant '{$expect['subject_kw']}'");
    assert_test(strpos($rendered['subject'], 'PS-2610-TESTVAR') !== false, "[$langCode] Variable numéro de suivi injectée dans le sujet");
    
    // HTML
    assert_test(strpos($rendered['html'], $expect['btn']) !== false, "[$langCode] Bouton d'action traduit ('{$expect['btn']}')");
    assert_test(strpos($rendered['html'], $expect['track_link']) !== false, "[$langCode] Lien public localisé ('{$expect['track_link']}')");
    
    // Protection XSS
    assert_test(strpos($rendered['html'], '<script>') === false, "[$langCode] Variable dynamique correctement échappée (aucun <script> brut)");
    assert_test(strpos($rendered['html'], '&lt;script&gt;') !== false, "[$langCode] Entité HTML présente (&lt;script&gt;)");

    // Texte brut
    assert_test(!empty($rendered['text']), "[$langCode] Version texte brut générée");
    assert_test(strpos($rendered['text'], 'https://pava-shipping.fr/' . $langCode . '/track?id=PS-2610-TESTVAR') !== false, "[$langCode] URL de suivi présente dans la version texte");
}

// --------------------------------------------------------------------------
// SUITE 5 : ENVOI MOCK & AUDIT LOGGING DANS EMAIL_LOGS
// --------------------------------------------------------------------------
echo "\n--- 5. Envoi réaliste (Mock Driver) et audit log (email_logs.email_lang) ---\n";

// Création d'une expédition de test pour la traçabilité
$stmtNewShip = $pdo->prepare("INSERT INTO shipments (tracking_number, recipient_name, recipient_phone, recipient_email, recipient_lang, address, city, country, origin_address, origin_city, shipped_at, status, transport_mode, created_at, updated_at) VALUES (?, 'Klaus Weber', '0600000000', 'klaus@example.com', 'de', 'Hauptstrasse 10', 'Berlin', 'Allemagne', '10 rue de la Paix', 'Paris', NOW(), 'shipped', 'road', NOW(), NOW())");
$testTrackingNum = 'PS-TEST-' . substr(md5(uniqid()), 0, 7);
$stmtNewShip->execute([$testTrackingNum]);
$testShipId = $pdo->lastInsertId();

// 5.1 Envoi automatique / normal respectant la préférence du client ('de')
$emailDataTest = render_tracking_email(['id' => $testShipId, 'tracking_number' => $testTrackingNum, 'recipient_name' => 'Klaus Weber', 'recipient_email' => 'klaus@example.com', 'recipient_lang' => 'de', 'country' => 'Allemagne', 'city' => 'Berlin', 'origin_city' => 'Paris', 'transport_mode' => 'road', 'status' => 'shipped'], $settings, 'http://127.0.0.1:8000');

$optionsTest = [
    'shipment_id' => $testShipId,
    'email_type' => 'tracking_link',
    'email_lang' => $emailDataTest['lang'],
    'mock' => true // Mode test garanti sans appel réseau externe
];

$resultSend = send_email('klaus@example.com', $emailDataTest['subject'], $emailDataTest['html'], $emailDataTest['text'], $optionsTest);
assert_test($resultSend['status'] === 'accepted', "Envoi d'email réussi (status 'accepted')");

// Vérification de l'enregistrement dans email_logs
$stmtCheckLog = $pdo->prepare("SELECT * FROM email_logs WHERE shipment_id = ? ORDER BY id DESC LIMIT 1");
$stmtCheckLog->execute([$testShipId]);
$logEntry = $stmtCheckLog->fetch();
assert_test(!empty($logEntry), "Entrée email_logs créée");
assert_test(($logEntry['email_lang'] ?? '') === 'de', "email_logs.email_lang correctement tracé comme 'de' (Obtenu: '{$logEntry['email_lang']}')");

// 5.2 Surcharge ponctuelle manuelle lors d'un envoi (ex: anglais 'en') sans modifier la BDD
$overrideEmailData = render_tracking_email(['id' => $testShipId, 'tracking_number' => $testTrackingNum, 'recipient_name' => 'Klaus Weber', 'recipient_email' => 'klaus@example.com', 'recipient_lang' => 'de', 'country' => 'Allemagne', 'city' => 'Berlin', 'origin_city' => 'Paris', 'transport_mode' => 'road', 'status' => 'shipped'], $settings, 'http://127.0.0.1:8000', 'en');

$overrideOptions = [
    'shipment_id' => $testShipId,
    'email_type' => 'tracking_link',
    'email_lang' => $overrideEmailData['lang'],
    'mock' => true
];
$resultOverride = send_email('klaus@example.com', $overrideEmailData['subject'], $overrideEmailData['html'], $overrideEmailData['text'], $overrideOptions);
assert_test($resultOverride['status'] === 'accepted', "Envoi ponctuel surchargé ('en') réussi");

$stmtCheckLog2 = $pdo->prepare("SELECT * FROM email_logs WHERE shipment_id = ? ORDER BY id DESC LIMIT 1");
$stmtCheckLog2->execute([$testShipId]);
$logEntry2 = $stmtCheckLog2->fetch();
assert_test(($logEntry2['email_lang'] ?? '') === 'en', "email_logs.email_lang tracé comme 'en' lors du second envoi");

// Vérification que la préférence permanente en BDD n'a pas été modifiée
$stmtCheckShip = $pdo->prepare("SELECT recipient_lang FROM shipments WHERE id = ?");
$stmtCheckShip->execute([$testShipId]);
$permLang = $stmtCheckShip->fetchColumn();
assert_test($permLang === 'de', "La préférence permanente du client sur la fiche colis est restée intacte ('de')");

// Nettoyage expédition et logs de test
$pdo->prepare("DELETE FROM email_logs WHERE shipment_id = ?")->execute([$testShipId]);
$pdo->prepare("DELETE FROM shipments WHERE id = ?")->execute([$testShipId]);

// --------------------------------------------------------------------------
// SUITE 6 : DÉCLENCHEUR D'ENVOI AUTOMATIQUE (notify_status_change)
// --------------------------------------------------------------------------
echo "\n--- 6. Déclencheur d'envoi automatique (notify_status_change) ---\n";
putenv('NOTIFY_EMAIL_ON_STATUS=0');
$resAutoDisabled = notify_status_change(99999, 'delivered', 'Paris');
assert_test($resAutoDisabled === true, "notify_status_change inactif si NOTIFY_EMAIL_ON_STATUS != 1 (sécurité délivrabilité)");

echo "\n=================================================================\n";
echo "   RÉSULTAT DES TESTS : $passedCount PASSÉ(S), $failedCount ÉCHOUÉ(S)\n";
echo "=================================================================\n";

if ($failedCount > 0) {
    exit(1);
}
exit(0);
