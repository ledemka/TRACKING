<?php
// scripts/audit-translations.php
// Audit exhaustif des clés de traduction : parité FR/EN/DE/NL et détection des clés manquantes dans le code

require_once __DIR__ . '/../api/bootstrap.php';

$langs = ['fr', 'en', 'de', 'nl'];
$dicts = [];

foreach ($langs as $l) {
    $file = __DIR__ . "/../lang/$l.php";
    if (!file_exists($file)) {
        die("Fichier manquant : $file\n");
    }
    $dicts[$l] = require $file;
}

echo "=================================================================\n";
echo "      AUDIT TECHNIQUE : PARITÉ ET COHÉRENCE DES TRADUCTIONS      \n";
echo "=================================================================\n\n";

$frKeys = array_keys($dicts['fr']);
$allKeys = $frKeys;
foreach ($langs as $l) {
    $allKeys = array_unique(array_merge($allKeys, array_keys($dicts[$l])));
}

echo "Nombre total de clés uniques répertoriées : " . count($allKeys) . "\n";
foreach ($langs as $l) {
    echo "  - $l : " . count($dicts[$l]) . " clés\n";
}
echo "\n";

$hasDiscrepancy = false;

// 1. Vérifier si des clés de FR manquent dans les autres langues
echo "--- 1. Vérification de la parité inter-langues ---\n";
foreach (['en', 'de', 'nl'] as $targetLang) {
    $missingInTarget = array_diff($frKeys, array_keys($dicts[$targetLang]));
    if (!empty($missingInTarget)) {
        echo "  [FAIL] Clés présentes en FR mais MANQUANTES en $targetLang (" . count($missingInTarget) . ") :\n";
        foreach ($missingInTarget as $k) {
            echo "    - $k\n";
        }
        $hasDiscrepancy = true;
    } else {
        echo "  [PASS] Toutes les clés FR sont présentes en $targetLang.\n";
    }

    $extraInTarget = array_diff(array_keys($dicts[$targetLang]), $frKeys);
    if (!empty($extraInTarget)) {
        echo "  [WARN] Clés présentes en $targetLang mais absentes en FR (" . count($extraInTarget) . ") :\n";
        foreach ($extraInTarget as $k) {
            echo "    - $k\n";
        }
    }
}

// 2. Recherche des appels __('...') dans le code source
echo "\n--- 2. Détection des clés appelées dans le code mais absentes des dictionnaires ---\n";
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(dirname(__DIR__), RecursiveDirectoryIterator::SKIP_DOTS)
);

$calledKeys = [];
$pattern = "/(?:__|__attr|__js)\(\s*['\"]([^'\"]+)['\"]/u";

foreach ($iterator as $file) {
    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['php', 'js', 'html'])) {
        $path = $file->getRealPath();
        // Ignorer les fichiers de scripts de test eux-mêmes et les logs
        if (strpos($path, DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR) !== false ||
            strpos($path, DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) !== false) {
            continue;
        }

        $content = file_get_contents($path);
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[1] as $key) {
                // Si la clé ne contient pas de variables PHP
                if (!str_contains($key, '$')) {
                    $calledKeys[$key][] = str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $path);
                }
            }
        }
    }
}

$missingFromDict = [];
foreach (array_keys($calledKeys) as $key) {
    if (!isset($dicts['fr'][$key])) {
        $missingFromDict[$key] = array_unique($calledKeys[$key]);
    }
}

if (!empty($missingFromDict)) {
    echo "  [FAIL] " . count($missingFromDict) . " clé(s) appelée(s) dans le code mais ABSENTE(S) de lang/fr.php :\n";
    foreach ($missingFromDict as $k => $locations) {
        echo "    - '$k' dans : " . implode(', ', $locations) . "\n";
    }
    $hasDiscrepancy = true;
} else {
    echo "  [PASS] 100% des clés appelées dans l'application existent dans le dictionnaire FR.\n";
}

// 3. Vérification des paramètres dynamiques (:param)
echo "\n--- 3. Contrôle des paramètres d'interpolation (:param) ---\n";
$paramErrors = 0;
foreach ($frKeys as $key) {
    preg_match_all('/:([a-zA-Z0-9_]+)/', $dicts['fr'][$key] ?? '', $frParams);
    $expectedParams = $frParams[1] ?? [];
    if (empty($expectedParams)) continue;

    foreach (['en', 'de', 'nl'] as $l) {
        preg_match_all('/:([a-zA-Z0-9_]+)/', $dicts[$l][$key] ?? '', $targetParams);
        $diff = array_diff($expectedParams, $targetParams[1] ?? []);
        if (!empty($diff)) {
            echo "  [WARN] Clé '$key' : paramètre(s) manquant(s) en $l (" . implode(', ', $diff) . ")\n";
            $paramErrors++;
        }
    }
}

if ($paramErrors === 0) {
    echo "  [PASS] Tous les paramètres d'interpolation (:param) sont alignés entre FR, EN, DE, NL.\n";
}

echo "\n=================================================================\n";
if ($hasDiscrepancy) {
    echo "RÉSULTAT : Des anomalies de traduction ont été identifiées.\n";
    exit(1);
} else {
    echo "RÉSULTAT : Audit des dictionnaires 100% CONFORME !\n";
    exit(0);
}
