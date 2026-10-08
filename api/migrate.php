<?php
// api/migrate.php
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.\n");
}

require_once __DIR__ . '/db.php';

$isSqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
$schemaTableQuery = "CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(255) PRIMARY KEY,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
try {
    $pdo->exec($schemaTableQuery);
} catch (\PDOException $e) {
    die("Erreur fatale : Impossible de créer schema_migrations. " . $e->getMessage() . "\n");
}

function has_migrated($pdo, $version) {
    $stmt = $pdo->prepare("SELECT version FROM schema_migrations WHERE version = ?");
    $stmt->execute([$version]);
    return $stmt->fetch() !== false;
}

function record_migration($pdo, $version) {
    $stmt = $pdo->prepare("INSERT INTO schema_migrations (version) VALUES (?)");
    $stmt->execute([$version]);
}

$migrations = [
    '001_create_users' => "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'STAFF',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    '002_create_admins' => "CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER UNIQUE NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    '003_create_carriers' => "CREATE TABLE IF NOT EXISTS carriers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL
    )",
    '004_create_shipments' => "CREATE TABLE IF NOT EXISTS shipments (
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
    '005_create_tracking_events' => "CREATE TABLE IF NOT EXISTS tracking_events (
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
    '006_create_sessions' => "CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY,
        user_id INTEGER NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        payload TEXT,
        last_activity INTEGER NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    '007_create_app_settings' => "CREATE TABLE IF NOT EXISTS app_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom VARCHAR(255) DEFAULT 'Mon Entreprise',
        logo VARCHAR(255),
        email VARCHAR(255),
        telephone VARCHAR(255),
        adresse TEXT,
        color_primary VARCHAR(50) DEFAULT '#0B1F3A',
        color_accent VARCHAR(50) DEFAULT '#F59E0B'
    )",
    '008_create_email_logs' => "CREATE TABLE IF NOT EXISTS email_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recipient VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        status VARCHAR(50) NOT NULL,
        error_message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    '009_create_login_attempts' => "CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        attempts INTEGER DEFAULT 1,
        last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    '010_index_shipments_status' => "CREATE INDEX idx_shipments_status ON shipments(status)",
    '011_create_rate_limits' => "CREATE TABLE IF NOT EXISTS rate_limits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        action VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        hits INT DEFAULT 1,
        window_started_at DATETIME NOT NULL
    )",
    '012_add_coordinates' => "ALTER TABLE tracking_events ADD COLUMN latitude DECIMAL(9,6) NULL;
    ALTER TABLE tracking_events ADD COLUMN longitude DECIMAL(9,6) NULL;",
    '013_add_destination_coordinates' => "ALTER TABLE shipments ADD COLUMN destination_lat DECIMAL(9,6) NULL;
    ALTER TABLE shipments ADD COLUMN destination_lng DECIMAL(9,6) NULL;",
    '014_update_colors' => "UPDATE app_settings SET color_primary = '#0B1F3A' WHERE color_primary = '#071A33';
    UPDATE app_settings SET color_accent = '#F59E0B' WHERE color_accent = '#F79009';"
];

foreach ($migrations as $version => $query) {
    if (has_migrated($pdo, $version)) {
        continue;
    }

    if (!$isSqlite) {
        $query = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'INT AUTO_INCREMENT PRIMARY KEY', $query);
        $query = str_replace('BOOLEAN', 'TINYINT(1)', $query);
        if (strpos(strtoupper($query), 'CREATE TABLE') !== false) {
            $query .= " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        }
    }
    
    try {
        $pdo->exec($query);
        record_migration($pdo, $version);
        echo "Migration appliquée : $version\n";
        
        // Add index on rate limits after table creation
        if ($version === '011_create_rate_limits') {
            $pdo->exec("CREATE INDEX idx_rate_limits ON rate_limits(action, ip_address)");
        }
    } catch (\PDOException $e) {
        echo "Erreur sur la migration $version : \n";
        echo $e->getMessage() . "\n";
        exit(1);
    }
}

$stmt = $pdo->query("SELECT COUNT(*) FROM app_settings");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO app_settings (nom) VALUES ('Mon Entreprise')");
}

echo "Toutes les migrations sont à jour.\n";
