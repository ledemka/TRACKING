<?php
require_once __DIR__ . '/api/bootstrap.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = $_GET['created'] ?? '' ? 'Colis créé avec succès !' : '';

$stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->execute([$id]);
$shipment = $stmt->fetch();

if (!$shipment) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM shipments WHERE id = ?")->execute([$id]);
        header("Location: /shipments");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'add_event') {
        $label = trim($_POST['label'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $new_status = $_POST['status'] ?? '';
        
        if ($label) {
            $pdo->beginTransaction();
            try {
                // If it's one of the main business statuses, update shipment main status
                if (is_valid_status($new_status)) {
                    $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?")->execute([$new_status, $id]);
                } else {
                    $new_status = null; // Historic event, no new status
                }
                
                $pdo->prepare("INSERT INTO tracking_events (shipment_id, label, location, status, created_by) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$id, $label, $location, $new_status, $_SESSION['user_id']]);
                
                $pdo->commit();
                $success = "Événement ajouté avec succès.";
                // Refresh
                $stmt->execute([$id]);
                $shipment = $stmt->fetch();
            } catch (\Exception $e) {
                $pdo->rollBack();
                $error = "Erreur lors de l'ajout de l'événement.";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT e.*, u.name as author FROM tracking_events e LEFT JOIN users u ON e.created_by = u.id WHERE e.shipment_id = ? ORDER BY e.occurred_at DESC");
$stmt->execute([$id]);
$events = $stmt->fetchAll();

$page_title = 'Administration - ' . escape_html($shipment['tracking_number']);
require_once __DIR__ . '/templates/admin_layout.php';
?>

<div class="mb-8 flex justify-between items-start">
    <div class="flex items-center gap-4">
        <a href="/shipments" class="p-2 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-primary"><?= escape_html($shipment['tracking_number']) ?></h1>
            <p class="text-slate-500 text-sm">Créé le <?= date('d/m/Y H:i', strtotime($shipment['created_at'])) ?></p>
        </div>
    </div>
    
    <form method="POST" onsubmit="return confirm('Supprimer définitivement ce colis ?');">
        <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg font-semibold transition-colors border border-red-200">
            Supprimer
        </button>
    </form>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 border border-green-100">
        <?= escape_html($success) ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100">
        <?= escape_html($error) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">
        <!-- Informations -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-primary border-b border-slate-100 pb-2 mb-4">Détails du colis</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Statut actuel</p>
                    <p class="font-semibold"><?= escape_html(get_status_labels()[$shipment['status']] ?? $shipment['status']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Destinataire</p>
                    <p class="font-semibold"><?= escape_html($shipment['recipient_name']) ?></p>
                    <p class="text-sm text-slate-600"><?= escape_html($shipment['recipient_email']) ?></p>
                    <p class="text-sm text-slate-600"><?= escape_html($shipment['recipient_phone']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Adresse de livraison</p>
                    <p class="text-sm text-slate-800"><?= escape_html($shipment['address']) ?><br><?= escape_html($shipment['city']) ?>, <?= escape_html($shipment['country']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Origine</p>
                    <p class="text-sm text-slate-800"><?= escape_html($shipment['origin_address']) ?><br><?= escape_html($shipment['origin_city']) ?></p>
                </div>
            </div>
        </div>

        <!-- Timeline / Événements -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-primary border-b border-slate-100 pb-2 mb-6">Historique (Timeline)</h3>
            
            <div class="space-y-6">
                <?php foreach ($events as $idx => $e): ?>
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full <?= $idx === 0 ? 'bg-accent' : 'bg-slate-300' ?> mt-1"></div>
                            <?php if ($idx < count($events) - 1): ?>
                                <div class="w-0.5 h-full bg-slate-200 my-1"></div>
                            <?php endif; ?>
                        </div>
                        <div class="pb-6">
                            <p class="font-bold text-slate-800"><?= escape_html($e['label']) ?></p>
                            <p class="text-sm text-slate-500"><?= escape_html($e['location']) ?> • <?= date('d/m/Y H:i', strtotime($e['occurred_at'])) ?></p>
                            <?php if ($e['status']): ?>
                                <span class="inline-block mt-2 px-2 py-1 bg-slate-100 text-slate-600 text-xs rounded border border-slate-200">
                                    Nouveau statut : <?= escape_html(get_status_labels()[$e['status']] ?? $e['status']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div>
        <!-- Nouvel événement -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 sticky top-24">
            <h3 class="text-lg font-bold text-primary border-b border-slate-100 pb-2 mb-4">Ajouter un événement</h3>
            <form method="POST" action="/shipments/<?= $id ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
                <input type="hidden" name="action" value="add_event">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description *</label>
                    <input type="text" name="label" required placeholder="Ex: Arrivé en douane" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Localisation</label>
                    <input type="text" name="location" placeholder="Ex: Paris, France" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Statut global du colis</label>
                    <select name="status" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white">
                        <option value="">-- Ne pas changer le statut -- (NULL)</option>
                        <?php foreach (get_status_labels() as $k => $v): ?>
                            <option value="<?= escape_html($k) ?>"><?= escape_html($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-slate-800 transition-colors">
                    Ajouter à l'historique
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/admin_footer.php'; ?>
