<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../lib/status.php';
require_once __DIR__ . '/../lib/utils.php';

function get_dashboard_stats() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as `total`,
            SUM(CASE WHEN status = '" . STATUS_SHIPPED . "' THEN 1 ELSE 0 END) as `shipped`,
            SUM(CASE WHEN status = '" . STATUS_OUT_FOR_DELIVERY . "' THEN 1 ELSE 0 END) as `out_for_delivery`,
            SUM(CASE WHEN status = '" . STATUS_DELIVERED . "' THEN 1 ELSE 0 END) as `delivered`,
            SUM(CASE WHEN status = '" . STATUS_DELAYED . "' THEN 1 ELSE 0 END) as `delayed`
        FROM shipments
    ");
    $stmt->execute();
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
        if ($e->getCode() === '23000' || ($e->errorInfo[1] ?? 0) == 1062 || strpos($e->getMessage(), 'UNIQUE') !== false) {
            throw new Exception("Ce numéro de suivi existe déjà");
        }
        throw $e;
    }
}

/**
 * Génère un numéro de suivi unique au format PS-AAMM-XXXXXXX
 * - AAMM : Année (2 chiffres) et Mois (2 chiffres) au fuseau Europe/Paris
 * - XXXXXXX : 7 caractères aléatoires cryptographiquement sûrs (random_int)
 * - Alphabet : ABCDEFGHJKMNPQRSTUVWXYZ23456789 (31 caractères, sans I, L, O, 0, 1)
 * - Jusqu'à 5 tentatives en cas de collision, puis exception propre
 */
function generate_unique_tracking_number(PDO $pdo): string {
    $tz = new DateTimeZone('Europe/Paris');
    $now = new DateTime('now', $tz);
    $yymm = $now->format('ym');
    
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $alphaLen = strlen($alphabet); // 31
    
    $stmtCheck = $pdo->prepare("SELECT 1 FROM shipments WHERE tracking_number = ? LIMIT 1");
    
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $suffix = '';
        for ($i = 0; $i < 7; $i++) {
            $suffix .= $alphabet[random_int(0, $alphaLen - 1)];
        }
        $candidate = "PS-{$yymm}-{$suffix}";
        
        $stmtCheck->execute([$candidate]);
        if (!$stmtCheck->fetch()) {
            return $candidate;
        }
    }
    
    throw new Exception("Impossible de générer un numéro de suivi unique après 5 tentatives.");
}
