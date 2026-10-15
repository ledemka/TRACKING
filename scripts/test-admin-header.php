<?php
// scripts/test-admin-header.php
// Vérification automatique de la structure, du positionnement et des z-index du header admin

ob_start();
require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../api/db.php';
ob_clean();

// Créer une session admin valide simulée
$_SESSION['user_id'] = 3; // Admin ID
$_SESSION['role'] = 'ADMIN';
$_SESSION['created_at'] = time();
$_SESSION['last_action'] = time();
$_SESSION['csrf_token'] = 'test-csrf-token';

$testsPassed = 0;
$testsFailed = 0;

function assert_test(bool $cond, string $msg): void {
    global $testsPassed, $testsFailed;
    if ($cond) {
        echo "  [PASS] $msg\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] $msg\n";
        $testsFailed++;
    }
}

echo "=================================================================\n";
echo "      TESTS DE VALIDATION DU HEADER ADMINISTRATION (STICKY)       \n";
echo "=================================================================\n\n";

// 1. Tester le rendu de templates/admin_header.php
ob_start();
$page_title = "Test Page";
$use_map = true;
require __DIR__ . '/../templates/admin_header.php';
$headerHtml = ob_get_clean();

echo "--- 1. Propriétés CSS du composant header admin ---\n";
assert_test(strpos($headerHtml, 'admin-header-sticky') !== false, "La classe 'admin-header-sticky' est appliquée sur <header>");
assert_test(strpos($headerHtml, 'position: sticky;') !== false, "La propriété 'position: sticky;' est déclarée en style inline de secours");
assert_test(strpos($headerHtml, 'top: 0;') !== false, "La propriété 'top: 0;' est déclarée pour l'ancrage au défilement");
assert_test(strpos($headerHtml, 'z-index: 1050;') !== false, "Le z-index de 1050 est explicitement défini (supérieur à Leaflet z:1000)");
assert_test(strpos($headerHtml, 'background-color: #171717;') !== false, "L'arrière-plan opaque #171717 est garanti pour masquer le contenu défilant");
assert_test(strpos($headerHtml, 'border-b-2 border-[#FFE500]') !== false, "Le liseré jaune #FFE500 distinctif est présent");

echo "\n--- 2. Présence et accessibilité des contrôles du header ---\n";
assert_test(strpos($headerHtml, 'href="/dashboard"') !== false, "Lien Dashboard présent");
assert_test(strpos($headerHtml, 'href="/shipments"') !== false, "Lien Expéditions présent");
assert_test(strpos($headerHtml, 'href="/customers"') !== false, "Lien Clients présent dans la barre de navigation");
assert_test(strpos($headerHtml, 'admin-burger-btn') !== false, "Bouton burger mobile présent");
assert_test(strpos($headerHtml, 'admin-mobile-menu') !== false, "Menu mobile admin présent");
assert_test(strpos($headerHtml, 'lang-switcher-trigger') !== false, "Déclencheur du sélecteur de langue présent");
assert_test(strpos($headerHtml, 'style="z-index: 1060;"') !== false, "Le menu déroulant de langue possède un z-index de 1060 (au-dessus du header z:1050)");
assert_test(strpos($headerHtml, 'action="/logout"') !== false, "Formulaire de déconnexion présent");

echo "\n--- 3. Vérification du conteneur <main> et de l'écoulement normal ---\n";
assert_test(strpos($headerHtml, '<main class="flex-grow py-6 px-4 pb-12 max-w-7xl mx-auto w-full relative z-0">') !== false, "Le conteneur <main> utilise un espacement py-6 sans pt-20 artificiel");

echo "\n--- 4. Vérification de la feuille de style assets/css/style.css ---\n";
$cssContent = file_get_contents(__DIR__ . '/../assets/css/style.css');
assert_test(strpos($cssContent, '.admin-header-sticky') !== false, "La règle '.admin-header-sticky' est compilée dans style.css");
assert_test(strpos($cssContent, 'position:sticky') !== false || strpos($cssContent, 'position:-webkit-sticky') !== false, "La propriété sticky est présente dans style.css");
assert_test(strpos($cssContent, 'z-index:1050') !== false, "Le z-index 1050 est présent dans style.css");

echo "\n--- 5. Rendu complet des pages de l'administration ---\n";
// Simulation de rendu des pages admin
$adminPages = [
    'dashboard' => __DIR__ . '/../dashboard.php',
    'shipments' => __DIR__ . '/../shipments.php',
    'shipment_new' => __DIR__ . '/../shipment_new.php',
    'customers' => __DIR__ . '/../customers.php',
];

foreach ($adminPages as $pageName => $pageFile) {
    ob_start();
    try {
        $_SERVER['REQUEST_URI'] = "/$pageName";
        include $pageFile;
        $html = ob_get_clean();
        $hasStickyHeader = (strpos($html, 'admin-header-sticky') !== false);
        assert_test($hasStickyHeader, "Page '$pageName' rend le header sticky avec succès");
    } catch (\Throwable $e) {
        ob_end_clean();
        assert_test(false, "Page '$pageName' a levé une exception : " . $e->getMessage());
    }
}

// Page spécifique de consultation d'expédition (/shipments/33)
ob_start();
try {
    $_GET['id'] = 33;
    $_SERVER['REQUEST_URI'] = "/shipments/33";
    include __DIR__ . '/../shipment.php';
    $shipmentHtml = ob_get_clean();
    assert_test(strpos($shipmentHtml, 'admin-header-sticky') !== false, "Page consultation expédition (/shipments/33) intègre le header sticky");
    assert_test(strpos($shipmentHtml, 'map-element') !== false, "La carte Leaflet coexiste avec le header");
    assert_test(strpos($shipmentHtml, 'z-0 relative') !== false, "Le conteneur map-element isole le z-index interne de Leaflet");
} catch (\Throwable $e) {
    ob_end_clean();
    assert_test(false, "Page /shipments/33 a levé une exception : " . $e->getMessage());
}

echo "\n=================================================================\n";
echo "   RÉSULTAT DES TESTS : $testsPassed PASS / $testsFailed FAIL\n";
echo "=================================================================\n";

exit($testsFailed > 0 ? 1 : 0);
