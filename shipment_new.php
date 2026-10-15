<?php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/api/lib/validation.php';
require_once __DIR__ . '/api/lib/admin_utils.php';
require_once __DIR__ . '/api/lib/shipments.php';

require_admin();

global $pdo;

$errors = [];
$form = [
    'tracking_number' => '', 'recipient_name' => '', 'recipient_phone' => '', 'recipient_email' => '', 'recipient_lang' => '',
    'address' => '', 'city' => '', 'zip_code' => '', 'country' => 'France', 'origin_address' => '', 'origin_city' => '',
    'shipped_at' => convert_utc_to_admin_time(gmdate('Y-m-d H:i:s')), 'estimated_delivery_at' => '',
    'status' => STATUS_SHIPPED, 'carrier_id' => '', 'transport_mode' => TRANSPORT_MODE_LAND, 'description' => '', 'weight' => '', 'packages_count' => '1',
    'destination_lat' => '', 'destination_lng' => '',
    'origin_lat' => '', 'origin_lng' => '',
    'is_company' => '0', 'company_name' => '', 'company_siret' => '', 'company_department' => ''
];

// Pré-remplissage du numéro au format PS-AAMM-XXXXXXX à l'ouverture initiale (GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    try {
        $form['tracking_number'] = generate_unique_tracking_number($pdo);
    } catch (Exception $e) {
        $form['tracking_number'] = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die("Erreur CSRF");
    
    // Remplissage $form
    foreach ($form as $k => $v) {
        if (isset($_POST[$k])) $form[$k] = trim($_POST[$k]);
    }
    
    // Normalisation obligatoire du numéro de suivi : trim + strtoupper
    $form['tracking_number'] = strtoupper(trim($form['tracking_number']));
    
    $data = [];
    // Validation du numéro de suivi : même regex que /track (autorise PS-..., COLIS-... et formats historiques)
    if ($form['tracking_number'] === '') {
        $errors['tracking_number'] = __('validation.tracking_required');
    } elseif (!preg_match('/^[A-Z0-9-]{3,40}$/', $form['tracking_number'])) {
        $errors['tracking_number'] = __('validation.tracking_format');
    } else {
        $data['tracking_number'] = $form['tracking_number'];
    }
    $data['recipient_name'] = validate_required_string($form['recipient_name'], 'recipient_name', 255, $errors);
    $data['recipient_phone'] = validate_required_string($form['recipient_phone'], 'recipient_phone', 50, $errors);
    $data['recipient_email'] = validate_email($form['recipient_email'], $errors);
    $data['recipient_lang'] = (!empty($form['recipient_lang']) && in_array(strtolower($form['recipient_lang']), ['fr', 'en', 'de', 'nl'], true))
        ? strtolower($form['recipient_lang'])
        : null;
    $data['address'] = validate_required_string($form['address'], 'address', 500, $errors);
    $data['zip_code'] = validate_required_string($form['zip_code'] ?? '', 'zip_code', 20, $errors);
    $data['city'] = validate_required_string($form['city'], 'city', 255, $errors);
    $data['country'] = validate_required_string($form['country'], 'country', 100, $errors);
    
    $data['is_company'] = !empty($form['is_company']) ? 1 : 0;
    if ($data['is_company']) {
        $data['company_name'] = substr($form['company_name'], 0, 255);
        $data['company_siret'] = substr($form['company_siret'], 0, 150);
        $data['company_department'] = substr($form['company_department'], 0, 150);
    } else {
        $data['company_name'] = null;
        $data['company_siret'] = null;
        $data['company_department'] = null;
    }

    $data['origin_address'] = validate_required_string($form['origin_address'], 'origin_address', 500, $errors);
    $data['origin_city'] = validate_required_string($form['origin_city'], 'origin_city', 255, $errors);
    
    $data['shipped_at'] = convert_admin_time_to_utc($form['shipped_at']);
    if (!$data['shipped_at']) $errors['shipped_at'] = __('validation.shipped_at_invalid');
    
    $data['estimated_delivery_at'] = null;
    if ($form['estimated_delivery_at']) {
        $data['estimated_delivery_at'] = convert_admin_time_to_utc($form['estimated_delivery_at']);
        if (!$data['estimated_delivery_at']) {
            $errors['estimated_delivery_at'] = __('validation.estimated_invalid');
        } elseif ($data['shipped_at'] && $data['estimated_delivery_at'] < $data['shipped_at']) {
            $errors['estimated_delivery_at'] = __('validation.estimated_after_shipped');
        }
    }
    
    $data['status'] = is_valid_status($form['status']) ? $form['status'] : STATUS_SHIPPED;
    $data['transport_mode'] = is_valid_transport_mode($form['transport_mode'] ?? '') ? $form['transport_mode'] : TRANSPORT_MODE_LAND;
    $data['carrier_id'] = empty($form['carrier_id']) ? null : (int)$form['carrier_id'];
    $data['description'] = substr(trim($form['description']), 0, 1000);
    $data['weight'] = validate_weight($form['weight'], $errors);
    $data['packages_count'] = validate_packages_count($form['packages_count'], $errors);
    
    list($data['destination_lat'], $data['destination_lng']) = validate_coordinates($form['destination_lat'], $form['destination_lng'], $errors, 'destination_coords');
    list($data['origin_lat'], $data['origin_lng']) = validate_coordinates($form['origin_lat'], $form['origin_lng'], $errors, 'origin_coords');
    
    if (empty($errors)) {
        try {
            $id = insert_shipment_and_event($data, [
                'label' => get_status_labels()[$data['status']],
                'location' => $data['origin_city'],
                'latitude' => $data['origin_lat'],
                'longitude' => $data['origin_lng']
            ]);
            $_SESSION['flash_message'] = __('flash.shipment_created');
            header("Location: /shipments/$id");
            exit;
        } catch (Exception $e) {
            $errors['tracking_number'] = $e->getMessage();
        }
    }
}

global $pdo;
$carriers = $pdo->query("SELECT id, name FROM carriers ORDER BY name")->fetchAll();

$page_title = __('shipment_form.title_new');
$use_map = true;
require __DIR__ . '/templates/admin_header.php';
?>

<div class="mb-6">
    <a href="/shipments" class="text-action hover:underline">&larr; <?= escape_html(__('common.back_to_list')) ?></a>
    <h1 class="text-3xl font-bold mt-2"><?= escape_html(__('shipment_form.title_new')) ?></h1>
</div>

<form method="POST" action="/shipments/new" class="bg-white p-6 rounded shadow w-full">
    <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
    
    <!-- Tracking Number -->
    <div class="mb-6 border-b pb-6">
        <h2 class="text-xl font-bold mb-4"><?= escape_html(__('shipment_form.sec_identification')) ?></h2>
        <label class="block text-sm font-semibold mb-1" for="tracking_number"><?= escape_html(__('shipment_form.tracking_number')) ?></label>
        <div class="flex gap-2">
            <input type="text" id="tracking_number" name="tracking_number" value="<?= escape_html($form['tracking_number']) ?>" 
                   class="border p-2 rounded flex-grow <?= isset($errors['tracking_number']) ? 'border-red-500' : '' ?>"
                   aria-describedby="err-tracking">
            <?php 
                $tzParis = new DateTimeZone('Europe/Paris');
                $serverYymm = (new DateTime('now', $tzParis))->format('ym');
            ?>
            <button id="btn-generate-tracking" type="button" data-yymm="<?= escape_html($serverYymm) ?>" class="bg-slate-200 px-4 py-2 rounded font-semibold hover:bg-slate-300"><?= escape_html(__('shipment_form.generate')) ?></button>
        </div>
        <?php if (isset($errors['tracking_number'])): ?>
            <p id="err-tracking" class="text-red-600 text-sm mt-1"><?= escape_html($errors['tracking_number']) ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Destinataire & Adresse -->
    <div class="mb-6 border-b pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <h2 class="text-xl font-bold md:col-span-2 mb-2"><?= escape_html(__('shipment_form.sec_recipient')) ?></h2>
        
        <div class="md:col-span-2 mb-2">
            <label class="flex items-center gap-2 cursor-pointer w-max">
                <input type="checkbox" name="is_company" id="is_company_cb" value="1" <?= $form['is_company'] ? 'checked' : '' ?>>
                <span class="font-semibold text-sm"><?= escape_html(__('shipment_form.is_company')) ?></span>
            </label>
        </div>
        
        <div id="company_fields_container" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded border <?= $form['is_company'] ? '' : 'hidden' ?>">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.company_name')) ?></label>
                <input type="text" name="company_name" id="company_name_input" value="<?= escape_html($form['company_name']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.company_siret')) ?></label>
                <input type="text" name="company_siret" id="company_siret_input" value="<?= escape_html($form['company_siret']) ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.company_dept')) ?></label>
                <input type="text" name="company_department" id="company_department_input" value="<?= escape_html($form['company_department']) ?>" class="border p-2 rounded w-full">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.recipient_name')) ?></label>
            <input type="text" name="recipient_name" value="<?= escape_html($form['recipient_name']) ?>" class="border p-2 rounded w-full <?= isset($errors['recipient_name']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['recipient_name'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['recipient_name']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.recipient_email')) ?></label>
            <input type="email" name="recipient_email" value="<?= escape_html($form['recipient_email']) ?>" class="border p-2 rounded w-full <?= isset($errors['recipient_email']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['recipient_email'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['recipient_email']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.recipient_phone')) ?></label>
            <?= render_phone_input_field('recipient_phone', $form['recipient_phone'] ?? '', isset($errors['recipient_phone'])) ?>
            <?php if (isset($errors['recipient_phone'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['recipient_phone']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1" for="recipient_lang"><?= escape_html(__('email.recipient_lang')) ?></label>
            <select name="recipient_lang" id="recipient_lang" class="border p-2 rounded w-full bg-white h-[42px]">
                <option value="" <?= empty($form['recipient_lang']) ? 'selected' : '' ?>><?= escape_html(__('email.lang_auto_rule')) ?></option>
                <option value="fr" <?= ($form['recipient_lang'] ?? '') === 'fr' ? 'selected' : '' ?>>🇫🇷 Français (fr)</option>
                <option value="en" <?= ($form['recipient_lang'] ?? '') === 'en' ? 'selected' : '' ?>>🇬🇧 English (en)</option>
                <option value="de" <?= ($form['recipient_lang'] ?? '') === 'de' ? 'selected' : '' ?>>🇩🇪 Deutsch (de)</option>
                <option value="nl" <?= ($form['recipient_lang'] ?? '') === 'nl' ? 'selected' : '' ?>>🇳🇱 Nederlands (nl)</option>
            </select>
            <p class="text-xs text-slate-500 mt-1"><?= escape_html(__('email.lang_help')) ?></p>
        </div>

        <div class="md:col-span-2 admin-map-picker p-4 bg-slate-50 border rounded-lg" data-target-address="[name='address']" data-target-zip="[name='zip_code']" data-target-city="[name='city']" data-target-country="[name='country']">
            <h3 class="text-lg font-bold mb-4"><?= escape_html(__('shipment_form.map_dest')) ?></h3>
            <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.latitude')) ?></label>
                    <input type="text" name="destination_lat" value="<?= escape_html($form['destination_lat']) ?>" class="input-lat border p-2 rounded w-full bg-white <?= isset($errors['destination_coords']) ? 'border-red-500' : '' ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.longitude')) ?></label>
                    <input type="text" name="destination_lng" value="<?= escape_html($form['destination_lng']) ?>" class="input-lng border p-2 rounded w-full bg-white <?= isset($errors['destination_coords']) ? 'border-red-500' : '' ?>">
                </div>
            </div>
            <?php if (isset($errors['destination_coords'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['destination_coords']).'</p>'; ?>
            <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline"><?= escape_html(__('shipment_form.clear_map')) ?></button>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.address')) ?></label>
            <textarea name="address" rows="2" class="border p-2 rounded w-full <?= isset($errors['address']) ? 'border-red-500' : '' ?>"><?= escape_html($form['address']) ?></textarea>
            <?php if (isset($errors['address'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['address']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.zip')) ?></label>
            <input type="text" name="zip_code" value="<?= escape_html($form['zip_code']) ?>" class="border p-2 rounded w-full <?= isset($errors['zip_code']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['zip_code'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['zip_code']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.city')) ?></label>
            <input type="text" name="city" value="<?= escape_html($form['city']) ?>" class="border p-2 rounded w-full <?= isset($errors['city']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['city'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['city']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.country')) ?></label>
            <input type="text" name="country" value="<?= escape_html($form['country']) ?>" class="border p-2 rounded w-full <?= isset($errors['country']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['country'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['country']).'</p>'; ?>
        </div>
    </div>
    
    <!-- Origine & Adresse -->
    <div class="mb-6 border-b pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <h2 class="text-xl font-bold md:col-span-2 mb-2"><?= escape_html(__('shipment_form.sec_origin')) ?></h2>
        
        <div class="md:col-span-2 admin-map-picker p-4 bg-slate-50 border rounded-lg" data-target-address="[name='origin_address']" data-target-city="[name='origin_city']">
            <h3 class="text-lg font-bold mb-4"><?= escape_html(__('shipment_form.map_origin')) ?></h3>
            <div class="map-element w-full h-64 bg-slate-200 rounded mb-4 z-0 relative"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.latitude')) ?></label>
                    <input type="text" name="origin_lat" value="<?= escape_html($form['origin_lat']) ?>" class="input-lat border p-2 rounded w-full bg-white <?= isset($errors['origin_coords']) ? 'border-red-500' : '' ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.longitude')) ?></label>
                    <input type="text" name="origin_lng" value="<?= escape_html($form['origin_lng']) ?>" class="input-lng border p-2 rounded w-full bg-white <?= isset($errors['origin_coords']) ? 'border-red-500' : '' ?>">
                </div>
            </div>
            <?php if (isset($errors['origin_coords'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['origin_coords']).'</p>'; ?>
            <button type="button" class="btn-clear-map mt-2 text-sm text-action hover:underline"><?= escape_html(__('shipment_form.clear_map')) ?></button>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.origin_address')) ?></label>
            <input type="text" name="origin_address" value="<?= escape_html($form['origin_address']) ?>" class="border p-2 rounded w-full <?= isset($errors['origin_address']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['origin_address'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['origin_address']).'</p>'; ?>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.origin_city')) ?></label>
            <input type="text" name="origin_city" value="<?= escape_html($form['origin_city']) ?>" class="border p-2 rounded w-full md:w-1/2 <?= isset($errors['origin_city']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['origin_city'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['origin_city']).'</p>'; ?>
        </div>
    </div>
    
    <!-- Expédition -->
    <div class="mb-6 border-b pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <h2 class="text-xl font-bold md:col-span-2 mb-2"><?= escape_html(__('shipment_form.sec_details')) ?></h2>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.carrier')) ?></label>
            <select name="carrier_id" class="border p-2 rounded w-full">
                <option value=""><?= escape_html(__('carrier.none')) ?></option>
                <?php foreach ($carriers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $form['carrier_id'] == $c['id'] ? 'selected' : '' ?>><?= escape_html($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.status')) ?></label>
            <select name="status" class="border p-2 rounded w-full">
                <?php foreach(get_status_labels() as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $form['status'] === $k ? 'selected' : '' ?>><?= escape_html($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1" for="transport_mode"><?= escape_html(__('shipment_form.transport_mode')) ?></label>
            <select name="transport_mode" id="transport_mode" class="border p-2 rounded w-full <?= isset($errors['transport_mode']) ? 'border-red-500' : '' ?>">
                <?php foreach(get_transport_modes() as $modeKey => $modeInfo): ?>
                    <option value="<?= escape_html($modeKey) ?>" <?= $form['transport_mode'] === $modeKey ? 'selected' : '' ?>>
                        <?= escape_html($modeInfo['label']) ?> (<?= escape_html($modeInfo['description']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['transport_mode'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['transport_mode']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.shipped_at')) ?></label>
            <input type="datetime-local" name="shipped_at" value="<?= escape_html($form['shipped_at']) ?>" class="border p-2 rounded w-full <?= isset($errors['shipped_at']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['shipped_at'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['shipped_at']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.estimated_delivery')) ?></label>
            <input type="datetime-local" name="estimated_delivery_at" value="<?= escape_html($form['estimated_delivery_at']) ?>" class="border p-2 rounded w-full <?= isset($errors['estimated_delivery_at']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['estimated_delivery_at'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['estimated_delivery_at']).'</p>'; ?>
        </div>
    </div>
    
    <!-- Poids & Description -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.weight')) ?></label>
            <input type="text" name="weight" value="<?= escape_html($form['weight']) ?>" class="border p-2 rounded w-full <?= isset($errors['weight']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['weight'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['weight']).'</p>'; ?>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.packages_count')) ?></label>
            <input type="number" name="packages_count" min="1" max="1000" value="<?= escape_html($form['packages_count']) ?>" class="border p-2 rounded w-full <?= isset($errors['packages_count']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['packages_count'])) echo '<p class="text-red-600 text-sm mt-1">'.escape_html($errors['packages_count']).'</p>'; ?>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1"><?= escape_html(__('shipment_form.description')) ?></label>
            <textarea name="description" rows="2" class="border p-2 rounded w-full"><?= escape_html($form['description']) ?></textarea>
        </div>
    </div>
    
    <div class="flex justify-end gap-4 border-t pt-6">
        <a href="/shipments" class="px-6 py-2 bg-slate-200 rounded font-semibold hover:bg-slate-300"><?= escape_html(__('common.cancel')) ?></a>
        <button type="submit" class="px-6 py-2 bg-action text-white rounded font-bold hover:bg-blue-700"><?= escape_html(__('shipment_form.btn_save_new')) ?></button>
    </div>
</form>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
