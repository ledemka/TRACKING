<?php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/api/lib/validation.php';
require_once __DIR__ . '/api/lib/admin_utils.php';
require_once __DIR__ . '/api/lib/notifications.php';
require_once __DIR__ . '/api/lib/shipments.php';
require_once __DIR__ . '/api/lib/utils.php';
require_once __DIR__ . '/api/lib/mailer.php';
require_once __DIR__ . '/templates/emails/tracking_email.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
global $pdo;

// Load shipment
$stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->execute([$id]);
$shipment = $stmt->fetch();
if (!$shipment) {
    header("HTTP/1.0 404 Not Found");
    die(escape_html(__('shipment_form.not_found')));
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die("Erreur CSRF");
    $action = $_POST['action'] ?? '';
    
    
    if ($action === 'add_event' || $action === 'edit_event') {
        $errors = [];
        $label = validate_required_string($_POST['label'] ?? '', 'label', 150, $errors);
        $location = validate_required_string($_POST['location'] ?? '', 'location', 150, $errors);
        
        $occurred_at_raw = $_POST['occurred_at'] ?? '';
        $occurred_at_utc = convert_admin_time_to_utc($occurred_at_raw);
        if (!$occurred_at_utc) {
            $errors['occurred_at'] = __('validation.shipped_at_invalid');
        } elseif (strtotime($occurred_at_utc) > time() + 86400) {
            $errors['occurred_at'] = __('events.future_date_error');
        }
        
        $newStatus = $_POST['status'] ?? '';
        if (!is_valid_status($newStatus)) {
            $newStatus = $shipment['status']; // Default to current if invalid
        }
        
        list($lat, $lng) = validate_coordinates($_POST['lat'] ?? '', $_POST['lng'] ?? '', $errors);
        
        if (empty($errors)) {
            $pdo->beginTransaction();
            $now_utc = gmdate('Y-m-d H:i:s');
            
            if ($action === 'add_event') {
                $stmt = $pdo->prepare("INSERT INTO tracking_events (shipment_id, label, location, status, occurred_at, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $label, $location, $newStatus, $occurred_at_utc, $lat, $lng]);
                
                $stmtUpdate = $pdo->prepare("UPDATE shipments SET status = ?, updated_at = ? WHERE id = ?");
                $stmtUpdate->execute([$newStatus, $now_utc, $id]);
                
                $_SESSION['flash_message'] = __('events.flash_added');
                if ($newStatus !== $shipment['status']) {
                    notify_status_change($id, $newStatus, '');
                }
            } else {
                $eventId = (int)$_POST['event_id'];
                $stmt = $pdo->prepare("UPDATE tracking_events SET label=?, location=?, occurred_at=?, latitude=?, longitude=? WHERE id=? AND shipment_id=?");
                $stmt->execute([$label, $location, $occurred_at_utc, $lat, $lng, $eventId, $id]);
                
                $stmtUpdate = $pdo->prepare("UPDATE shipments SET updated_at = ? WHERE id = ?");
                $stmtUpdate->execute([$now_utc, $id]);
                
                $_SESSION['flash_message'] = __('events.flash_updated');
            }
            $pdo->commit();
            header("Location: /shipments/$id");
            exit;
        } else {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header("Location: /shipments/$id" . ($action==='edit_event' ? '?edit_event='.(int)$_POST['event_id'] : ''));
            exit;
        }
    }
    
    if ($action === 'delete_event') {
        if (empty($_POST['confirm_delete'])) {
            $_SESSION['flash_error'] = __('events.flash_delete_confirm_required');
            header("Location: /shipments/$id");
            exit;
        }
        $eventId = (int)$_POST['event_id'];
        $stmt = $pdo->prepare("DELETE FROM tracking_events WHERE id = ? AND shipment_id = ?");
        $stmt->execute([$eventId, $id]);
        $_SESSION['flash_message'] = __('events.flash_deleted');
        header("Location: /shipments/$id");
        exit;
    }
    
    if ($action === 'edit_shipment') {
        $errors = [];
        $recipient_name = validate_required_string($_POST['recipient_name'] ?? '', __('common.name'), 255, $errors);
        $recipient_email = validate_required_string($_POST['recipient_email'] ?? '', __('common.email'), 255, $errors);
        if ($recipient_email && !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
            $errors['recipient_email'] = __('validation.email_invalid');
        }
        $recipient_phone = trim($_POST['recipient_phone'] ?? '');
        $raw_recipient_lang = trim($_POST['recipient_lang'] ?? '');
        $recipient_lang = in_array(strtolower($raw_recipient_lang), ['fr', 'en', 'de', 'nl'], true) ? strtolower($raw_recipient_lang) : null;
        $address = validate_required_string($_POST['address'] ?? '', __('common.address'), 255, $errors);
        $city = validate_required_string($_POST['city'] ?? '', __('common.city'), 255, $errors);
        $zip_code = trim($_POST['zip_code'] ?? '');
        $country = validate_required_string($_POST['country'] ?? '', __('common.country'), 255, $errors);
        $origin_address = validate_required_string($_POST['origin_address'] ?? '', __('shipment_form.origin_address'), 255, $errors);
        $origin_city = validate_required_string($_POST['origin_city'] ?? '', __('shipment_form.origin_city'), 255, $errors);
        
        $is_company = !empty($_POST['is_company']) ? 1 : 0;
        $company_name = $is_company ? trim($_POST['company_name'] ?? '') : null;
        $company_siret = $is_company ? trim($_POST['company_siret'] ?? '') : null;
        $company_department = $is_company ? trim($_POST['company_department'] ?? '') : null;

        $shipped_at = convert_admin_time_to_utc($_POST['shipped_at'] ?? '');
        if (!$shipped_at) $errors['shipped_at'] = __('validation.shipped_at_invalid');
        
        $estimated_delivery_at = null;
        if (!empty($_POST['estimated_delivery_at'])) {
            $estimated_delivery_at = convert_admin_time_to_utc($_POST['estimated_delivery_at']);
            if (!$estimated_delivery_at) {
                $errors['estimated_delivery_at'] = __('validation.estimated_invalid');
            } elseif ($shipped_at && $estimated_delivery_at < $shipped_at) {
                $errors['estimated_delivery_at'] = __('validation.estimated_after_shipped');
            }
        }
        
        $carrier_id = empty($_POST['carrier_id']) ? null : (int)$_POST['carrier_id'];
        $raw_mode = trim($_POST['transport_mode'] ?? '');
        $transport_mode = is_valid_transport_mode($raw_mode) ? $raw_mode : ($shipment['transport_mode'] ?? TRANSPORT_MODE_LAND);
        $description = substr(trim($_POST['description'] ?? ''), 0, 1000);
        $weight = validate_weight($_POST['weight'] ?? '', $errors);
        $packages_count = validate_packages_count($_POST['packages_count'] ?? '', $errors);

        list($dest_lat, $dest_lng) = validate_coordinates($_POST['destination_lat'] ?? '', $_POST['destination_lng'] ?? '', $errors, 'destination_coords');
        list($origin_lat, $origin_lng) = validate_coordinates($_POST['origin_lat'] ?? '', $_POST['origin_lng'] ?? '', $errors, 'origin_coords');
        
        if (empty($errors)) {
            $now_utc = gmdate('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE shipments SET 
                recipient_name=?, recipient_email=?, recipient_phone=?, recipient_lang=?,
                address=?, city=?, zip_code=?, country=?, 
                origin_address=?, origin_city=?, 
                is_company=?, company_name=?, company_siret=?, company_department=?,
                destination_lat=?, destination_lng=?,
                origin_lat=?, origin_lng=?,
                shipped_at=?, estimated_delivery_at=?,
                carrier_id=?, transport_mode=?, description=?, weight_kg=?, parcel_count=?,
                updated_at=?
                WHERE id=?");
            $stmt->execute([
                $recipient_name, $recipient_email, $recipient_phone, $recipient_lang,
                $address, $city, $zip_code, $country,
                $origin_address, $origin_city,
                $is_company, $company_name, $company_siret, $company_department,
                $dest_lat, $dest_lng,
                $origin_lat, $origin_lng,
                $shipped_at, $estimated_delivery_at,
                $carrier_id, $transport_mode, $description, $weight, $packages_count,
                $now_utc, $id
            ]);
            $_SESSION['flash_message'] = __('flash.shipment_updated');
            header("Location: /shipments/$id");
            exit;
        } else {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header("Location: /shipments/$id");
            exit;
        }
    }

    if ($action === 'send_tracking_email') {
        $recipientEmail = trim($shipment['recipient_email'] ?? '');
        if (!$recipientEmail || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = __('email_action.flash_invalid_recipient');
            header("Location: /shipments/$id");
            exit;
        }

        // Vérification de la configuration APP_URL
        $appUrl = getenv('APP_URL');
        $isProd = (getenv('APP_ENV') === 'production');
        if ($isProd && (!$appUrl || strpos($appUrl, 'localhost') !== false || !filter_var($appUrl, FILTER_VALIDATE_URL))) {
            $_SESSION['flash_error'] = __('email_action.flash_config_error');
            header("Location: /shipments/$id");
            exit;
        }
        if (!$appUrl) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
            $appUrl = "$scheme://$host";
        }

        // Protection serveur contre les soumissions répétées et concurrentes (verrou temporel 10 secondes)
        $stmtLock = $pdo->prepare("SELECT created_at FROM email_logs WHERE shipment_id = ? AND email_type = 'tracking_link' ORDER BY id DESC LIMIT 1");
        $stmtLock->execute([$id]);
        $lastLog = $stmtLock->fetch();
        if ($lastLog && (time() - strtotime($lastLog['created_at'])) < 10) {
            $_SESSION['flash_error'] = __('email_action.flash_rate_limit');
            header("Location: /shipments/$id");
            exit;
        }

        // Optionnel : Langue ponctuelle d'envoi demandée par l'administrateur
        $rawSendLang = trim($_POST['send_lang'] ?? '');
        $targetLang = in_array(strtolower($rawSendLang), ['fr', 'en', 'de', 'nl'], true) ? strtolower($rawSendLang) : null;

        // Rendu du modèle d'email dans la langue cible résolue ou surchargée
        $settings = get_app_settings();
        $emailData = render_tracking_email($shipment, $settings, $appUrl, $targetLang);

        // Envoi via le composant centralisé mailer
        $options = [
            'shipment_id' => $id,
            'email_type' => 'tracking_link',
            'email_lang' => $emailData['lang']
        ];
        $result = send_email($recipientEmail, $emailData['subject'], $emailData['html'], $emailData['text'], $options);

        if ($result['status'] === 'accepted') {
            $_SESSION['flash_message'] = __('email_action.flash_accepted', ['email' => $recipientEmail]) . ' (' . strtoupper($emailData['lang']) . ')';
        } elseif ($result['status'] === 'timeout') {
            $_SESSION['flash_error'] = __('email_action.flash_timeout');
        } else {
            $_SESSION['flash_error'] = __('email_action.flash_failed', ['error' => ($result['error_message'] ?? 'Erreur inconnue')]);
        }

        header("Location: /shipments/$id");
        exit;
    }
}

// GET events
$stmt = $pdo->prepare("SELECT * FROM tracking_events WHERE shipment_id = ? ORDER BY occurred_at DESC, id DESC");
$stmt->execute([$id]);
$events = $stmt->fetchAll();

// Check if edit mode
$editEvent = null;
if (isset($_GET['edit_event'])) {
    $eId = (int)$_GET['edit_event'];
    foreach ($events as $e) {
        if ($e['id'] === $eId) {
            $editEvent = $e;
            break;
        }
    }
}

$carriers = $pdo->query("SELECT id, name FROM carriers ORDER BY name")->fetchAll();

// Chargement du statut de l'e-mail de suivi
$stmtMailLog = $pdo->prepare("SELECT * FROM email_logs WHERE shipment_id = ? AND email_type = 'tracking_link' ORDER BY id DESC LIMIT 1");
$stmtMailLog->execute([$id]);
$lastEmailLog = $stmtMailLog->fetch();

// Détermination de l'URL publique pour le lien de suivi et la prévisualisation
$appUrlConfigured = getenv('APP_URL');
if (!$appUrlConfigured) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $effectiveAppUrl = "$scheme://$host";
} else {
    $effectiveAppUrl = rtrim($appUrlConfigured, '/');
}

$settings = get_app_settings();
$resolvedRecipientLang = resolve_recipient_language($shipment);

$localLogoPath = __DIR__ . '/assets/img/logo-mark-fond-clair.png';
$logoDataUri = file_exists($localLogoPath) ? ('data:image/png;base64,' . base64_encode(file_get_contents($localLogoPath))) : '';

// Préparation des prévisualisations pour toutes les 4 langues (FR, EN, DE, NL)
$allPreviews = [];
foreach (['fr', 'en', 'de', 'nl'] as $lCode) {
    $pData = render_tracking_email($shipment, $settings, $effectiveAppUrl, $lCode);
    if (!empty($logoDataUri)) {
        $pData['html'] = str_replace(
            $effectiveAppUrl . '/assets/img/logo-mark-fond-clair.png',
            $logoDataUri,
            $pData['html']
        );
    }
    $allPreviews[$lCode] = $pData;
}
$previewEmail = $allPreviews[$resolvedRecipientLang['lang']];

$hasRecipientEmail = !empty($shipment['recipient_email']) && filter_var($shipment['recipient_email'], FILTER_VALIDATE_EMAIL);

$page_title = __('shipment_form.title_edit', ['reference' => $shipment['tracking_number']]);
$use_map = true;
require __DIR__ . '/templates/admin_header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="/shipments" class="text-action hover:underline">&larr; <?= escape_html(__('common.back_to_list')) ?></a>
        <h1 class="text-3xl font-bold mt-2"><?= escape_html(__('shipment_form.title_edit', ['reference' => $shipment['tracking_number']])) ?></h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Colonne gauche : infos + e-mail de suivi -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4"><?= escape_html(__('common.information')) ?></h2>
            <div class="space-y-3 text-sm">
                <div><strong><?= escape_html(__('track.recipient_label')) ?> :</strong> <?= escape_html($shipment['recipient_name']) ?></div>
                <?php if ($shipment['is_company']): ?>
                <div class="bg-slate-50 p-2 border rounded">
                    <strong><?= escape_html(__('shipment_form.company_name')) ?> :</strong> <?= escape_html($shipment['company_name']) ?><br>
                    <?php if ($shipment['company_siret']) echo '<strong>' . escape_html(__('shipment_form.company_siret')) . ' :</strong> ' . escape_html($shipment['company_siret']) . '<br>'; ?>
                    <?php if ($shipment['company_department']) echo '<strong>' . escape_html(__('shipment_form.company_dept')) . ' :</strong> ' . escape_html($shipment['company_department']); ?>
                </div>
                <?php endif; ?>
                <div><strong><?= escape_html(__('common.email')) ?> :</strong> <?= escape_html($shipment['recipient_email']) ?></div>
                <div>
                    <strong><?= escape_html(__('email.recipient_lang')) ?> :</strong>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-800" title="<?= escape_html($resolvedRecipientLang['source_label']) ?>">
                        <?= render_flag_svg($resolvedRecipientLang['lang'], 'w-4 h-3') ?>
                        <span class="uppercase font-bold"><?= strtoupper($resolvedRecipientLang['lang']) ?></span>
                        <span class="text-slate-400">·</span>
                        <span class="text-slate-500 text-[11px]"><?= escape_html($resolvedRecipientLang['source_label']) ?></span>
                    </span>
                </div>
                <div><strong><?= escape_html(__('common.address')) ?> :</strong> <?= escape_html($shipment['address']) ?>, <?= escape_html($shipment['zip_code']) ?> <?= escape_html($shipment['city']) ?></div>
                <div><strong><?= escape_html(__('dashboard.th_route')) ?> :</strong> <?= escape_html($shipment['origin_city']) ?> &rarr; <?= escape_html($shipment['city']) ?></div>
                <div><strong><?= escape_html(__('transport.mode')) ?> :</strong> <?= escape_html(get_transport_mode_label($shipment['transport_mode'] ?? 'land')) ?></div>
                <div><strong><?= escape_html(__('common.created_at')) ?></strong> <?= format_date($shipment['created_at']) ?></div>
                <div class="pt-3 border-t">
                    <strong><?= escape_html(__('track.status_label')) ?> :</strong><br>
                    <div class="mt-1">
                        <?= render_status_badge($shipment['status']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloc E-mail de suivi de marque (Phase 4B) -->
        <div class="bg-white p-6 rounded shadow border-t-4 border-[#171717]" id="bloc-tracking-email">
            <div class="flex items-center justify-between gap-2 mb-4">
                <h2 class="text-xl font-bold text-[#171717]"><?= escape_html(__('email_action.box_title')) ?></h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-800">
                    <?= escape_html(__('email_action.badge_manual')) ?>
                </span>
            </div>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-slate-500 font-semibold block text-xs"><?= escape_html(__('email_action.recipient')) ?></span>
                    <?php if ($hasRecipientEmail): ?>
                        <span class="font-medium text-[#171717]"><?= escape_html($shipment['recipient_email']) ?></span>
                    <?php else: ?>
                        <span class="text-red-600 font-semibold text-xs"><?= escape_html(__('email_action.email_missing')) ?></span>
                    <?php endif; ?>
                </div>

                <div>
                    <span class="text-slate-500 font-semibold block text-xs mb-1"><?= escape_html(__('email.recipient_lang')) ?></span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded border bg-slate-50 text-xs font-semibold text-slate-800" title="<?= escape_html($resolvedRecipientLang['source_label']) ?>">
                        <?= render_flag_svg($resolvedRecipientLang['lang'], 'w-4 h-3') ?>
                        <span class="font-bold uppercase"><?= strtoupper($resolvedRecipientLang['lang']) ?></span>
                        <span class="text-slate-400">·</span>
                        <span class="text-slate-600 text-[11px]"><?= escape_html($resolvedRecipientLang['source_label']) ?></span>
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 font-semibold block text-xs"><?= escape_html(__('email_action.public_link')) ?></span>
                    <a href="<?= escape_html($previewEmail['tracking_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-action hover:underline break-all text-xs font-mono">
                        <?= escape_html($previewEmail['tracking_url']) ?>
                    </a>
                </div>

                <div class="pt-2 border-t">
                    <span class="text-slate-500 font-semibold block text-xs mb-1"><?= escape_html(__('email_action.send_status')) ?></span>
                    <?php if (!$lastEmailLog): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <?= escape_html(__('email_action.status_not_sent')) ?>
                        </span>
                    <?php elseif ($lastEmailLog['status'] === 'accepted' || $lastEmailLog['status'] === 'sent'): ?>
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                <span class="w-2 h-2 rounded-full bg-green-600"></span>
                                <?= escape_html(__('email_action.status_accepted')) ?>
                            </span>
                            <div class="text-[11px] text-slate-500">
                                <?= escape_html(__('email_action.date_prefix', ['date' => format_date($lastEmailLog['created_at'])])) ?>
                                <?php if (!empty($lastEmailLog['provider_message_id'])): ?>
                                    <br><span class="font-mono text-[10px] text-slate-400"><?= escape_html(__('email_action.id_prefix', ['id' => $lastEmailLog['provider_message_id']])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($lastEmailLog['status'] === 'timeout'): ?>
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <?= escape_html(__('email_action.status_timeout')) ?>
                            </span>
                            <div class="text-[11px] text-slate-500">
                                <?= escape_html(__('email_action.attempt_prefix', ['date' => format_date($lastEmailLog['created_at'])])) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                <?= escape_html(__('email_action.status_failed')) ?>
                            </span>
                            <div class="text-[11px] text-slate-500">
                                <?= escape_html(__('email_action.date_prefix', ['date' => format_date($lastEmailLog['created_at'])])) ?>
                            </div>
                            <?php if (!empty($lastEmailLog['error_message'])): ?>
                                <div class="text-xs text-red-600 bg-red-50 p-2 rounded border border-red-100 font-mono text-[11px] break-words">
                                    <?= escape_html($lastEmailLog['error_message']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Actions Prévisualiser & Envoyer -->
                <div class="pt-4 border-t space-y-2">
                    <button type="button" id="btn-open-preview" class="w-full px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded flex items-center justify-center gap-2 transition-colors">
                        <span class="material-symbols-outlined text-base">visibility</span>
                        <span><?= escape_html(__('email_action.btn_preview')) ?></span>
                    </button>

                    <form method="POST" action="/shipments/<?= $id ?>" id="form-send-tracking-email" class="m-0 space-y-2">
                        <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                        <input type="hidden" name="action" value="send_tracking_email">
                        
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1" for="select-send-lang"><?= escape_html(__('email.preview_lang_label')) ?></label>
                            <select name="send_lang" id="select-send-lang" class="w-full text-xs border rounded p-1.5 bg-white">
                                <option value="" selected><?= escape_html(__('email.lang_auto_rule')) ?> : <?= strtoupper($resolvedRecipientLang['lang']) ?></option>
                                <option value="fr">🇫🇷 Français (FR)</option>
                                <option value="en">🇬🇧 English (EN)</option>
                                <option value="de">🇩🇪 Deutsch (DE)</option>
                                <option value="nl">🇳🇱 Nederlands (NL)</option>
                            </select>
                        </div>

                        <?php 
                        $isAlreadyAccepted = ($lastEmailLog && in_array($lastEmailLog['status'], ['accepted', 'sent'], true));
                        ?>
                        <button type="submit" 
                                id="btn-submit-send-email" 
                                class="w-full px-4 py-2.5 bg-[#FFE500] hover:bg-[#E6CE00] text-[#171717] text-xs font-extrabold rounded flex items-center justify-center gap-2 shadow-sm transition-all <?= !$hasRecipientEmail ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                <?= !$hasRecipientEmail ? 'disabled title="' . __attr('email_action.email_missing') . '"' : '' ?>
                                data-confirm="<?= $isAlreadyAccepted ? '1' : '0' ?>">
                            <span class="material-symbols-outlined text-base">send</span>
                            <span id="btn-send-email-text"><?= $isAlreadyAccepted ? escape_html(__('email_action.btn_resend')) : escape_html(__('email_action.btn_send')) ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale native de Prévisualisation (<dialog>) -->
    <dialog id="dialog-preview-email" class="p-0 rounded-2xl shadow-2xl border-0 backdrop:bg-slate-900/60 max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <div class="bg-[#171717] text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#FFE500]">mail</span>
                <span class="font-bold text-sm"><?= escape_html(__('email_action.preview_title')) ?></span>
            </div>
            <div class="flex items-center gap-2">
                <!-- Sélecteur de langue de prévisualisation -->
                <div class="flex items-center bg-white/10 rounded-lg p-0.5 text-xs font-bold" id="preview-lang-switchers">
                    <?php foreach (['fr' => '🇫🇷 FR', 'en' => '🇬🇧 EN', 'de' => '🇩🇪 DE', 'nl' => '🇳🇱 NL'] as $pLang => $pLabel): 
                        $isDef = ($pLang === $resolvedRecipientLang['lang']);
                    ?>
                        <button type="button" 
                                data-preview-lang="<?= $pLang ?>" 
                                class="btn-preview-lang px-2 py-1 rounded transition-colors <?= $isDef ? 'bg-[#FFE500] text-[#171717]' : 'text-white/80 hover:text-white hover:bg-white/10' ?>">
                            <?= $pLabel ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="btn-close-preview" class="text-slate-400 hover:text-white p-1">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-b text-xs space-y-1">
            <div><strong><?= escape_html(__('email_action.preview_to')) ?></strong> <?= escape_html($shipment['recipient_email'] ?: __('email_action.preview_not_set')) ?></div>
            <div><strong><?= escape_html(__('email_action.preview_subject')) ?></strong> <span id="preview-subject"><?= escape_html($previewEmail['subject']) ?></span></div>
            <div><strong><?= escape_html(__('email_action.preview_generated_link')) ?></strong> <span class="font-mono" id="preview-url"><?= escape_html($previewEmail['tracking_url']) ?></span></div>
        </div>

        <!-- Onglets HTML / Texte brut -->
        <div class="border-b bg-white px-4 flex gap-4 text-xs font-bold">
            <button type="button" id="tab-btn-html" class="py-2.5 border-b-2 border-[#FFE500] text-[#171717]"><?= escape_html(__('email_action.preview_tab_html')) ?></button>
            <button type="button" id="tab-btn-text" class="py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800"><?= escape_html(__('email_action.preview_tab_text')) ?></button>
        </div>

        <div class="p-4 overflow-y-auto max-h-[60vh] bg-slate-100">
            <div id="preview-panel-html" class="bg-white rounded-lg shadow-sm overflow-hidden border">
                <iframe id="preview-iframe" srcdoc="<?= htmlspecialchars($previewEmail['html'], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-[380px] border-0" title="<?= __attr('email_action.preview_title') ?>"></iframe>
            </div>
            <div id="preview-panel-text" class="hidden">
                <pre id="preview-text-content" class="bg-white p-4 rounded border text-xs font-mono text-slate-800 whitespace-pre-wrap leading-relaxed"><?= escape_html($previewEmail['text']) ?></pre>
            </div>
        </div>

        <div class="p-3 bg-white border-t flex justify-end">
            <button type="button" id="btn-close-preview-footer" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded">
                <?= escape_html(__('email_action.preview_close')) ?>
            </button>
        </div>
    </dialog>
    
    <!-- Colonne droite : événements -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4"><?= $editEvent ? escape_html(__('events.edit_heading')) : escape_html(__('events.add_heading')) ?></h2>
            
            <form method="POST" action="/shipments/<?= $id ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                <input type="hidden" name="action" value="<?= $editEvent ? 'edit_event' : 'add_event' ?>">
                <?php if ($editEvent): ?>
                    <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('events.label')) ?></label>
                        <input type="text" name="label" required maxlength="150" value="<?= escape_html($editEvent['label'] ?? '') ?>" class="border p-2 rounded w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('events.location')) ?></label>
                        <input type="text" name="location" required maxlength="150" value="<?= escape_html($editEvent['location'] ?? '') ?>" class="border p-2 rounded w-full">
                    </div>
                    <?php if (!$editEvent): ?>
                    <div>
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('events.status')) ?></label>
                        <select name="status" class="border p-2 rounded w-full bg-white">
                            <?php 
                                $currentStatus = $shipment['status'];
                                foreach(get_status_labels() as $k => $v): 
                            ?>
                                <option value="<?= $k ?>" <?= $currentStatus === $k ? 'selected' : '' ?>><?= escape_html($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="<?= $editEvent ? 'md:col-span-2' : 'md:col-span-1' ?>">
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('events.datetime')) ?></label>
                        <input type="datetime-local" name="occurred_at" required 
                               value="<?= escape_html(convert_utc_to_admin_time($editEvent['occurred_at'] ?? gmdate('Y-m-d H:i:s'))) ?>" 
                               class="border p-2 rounded w-full">
                    </div>
                </div>
                
                <div class="admin-map-picker border-t pt-4 mt-4" data-target-location="[name='location']">
                    <h3 class="text-lg font-bold mb-2"><?= escape_html(__('events.map_title')) ?></h3>
                    <p class="text-sm text-slate-600 mb-4 bg-yellow-50 p-3 rounded border border-yellow-200">
                        <?= escape_html(__('events.map_warning')) ?>
                    </p>
                    <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.latitude')) ?></label>
                            <input type="text" name="lat" value="<?= escape_html($editEvent['latitude'] ?? '') ?>" class="input-lat border p-2 rounded w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.longitude')) ?></label>
                            <input type="text" name="lng" value="<?= escape_html($editEvent['longitude'] ?? '') ?>" class="input-lng border p-2 rounded w-full">
                        </div>
                    </div>
                    <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline"><?= escape_html(__('shipment_form.clear_map')) ?></button>
                </div>
                
                <div class="flex justify-end gap-2 border-t pt-4">
                    <?php if ($editEvent): ?>
                        <a href="/shipments/<?= $id ?>" class="px-4 py-2 bg-slate-200 rounded font-semibold hover:bg-slate-300"><?= escape_html(__('common.cancel')) ?></a>
                        <button type="submit" class="px-4 py-2 bg-action text-white rounded font-bold hover:bg-blue-700"><?= escape_html(__('events.submit_edit')) ?></button>
                    <?php else: ?>
                        <button type="submit" class="px-4 py-2 bg-action text-white rounded font-bold hover:bg-blue-700"><?= escape_html(__('events.submit_add')) ?></button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4"><?= escape_html(__('events.section_title')) ?></h2>
            <?php if (empty($events)): ?>
                <p class="text-slate-500"><?= escape_html(__('events.no_events')) ?></p>
            <?php else: ?>
                <div class="space-y-4 relative before:absolute before:inset-y-2 before:left-[11px] before:-z-10 before:w-0.5 before:bg-slate-200">
                    <?php foreach ($events as $idx => $e): ?>
                        <div class="flex gap-4 group">
                            <div class="mt-1 w-6 h-6 flex-shrink-0 rounded-full bg-white border-4 border-slate-200 z-0"></div>
                            <div class="flex-grow bg-slate-50 p-3 rounded border">
                                <div class="flex justify-between items-start mb-1">
                                    <strong class="text-sm"><?= escape_html($e['label']) ?></strong>
                                    <span class="text-xs text-slate-500"><?= format_date($e['occurred_at']) ?></span>
                                </div>
                                <div class="text-sm text-slate-600 mb-2"><?= escape_html($e['location']) ?></div>
                                <div class="flex gap-3 text-xs items-center">
                                    <a href="/shipments/<?= $id ?>?edit_event=<?= $e['id'] ?>" class="text-action hover:underline"><?= escape_html(__('common.edit')) ?></a>
                                    
                                    <form method="POST" action="/shipments/<?= $id ?>" class="delete-form-js-modal inline-flex items-center gap-2 m-0">
                                        <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                        <label class="no-js-confirm flex items-center gap-1 text-red-600">
                                            <input type="checkbox" name="confirm_delete" value="1" class="confirm-checkbox"> 
                                            <span><?= escape_html(__('common.confirm')) ?></span>
                                        </label>
                                        <button type="submit" class="text-red-600 hover:underline btn-delete-submit"><?= escape_html(__('common.delete')) ?></button>
                                    </form>
                                </div>
                                
                                <dialog id="del-evt-<?= $e['id'] ?>" class="p-6 rounded shadow-lg border-0 backdrop:bg-slate-800/50">
                                    <h3 class="text-lg font-bold mb-4"><?= escape_html(__('events.confirm_delete_title')) ?></h3>
                                    <p class="mb-6"><?= escape_html(__('events.confirm_delete_msg')) ?></p>
                                    <div class="flex gap-4">
                                        <button type="button" class="btn-close-dialog bg-slate-200 px-4 py-2 rounded"><?= escape_html(__('common.cancel')) ?></button>
                                        <button type="button" class="btn-confirm-dialog bg-red-600 text-white px-4 py-2 rounded"><?= escape_html(__('common.delete_permanently')) ?></button>
                                    </div>
                                </dialog>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="mt-8 bg-white p-6 rounded shadow w-full">
    <h2 class="text-2xl font-bold mb-6 border-b pb-2"><?= escape_html(__('shipment_form.title_edit_heading')) ?></h2>
    <form method="POST" action="/shipments/<?= $id ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
        <input type="hidden" name="action" value="edit_shipment">
        
        <!-- Tracking Number (Readonly) -->
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.tracking_number')) ?></label>
            <input type="text" value="<?= escape_html($shipment['tracking_number']) ?>" class="border p-2 rounded w-full bg-slate-100" readonly>
            <p class="text-xs text-slate-500 mt-1"><?= escape_html(__('shipment_form.tracking_readonly_info')) ?></p>
        </div>
        
        <!-- Destinataire & Adresse -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <h3 class="text-lg font-bold md:col-span-2 mt-4"><?= escape_html(__('shipment_form.sec_recipient')) ?></h3>
            
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer w-max">
                    <input type="checkbox" name="is_company" id="is_company_cb" value="1" <?= $shipment['is_company'] ? 'checked' : '' ?>>
                    <span class="font-semibold text-sm"><?= escape_html(__('shipment_form.is_company')) ?></span>
                </label>
            </div>
            
            <div id="company_fields_container" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded border <?= $shipment['is_company'] ? '' : 'hidden' ?>">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.company_name_opt')) ?></label>
                    <input type="text" name="company_name" id="company_name_input" value="<?= escape_html($shipment['company_name']) ?>" class="border p-2 rounded w-full">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.company_siret_opt')) ?></label>
                    <input type="text" name="company_siret" id="company_siret_input" value="<?= escape_html($shipment['company_siret']) ?>" class="border p-2 rounded w-full">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.company_dept_opt')) ?></label>
                    <input type="text" name="company_department" id="company_department_input" value="<?= escape_html($shipment['company_department']) ?>" class="border p-2 rounded w-full">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.recipient_name')) ?></label>
                <input type="text" name="recipient_name" required value="<?= escape_html($shipment['recipient_name']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.recipient_email')) ?></label>
                <input type="email" name="recipient_email" required value="<?= escape_html($shipment['recipient_email']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.recipient_phone')) ?></label>
                <?= render_phone_input_field('recipient_phone', $shipment['recipient_phone'] ?? '') ?>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1" for="edit_recipient_lang"><?= escape_html(__('email.recipient_lang')) ?></label>
                <select name="recipient_lang" id="edit_recipient_lang" class="border p-2 rounded w-full bg-white h-[42px]">
                    <option value="" <?= empty($shipment['recipient_lang']) ? 'selected' : '' ?>><?= escape_html(__('email.lang_auto_rule')) ?></option>
                    <option value="fr" <?= ($shipment['recipient_lang'] ?? '') === 'fr' ? 'selected' : '' ?>>🇫🇷 Français (fr)</option>
                    <option value="en" <?= ($shipment['recipient_lang'] ?? '') === 'en' ? 'selected' : '' ?>>🇬🇧 English (en)</option>
                    <option value="de" <?= ($shipment['recipient_lang'] ?? '') === 'de' ? 'selected' : '' ?>>🇩🇪 Deutsch (de)</option>
                    <option value="nl" <?= ($shipment['recipient_lang'] ?? '') === 'nl' ? 'selected' : '' ?>>🇳🇱 Nederlands (nl)</option>
                </select>
                <p class="text-xs text-slate-500 mt-1"><?= escape_html(__('email.lang_help')) ?></p>
            </div>

            <div class="md:col-span-2 admin-map-picker p-4 bg-slate-50 border rounded-lg" data-target-address="[name='address']" data-target-zip="[name='zip_code']" data-target-city="[name='city']" data-target-country="[name='country']">
                <h4 class="text-md font-bold mb-4"><?= escape_html(__('shipment_form.map_dest')) ?></h4>
                <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.latitude')) ?></label>
                        <input type="text" name="destination_lat" value="<?= escape_html($shipment['destination_lat']) ?>" class="input-lat border p-2 rounded w-full bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.longitude')) ?></label>
                        <input type="text" name="destination_lng" value="<?= escape_html($shipment['destination_lng']) ?>" class="input-lng border p-2 rounded w-full bg-white">
                    </div>
                </div>
                <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline"><?= escape_html(__('shipment_form.clear_map')) ?></button>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.address')) ?></label>
                <textarea name="address" rows="2" required class="border p-2 rounded w-full"><?= escape_html($shipment['address']) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.zip')) ?></label>
                <input type="text" name="zip_code" required value="<?= escape_html($shipment['zip_code']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.city')) ?></label>
                <input type="text" name="city" required value="<?= escape_html($shipment['city']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.country')) ?></label>
                <input type="text" name="country" required value="<?= escape_html($shipment['country']) ?>" class="border p-2 rounded w-full">
            </div>
        </div>
        
        <!-- Origine & Adresse -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <h3 class="text-lg font-bold md:col-span-2 mt-4"><?= escape_html(__('shipment_form.sec_origin')) ?></h3>
            
            <div class="md:col-span-2 admin-map-picker p-4 bg-slate-50 border rounded-lg" data-target-address="[name='origin_address']" data-target-city="[name='origin_city']">
                <h4 class="text-md font-bold mb-4"><?= escape_html(__('shipment_form.map_origin')) ?></h4>
                <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.latitude')) ?></label>
                        <input type="text" name="origin_lat" value="<?= escape_html($shipment['origin_lat'] ?? '') ?>" class="input-lat border p-2 rounded w-full bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.longitude')) ?></label>
                        <input type="text" name="origin_lng" value="<?= escape_html($shipment['origin_lng'] ?? '') ?>" class="input-lng border p-2 rounded w-full bg-white">
                    </div>
                </div>
                <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline"><?= escape_html(__('shipment_form.clear_map')) ?></button>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.origin_address')) ?></label>
                <input type="text" name="origin_address" required value="<?= escape_html($shipment['origin_address']) ?>" class="border p-2 rounded w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.origin_city')) ?></label>
                <input type="text" name="origin_city" required value="<?= escape_html($shipment['origin_city']) ?>" class="border p-2 rounded w-full md:w-1/2">
            </div>
        </div>
        
        <!-- Expédition -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <h3 class="text-lg font-bold md:col-span-2 mt-4"><?= escape_html(__('shipment_form.sec_details')) ?></h3>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('carrier.label')) ?></label>
                <select name="carrier_id" class="border p-2 rounded w-full">
                    <option value=""><?= escape_html(__('carrier.none')) ?></option>
                    <?php foreach ($carriers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $shipment['carrier_id'] == $c['id'] ? 'selected' : '' ?>><?= escape_html($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1" for="edit_transport_mode"><?= escape_html(__('transport.mode')) ?> *</label>
                <select name="transport_mode" id="edit_transport_mode" class="border p-2 rounded w-full">
                    <?php foreach(get_transport_modes() as $modeKey => $modeInfo): ?>
                        <option value="<?= escape_html($modeKey) ?>" <?= ($shipment['transport_mode'] ?? 'land') === $modeKey ? 'selected' : '' ?>>
                            <?= escape_html($modeInfo['label']) ?> (<?= escape_html($modeInfo['description']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.shipped_at')) ?></label>
                <input type="datetime-local" name="shipped_at" required value="<?= escape_html(convert_utc_to_admin_time($shipment['shipped_at'])) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.estimated_delivery_opt')) ?></label>
                <input type="datetime-local" name="estimated_delivery_at" value="<?= escape_html(convert_utc_to_admin_time($shipment['estimated_delivery_at'])) ?>" class="border p-2 rounded w-full">
            </div>
        </div>
        
        <!-- Poids & Description -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.weight_opt')) ?></label>
                <input type="text" name="weight" value="<?= escape_html($shipment['weight_kg']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.packages_count')) ?> *</label>
                <input type="number" name="packages_count" min="1" max="1000" required value="<?= escape_html($shipment['parcel_count']) ?>" class="border p-2 rounded w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.desc_opt')) ?></label>
                <textarea name="description" rows="2" class="border p-2 rounded w-full"><?= escape_html($shipment['description']) ?></textarea>
            </div>
        </div>
        
        <div class="flex justify-end gap-4 border-t pt-6 mt-6">
            <button type="submit" class="px-6 py-2 bg-action text-white rounded font-bold hover:bg-blue-700"><?= escape_html(__('common.save_changes')) ?></button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Hide the No-JS confirm checkbox since we have JS
    document.querySelectorAll('.no-js-confirm').forEach(el => el.style.display = 'none');
    
    document.querySelectorAll('.delete-form-js-modal').forEach(form => {
        form.addEventListener('submit', (e) => {
            // Si la case est déjà cochée, on laisse passer (soumission depuis la popup)
            const cb = form.querySelector('.confirm-checkbox');
            if (cb && cb.checked) return;
            
            e.preventDefault();
            const eventId = form.querySelector('input[name="event_id"]').value;
            const dialog = document.getElementById('del-evt-' + eventId);
            if (dialog) {
                dialog.showModal();
                
                const btnConfirm = dialog.querySelector('.btn-confirm-dialog');
                btnConfirm.onclick = () => {
                    if (cb) cb.checked = true;
                    form.submit();
                };
            }
        });
    });

    // Gestion de la modale de prévisualisation d'e-mail
    const previewDialog = document.getElementById('dialog-preview-email');
    const btnOpenPreview = document.getElementById('btn-open-preview');
    const btnClosePreview = document.getElementById('btn-close-preview');
    const btnClosePreviewFooter = document.getElementById('btn-close-preview-footer');
    const tabBtnHtml = document.getElementById('tab-btn-html');
    const tabBtnText = document.getElementById('tab-btn-text');
    const panelHtml = document.getElementById('preview-panel-html');
    const panelText = document.getElementById('preview-panel-text');

    if (previewDialog && btnOpenPreview) {
        btnOpenPreview.addEventListener('click', () => {
            previewDialog.showModal();
        });
        const closePreview = () => previewDialog.close();
        if (btnClosePreview) btnClosePreview.addEventListener('click', closePreview);
        if (btnClosePreviewFooter) btnClosePreviewFooter.addEventListener('click', closePreview);

        if (tabBtnHtml && tabBtnText && panelHtml && panelText) {
            tabBtnHtml.addEventListener('click', () => {
                tabBtnHtml.className = "py-2.5 border-b-2 border-[#FFE500] text-[#171717]";
                tabBtnText.className = "py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800";
                panelHtml.classList.remove('hidden');
                panelText.classList.add('hidden');
            });
            tabBtnText.addEventListener('click', () => {
                tabBtnText.className = "py-2.5 border-b-2 border-[#FFE500] text-[#171717]";
                tabBtnHtml.className = "py-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-800";
                panelText.classList.remove('hidden');
                panelHtml.classList.add('hidden');
            });
        }

        // Commutateur interactif de langue dans la modale de prévisualisation
        const emailPreviewsData = <?= json_encode($allPreviews, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const previewSubject = document.getElementById('preview-subject');
        const previewUrl = document.getElementById('preview-url');
        const previewIframe = document.getElementById('preview-iframe');
        const previewTextContent = document.getElementById('preview-text-content');
        const langSwitchBtns = document.querySelectorAll('.btn-preview-lang');
        const selectSendLang = document.getElementById('select-send-lang');

        langSwitchBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-preview-lang');
                if (!emailPreviewsData[target]) return;

                langSwitchBtns.forEach(b => {
                    b.className = 'btn-preview-lang px-2 py-1 rounded text-white/80 hover:text-white hover:bg-white/10';
                });
                btn.className = 'btn-preview-lang px-2 py-1 rounded bg-[#FFE500] text-[#171717]';

                const d = emailPreviewsData[target];
                if (previewSubject) previewSubject.textContent = d.subject;
                if (previewUrl) {
                    previewUrl.textContent = d.tracking_url;
                    previewUrl.href = d.tracking_url;
                }
                if (previewIframe) previewIframe.srcdoc = d.html;
                if (previewTextContent) previewTextContent.textContent = d.text;
            });
        });

        if (selectSendLang) {
            selectSendLang.addEventListener('change', () => {
                const chosen = selectSendLang.value;
                if (chosen && emailPreviewsData[chosen]) {
                    const btn = document.querySelector(`.btn-preview-lang[data-preview-lang="${chosen}"]`);
                    if (btn) btn.click();
                }
            });
        }
    }

    // Protection anti-double-clic et confirmation de renvoi
    const formSendMail = document.getElementById('form-send-tracking-email');
    const btnSendMail = document.getElementById('btn-submit-send-email');
    const txtSendMail = document.getElementById('btn-send-email-text');

    if (formSendMail && btnSendMail) {
        formSendMail.addEventListener('submit', (e) => {
            const isAlreadyAccepted = (btnSendMail.getAttribute('data-confirm') === '1');
            if (isAlreadyAccepted) {
                const confirmed = confirm(<?= json_encode(__('email_action.confirm_resend')) ?>);
                if (!confirmed) {
                    e.preventDefault();
                    return;
                }
            }

            // Désactivation immédiate pour éviter le double clic
            btnSendMail.disabled = true;
            btnSendMail.classList.add('opacity-50', 'cursor-not-allowed');
            if (txtSendMail) {
                txtSendMail.textContent = <?= json_encode(__('email_action.btn_sending')) ?>;
            }
        });
    }
});
</script>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
