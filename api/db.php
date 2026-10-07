<?php
// api/db.php
$envFile = getenv('APP_ENV_FILE') ?: __DIR__ . '/../.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove quotes if present
            if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }
            putenv("$name=$value");
        }
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'tracking_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$useSqlite = (getenv('APP_ENV') === 'local' && empty(getenv('DB_USER')));

if ($useSqlite) {
    $dsn = "sqlite:" . __DIR__ . '/../database.sqlite';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
} else {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
}

try {
    $pdo = new PDO($dsn, $useSqlite ? null : $user, $useSqlite ? null : $pass, $options);
    if ($useSqlite) {
        $pdo->exec('PRAGMA foreign_keys = ON;');
    }
} catch (\PDOException $e) {
    if (getenv('APP_ENV') === 'local') {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    } else {
        // Pas d'erreur affichée en prod
        die("Erreur de connexion a la base de donnees.");
    }
}
