<?php
// scripts/audit-syntax-and-lint.php
// Lint exhaustif de tous les fichiers PHP du projet

$dir = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$phpBinary = 'C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe';
if (!file_exists($phpBinary)) {
    $phpBinary = 'php';
}

$total = 0;
$errors = [];

echo "=================================================================\n";
echo "      AUDIT TECHNIQUE : CONTRÔLE DE SYNTAXE PHP (php -l)         \n";
echo "=================================================================\n";

foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $filePath = $file->getRealPath();
        
        // Ignorer les dossiers vendor s'il y en avait
        if (strpos($filePath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) {
            continue;
        }

        $total++;
        $cmd = escapeshellarg($phpBinary) . ' -l ' . escapeshellarg($filePath);
        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            $errors[$filePath] = implode("\n", $output);
            echo "  [FAIL] " . str_replace($dir . DIRECTORY_SEPARATOR, '', $filePath) . "\n";
        }
    }
}

if (empty($errors)) {
    echo "  [PASS] Syntaxe 100% valide sur l'ensemble des $total fichiers PHP.\n";
} else {
    echo "  [FAIL] " . count($errors) . " fichier(s) avec erreur(s) de syntaxe :\n";
    foreach ($errors as $f => $msg) {
        echo "    $f :\n$msg\n";
    }
}

echo "=================================================================\n\n";

if (!empty($errors)) {
    exit(1);
}
exit(0);
