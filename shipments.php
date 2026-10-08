<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/lib/auth.php';
require_once __DIR__ . '/api/lib/shipments.php';

require_admin();

// Traitement suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF");
    }
    if (empty($_POST['confirm_delete'])) {
        $_SESSION['flash_error'] = "Vous devez cocher la case de confirmation.";
        header('Location: /shipments');
        exit;
    }
    $id = (int)$_POST['shipment_id'];
    global $pdo;
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("DELETE FROM tracking_events WHERE shipment_id = ?");
    $stmt->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM shipments WHERE id = ?");
    $stmt->execute([$id]);
    $pdo->commit();
    $_SESSION['flash_message'] = "Colis supprimé avec succès.";
    header('Location: /shipments');
    exit;
}

// Filtres
$q = $_GET['q'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$delayed = $_GET['delayed'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$where = ["1=1"];
$params = [];

if ($q !== '') {
    $where[] = "(tracking_number LIKE ? OR recipient_name LIKE ? OR city LIKE ?)";
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($statusFilter !== '') {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}
if ($delayed === '1') {
    $where[] = "status != ? AND estimated_delivery_at IS NOT NULL AND estimated_delivery_at < UTC_TIMESTAMP()";
    $params[] = STATUS_DELIVERED;
}

$whereClause = implode(' AND ', $where);

global $pdo;
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM shipments WHERE $whereClause");
$stmtCount->execute($params);
$total = $stmtCount->fetchColumn();
$totalPages = ceil($total / $limit) ?: 1;
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$stmt = $pdo->prepare("SELECT * FROM shipments WHERE $whereClause ORDER BY updated_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$shipments = $stmt->fetchAll();

$page_title = "Colis";
require __DIR__ . '/templates/admin_header.php';
?>

<div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <h1 class="text-3xl font-bold">Gestion des Colis</h1>
    <a href="/shipments/new" class="bg-action text-white px-4 py-2 rounded hover:bg-blue-700">Nouveau colis</a>
</div>

<form method="GET" action="/shipments" class="bg-white p-4 rounded shadow mb-6 flex flex-wrap gap-4 items-end">
    <div class="flex-grow min-w-[200px]">
        <label for="q" class="block text-sm font-semibold mb-1">Recherche (N°, Nom, Ville)</label>
        <input type="text" name="q" id="q" value="<?= escape_html($q) ?>" class="w-full border p-2 rounded">
    </div>
    <div>
        <label for="status" class="block text-sm font-semibold mb-1">Statut</label>
        <select name="status" id="status" class="w-full border p-2 rounded">
            <option value="">Tous les statuts</option>
            <?php foreach(get_status_labels() as $k => $v): ?>
                <option value="<?= $k ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= escape_html($v) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-center h-10 px-2">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="delayed" value="1" <?= $delayed === '1' ? 'checked' : '' ?>>
            <span class="text-sm font-semibold">Retardés uniquement</span>
        </label>
    </div>
    <div>
        <button type="submit" class="bg-slate-200 px-4 py-2 rounded hover:bg-slate-300">Filtrer</button>
        <a href="/shipments" class="ml-2 text-slate-500 hover:underline text-sm">Réinitialiser</a>
    </div>
</form>

<div class="bg-white rounded shadow overflow-hidden hidden md:block mb-6">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-100 text-sm text-slate-600">
                <th class="p-3 border-b">N° Suivi</th>
                <th class="p-3 border-b">Destinataire</th>
                <th class="p-3 border-b">Trajet</th>
                <th class="p-3 border-b">Statut</th>
                <th class="p-3 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($shipments as $s): ?>
            <tr class="border-b hover:bg-slate-50">
                <td class="p-3 font-mono text-sm">
                    <a href="/shipments/<?= $s['id'] ?>" class="text-action hover:underline font-bold"><?= escape_html($s['tracking_number']) ?></a>
                </td>
                <td class="p-3 text-sm"><?= escape_html(format_anonymous_name($s['recipient_name'])) ?></td>
                <td class="p-3 text-sm"><?= escape_html($s['origin_city']) ?> &rarr; <?= escape_html($s['city']) ?></td>
                <td class="p-3 text-sm">
                    <?php
                    $isDelayed = is_delayed($s['status'], $s['estimated_delivery_at']);
                    if ($isDelayed) echo '<span class="text-red-600 font-bold">Retardé</span><br>';
                    echo escape_html(get_status_labels()[$s['status']] ?? $s['status']);
                    ?>
                </td>
                <td class="p-3 text-sm flex gap-2">
                    <a href="/shipments/<?= $s['id'] ?>" class="text-action hover:underline">Modifier</a>
                    <button class="text-red-600 hover:underline btn-delete-prompt" data-dialog="delete-dialog-<?= $s['id'] ?>">Supprimer</button>
                    
                    <dialog id="delete-dialog-<?= $s['id'] ?>" class="p-6 rounded shadow-lg border-0 backdrop:bg-slate-800/50">
                        <h3 class="text-lg font-bold mb-4">Supprimer le colis <?= escape_html($s['tracking_number']) ?> ?</h3>
                        <p class="mb-4">Cette action est irréversible et supprimera tout l'historique.</p>
                        <form method="POST" action="/shipments" class="delete-form-js-modal">
                            <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                            <label class="flex items-center gap-2 mb-6">
                                <input type="checkbox" name="confirm_delete" required class="confirm-checkbox">
                                <span>Je confirme la suppression</span>
                            </label>
                            <div class="flex justify-end gap-4">
                                <button type="button" class="btn-close-dialog px-4 py-2 bg-slate-200 rounded">Annuler</button>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Supprimer</button>
                            </div>
                        </form>
                    </dialog>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($shipments)): ?>
            <tr><td colspan="5" class="p-6 text-center text-slate-500">Aucun colis trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="md:hidden space-y-4 mb-6">
    <?php foreach($shipments as $s): ?>
    <div class="bg-white p-4 rounded shadow">
        <div class="flex justify-between items-start mb-2">
            <a href="/shipments/<?= $s['id'] ?>" class="font-mono text-action font-bold hover:underline"><?= escape_html($s['tracking_number']) ?></a>
            <span class="text-sm font-bold <?= is_delayed($s['status'], $s['estimated_delivery_at']) ? 'text-red-600' : 'text-slate-600' ?>">
                <?= is_delayed($s['status'], $s['estimated_delivery_at']) ? 'Retardé' : escape_html(get_status_labels()[$s['status']] ?? $s['status']) ?>
            </span>
        </div>
        <div class="text-sm mb-1"><strong>Dest. :</strong> <?= escape_html(format_anonymous_name($s['recipient_name'])) ?></div>
        <div class="text-sm mb-4"><strong>Trajet :</strong> <?= escape_html($s['origin_city']) ?> &rarr; <?= escape_html($s['city']) ?></div>
        <div class="flex gap-4 text-sm border-t pt-2 mt-2">
            <a href="/shipments/<?= $s['id'] ?>" class="text-action">Modifier</a>
            <button class="text-red-600 btn-delete-prompt" data-dialog="delete-dialog-mob-<?= $s['id'] ?>">Supprimer</button>
            <dialog id="delete-dialog-mob-<?= $s['id'] ?>" class="p-6 rounded shadow-lg border-0 backdrop:bg-slate-800/50">
                <h3 class="text-lg font-bold mb-4">Supprimer ?</h3>
                <form method="POST" action="/shipments" class="delete-form-js-modal">
                    <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                    <label class="flex items-center gap-2 mb-6"><input type="checkbox" name="confirm_delete" required class="confirm-checkbox"><span>Je confirme</span></label>
                    <div class="flex gap-4">
                        <button type="button" class="btn-close-dialog bg-slate-200 px-4 py-2 rounded">Annuler</button>
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Supprimer</button>
                    </div>
                </form>
            </dialog>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($shipments)): ?>
    <div class="bg-white p-4 rounded shadow text-center text-slate-500">Aucun colis trouvé.</div>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="flex justify-center gap-2 mb-8">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?q=<?= urlencode($q) ?>&status=<?= urlencode($statusFilter) ?>&delayed=<?= urlencode($delayed) ?>&page=<?= $i ?>" 
       class="px-3 py-1 border rounded <?= $i === $page ? 'bg-primary text-white' : 'bg-white hover:bg-slate-50' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
