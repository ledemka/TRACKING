<?php
require_once __DIR__ . '/api/bootstrap.php';
require_admin();

$now = gmdate('Y-m-d H:i:s');

// Fetch counts
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM shipments GROUP BY status");
$stmt->execute();
$counts_by_status = [];
foreach ($stmt->fetchAll() as $row) {
    $counts_by_status[$row['status']] = $row['count'];
}

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM shipments");
$stmt->execute();
$total_shipments = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM shipments WHERE estimated_delivery_at < ? AND status != ?");
$stmt->execute([$now, STATUS_DELIVERED]);
$delayed_shipments = $stmt->fetch()['count'];

// Fetch latest
$stmt = $pdo->prepare("SELECT * FROM shipments ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$latest_shipments = $stmt->fetchAll();

$page_title = 'Tableau de bord';
require_once __DIR__ . '/templates/admin_layout.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-primary">Tableau de bord</h1>
    <p class="text-slate-500">Aperçu général de l'activité</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col">
        <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Total Colis</span>
        <span class="text-3xl font-bold text-slate-800"><?= (int)$total_shipments ?></span>
    </div>
    
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col border-b-4 border-status-shipped-bg">
        <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Expédiés</span>
        <span class="text-3xl font-bold text-slate-800"><?= (int)($counts_by_status[STATUS_SHIPPED] ?? 0) ?></span>
    </div>
    
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col border-b-4 border-status-delivery-bg">
        <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">En cours</span>
        <span class="text-3xl font-bold text-slate-800"><?= (int)($counts_by_status[STATUS_OUT_FOR_DELIVERY] ?? 0) ?></span>
    </div>
    
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col border-b-4 border-status-delivered-bg">
        <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Livrés</span>
        <span class="text-3xl font-bold text-slate-800"><?= (int)($counts_by_status[STATUS_DELIVERED] ?? 0) ?></span>
    </div>
    
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-red-100 flex flex-col border-b-4 border-red-500">
        <span class="text-sm font-semibold text-red-500 uppercase tracking-wider mb-2">En retard</span>
        <span class="text-3xl font-bold text-red-600"><?= (int)$delayed_shipments ?></span>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-bold text-primary">Derniers colis</h2>
        <a href="/shipments" class="text-sm font-semibold text-accent hover:text-amber-700">Voir tout</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-sm border-b border-slate-100">
                    <th class="p-4 font-semibold">N° Suivi</th>
                    <th class="p-4 font-semibold">Destinataire</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($latest_shipments)): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">Aucune expédition récente.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($latest_shipments as $s): 
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
                            <td class="p-4 text-slate-800">
                                <?= escape_html($s['recipient_name']) ?>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $statusClass ?>">
                                    <?= escape_html($statusLabel) ?>
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-600">
                                <?= date('d/m/Y', strtotime($s['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/templates/admin_footer.php'; ?>
