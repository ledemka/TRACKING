<?php
// scripts/create-admin.php
require_once __DIR__ . '/../api/db.php';

$email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
$password = getenv('ADMIN_PASSWORD') ?: 'admin';

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("L'administrateur avec cet email existe déjà.\n");
}

$hash = password_hash($password, PASSWORD_ARGON2ID);
if (!$hash) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

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
