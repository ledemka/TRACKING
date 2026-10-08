<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/lib/auth.php';
require_once __DIR__ . '/api/lib/validation.php';
require_once __DIR__ . '/api/lib/admin_utils.php';
require_once __DIR__ . '/api/lib/shipments.php';

require_admin();

$errors = [];
$form = [
    'tracking_number' => '', 'recipient_name' => '', 'recipient_phone' => '', 'recipient_email' => '',
    'address' => '', 'city' => '', 'country' => 'France', 'origin_address' => '', 'origin_city' => '',
    'shipped_at' => convert_utc_to_admin_time(gmdate('Y-m-d H:i:s')), 'estimated_delivery_at' => '',
    'status' => STATUS_SHIPPED, 'carrier_id' => '', 'description' => '', 'weight' => '', 'packages_count' => '1',
    'destination_lat' => '', 'destination_lng' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die("Erreur CSRF");
    
    // Remplissage $form
    foreach ($form as $k => $v) {
        if (isset($_POST[$k])) $form[$k] = trim($_POST[$k]);
    }
    
    $data = [];
    $data['tracking_number'] = validate_required_string($form['tracking_number'], 'tracking_number', 50, $errors);
    $data['recipient_name'] = validate_required_string($form['recipient_name'], 'recipient_name', 255, $errors);
    $data['recipient_phone'] = validate_required_string($form['recipient_phone'], 'recipient_phone', 50, $errors);
    $data['recipient_email'] = validate_email($form['recipient_email'], $errors);
    $data['address'] = validate_required_string($form['address'], 'address', 500, $errors);
    $data['city'] = validate_required_string($form['city'], 'city', 255, $errors);
    $data['country'] = validate_required_string($form['country'], 'country', 100, $errors);
    $data['origin_address'] = validate_required_string($form['origin_address'], 'origin_address', 500, $errors);
    $data['origin_city'] = validate_required_string($form['origin_city'], 'origin_city', 255, $errors);
    
    $data['shipped_at'] = convert_admin_time_to_utc($form['shipped_at']);
    if (!$data['shipped_at']) $errors['shipped_at'] = 'Date d\'expédition invalide.';
    
    $data['estimated_delivery_at'] = null;
    if ($form['estimated_delivery_at']) {
        $data['estimated_delivery_at'] = convert_admin_time_to_utc($form['estimated_delivery_at']);
        if (!$data['estimated_delivery_at']) {
            $errors['estimated_delivery_at'] = 'Date estimée invalide.';
        } elseif ($data['shipped_at'] && $data['estimated_delivery_at'] < $data['shipped_at']) {
            $errors['estimated_delivery_at'] = 'La date estimée doit être >= à l\'expédition.';
        }
    }
    
    $data['status'] = is_valid_status($form['status']) ? $form['status'] : STATUS_SHIPPED;
    $data['carrier_id'] = empty($form['carrier_id']) ? null : (int)$form['carrier_id'];
    $data['description'] = substr(trim($form['description']), 0, 1000);
    $data['weight'] = validate_weight($form['weight'], $errors);
    $data['packages_count'] = validate_packages_count($form['packages_count'], $errors);
    
    list($data['destination_lat'], $data['destination_lng']) = validate_coordinates($form['destination_lat'], $form['destination_lng'], $errors);
    
    if (empty($errors)) {
        try {
            $id = insert_shipment_and_event($data, [
                'label' => get_status_labels()[$data['status']],
                'location' => $data['origin_city']
            ]);
            $_SESSION['flash_message'] = "Colis créé avec succès.";
            header("Location: /shipments/$id");
            exit;
        } catch (Exception $e) {
            $errors['tracking_number'] = $e->getMessage();
        }
    }
}

global $pdo;
$carriers = $pdo->query("SELECT id, name FROM carriers ORDER BY name")->fetchAll();

$page_title = "Nouveau Colis";
$use_map = true;
require __DIR__ . '/templates/admin_header.php';
?>

<div class="mb-6">
    <a href="/shipments" class="text-action hover:underline">&larr; Retour à la liste</a>
    <h1 class="text-3xl font-bold mt-2">Nouveau Colis</h1>
</div>

<form method="POST" action="/shipments/new" class="bg-white p-6 rounded shadow max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
    
    <!-- Tracking Number -->
    <div class="mb-6 border-b pb-6">
        <h2 class="text-xl font-bold mb-4">Identification</h2>
        <label class="block text-sm font-semibold mb-1" for="tracking_number">Numéro de suivi *</label>
        <div class="flex gap-2">
            <input type="text" id="tracking_number" name="tracking_number" value="<?= escape_html($form['tracking_number']) ?>" 
                   class="border p-2 rounded flex-grow <?= isset($errors['tracking_number']) ? 'border-red-500' : '' ?>"
                   aria-describedby="err-tracking">
            <button id="btn-generate-tracking" class="bg-slate-200 px-4 py-2 rounded font-semibold hover:bg-slate-300">Générer</button>
        </div>
        <?php if (isset($errors['tracking_number'])): ?>
            <p id="err-tracking" class="text-red-600 text-sm mt-1"><?= escape_html($errors['tracking_number']) ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Destinataire -->
    <div class="mb-6 border-b pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <h2 class="text-xl font-bold md:col-span-2 mb-2">Destinataire</h2>
        <div>
            <label class="block text-sm font-semibold mb-1">Nom / Entreprise *</label>
            <input type="text" name="recipient_name" value="<?= escape_html($form['recipient_name']) ?>" class="border p-2 rounded w-full <?= isset($errors['recipient_name']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['recipient_name'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['recipient_name']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">E-mail *</label>
            <input type="email" name="recipient_email" value="<?= escape_html($form['recipient_email']) ?>" class="border p-2 rounded w-full <?= isset($errors['recipient_email']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['recipient_email'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['recipient_email']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Téléphone *</label>
            <input type="text" name="recipient_phone" value="<?= escape_html($form['recipient_phone']) ?>" class="border p-2 rounded w-full <?= isset($errors['recipient_phone']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['recipient_phone'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['recipient_phone']).'</p>'; ?>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1">Adresse complète *</label>
            <textarea name="address" rows="2" class="border p-2 rounded w-full <?= isset($errors['address']) ? 'border-red-500' : '' ?>"><?= escape_html($form['address']) ?></textarea>
            <?php if (isset($errors['address'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['address']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Ville *</label>
            <input type="text" name="city" value="<?= escape_html($form['city']) ?>" class="border p-2 rounded w-full <?= isset($errors['city']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['city'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['city']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Pays *</label>
            <input type="text" name="country" value="<?= escape_html($form['country']) ?>" class="border p-2 rounded w-full <?= isset($errors['country']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['country'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['country']).'</p>'; ?>
        </div>
    </div>
    
    <!-- Origine -->
    <div class="mb-6 border-b pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <h2 class="text-xl font-bold md:col-span-2 mb-2">Origine</h2>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1">Adresse d'origine *</label>
            <input type="text" name="origin_address" value="<?= escape_html($form['origin_address']) ?>" class="border p-2 rounded w-full <?= isset($errors['origin_address']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['origin_address'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['origin_address']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Ville d'origine *</label>
            <input type="text" name="origin_city" value="<?= escape_html($form['origin_city']) ?>" class="border p-2 rounded w-full <?= isset($errors['origin_city']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['origin_city'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['origin_city']).'</p>'; ?>
        </div>
    </div>
    
    <!-- Dates & Statut -->
    <div class="mb-6 border-b pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <h2 class="text-xl font-bold md:col-span-2 mb-2">Expédition</h2>
        <div>
            <label class="block text-sm font-semibold mb-1">Transporteur</label>
            <select name="carrier_id" class="border p-2 rounded w-full">
                <option value="">-- Aucun --</option>
                <?php foreach ($carriers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $form['carrier_id'] == $c['id'] ? 'selected' : '' ?>><?= escape_html($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Statut initial *</label>
            <select name="status" class="border p-2 rounded w-full">
                <?php foreach(get_status_labels() as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $form['status'] === $k ? 'selected' : '' ?>><?= escape_html($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Date d'expédition *</label>
            <input type="datetime-local" name="shipped_at" value="<?= escape_html($form['shipped_at']) ?>" class="border p-2 rounded w-full <?= isset($errors['shipped_at']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['shipped_at'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['shipped_at']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Livraison estimée (optionnel)</label>
            <input type="datetime-local" name="estimated_delivery_at" value="<?= escape_html($form['estimated_delivery_at']) ?>" class="border p-2 rounded w-full <?= isset($errors['estimated_delivery_at']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['estimated_delivery_at'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['estimated_delivery_at']).'</p>'; ?>
        </div>
    </div>
    
    <!-- Destination Map -->
    <div class="mb-6 border-b pb-6 admin-map-picker">
        <h2 class="text-xl font-bold mb-2">Destination (Carte)</h2>
        <p class="text-sm text-slate-600 mb-4 bg-yellow-50 p-3 rounded border border-yellow-200">
            <strong>Avertissement :</strong> Indiquez le centre de la ville de destination (affiché publiquement avec ~1 km de précision). N'indiquez jamais l'adresse exacte du destinataire.
        </p>
        <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Latitude</label>
                <input type="text" name="destination_lat" value="<?= escape_html($form['destination_lat']) ?>" class="input-lat border p-2 rounded w-full <?= isset($errors['coordinates']) ? 'border-red-500' : '' ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Longitude</label>
                <input type="text" name="destination_lng" value="<?= escape_html($form['destination_lng']) ?>" class="input-lng border p-2 rounded w-full <?= isset($errors['coordinates']) ? 'border-red-500' : '' ?>">
            </div>
        </div>
        <?php if (isset($errors['coordinates'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['coordinates']).'</p>'; ?>
        <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline">Effacer la position</button>
    </div>
    
    <!-- Poids & Description -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-1">Poids (kg, optionnel)</label>
            <input type="text" name="weight" value="<?= escape_html($form['weight']) ?>" class="border p-2 rounded w-full <?= isset($errors['weight']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['weight'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['weight']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Nombre de colis *</label>
            <input type="number" name="packages_count" min="1" max="1000" value="<?= escape_html($form['packages_count']) ?>" class="border p-2 rounded w-full <?= isset($errors['packages_count']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['packages_count'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['packages_count']).'</p>'; ?>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1">Description (optionnel)</label>
            <textarea name="description" rows="2" class="border p-2 rounded w-full"><?= escape_html($form['description']) ?></textarea>
        </div>
    </div>
    
    <div class="flex justify-end gap-4 border-t pt-6">
        <a href="/shipments" class="px-6 py-2 bg-slate-200 rounded font-semibold hover:bg-slate-300">Annuler</a>
        <button type="submit" class="px-6 py-2 bg-action text-white rounded font-bold hover:bg-blue-700">Créer le colis</button>
    </div>
</form>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
