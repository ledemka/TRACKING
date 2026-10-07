<?php
require_once __DIR__ . '/api/bootstrap.php';
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $recipient_email = trim($_POST['recipient_email'] ?? '');
    $recipient_phone = trim($_POST['recipient_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    $origin_address = trim($_POST['origin_address'] ?? '');
    $origin_city = trim($_POST['origin_city'] ?? '');
    
    $weight_kg = (float)($_POST['weight_kg'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if (!$recipient_name || !$recipient_email || !$address || !$city || !$country || !$origin_address || !$origin_city) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse e-mail n'est pas valide.";
    } else {
        // Generate unique tracking number
        $year = date('Y');
        $tracking_number = '';
        while (true) {
            $random = str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $tracking_number = "COLIS-$year-$random";
            $stmt = $pdo->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
            $stmt->execute([$tracking_number]);
            if (!$stmt->fetch()) {
                break;
            }
        }
        
        $shipped_at = gmdate('Y-m-d H:i:s');
        $status = STATUS_SHIPPED;
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO shipments 
                (tracking_number, recipient_name, recipient_email, recipient_phone, address, city, country, origin_address, origin_city, weight_kg, description, status, shipped_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tracking_number, $recipient_name, $recipient_email, $recipient_phone, $address, $city, $country, 
                $origin_address, $origin_city, $weight_kg, $description, $status, $shipped_at
            ]);
            
            $shipment_id = $pdo->lastInsertId();
            
            // Log initial event
            $stmt = $pdo->prepare("INSERT INTO tracking_events (shipment_id, label, location, status, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $shipment_id,
                "Colis pris en charge",
                $origin_city,
                $status,
                $_SESSION['user_id']
            ]);
            
            $pdo->commit();
            header("Location: /shipments/$shipment_id?created=1");
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la création du colis : " . $e->getMessage();
        }
    }
}

$page_title = 'Administration';
require_once __DIR__ . '/templates/admin_layout.php';
?>

<div class="mb-8 flex items-center gap-4">
    <a href="/shipments" class="p-2 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <h1 class="text-2xl font-bold text-primary">Nouveau Colis</h1>
</div>

<?php if ($error): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100">
        <?= escape_html($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
    <form method="POST" action="/shipments/new" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Destinataire -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-primary border-b border-slate-100 pb-2">Destinataire</h3>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nom complet *</label>
                    <input type="text" name="recipient_name" required value="<?= escape_html($_POST['recipient_name'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">E-mail *</label>
                    <input type="email" name="recipient_email" required value="<?= escape_html($_POST['recipient_email'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                    <input type="text" name="recipient_phone" value="<?= escape_html($_POST['recipient_phone'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Adresse *</label>
                    <input type="text" name="address" required value="<?= escape_html($_POST['address'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ville *</label>
                        <input type="text" name="city" required value="<?= escape_html($_POST['city'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pays *</label>
                        <input type="text" name="country" required value="<?= escape_html($_POST['country'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                </div>
            </div>

            <!-- Origine et détails -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-primary border-b border-slate-100 pb-2">Expédition</h3>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Adresse de départ *</label>
                    <input type="text" name="origin_address" required value="<?= escape_html($_POST['origin_address'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ville de départ *</label>
                    <input type="text" name="origin_city" required value="<?= escape_html($_POST['origin_city'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Poids (kg)</label>
                    <input type="number" step="0.01" name="weight_kg" value="<?= escape_html($_POST['weight_kg'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description / Notes</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none"><?= escape_html($_POST['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-accent text-white px-8 py-3 rounded-lg font-bold hover:bg-amber-600 transition-colors">
                Créer l'expédition
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/templates/admin_footer.php'; ?>
