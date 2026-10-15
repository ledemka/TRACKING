<?php
// scripts/test-i18n-routing.php
// Suite complète de tests pour la gestion de la langue initiale, le sélecteur et le routage multilingue

require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/lib/security.php';
require_once __DIR__ . '/../api/lib/i18n.php';
require_once __DIR__ . '/../api/lib/auth.php';

echo "=========================================================\n";
echo " TEST SUITE: SYSTÈME I18N, PRIORITÉS, PERSISTANCE & ROUTAGE\n";
echo "=========================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assert_test($description, $condition, $extraInfo = '') {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] $description\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] $description" . ($extraInfo ? " ($extraInfo)" : "") . "\n";
        $testsFailed++;
    }
}

function reset_request_state() {
    global $current_lang;
    $current_lang = null;
    $_GET = [];
    $_COOKIE = [];
    $_SESSION = [];
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
    $_SERVER['HTTP_CF_IPCOUNTRY'] = '';
    $_SERVER['HTTP_GEOIP_COUNTRY_CODE'] = '';
    $_SERVER['HTTP_X_GEOIP_COUNTRY'] = '';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['REQUEST_URI'] = '/';
}

// -------------------------------------------------------------
// TEST 1 : Première visite avec navigateur fr, en, de, nl
// -------------------------------------------------------------
echo "1. Tests Première visite (Accept-Language du navigateur)\n";

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR,fr;q=0.9,en;q=0.8';
$lang = init_i18n();
assert_test("Accept-Language 'fr-FR' détecte 'fr'", $lang === 'fr', "Reçu: $lang");

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9,fr;q=0.5';
$lang = init_i18n();
assert_test("Accept-Language 'en-US' détecte 'en'", $lang === 'en', "Reçu: $lang");

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.9,en;q=0.5';
$lang = init_i18n();
assert_test("Accept-Language 'de-DE' détecte 'de'", $lang === 'de', "Reçu: $lang");

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'nl-BE,nl;q=0.9,fr;q=0.8';
$lang = init_i18n();
assert_test("Accept-Language 'nl-BE' détecte 'nl'", $lang === 'nl', "Reçu: $lang");

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'da, en-gb;q=0.8, en;q=0.7';
$lang = init_i18n();
assert_test("Accept-Language complexe avec pondérations détecte 'en'", $lang === 'en', "Reçu: $lang");

// -------------------------------------------------------------
// TEST 2 : Navigateur dans une langue non prise en charge
// -------------------------------------------------------------
echo "\n2. Tests Navigateur langue non supportée (es, it, ru, ja...)\n";

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES,es;q=0.9,it;q=0.8';
// Sans IP Geo -> Fallback français
$lang = init_i18n();
assert_test("Langue non supportée (es-ES) sans indice Geo replie sur 'fr'", $lang === 'fr', "Reçu: $lang");

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja-JP,ja;q=0.9';
// Avec indice Geo secondaire (ex: pays DE)
$_SERVER['HTTP_X_GEOIP_COUNTRY'] = 'DE';
$lang = init_i18n();
assert_test("Langue non supportée (ja-JP) avec IP en Allemagne utilise indice secondaire 'de'", $lang === 'de', "Reçu: $lang");

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru-RU,ru;q=0.9';
// Avec indice Geo secondaire (ex: pays NL)
$_SERVER['HTTP_X_GEOIP_COUNTRY'] = 'NL';
$lang = init_i18n();
assert_test("Langue non supportée (ru-RU) avec IP aux Pays-Bas utilise indice secondaire 'nl'", $lang === 'nl', "Reçu: $lang");

reset_request_state();
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pt-BR,pt;q=0.9';
// Avec indice Geo secondaire pays non lié aux 4 langues (ex: Brésil BR) -> Fallback FR
$_SERVER['HTTP_X_GEOIP_COUNTRY'] = 'BR';
$lang = init_i18n();
assert_test("Langue non supportée avec pays non lié replie sur 'fr'", $lang === 'fr', "Reçu: $lang");

// -------------------------------------------------------------
// TEST 3 : Utilisateur connecté avec préférence enregistrée en BDD
// -------------------------------------------------------------
echo "\n3. Tests Utilisateur connecté avec préférence en base de données\n";

// Trouver ou préparer l'admin user
$stmt = $pdo->query("SELECT id FROM users LIMIT 1");
$testUserId = (int)$stmt->fetchColumn();

// Enregistrer la préférence 'de' en BDD
$updateStmt = $pdo->prepare("UPDATE users SET lang = 'de' WHERE id = ?");
$updateStmt->execute([$testUserId]);

reset_request_state();
$_SESSION['user_id'] = $testUserId;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR'; // Même si le navigateur est en FR !
$lang = init_i18n();
assert_test("Utilisateur connecté avec 'de' en BDD : priorité absolue à 'de' malgré Accept-Language FR", $lang === 'de', "Reçu: $lang");

// Enregistrer la préférence 'nl' en BDD
$updateStmt = $pdo->prepare("UPDATE users SET lang = 'nl' WHERE id = ?");
$updateStmt->execute([$testUserId]);

reset_request_state();
$_SESSION['user_id'] = $testUserId;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
$lang = init_i18n();
assert_test("Utilisateur connecté avec 'nl' en BDD : priorité absolue à 'nl' malgré Accept-Language EN", $lang === 'nl', "Reçu: $lang");

// -------------------------------------------------------------
// TEST 4 : Changement manuel de langue et persistance
// -------------------------------------------------------------
echo "\n4. Tests Changement manuel de langue (URL, BDD et cookies)\n";

// A. Utilisateur connecté change manuellement de langue via ?lang=en
reset_request_state();
$_SESSION['user_id'] = $testUserId;
$_GET['lang'] = 'en';
$lang = init_i18n();
assert_test("Changement manuel via ?lang=en pour utilisateur connecté retourne 'en'", $lang === 'en', "Reçu: $lang");

// Vérifier que la BDD a bien été mise à jour à 'en'
$checkStmt = $pdo->prepare("SELECT lang FROM users WHERE id = ?");
$checkStmt->execute([$testUserId]);
$savedLang = $checkStmt->fetchColumn();
assert_test("La préférence du profil utilisateur en BDD a bien été mise à jour vers 'en'", $savedLang === 'en', "En BDD: $savedLang");

// B. Visiteur anonyme change manuellement via set_current_lang('de')
reset_request_state();
set_current_lang('de');
assert_test("set_current_lang('de') positionne session et current_lang sur 'de'", get_current_lang() === 'de');
assert_test("Session 'lang_manual' est marquée true pour interdire tout écrasement automatique", !empty($_SESSION['lang_manual']));

// C. Requête suivante du même visiteur sans paramètre d'URL (ne doit JAMAIS être écrasée)
reset_request_state();
$_SESSION['lang'] = 'de';
$_SESSION['lang_manual'] = true;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR,fr;q=0.9'; // Navigateur FR
$lang = init_i18n();
assert_test("Préférence manuelle 'de' en session conservée et JAMAIS écrasée par Accept-Language FR", $lang === 'de', "Reçu: $lang");

// -------------------------------------------------------------
// TEST 5 : Navigation entre pages, conservation des paramètres et SEO
// -------------------------------------------------------------
echo "\n5. Tests Conservation de l'URL, des paramètres et balises SEO\n";

$_SERVER['REQUEST_URI'] = '/services/livraison?q=express&sort=date';
$_GET = ['q' => 'express', 'sort' => 'date'];

$urlEn = get_localized_url('en');
assert_test("get_localized_url('en') préfixe la route et préserve les paramètres", $urlEn === '/en/services/livraison?q=express&sort=date', "Généré: $urlEn");

$urlNl = get_localized_url('nl');
assert_test("get_localized_url('nl') préfixe la route et préserve les paramètres", $urlNl === '/nl/services/livraison?q=express&sort=date', "Généré: $urlNl");

// Si déjà sur une URL localisée /en/track?id=TRK-100 et bascule vers 'de'
$_SERVER['REQUEST_URI'] = '/en/track?id=TRK-100';
$_GET = ['id' => 'TRK-100'];
$urlDe = get_localized_url('de');
assert_test("Bascule d'une URL localisée /en/ vers /de/ remplace proprement le préfixe", $urlDe === '/de/track?id=TRK-100', "Généré: $urlDe");

// Vérification de la génération du composant sélecteur de langue
$switcherHtml = render_language_switcher('dark');
assert_test("Sélecteur de langue contient le conteneur data-lang-switcher", str_contains($switcherHtml, 'data-lang-switcher'));
assert_test("Sélecteur de langue contient les 4 langues avec leurs noms natifs", 
    str_contains($switcherHtml, 'Français') && 
    str_contains($switcherHtml, 'English') && 
    str_contains($switcherHtml, 'Deutsch') && 
    str_contains($switcherHtml, 'Nederlands')
);
assert_test("Sélecteur de langue contient les drapeaux SVG vectoriels", str_contains($switcherHtml, '<svg'));

// -------------------------------------------------------------
// TEST 6 : URL invalide et service de géolocalisation indisponible
// -------------------------------------------------------------
echo "\n6. Tests URL invalide et résilience secours (Fallback)\n";

reset_request_state();
$_GET['lang'] = 'invalid_language_code_123';
$lang = init_i18n();
assert_test("Paramètre ?lang= invalide ignoré proprement, replie sur le français par défaut", $lang === 'fr', "Reçu: $lang");

reset_request_state();
$_SERVER['REMOTE_ADDR'] = '999.999.999.999'; // IP invalide
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = ''; // Pas de header
$lang = init_i18n();
assert_test("IP invalide et absence de Accept-Language replie gracieusement sur 'fr' sans erreur", $lang === 'fr', "Reçu: $lang");

echo "\n=========================================================\n";
echo " RÉSULTATS DES TESTS I18N : $testsPassed PASS / $testsFailed FAIL\n";
echo "=========================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
