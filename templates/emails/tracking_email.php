<?php
// templates/emails/tracking_email.php
// Modèle d'e-mail de marque pour le suivi de colis (HTML et texte brut), 100% multilingue (fr, en, de, nl)
// Charte : En-tête noir #171717, bouton jaune #FFE500, texte noir #171717, logo clair local

require_once __DIR__ . '/../../api/lib/mailer.php';

/**
 * Génère le sujet, la version HTML et la version texte brut de l'e-mail de suivi
 * dans la langue préférée du destinataire (indépendamment de la langue de l'administrateur).
 *
 * @param array $shipment Données du colis (tracking_number, recipient_name, city, origin_city, country, recipient_lang, etc.)
 * @param array $settings Paramètres généraux (nom, telephone, email, adresse)
 * @param string $appUrl URL publique absolue (ex: https://pava-shipping.fr)
 * @param string|null $targetLang Langue cible forcée (si null, résolue selon les préférences du destinataire)
 * @return array ['subject' => string, 'html' => string, 'text' => string, 'tracking_url' => string, 'lang' => string, 'lang_source' => string]
 */
function render_tracking_email(array $shipment, array $settings, string $appUrl, ?string $targetLang = null): array {
    $nomEntreprise = !empty($settings['nom']) ? $settings['nom'] : 'Pava Shipping';
    $trackingNumber = $shipment['tracking_number'] ?? '';
    $recipientName = !empty($shipment['recipient_name']) ? $shipment['recipient_name'] : 'Client';
    
    // Résolution de la langue du destinataire (indépendante de la session admin)
    $resolved = resolve_recipient_language($shipment, $targetLang);
    $lang = $resolved['lang'];
    $langSource = $resolved['source_label'];

    // Normalisation de l'URL de base et génération du lien de suivi localisé
    $cleanAppUrl = rtrim($appUrl, '/');
    $trackingUrl = $cleanAppUrl . '/' . $lang . '/track?id=' . rawurlencode($trackingNumber);
    $logoUrl = $cleanAppUrl . '/assets/img/logo-mark-fond-clair.png';

    // Sujet traduit dans la langue du destinataire
    $subject = __('email.subject', ['number' => $trackingNumber], $lang);

    // Données logistiques
    $originCity = !empty($shipment['origin_city']) ? $shipment['origin_city'] : null;
    $destinationCity = !empty($shipment['city']) ? $shipment['city'] : null;
    $carrierName = !empty($shipment['carrier_name']) ? $shipment['carrier_name'] : null;
    $transportMode = !empty($shipment['transport_mode']) ? $shipment['transport_mode'] : 'land';
    $transportModeLabel = get_transport_mode_label($transportMode, $lang);

    // Coordonnées de contact
    $contactEmail = !empty($settings['email']) ? $settings['email'] : 'contact@pava-shipping.fr';
    $contactPhone = !empty($settings['telephone']) ? $settings['telephone'] : null;

    // --- 1. Version HTML ---
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="<?= escape_html($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { margin: 0; padding: 0; background-color: #F4F3ED; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #171717; line-height: 1.6; }
        table { border-collapse: collapse; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; border: 1px solid #E5E5E5; }
        .header { background-color: #171717; padding: 28px 32px; text-align: center; }
        .content { padding: 36px 32px; }
        .h1 { font-size: 22px; font-weight: 800; color: #171717; margin: 0 0 16px 0; }
        .badge { display: inline-block; background-color: #F4F3ED; border: 1px solid #E5E5E0; color: #171717; font-weight: 800; font-family: monospace; font-size: 16px; padding: 8px 16px; border-radius: 8px; margin: 12px 0 24px 0; letter-spacing: 0.05em; }
        .info-box { background-color: #F4F3ED; border-radius: 10px; padding: 20px 24px; margin: 24px 0; border-left: 4px solid #FFE500; }
        .info-row { margin: 6px 0; font-size: 14px; color: #333333; }
        .info-label { font-weight: 700; color: #171717; }
        .btn-container { text-align: center; margin: 36px 0 28px 0; }
        .btn { display: inline-block; background-color: #FFE500; color: #171717; font-weight: 800; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; border: 1px solid #E6CE00; }
        .fallback { font-size: 12px; color: #6B6B6B; word-break: break-all; margin-top: 24px; padding-top: 20px; border-top: 1px solid #EEEEEE; }
        .footer { background-color: #FAFAFA; padding: 24px 32px; text-align: center; font-size: 12px; color: #6B6B6B; border-top: 1px solid #EEEEEE; }
    </style>
</head>
<body>
    <div style="background-color: #F4F3ED; padding: 32px 16px;">
        <table role="presentation" class="container" align="center" width="600">
            <!-- En-tête noir #171717 -->
            <tr>
                <td class="header">
                    <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($nomEntreprise, ENT_QUOTES, 'UTF-8') ?>" width="44" height="44" style="display: block; margin: 0 auto; border: 0;" />
                    <div style="color: #FFFFFF; font-weight: 800; font-size: 18px; margin-top: 10px; letter-spacing: -0.02em;">
                        <?= htmlspecialchars($nomEntreprise, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </td>
            </tr>

            <!-- Corps du message -->
            <tr>
                <td class="content">
                    <p style="margin: 0 0 16px 0; font-size: 16px; color: #171717;">
                        <?= escape_html(__('email.greeting', ['name' => $recipientName], $lang)) ?>
                    </p>
                    <p style="margin: 0 0 16px 0; font-size: 15px; color: #333333;">
                        <?= escape_html(__('email.intro', [], $lang)) ?>
                    </p>

                    <div style="text-align: center;">
                        <span class="badge"><?= htmlspecialchars($trackingNumber, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <!-- Bloc récapitulatif -->
                    <div class="info-box">
                        <?php if ($originCity && $destinationCity): ?>
                        <div class="info-row">
                            <span class="info-label"><?= escape_html(__('email.route', [], $lang)) ?></span>
                            <span><?= htmlspecialchars($originCity, ENT_QUOTES, 'UTF-8') ?> &rarr; <?= htmlspecialchars($destinationCity, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php elseif ($destinationCity): ?>
                        <div class="info-row">
                            <span class="info-label"><?= escape_html(__('email.destination', [], $lang)) ?></span>
                            <span><?= htmlspecialchars($destinationCity, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($transportModeLabel): ?>
                        <div class="info-row">
                            <span class="info-label"><?= escape_html(__('email.transport_mode', [], $lang)) ?></span>
                            <span><?= htmlspecialchars($transportModeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($carrierName): ?>
                        <div class="info-row">
                            <span class="info-label"><?= escape_html(__('email.carrier', [], $lang)) ?></span>
                            <span><?= htmlspecialchars($carrierName, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bouton jaune #FFE500 texte noir -->
                    <div class="btn-container">
                        <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= htmlspecialchars($trackingUrl, ENT_QUOTES, 'UTF-8') ?>" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="16%" stroke="f" fillcolor="#FFE500">
                        <w:anchorlock/>
                        <center style="color:#171717;font-family:sans-serif;font-size:16px;font-weight:bold;"><?= escape_html(__('email.track_btn', [], $lang)) ?></center>
                        </v:roundrect>
                        <![endif]-->
                        <a href="<?= htmlspecialchars($trackingUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn" target="_blank" rel="noopener noreferrer"><?= escape_html(__('email.track_btn', [], $lang)) ?></a>
                    </div>

                    <!-- Lien brut de secours -->
                    <div class="fallback">
                        <?= escape_html(__('email.fallback_text', [], $lang)) ?><br>
                        <a href="<?= htmlspecialchars($trackingUrl, ENT_QUOTES, 'UTF-8') ?>" style="color: #171717; font-weight: 600;"><?= htmlspecialchars($trackingUrl, ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </td>
            </tr>

            <!-- Pied de page -->
            <tr>
                <td class="footer">
                    <div><strong><?= htmlspecialchars($nomEntreprise, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div style="margin-top: 6px;">
                        <?= escape_html(__('email.footer_help', ['email' => $contactEmail], $lang)) ?>
                        <?php if ($contactPhone): ?>
                        <?= escape_html(__('email.footer_phone', ['phone' => $contactPhone], $lang)) ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
    <?php
    $html = ob_get_clean();

    // --- 2. Version Texte Brut ---
    $textLines = [];
    $textLines[] = __('email.text_header', ['company' => strtoupper($nomEntreprise)], $lang);
    $textLines[] = str_repeat("=", 45);
    $textLines[] = "";
    $textLines[] = __('email.greeting', ['name' => $recipientName], $lang);
    $textLines[] = "";
    $textLines[] = __('email.intro', [], $lang);
    $textLines[] = __('email.text_tracking_number', ['number' => $trackingNumber], $lang);
    if ($originCity && $destinationCity) {
        $textLines[] = __('email.route', [], $lang) . " " . $originCity . " -> " . $destinationCity;
    } elseif ($destinationCity) {
        $textLines[] = __('email.destination', [], $lang) . " " . $destinationCity;
    }
    if ($transportModeLabel) {
        $textLines[] = __('email.transport_mode', [], $lang) . " " . $transportModeLabel;
    }
    if ($carrierName) {
        $textLines[] = __('email.carrier', [], $lang) . " " . $carrierName;
    }
    $textLines[] = "";
    $textLines[] = __('email.text_view_link', [], $lang);
    $textLines[] = $trackingUrl;
    $textLines[] = "";
    $textLines[] = str_repeat("-", 45);
    $textLines[] = __('email.text_support', [
        'email' => $contactEmail, 
        'phone' => ($contactPhone ? ' / ' . $contactPhone : '')
    ], $lang);
    $textLines[] = $nomEntreprise;

    $text = implode("\r\n", $textLines);

    return [
        'subject' => $subject,
        'html' => $html,
        'text' => $text,
        'tracking_url' => $trackingUrl,
        'lang' => $lang,
        'lang_source' => $langSource
    ];
}
