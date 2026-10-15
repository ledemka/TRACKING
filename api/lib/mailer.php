<?php
// api/lib/mailer.php
// Composant centralisé d'envoi d'e-mails (Resend ou transport simulé 'mock')
// Sécurité : ne jamais logger ou exposer RESEND_API_KEY.

/**
 * Résout la langue préférée du destinataire d'une expédition selon les règles métier strictes :
 * 1. Changement ponctuel spécifié pour l'envoi en cours ($overrideLang).
 * 2. Langue explicitement définie sur la fiche colis/client ($shipment['recipient_lang']).
 * 3. Langue du compte utilisateur associé par adresse e-mail (si un compte client existe en BDD).
 * 4. Dernière préférence linguistique connue pour cet e-mail dans une expédition précédente.
 * 5. Indice du pays de destination ($shipment['country']) pour les pays à langue unique/dominante.
 * 6. Règle de secours par défaut documentée : français (DEFAULT_LANGUAGE).
 *
 * Règles strictes :
 * - Ne déduit JAMAIS la langue à partir du nom de domaine de l'adresse e-mail.
 * - N'utilise JAMAIS la langue de l'administrateur connecté.
 *
 * @param array $shipment Données du colis (recipient_email, recipient_lang, country, id...)
 * @param string|null $overrideLang Langue ponctuelle d'envoi choisie par l'admin
 * @return array ['lang' => string, 'source' => string, 'source_label' => string]
 */
function resolve_recipient_language(array $shipment, ?string $overrideLang = null): array {
    global $pdo;

    $supported = defined('SUPPORTED_LANGUAGES') ? SUPPORTED_LANGUAGES : ['fr', 'en', 'de', 'nl'];
    $default = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'fr';

    // 1. Changement ponctuel pour cet envoi spécifique (choisi manuellement dans la modale)
    if (!empty($overrideLang)) {
        $cleanOverride = strtolower(trim($overrideLang));
        if (in_array($cleanOverride, $supported, true)) {
            return [
                'lang' => $cleanOverride,
                'source' => 'manual_override',
                'source_label' => 'Changement ponctuel d\'envoi'
            ];
        }
    }

    // 2. Langue explicitement définie sur la fiche colis / client (champ recipient_lang)
    if (!empty($shipment['recipient_lang'])) {
        $cleanShipmentLang = strtolower(trim($shipment['recipient_lang']));
        if (in_array($cleanShipmentLang, $supported, true)) {
            return [
                'lang' => $cleanShipmentLang,
                'source' => 'shipment_profile',
                'source_label' => 'Fiche colis / client'
            ];
        }
    }

    $recipientEmail = trim($shipment['recipient_email'] ?? '');

    // 3. Compte utilisateur associé par e-mail
    if ($recipientEmail && isset($pdo)) {
        try {
            $stmtUser = $pdo->prepare("SELECT lang FROM users WHERE email = ? AND lang IS NOT NULL LIMIT 1");
            $stmtUser->execute([$recipientEmail]);
            $userAccountLang = $stmtUser->fetchColumn();
            if ($userAccountLang && in_array(strtolower($userAccountLang), $supported, true)) {
                return [
                    'lang' => strtolower($userAccountLang),
                    'source' => 'user_account',
                    'source_label' => 'Compte client associé'
                ];
            }
        } catch (\Throwable $e) {
            // Ignorer silencieusement
        }
    }

    // 4. Dernière préférence connue pour cet e-mail dans une expédition précédente
    if ($recipientEmail && isset($pdo)) {
        try {
            $currentId = (int)($shipment['id'] ?? 0);
            $stmtPrev = $pdo->prepare("SELECT recipient_lang FROM shipments WHERE recipient_email = ? AND recipient_lang IS NOT NULL AND id != ? ORDER BY id DESC LIMIT 1");
            $stmtPrev->execute([$recipientEmail, $currentId]);
            $prevShipmentLang = $stmtPrev->fetchColumn();
            if ($prevShipmentLang && in_array(strtolower($prevShipmentLang), $supported, true)) {
                return [
                    'lang' => strtolower($prevShipmentLang),
                    'source' => 'previous_shipment',
                    'source_label' => 'Historique d\'expédition client'
                ];
            }
        } catch (\Throwable $e) {
            // Ignorer silencieusement
        }
    }

    // 5. Indice secondaire : Pays de destination
    if (!empty($shipment['country'])) {
        $country = strtolower(trim($shipment['country']));
        $countryMap = [
            'france' => 'fr', 'fr' => 'fr', 'monaco' => 'fr', 'mc' => 'fr',
            'allemagne' => 'de', 'germany' => 'de', 'deutschland' => 'de', 'de' => 'de',
            'autriche' => 'de', 'austria' => 'de', 'österreich' => 'de', 'at' => 'de',
            'pays-bas' => 'nl', 'pays bas' => 'nl', 'netherlands' => 'nl', 'nederland' => 'nl', 'nl' => 'nl',
            'royaume-uni' => 'en', 'united kingdom' => 'en', 'uk' => 'en', 'gb' => 'en',
            'états-unis' => 'en', 'etats-unis' => 'en', 'united states' => 'en', 'usa' => 'en', 'us' => 'en',
            'irlande' => 'en', 'ireland' => 'en', 'ie' => 'en',
            'canada' => 'en', 'ca' => 'en',
            'australie' => 'en', 'australia' => 'en', 'au' => 'en',
        ];

        if (isset($countryMap[$country]) && in_array($countryMap[$country], $supported, true)) {
            return [
                'lang' => $countryMap[$country],
                'source' => 'country',
                'source_label' => 'Pays de destination (' . htmlspecialchars($shipment['country'], ENT_QUOTES, 'UTF-8') . ')'
            ];
        }
    }

    // 6. Règle de secours finale documentée : français
    return [
        'lang' => $default,
        'source' => 'default',
        'source_label' => 'Défaut de l\'application (Français)'
    ];
}

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
 *                       - 'email_lang' (string|null) code langue utilisé pour l'envoi
 * @return array ['status' => 'accepted'|'failed'|'timeout', 'provider_message_id' => string|null, 'http_code' => int, 'error_message' => string|null]
 */
function send_email(string $to, string $subject, string $htmlContent, string $textContent, array $options = []): array {
    global $pdo;

    $driver = strtolower(getenv('MAIL_DRIVER') ?: 'resend');
    $isMock = !empty($options['mock']) || ($driver === 'mock');
    $emailType = $options['email_type'] ?? 'general';
    $shipmentId = !empty($options['shipment_id']) ? (int)$options['shipment_id'] : null;
    $emailLang = $options['email_lang'] ?? null;

    // 1. Validation de l'adresse de destination
    $to = trim($to);
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $res = [
            'status' => 'failed',
            'provider_message_id' => null,
            'http_code' => 400,
            'error_message' => "Adresse de destination invalide."
        ];
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null, $emailLang);
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

        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], $res['provider_message_id'], $emailLang);
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
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null, $emailLang);
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
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null, $emailLang);
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
        log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], null, $emailLang);
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

    log_email_attempt($pdo, $shipmentId, $to, $subject, $emailType, $res['status'], $res['error_message'], $res['provider_message_id'], $emailLang);
    return $res;
}

/**
 * Enregistre une tentative dans la table email_logs sans fuite de secrets.
 */
function log_email_attempt($pdo, ?int $shipmentId, string $to, string $subject, string $type, string $status, ?string $errorMessage, ?string $providerMessageId, ?string $emailLang = null): void {
    if (!$pdo) return;
    try {
        $subjectLog = substr($subject, 0, 255);
        $errorLog = $errorMessage !== null ? substr($errorMessage, 0, 1000) : null;
        $stmt = $pdo->prepare("INSERT INTO email_logs (shipment_id, recipient, subject, email_type, status, error_message, provider_message_id, email_lang, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $shipmentId,
            $to,
            $subjectLog,
            $type,
            $status,
            $errorLog,
            $providerMessageId,
            $emailLang,
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
