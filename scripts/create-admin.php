<?php
// scripts/create-admin.php
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.\n");
}

require_once __DIR__ . '/../api/db.php';

$email = getenv('ADMIN_EMAIL');
$password = getenv('ADMIN_PASSWORD');

if (!$email) {
    echo "Email de l'administrateur : ";
    $email = trim(fgets(STDIN));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Erreur : Email invalide.\n");
}

if (!$password) {
    // Masquage basique du mot de passe en CLI
    echo "Mot de passe (saisie masquée si possible, sinon visible) : ";
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $password = trim(shell_exec('powershell -Command "$pwd = Read-Host -AsSecureString; $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($pwd); [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)"'));
    } else {
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
    }
}

if (strlen($password) < 12) {
    die("Erreur : Le mot de passe doit contenir au moins 12 caractères.\n");
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("L'administrateur avec cet email existe déjà.\n");
}

if (defined('PASSWORD_ARGON2ID')) {
    $algo = PASSWORD_ARGON2ID;
    $options = [];
    $algoName = 'Argon2id';
} else {
    $algo = PASSWORD_BCRYPT;
    $options = ['cost' => 12];
    $algoName = 'Bcrypt (cost 12)';
}

$hash = password_hash($password, $algo, $options);
if (!$hash) {
    die("Erreur lors du hachage du mot de passe.\n");
}

echo "Algorithme de hachage utilisé : $algoName\n";

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'ADMIN')");
    $stmt->execute(['Admin', $email, $hash]);
    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO admins (user_id) VALUES (?)");
    $stmt->execute([$userId]);
    $pdo->commit();
    echo "Administrateur créé avec succès ($email).\n";
} catch (\Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de la création de l'admin : " . $e->getMessage() . "\n");
}
