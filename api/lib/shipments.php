<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../lib/status.php';
require_once __DIR__ . '/../lib/utils.php';

function get_dashboard_stats() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as `total`,
            SUM(CASE WHEN status = '" . STATUS_SHIPPED . "' THEN 1 ELSE 0 END) as `shipped`,
            SUM(CASE WHEN status = '" . STATUS_OUT_FOR_DELIVERY . "' THEN 1 ELSE 0 END) as `out_for_delivery`,
            SUM(CASE WHEN status = '" . STATUS_DELIVERED . "' THEN 1 ELSE 0 END) as `delivered`,
            SUM(CASE WHEN status != '" . STATUS_DELIVERED . "' AND estimated_delivery_at IS NOT NULL AND estimated_delivery_at < UTC_TIMESTAMP() THEN 1 ELSE 0 END) as `delayed`
        FROM shipments
    ");
    return $stmt->fetch();
}

function get_recent_shipments($limit = 8) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT *
        FROM shipments 
        ORDER BY updated_at DESC 
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    return $stmt->fetchAll();
}

function format_anonymous_name($fullName) {
    $parts = explode(' ', trim($fullName));
    if (count($parts) > 1) {
        $lastName = array_pop($parts);
        return implode(' ', $parts) . ' ' . mb_substr($lastName, 0, 1) . '.';
    }
    return mb_substr($fullName, 0, 1) . '.';
}

function insert_shipment_and_event($data, $eventData) {
    global $pdo;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO shipments (
                tracking_number, carrier_id, recipient_name, recipient_phone, recipient_email,
                address, zip_code, city, country, origin_address, origin_city, 
                shipped_at, estimated_delivery_at, status, description, is_demo,
                destination_lat, destination_lng, is_company, company_name, company_siret, company_department
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tracking_number'], $data['carrier_id'] ?: null, $data['recipient_name'], 
            $data['recipient_phone'], $data['recipient_email'], $data['address'], $data['zip_code'], 
            $data['city'], $data['country'], $data['origin_address'], $data['origin_city'], 
            $data['shipped_at'], $data['estimated_delivery_at'] ?: null, $data['status'], 
            $data['description'], 0, $data['destination_lat'], $data['destination_lng'],
            $data['is_company'] ?? 0, $data['company_name'] ?? null, $data['company_siret'] ?? null, $data['company_department'] ?? null
        ]);
        
        $shipmentId = $pdo->lastInsertId();
        
        $stmtEvent = $pdo->prepare("INSERT INTO tracking_events (shipment_id, label, location, status, occurred_at, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtEvent->execute([
            $shipmentId,
            $eventData['label'],
            $eventData['location'],
            $data['status'],
            $data['shipped_at'],
            $eventData['latitude'] ?? null,
            $eventData['longitude'] ?? null
        ]);
        
        $pdo->commit();
        return $shipmentId;
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062 || strpos($e->getMessage(), 'UNIQUE') !== false) {
            throw new Exception("Ce numéro de suivi existe déjà");
        }
        throw $e;
    }
}
