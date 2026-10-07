<?php
require_once __DIR__ . '/api/bootstrap.php';
require_admin();

$status_filter = $_GET['status'] ?? '';
$search = $_GET['q'] ?? '';

$query = "SELECT s.*, c.name as carrier_name FROM shipments s LEFT JOIN carriers c ON s.carrier_id = c.id WHERE 1=1";
$params = [];

if ($status_filter) {
    $query .= " AND s.status = ?";
    $params[] = $status_filter;
}
if ($search) {
    $query .= " AND (s.tracking_number LIKE ? OR s.recipient_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shipments = $stmt->fetchAll();

$page_title = 'Administration';
require_once __DIR__ . '/templates/admin_layout.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-primary">Expéditions</h1>
    <a href="/shipments/new" class="bg-accent text-white px-4 py-2 rounded-lg font-semibold hover:bg-amber-600 transition-colors">
        Nouveau colis
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row gap-4 justify-between items-center bg-slate-50">
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="text" name="q" value="<?= escape_html($search) ?>" placeholder="Rechercher..." class="px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            <select name="status" class="px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white">
                <option value="">Tous les statuts</option>
                <?php foreach (get_status_labels() as $k => $v): ?>
                    <option value="<?= escape_html($k) ?>" <?= $status_filter === $k ? 'selected' : '' ?>><?= escape_html($v) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition-colors">Filtrer</button>
            <?php if ($search || $status_filter): ?>
                <a href="/shipments" class="px-4 py-2 text-slate-500 hover:text-slate-700 underline flex items-center">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-sm border-b border-slate-100">
                    <th class="p-4 font-semibold">N° Suivi</th>
                    <th class="p-4 font-semibold">Destinataire</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold">Date d'expédition</th>
                    <th class="p-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shipments)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">Aucune expédition trouvée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($shipments as $s): 
                        $statusLabel = get_status_labels()[$s['status']] ?? $s['status'];
                        $statusClass = 'bg-slate-100 text-slate-700';
                        if ($s['status'] === STATUS_SHIPPED) $statusClass = 'bg-status-shipped-bg text-status-shipped-text';
                        if ($s['status'] === STATUS_OUT_FOR_DELIVERY) $statusClass = 'bg-status-delivery-bg text-status-delivery-text';
                        if ($s['status'] === STATUS_DELIVERED) $statusClass = 'bg-status-delivered-bg text-status-delivered-text';
                    ?>
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-mono font-medium text-primary">
                                <a href="/shipments/<?= $s['id'] ?>" class="hover:underline"><?= escape_html($s['tracking_number']) ?></a>
                            </td>
                            <td class="p-4">
                                <div><?= escape_html($s['recipient_name']) ?></div>
                                <div class="text-xs text-slate-500"><?= escape_html($s['city']) ?>, <?= escape_html($s['country']) ?></div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $statusClass ?>">
                                    <?= escape_html($statusLabel) ?>
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-600">
                                <?= $s['shipped_at'] ? date('d/m/Y', strtotime($s['shipped_at'])) : '-' ?>
                            </td>
                            <td class="p-4">
                                <a href="/shipments/<?= $s['id'] ?>" class="text-accent hover:text-amber-700 text-sm font-semibold">Gérer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/templates/admin_footer.php'; ?>
