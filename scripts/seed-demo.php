<?php
// scripts/seed-demo.php
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.\n");
}

require_once __DIR__ . '/../api/db.php';

// Création du transporteur si inexistant
$stmt = $pdo->prepare("SELECT id FROM carriers WHERE name = 'Transport Express'");
$stmt->execute();
$carrier = $stmt->fetch();
if (!$carrier) {
    $pdo->exec("INSERT INTO carriers (name) VALUES ('Transport Express')");
    $carrierId = $pdo->lastInsertId();
} else {
    $carrierId = $carrier['id'];
}

$tracking = 'COLIS-2026-00125';

// Vérification de l'idempotence
$stmt = $pdo->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
$stmt->execute([$tracking]);
$shipment = $stmt->fetch();

if ($shipment) {
    echo "Le colis de démonstration $tracking existe déjà.\n";
    exit(0);
}

// Calcul des dates en UTC relatives à l'exécution (maintenant = expédié)
$now = time();
$date_order = gmdate('Y-m-d H:i:s', $now - 86400 * 2); // Il y a 2 jours
$date_pickup = gmdate('Y-m-d H:i:s', $now - 86400); // Hier
$date_shipped = gmdate('Y-m-d H:i:s', $now); // Maintenant
$date_estimate = gmdate('Y-m-d H:i:s', $now + 86400 * 2); // Dans 2 jours

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO shipments (
            tracking_number, carrier_id, recipient_name, recipient_phone, recipient_email,
            address, city, country, origin_address, origin_city, 
            shipped_at, estimated_delivery_at, status, description, is_demo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
        $tracking, $carrierId, 'Jean Dupont', '0600000000', 'jean.dupont@example.com',
        '10 Rue de Rivoli', 'Paris', 'France', 'Port de Rotterdam', 'Rotterdam',
        $date_shipped, $date_estimate, 'shipped', 'Colis de démonstration standard'
    ]);
    
    $shipmentId = $pdo->lastInsertId();
    
    $events = [
        ['Commande enregistrée', 'Rotterdam', 'pending', $date_order],
        ['Colis récupéré par le transporteur', 'Plateforme Logistique, Rotterdam', 'pending', $date_pickup],
        ['Expédié (En transit)', 'Centre de Tri International', 'shipped', $date_shipped]
    ];
    
    $stmtEvent = $pdo->prepare("INSERT INTO tracking_events (shipment_id, label, location, status, occurred_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($events as $event) {
        $stmtEvent->execute([$shipmentId, $event[0], $event[1], $event[2], $event[3]]);
    }
    
    $pdo->commit();
    echo "Colis de démonstration $tracking généré avec succès.\n";
    
} catch (\Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de la génération : " . $e->getMessage() . "\n");
}
