<?php
// api/migrate.php
require_once __DIR__ . '/db.php';

$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'STAFF',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER UNIQUE NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS carriers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS shipments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tracking_number VARCHAR(255) UNIQUE NOT NULL,
        carrier_id INTEGER NULL,
        recipient_name VARCHAR(255) NOT NULL,
        recipient_phone VARCHAR(255),
        recipient_email VARCHAR(255) NOT NULL,
        address VARCHAR(255) NOT NULL,
        city VARCHAR(255) NOT NULL,
        country VARCHAR(255) NOT NULL,
        origin_address VARCHAR(255) NOT NULL,
        origin_city VARCHAR(255) NOT NULL,
        shipped_at DATETIME,
        estimated_delivery_at DATETIME,
        status VARCHAR(50) DEFAULT 'shipped',
        description TEXT,
        weight_kg DECIMAL(10,2),
        parcel_count INTEGER DEFAULT 1,
        is_demo BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(carrier_id) REFERENCES carriers(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS tracking_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        shipment_id INTEGER NOT NULL,
        label VARCHAR(255) NOT NULL,
        location VARCHAR(255),
        status VARCHAR(50),
        occurred_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by INTEGER NULL,
        FOREIGN KEY(shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
        FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY,
        user_id INTEGER NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        payload TEXT,
        last_activity INTEGER NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS app_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom VARCHAR(255) DEFAULT '[NOM DE MON ENTREPRISE]',
        logo VARCHAR(255),
        email VARCHAR(255),
        telephone VARCHAR(255),
        adresse TEXT,
        color_primary VARCHAR(50) DEFAULT '#0B1F3A',
        color_accent VARCHAR(50) DEFAULT '#F59E0B'
    )",
    "CREATE TABLE IF NOT EXISTS email_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recipient VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        status VARCHAR(50) NOT NULL,
        error_message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        attempts INTEGER DEFAULT 1,
        last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP
    )"
];

// SQLite vs MySQL syntax adjustments
$isSqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';

foreach ($queries as $query) {
    if (!$isSqlite) {
        $query = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'INT AUTO_INCREMENT PRIMARY KEY', $query);
        $query = str_replace('BOOLEAN', 'TINYINT(1)', $query);
        $query = str_replace('DATETIME DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $query); // Ok in both
    }
    try {
        $pdo->exec($query);
    } catch (\PDOException $e) {
        echo "Erreur sur la requête : $query\n";
        echo $e->getMessage() . "\n";
    }
}

// Ensure at least one setting row exists
$stmt = $pdo->query("SELECT COUNT(*) FROM app_settings");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO app_settings (nom) VALUES ('[NOM DE MON ENTREPRISE]')");
}

echo "Migrations terminées avec succès.\n";
