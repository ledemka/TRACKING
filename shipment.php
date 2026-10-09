<?php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/api/lib/validation.php';
require_once __DIR__ . '/api/lib/admin_utils.php';
require_once __DIR__ . '/api/lib/notifications.php';
require_once __DIR__ . '/api/lib/shipments.php';
require_once __DIR__ . '/api/lib/utils.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
global $pdo;

// Load shipment
$stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->execute([$id]);
$shipment = $stmt->fetch();
if (!$shipment) {
    header("HTTP/1.0 404 Not Found");
    die("Colis introuvable.");
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
            $errors['occurred_at'] = 'Date invalide.';
        } elseif (strtotime($occurred_at_utc) > time() + 86400) {
            $errors['occurred_at'] = 'La date ne peut pas être plus d\'un jour dans le futur.';
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
                
                $_SESSION['flash_message'] = "Événement ajouté et statut mis à jour.";
                if ($newStatus !== $shipment['status']) {
                    notify_status_change($id, $newStatus, '');
                }
            } else {
                $eventId = (int)$_POST['event_id'];
                $stmt = $pdo->prepare("UPDATE tracking_events SET label=?, location=?, occurred_at=?, latitude=?, longitude=? WHERE id=? AND shipment_id=?");
                $stmt->execute([$label, $location, $occurred_at_utc, $lat, $lng, $eventId, $id]);
                
                $stmtUpdate = $pdo->prepare("UPDATE shipments SET updated_at = ? WHERE id = ?");
                $stmtUpdate->execute([$now_utc, $id]);
                
                $_SESSION['flash_message'] = "Événement modifié.";
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
            $_SESSION['flash_error'] = "Veuillez confirmer la suppression.";
            header("Location: /shipments/$id");
            exit;
        }
        $eventId = (int)$_POST['event_id'];
        $stmt = $pdo->prepare("DELETE FROM tracking_events WHERE id = ? AND shipment_id = ?");
        $stmt->execute([$eventId, $id]);
        $_SESSION['flash_message'] = "Événement supprimé.";
        header("Location: /shipments/$id");
        exit;
    }
    
    if ($action === 'edit_shipment') {
        $errors = [];
        $recipient_name = validate_required_string($_POST['recipient_name'] ?? '', 'Nom du destinataire', 255, $errors);
        $recipient_email = validate_required_string($_POST['recipient_email'] ?? '', 'Email du destinataire', 255, $errors);
        if ($recipient_email && !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
            $errors['recipient_email'] = "L'adresse email du destinataire n'est pas valide.";
        }
        $recipient_phone = trim($_POST['recipient_phone'] ?? '');
        $address = validate_required_string($_POST['address'] ?? '', 'Adresse', 255, $errors);
        $city = validate_required_string($_POST['city'] ?? '', 'Ville de destination', 255, $errors);
        $zip_code = trim($_POST['zip_code'] ?? '');
        $country = validate_required_string($_POST['country'] ?? '', 'Pays', 255, $errors);
        $origin_address = validate_required_string($_POST['origin_address'] ?? '', 'Adresse d\'origine', 255, $errors);
        $origin_city = validate_required_string($_POST['origin_city'] ?? '', 'Ville d\'origine', 255, $errors);
        
        $is_company = !empty($_POST['is_company']) ? 1 : 0;
        $company_name = $is_company ? trim($_POST['company_name'] ?? '') : null;
        $company_siret = $is_company ? trim($_POST['company_siret'] ?? '') : null;
        $company_department = $is_company ? trim($_POST['company_department'] ?? '') : null;

        $shipped_at = convert_admin_time_to_utc($_POST['shipped_at'] ?? '');
        if (!$shipped_at) $errors['shipped_at'] = 'Date d\'expédition invalide.';
        
        $estimated_delivery_at = null;
        if (!empty($_POST['estimated_delivery_at'])) {
            $estimated_delivery_at = convert_admin_time_to_utc($_POST['estimated_delivery_at']);
            if (!$estimated_delivery_at) {
                $errors['estimated_delivery_at'] = 'Date estimée invalide.';
            } elseif ($shipped_at && $estimated_delivery_at < $shipped_at) {
                $errors['estimated_delivery_at'] = 'La date estimée doit être >= à l\'expédition.';
            }
        }
        
        $carrier_id = empty($_POST['carrier_id']) ? null : (int)$_POST['carrier_id'];
        $description = substr(trim($_POST['description'] ?? ''), 0, 1000);
        $weight = validate_weight($_POST['weight'] ?? '', $errors);
        $packages_count = validate_packages_count($_POST['packages_count'] ?? '', $errors);

        list($dest_lat, $dest_lng) = validate_coordinates($_POST['destination_lat'] ?? '', $_POST['destination_lng'] ?? '', $errors, 'destination_coords');
        list($origin_lat, $origin_lng) = validate_coordinates($_POST['origin_lat'] ?? '', $_POST['origin_lng'] ?? '', $errors, 'origin_coords');
        
        if (empty($errors)) {
            $now_utc = gmdate('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE shipments SET 
                recipient_name=?, recipient_email=?, recipient_phone=?, 
                address=?, city=?, zip_code=?, country=?, 
                origin_address=?, origin_city=?, 
                is_company=?, company_name=?, company_siret=?, company_department=?,
                destination_lat=?, destination_lng=?,
                origin_lat=?, origin_lng=?,
                shipped_at=?, estimated_delivery_at=?,
                carrier_id=?, description=?, weight_kg=?, parcel_count=?,
                updated_at=?
                WHERE id=?");
            $stmt->execute([
                $recipient_name, $recipient_email, $recipient_phone,
                $address, $city, $zip_code, $country,
                $origin_address, $origin_city,
                $is_company, $company_name, $company_siret, $company_department,
                $dest_lat, $dest_lng,
                $origin_lat, $origin_lng,
                $shipped_at, $estimated_delivery_at,
                $carrier_id, $description, $weight, $packages_count,
                $now_utc, $id
            ]);
            $_SESSION['flash_message'] = "Le colis a été modifié avec succès.";
            header("Location: /shipments/$id");
            exit;
        } else {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header("Location: /shipments/$id");
            exit;
        }
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

$page_title = "Colis " . $shipment['tracking_number'];
$use_map = true;
require __DIR__ . '/templates/admin_header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="/shipments" class="text-action hover:underline">&larr; Retour à la liste</a>
        <h1 class="text-3xl font-bold mt-2">Colis <?= escape_html($shipment['tracking_number']) ?></h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Colonne gauche : infos + changement rapide -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Informations</h2>
            <div class="space-y-3 text-sm">
                <div><strong>Destinataire :</strong> <?= escape_html($shipment['recipient_name']) ?></div>
                <?php if ($shipment['is_company']): ?>
                <div class="bg-slate-50 p-2 border rounded">
                    <strong>Entreprise :</strong> <?= escape_html($shipment['company_name']) ?><br>
                    <?php if ($shipment['company_siret']) echo '<strong>SIRET/TVA :</strong> ' . escape_html($shipment['company_siret']) . '<br>'; ?>
                    <?php if ($shipment['company_department']) echo '<strong>Service :</strong> ' . escape_html($shipment['company_department']); ?>
                </div>
                <?php endif; ?>
                <div><strong>Adresse :</strong> <?= escape_html($shipment['address']) ?>, <?= escape_html($shipment['zip_code']) ?> <?= escape_html($shipment['city']) ?></div>
                <div><strong>Trajet :</strong> <?= escape_html($shipment['origin_city']) ?> &rarr; <?= escape_html($shipment['city']) ?></div>
                <div><strong>Créé le :</strong> <?= format_date($shipment['created_at']) ?></div>
                <div class="pt-3 border-t">
                    <strong>Statut actuel :</strong><br>
                    <div class="mt-1">
                        <?= render_status_badge($shipment['status']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Colonne droite : événements -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4"><?= $editEvent ? 'Modifier un événement' : 'Ajouter un événement' ?></h2>
            
            <form method="POST" action="/shipments/<?= $id ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                <input type="hidden" name="action" value="<?= $editEvent ? 'edit_event' : 'add_event' ?>">
                <?php if ($editEvent): ?>
                    <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Libellé *</label>
                        <input type="text" name="label" required maxlength="150" value="<?= escape_html($editEvent['label'] ?? '') ?>" class="border p-2 rounded w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Lieu *</label>
                        <input type="text" name="location" required maxlength="150" value="<?= escape_html($editEvent['location'] ?? '') ?>" class="border p-2 rounded w-full">
                    </div>
                    <?php if (!$editEvent): ?>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nouveau Statut *</label>
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
                        <label class="block text-sm font-semibold mb-1">Date et heure *</label>
                        <input type="datetime-local" name="occurred_at" required 
                               value="<?= escape_html(convert_utc_to_admin_time($editEvent['occurred_at'] ?? gmdate('Y-m-d H:i:s'))) ?>" 
                               class="border p-2 rounded w-full">
                    </div>
                </div>
                
                <div class="admin-map-picker border-t pt-4 mt-4">
                    <h3 class="text-lg font-bold mb-2">Position de l'événement (Carte)</h3>
                    <p class="text-sm text-slate-600 mb-4 bg-yellow-50 p-3 rounded border border-yellow-200">
                        <strong>Avertissement :</strong> Indiquez la position du colis (centre de tri, véhicule...), jamais l'adresse du destinataire.
                    </p>
                    <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Latitude</label>
                            <input type="text" name="lat" value="<?= escape_html($editEvent['latitude'] ?? '') ?>" class="input-lat border p-2 rounded w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Longitude</label>
                            <input type="text" name="lng" value="<?= escape_html($editEvent['longitude'] ?? '') ?>" class="input-lng border p-2 rounded w-full">
                        </div>
                    </div>
                    <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline">Effacer la position</button>
                </div>
                
                <div class="flex justify-end gap-2 border-t pt-4">
                    <?php if ($editEvent): ?>
                        <a href="/shipments/<?= $id ?>" class="px-4 py-2 bg-slate-200 rounded font-semibold hover:bg-slate-300">Annuler</a>
                        <button type="submit" class="px-4 py-2 bg-action text-white rounded font-bold hover:bg-blue-700">Mettre à jour</button>
                    <?php else: ?>
                        <button type="submit" class="px-4 py-2 bg-action text-white rounded font-bold hover:bg-blue-700">Ajouter</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Historique des événements</h2>
            <?php if (empty($events)): ?>
                <p class="text-slate-500">Aucun événement enregistré.</p>
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
                                    <a href="/shipments/<?= $id ?>?edit_event=<?= $e['id'] ?>" class="text-action hover:underline">Modifier</a>
                                    
                                    <form method="POST" action="/shipments/<?= $id ?>" class="delete-form-js-modal inline-flex items-center gap-2 m-0">
                                        <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                        <label class="no-js-confirm flex items-center gap-1 text-red-600">
                                            <input type="checkbox" name="confirm_delete" value="1" class="confirm-checkbox"> 
                                            <span>Confirmer</span>
                                        </label>
                                        <button type="submit" class="text-red-600 hover:underline btn-delete-submit">Supprimer</button>
                                    </form>
                                </div>
                                
                                <dialog id="del-evt-<?= $e['id'] ?>" class="p-6 rounded shadow-lg border-0 backdrop:bg-slate-800/50">
                                    <h3 class="text-lg font-bold mb-4">Supprimer l'événement ?</h3>
                                    <p class="mb-6">Êtes-vous sûr de vouloir supprimer cet événement ?</p>
                                    <div class="flex gap-4">
                                        <button type="button" class="btn-close-dialog bg-slate-200 px-4 py-2 rounded">Annuler</button>
                                        <button type="button" class="btn-confirm-dialog bg-red-600 text-white px-4 py-2 rounded">Supprimer définitivement</button>
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
    <h2 class="text-2xl font-bold mb-6 border-b pb-2">Modifier le colis</h2>
    <form method="POST" action="/shipments/<?= $id ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
        <input type="hidden" name="action" value="edit_shipment">
        
        <!-- Tracking Number (Readonly) -->
        <div>
            <label class="block text-sm font-semibold mb-1">Numéro de suivi</label>
            <input type="text" value="<?= escape_html($shipment['tracking_number']) ?>" class="border p-2 rounded w-full bg-slate-100" readonly>
            <p class="text-xs text-slate-500 mt-1">Le numéro de suivi ne peut pas être modifié.</p>
        </div>
        
        <!-- Destinataire & Adresse -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <h3 class="text-lg font-bold md:col-span-2 mt-4">Destinataire & Adresse</h3>
            
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer w-max">
                    <input type="checkbox" name="is_company" id="is_company_cb" value="1" <?= $shipment['is_company'] ? 'checked' : '' ?>>
                    <span class="font-semibold text-sm">Ce destinataire est une entreprise</span>
                </label>
            </div>
            
            <div id="company_fields_container" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded border <?= $shipment['is_company'] ? '' : 'hidden' ?>">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Nom de la société / Raison sociale (optionnel)</label>
                    <input type="text" name="company_name" id="company_name_input" value="<?= escape_html($shipment['company_name']) ?>" class="border p-2 rounded w-full">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Numéro SIRET / Numéro de TVA (optionnel)</label>
                    <input type="text" name="company_siret" id="company_siret_input" value="<?= escape_html($shipment['company_siret']) ?>" class="border p-2 rounded w-full">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Complément / Service interne (optionnel)</label>
                    <input type="text" name="company_department" id="company_department_input" value="<?= escape_html($shipment['company_department']) ?>" class="border p-2 rounded w-full">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Nom / Prénom *</label>
                <input type="text" name="recipient_name" required value="<?= escape_html($shipment['recipient_name']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">E-mail *</label>
                <input type="email" name="recipient_email" required value="<?= escape_html($shipment['recipient_email']) ?>" class="border p-2 rounded w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Téléphone *</label>
                <input type="text" name="recipient_phone" required value="<?= escape_html($shipment['recipient_phone']) ?>" class="border p-2 rounded w-full md:w-1/2">
            </div>

            <div class="md:col-span-2 admin-map-picker p-4 bg-slate-50 border rounded-lg" data-target-address="[name='address']" data-target-zip="[name='zip_code']" data-target-city="[name='city']" data-target-country="[name='country']">
                <h4 class="text-md font-bold mb-4">Carte & Autocomplétion (Destination)</h4>
                <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Latitude</label>
                        <input type="text" name="dest_lat" value="<?= escape_html($shipment['destination_lat']) ?>" class="input-lat border p-2 rounded w-full bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Longitude</label>
                        <input type="text" name="dest_lng" value="<?= escape_html($shipment['destination_lng']) ?>" class="input-lng border p-2 rounded w-full bg-white">
                    </div>
                </div>
                <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline">Effacer la position</button>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Adresse complète *</label>
                <textarea name="address" rows="2" required class="border p-2 rounded w-full"><?= escape_html($shipment['address']) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Code Postal *</label>
                <input type="text" name="zip_code" required value="<?= escape_html($shipment['zip_code']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Ville *</label>
                <input type="text" name="city" required value="<?= escape_html($shipment['city']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Pays *</label>
                <input type="text" name="country" required value="<?= escape_html($shipment['country']) ?>" class="border p-2 rounded w-full">
            </div>
        </div>
        
        <!-- Origine & Adresse -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <h3 class="text-lg font-bold md:col-span-2 mt-4">Origine & Adresse</h3>
            
            <div class="md:col-span-2 admin-map-picker p-4 bg-slate-50 border rounded-lg" data-target-address="[name='origin_address']" data-target-city="[name='origin_city']">
                <h4 class="text-md font-bold mb-4">Carte & Autocomplétion (Origine)</h4>
                <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Latitude</label>
                        <input type="text" name="origin_lat" value="<?= escape_html($shipment['origin_lat'] ?? '') ?>" class="input-lat border p-2 rounded w-full bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Longitude</label>
                        <input type="text" name="origin_lng" value="<?= escape_html($shipment['origin_lng'] ?? '') ?>" class="input-lng border p-2 rounded w-full bg-white">
                    </div>
                </div>
                <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline">Effacer la position</button>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Adresse d'origine *</label>
                <input type="text" name="origin_address" required value="<?= escape_html($shipment['origin_address']) ?>" class="border p-2 rounded w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Ville d'origine *</label>
                <input type="text" name="origin_city" required value="<?= escape_html($shipment['origin_city']) ?>" class="border p-2 rounded w-full md:w-1/2">
            </div>
        </div>
        
        <!-- Expédition -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <h3 class="text-lg font-bold md:col-span-2 mt-4">Expédition</h3>
            <div>
                <label class="block text-sm font-semibold mb-1">Transporteur</label>
                <select name="carrier_id" class="border p-2 rounded w-full">
                    <option value="">-- Aucun --</option>
                    <?php foreach ($carriers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $shipment['carrier_id'] == $c['id'] ? 'selected' : '' ?>><?= escape_html($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Date d'expédition *</label>
                <input type="datetime-local" name="shipped_at" required value="<?= escape_html(convert_utc_to_admin_time($shipment['shipped_at'])) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Livraison estimée (optionnel)</label>
                <input type="datetime-local" name="estimated_delivery_at" value="<?= escape_html(convert_utc_to_admin_time($shipment['estimated_delivery_at'])) ?>" class="border p-2 rounded w-full">
            </div>
        </div>
        
        <!-- Poids & Description -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Poids (kg, optionnel)</label>
                <input type="text" name="weight" value="<?= escape_html($shipment['weight_kg']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Nombre de colis *</label>
                <input type="number" name="packages_count" min="1" max="1000" required value="<?= escape_html($shipment['parcel_count']) ?>" class="border p-2 rounded w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Description (optionnel)</label>
                <textarea name="description" rows="2" class="border p-2 rounded w-full"><?= escape_html($shipment['description']) ?></textarea>
            </div>
        </div>
        
        <div class="flex justify-end gap-4 border-t pt-6 mt-6">
            <button type="submit" class="px-6 py-2 bg-action text-white rounded font-bold hover:bg-blue-700">Enregistrer les modifications</button>
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
});
</script>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
