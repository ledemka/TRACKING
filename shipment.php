<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/lib/auth.php';
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
            if ($action === 'add_event') {
                $stmt = $pdo->prepare("INSERT INTO tracking_events (shipment_id, label, location, status, occurred_at, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $label, $location, $newStatus, $occurred_at_utc, $lat, $lng]);
                $_SESSION['flash_message'] = "Événement ajouté et statut mis à jour.";
            } else {
                $eventId = (int)$_POST['event_id'];
                $stmt = $pdo->prepare("UPDATE tracking_events SET label=?, location=?, status=?, occurred_at=?, latitude=?, longitude=? WHERE id=? AND shipment_id=?");
                $stmt->execute([$label, $location, $newStatus, $occurred_at_utc, $lat, $lng, $eventId, $id]);
                $_SESSION['flash_message'] = "Événement et statut modifiés.";
            }
            $pdo->exec("UPDATE shipments SET status = " . $pdo->quote($newStatus) . ", updated_at = UTC_TIMESTAMP() WHERE id = $id");
            $pdo->commit();
            
            // Only notify if we added a new event and status changed, or always notify?
            // Actually, keep it simple and just do it if action == add_event
            if ($action === 'add_event' && $newStatus !== $shipment['status']) {
                notify_status_change($id, $newStatus, '');
            }
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

$page_title = "Colis " . $shipment['tracking_number'];
$use_map = true;
require __DIR__ . '/templates/admin_header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="/shipments" class="text-action hover:underline">&larr; Retour à la liste</a>
        <h1 class="text-3xl font-bold mt-2">Colis <?= escape_html($shipment['tracking_number']) ?></h1>
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
                    <span class="inline-block px-3 py-1 bg-slate-200 rounded font-bold mt-1">
                        <?= escape_html(get_status_labels()[$shipment['status']] ?? $shipment['status']) ?>
                    </span>
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
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nouveau Statut *</label>
                        <select name="status" class="border p-2 rounded w-full bg-white">
                            <?php 
                                $currentStatus = $editEvent ? ($editEvent['status'] ?: $shipment['status']) : $shipment['status'];
                                foreach(get_status_labels() as $k => $v): 
                            ?>
                                <option value="<?= $k ?>" <?= $currentStatus === $k ? 'selected' : '' ?>><?= escape_html($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-1">
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
                    <div class="map-element w-full h-48 bg-slate-200 rounded mb-4 z-0 relative"></div>
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
