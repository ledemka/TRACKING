<?php
// scripts/check-i18n.php
// Script d'audit et de validation de la couverture i18n pour TRACKING

$rootDir = dirname(__DIR__);
$langDir = $rootDir . '/lang';

$languages = ['fr', 'en', 'de', 'nl'];
$dicts = [];

echo "========================================================\n";
echo "   AUDIT DU SYSTÈME DE TRADUCTION TRACKING (i18n)\n";
echo "========================================================\n\n";

// 1. Chargement des dictionnaires
foreach ($languages as $lang) {
    $file = $langDir . '/' . $lang . '.php';
    if (!file_exists($file)) {
        die("ERREUR: Le fichier $file n'existe pas !\n");
    }
    $data = require $file;
    if (!is_array($data)) {
        die("ERREUR: Le fichier $file ne retourne pas un tableau !\n");
    }
    $dicts[$lang] = $data;
    echo sprintf("✓ Dictionnaire [%s] chargé : %d clés\n", strtoupper($lang), count($data));
}
echo "\n";

// 2. Vérification des écarts de clés entre FR et les autres langues
$frKeys = array_keys($dicts['fr']);
$hasError = false;

foreach (['en', 'de', 'nl'] as $lang) {
    $missingInLang = array_diff($frKeys, array_keys($dicts[$lang]));
    $extraInLang = array_diff(array_keys($dicts[$lang]), $frKeys);

    if (count($missingInLang) > 0) {
        $hasError = true;
        echo sprintf("❌ [%s] Clés manquantes par rapport à FR (%d) :\n", strtoupper($lang), count($missingInLang));
        foreach ($missingInLang as $mKey) {
            echo "   - $mKey\n";
        }
    } else {
        echo sprintf("✓ [%s] Aucune clé manquante par rapport à FR\n", strtoupper($lang));
    }

    if (count($extraInLang) > 0) {
        $hasError = true;
        echo sprintf("⚠️  [%s] Clés supplémentaires non présentes dans FR (%d) :\n", strtoupper($lang), count($extraInLang));
        foreach ($extraInLang as $eKey) {
            echo "   - $eKey\n";
        }
    }
}
echo "\n";

// 3. Vérification des valeurs vides
foreach ($languages as $lang) {
    $emptyKeys = [];
    foreach ($dicts[$lang] as $k => $v) {
        if ($v === null || $v === '') {
            $emptyKeys[] = $k;
        }
    }
    if (count($emptyKeys) > 0) {
        $hasError = true;
        echo sprintf("⚠️  [%s] Valeurs vides détectées (%d) :\n", strtoupper($lang), count($emptyKeys));
        foreach ($emptyKeys as $ek) {
            echo "   - $ek\n";
        }
    } else {
        echo sprintf("✓ [%s] Aucune valeur vide\n", strtoupper($lang));
    }
}
echo "\n";

// 4. Recherche de toutes les clés utilisées dans le code PHP
echo "Scan des fichiers PHP du projet...\n";
$phpFiles = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
        function ($file, $key, $iterator) {
            $path = $file->getPathname();
            if (strpos($path, 'node_modules') !== false || strpos($path, '.git') !== false || strpos($path, 'lang') !== false) {
                return false;
            }
            return true;
        }
    )
);

$usedKeys = [];
$regex = '/(?:__|__attr|__js|_e)\s*\(\s*[\'"]([a-zA-Z0-9_\.]+)[\'"]/';

foreach ($phpFiles as $file) {
    if ($file->getExtension() === 'php' && $file->getFilename() !== 'check-i18n.php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all($regex, $content, $matches)) {
            foreach ($matches[1] as $k) {
                $usedKeys[$k][] = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
    }
}

echo sprintf("Total de clés uniques utilisées dans le code PHP : %d\n\n", count($usedKeys));

// Vérifier si des clés utilisées dans le code n'existent pas dans fr.php
$undefinedInFr = [];
foreach ($usedKeys as $k => $locations) {
    if (!isset($dicts['fr'][$k])) {
        $undefinedInFr[$k] = array_unique($locations);
    }
}

if (count($undefinedInFr) > 0) {
    $hasError = true;
    echo sprintf("❌ Clés utilisées dans le code PHP mais ABSENTES de lang/fr.php (%d) :\n", count($undefinedInFr));
    foreach ($undefinedInFr as $k => $locs) {
        echo sprintf("   - %s (dans %s)\n", $k, implode(', ', $locs));
    }
    echo "\n";
} else {
    echo "✓ Toutes les clés utilisées dans le code sont bien définies dans lang/fr.php\n\n";
}

// 5. Clés définies dans les dictionnaires mais jamais utilisées dans le code
$unusedInCode = array_diff($frKeys, array_keys($usedKeys));
echo sprintf("ℹ️  Clés dans le dictionnaire mais non détectées par regex dans le code : %d\n", count($unusedInCode));

echo "\n========================================================\n";
if ($hasError) {
    echo "STATUT: Des ajustements sont nécessaires (voir ci-dessus).\n";
} else {
    echo "STATUT: Dictionnaires cohérents et alignés à 100% !\n";
}
echo "========================================================\n";
