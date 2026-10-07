<?php
// scripts/seed-demo.php
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.\n");
}

require_once __DIR__ . '/../api/db.php';

// Création du transporteur si inexistant
$stmt = $pdo->prepare("SELECT id FROM carriers WHERE name = 'Transporteur de démonstration'");
$stmt->execute();
$carrier = $stmt->fetch();
if (!$carrier) {
    $pdo->exec("INSERT INTO carriers (name) VALUES ('Transporteur de démonstration')");
    $carrierId = $pdo->lastInsertId();
} else {
    $carrierId = $carrier['id'];
}

$tracking = 'COLIS-2026-00125';

// Vérification du flag reset
$isReset = in_array('--reset', $argv);
if ($isReset) {
    $stmt = $pdo->prepare("SELECT id FROM shipments WHERE tracking_number = ? AND is_demo = 1");
    $stmt->execute([$tracking]);
    $demoShipment = $stmt->fetch();
    if ($demoShipment) {
        $pdo->exec("DELETE FROM tracking_events WHERE shipment_id = " . (int)$demoShipment['id']);
        $pdo->exec("DELETE FROM shipments WHERE id = " . (int)$demoShipment['id']);
        echo "Colis de démonstration $tracking supprimé (--reset).\n";
    }
}

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
        $tracking, $carrierId, 'Client Fictif', '0600000000', 'client.fictif@example.com',
        '10 Rue de Rivoli', 'Paris', 'France', 'Port de Rotterdam', 'Rotterdam',
        $date_shipped, $date_estimate, 'shipped', 'Colis de démonstration standard'
    ]);
    
    $shipmentId = $pdo->lastInsertId();
    
    // [label, location, status, date, latitude, longitude]
    $events = [
        ['Commande enregistrée', 'Rotterdam', NULL, $date_order, 51.9225, 4.47917],
        ['Colis récupéré par le transporteur', 'Plateforme Logistique, Rotterdam', NULL, $date_pickup, 51.890, 4.490],
        ['Expédié', 'Centre de Tri International, Anvers', 'shipped', $date_shipped, 51.2194, 4.4025]
    ];
    
    $stmtEvent = $pdo->prepare("INSERT INTO tracking_events (shipment_id, label, location, status, occurred_at, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($events as $event) {
        $stmtEvent->execute([$shipmentId, $event[0], $event[1], $event[2], $event[3], $event[4], $event[5]]);
    }
    
    $pdo->commit();
    echo "Colis de démonstration $tracking généré avec succès.\n";
    
} catch (\Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de la génération : " . $e->getMessage() . "\n");
}
