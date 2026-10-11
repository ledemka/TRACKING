<?php
// api/lib/mailer.php
// Composant centralisé d'envoi d'e-mails (Resend ou transport simulé 'mock')
// Sécurité : ne jamais logger ou exposer RESEND_API_KEY.

/**
 * Envoie un e-mail via Resend ou un transport simulé selon MAIL_DRIVER / options.
 *
 * @param string $to Adresse du destinataire
 * @param string $subject Sujet du message
 * @param string $htmlContent Corps HTML
 * @param string $textContent Corps texte brut
 * @param array $options Options additionnelles :
 *                       - 'reply_to' (string)
 *                       - 'mock' (bool) forcer le transport simulé
 *                       - 'mock_fail' (bool) forcer un échec en mode simulation
 *                       - 'mock_timeout' (bool) forcer un timeout en mode simulation
 *                       - 'shipment_id' (int|null)
 *                       - 'email_type' (string) défaut: 'general'
 * @return array ['status' => 'accepted'|'failed'|'timeout', 'provider_message_id' => string|null, 'http_code' => int, 'error_message' => string|null]
 */
function send_email(string $to, string $subject, string $htmlContent, string $textContent, array $options = []): array {
    global $pdo;

    $driver = strtolower(getenv('MAIL_DRIVER') ?: 'resend');
    $isMock = !empty($options['mock']) || ($driver === 'mock');
    $emailType = $options['email_type'] ?? 'general';
    $shipmentId = !empty($options['shipment_id']) ? (int)$options['shipment_id'] : null;

    // 1. Validation de l'adresse de destination
    $to = trim($to);
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $res = [
            'status' => 'failed',
            'provider_message_id' => null,
            'http_code' => 400,
            'error_message' => "Adresse de destination invalide."
        ];
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null);
        return $res;
    }

    // 2. Mode Simulation (Mock) - Zéro appel réseau
    if ($isMock) {
        if (!empty($options['mock_timeout'])) {
            $res = [
                'status' => 'timeout',
                'provider_message_id' => null,
                'http_code' => 0,
                'error_message' => "Délai d'attente dépassé (résultat incertain)"
            ];
        } elseif (!empty($options['mock_fail'])) {
            $res = [
                'status' => 'failed',
                'provider_message_id' => null,
                'http_code' => 422,
                'error_message' => "Simulation d'erreur fournisseur (domaine non vérifié ou rejet)"
            ];
        } else {
            $mockId = 'mock_msg_' . bin2hex(random_bytes(10));
            $res = [
                'status' => 'accepted',
                'provider_message_id' => $mockId,
                'http_code' => 200,
                'error_message' => null
            ];
        }

        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], $res['provider_message_id']);
        return $res;
    }

    // 3. Mode Réel Resend
    $apiKey = getenv('RESEND_API_KEY');
    $fromEmail = getenv('RESEND_FROM_EMAIL') ?: 'contact@pava-shipping.fr';
    $fromName = getenv('RESEND_FROM_NAME') ?: 'Pava Shipping';

    if (!$apiKey) {
        $res = [
            'status' => 'failed',
            'provider_message_id' => null,
            'http_code' => 500,
            'error_message' => "Configuration du service e-mail manquante (clé API)."
        ];
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null);
        return $res;
    }

    $payloadData = [
        'from' => "$fromName <$fromEmail>",
        'to' => [$to],
        'subject' => $subject,
        'html' => $htmlContent,
        'text' => $textContent
    ];

    if (!empty($options['reply_to'])) {
        $payloadData['reply_to'] = $options['reply_to'];
    }

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadData));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $curlErrno = curl_errno($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Gestion du timeout (résultat incertain)
    if ($curlErrno === CURLE_OPERATION_TIMEDOUT) {
        $res = [
            'status' => 'timeout',
            'provider_message_id' => null,
            'http_code' => 0,
            'error_message' => "Délai d'attente dépassé (résultat incertain)"
        ];
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null);
        return $res;
    }

    // Erreur réseau cURL autre que timeout
    if ($curlErrno !== 0) {
        $cleanErr = expunge_secrets($curlError, $apiKey);
        $res = [
            'status' => 'failed',
            'provider_message_id' => null,
            'http_code' => 0,
            'error_message' => "Erreur de connexion : " . $cleanErr
        ];
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null);
        return $res;
    }

    // Traitement de la réponse JSON Resend
    $responseData = json_decode($response, true);
    $providerMsgId = is_array($responseData) && !empty($responseData['id']) ? $responseData['id'] : null;

    if ($httpCode >= 200 && $httpCode < 300) {
        $res = [
            'status' => 'accepted',
            'provider_message_id' => $providerMsgId,
            'http_code' => $httpCode,
            'error_message' => null
        ];
    } else {
        $apiErrorMsg = "Erreur HTTP " . $httpCode;
        if (is_array($responseData) && !empty($responseData['message'])) {
            $apiErrorMsg .= " : " . $responseData['message'];
        } elseif (is_string($response) && $response !== '') {
            $apiErrorMsg .= " : " . substr($response, 0, 300);
        }
        $cleanErrMsg = expunge_secrets($apiErrorMsg, $apiKey);
        $res = [
            'status' => 'failed',
            'provider_message_id' => null,
            'http_code' => $httpCode,
            'error_message' => $cleanErrMsg
        ];
    }

    log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], $res['provider_message_id']);
    return $res;
}

/**
 * Enregistre une tentative dans la table email_logs sans fuite de secrets.
 */
function log_email_attempt($pdo, ?int $shipmentId, string $to, string $subject, string $type, string $status, ?string $errorMessage, ?string $providerMessageId): void {
    if (!$pdo) return;
    try {
        $subjectLog = substr($subject, 0, 255);
        $errorLog = $errorMessage !== null ? substr($errorMessage, 0, 1000) : null;
        $stmt = $pdo->prepare("INSERT INTO email_logs (shipment_id, recipient, subject, email_type, status, error_message, provider_message_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $shipmentId,
            $to,
            $subjectLog,
            $type,
            $status,
            $errorLog,
            $providerMessageId,
            gmdate('Y-m-d H:i:s')
        ]);
    } catch (\Exception $e) {
        error_log("Impossible de journaliser l'email: " . $e->getMessage());
    }
}

/**
 * Expurgation stricte des secrets (clés d'API, tokens) des messages d'erreur.
 */
function expunge_secrets(string $str, ?string $secretKey): string {
    if ($secretKey && strlen($secretKey) > 4) {
        $str = str_replace($secretKey, '[EXPURGÉ]', $str);
    }
    // Nettoyer également tout pattern ressemblant à une clé d'API (ex: re_xxxx ou bearer xxxx)
    $str = preg_replace('/(re_[a-zA-Z0-9_-]{10,})/i', '[EXPURGÉ]', $str);
    $str = preg_replace('/(Bearer\s+)[a-zA-Z0-9_\-\.]{10,}/i', '$1[EXPURGÉ]', $str);
    return $str;
}
