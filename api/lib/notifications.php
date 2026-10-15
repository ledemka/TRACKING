<?php
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/../../templates/emails/tracking_email.php';

/**
 * Déclencheur d'email automatique lors d'un changement de statut.
 * Envoie l'email de suivi dans la langue résolue du destinataire (fr, en, de, nl).
 */
function notify_status_change($shipmentId, $newStatus, $eventLocation) {
    global $pdo;
    
    // Actif uniquement si configuré (sécurité délivrabilité)
    $autoNotify = getenv('NOTIFY_EMAIL_ON_STATUS');
    if ($autoNotify !== 'true' && $autoNotify !== '1') {
        return true;
    }

    try {
        if (!isset($pdo)) {
            require_once __DIR__ . '/../db.php';
        }
        $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
        $stmt->execute([(int)$shipmentId]);
        $shipment = $stmt->fetch();
        if (!$shipment || empty($shipment['recipient_email'])) {
            return false;
        }

        $appUrl = getenv('APP_URL') ?: 'http://127.0.0.1:8000';
        $settings = function_exists('get_app_settings') ? get_app_settings() : [];

        // Rendu automatique dans la langue préférée du destinataire
        $emailData = render_tracking_email($shipment, $settings, $appUrl);

        $options = [
            'shipment_id' => $shipment['id'],
            'email_type' => 'status_update',
            'email_lang' => $emailData['lang']
        ];

        $res = send_email($shipment['recipient_email'], $emailData['subject'], $emailData['html'], $emailData['text'], $options);
        return ($res['status'] === 'accepted');
    } catch (\Throwable $e) {
        error_log("Error in notify_status_change: " . $e->getMessage());
        return false;
    }
}
