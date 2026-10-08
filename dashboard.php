<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/lib/auth.php';
require_once __DIR__ . '/api/lib/shipments.php';

require_admin();

$stats = get_dashboard_stats();
$recent = get_recent_shipments(8);

$page_title = "Dashboard";
require __DIR__ . '/templates/admin_header.php';
?>
<div class="mb-8">
    <h1 class="text-3xl font-bold mb-6">Tableau de Bord</h1>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <a href="/shipments" class="bg-white p-4 rounded shadow block hover:shadow-md transition">
            <div class="text-slate-500 text-sm font-semibold uppercase">Total</div>
            <div class="text-2xl font-bold"><?= $stats['total'] ?></div>
        </a>
        <a href="/shipments?status=shipped" class="bg-status-shipped-bg border border-blue-200 p-4 rounded shadow block hover:shadow-md transition">
            <div class="text-blue-800 text-sm font-semibold uppercase">Expédiés</div>
            <div class="text-2xl font-bold text-blue-900"><?= $stats['shipped'] ?></div>
        </a>
        <a href="/shipments?status=out_for_delivery" class="bg-status-delivery-bg border border-yellow-200 p-4 rounded shadow block hover:shadow-md transition">
            <div class="text-yellow-800 text-sm font-semibold uppercase">En cours</div>
            <div class="text-2xl font-bold text-yellow-900"><?= $stats['out_for_delivery'] ?></div>
        </a>
        <a href="/shipments?status=delivered" class="bg-status-delivered-bg border border-green-200 p-4 rounded shadow block hover:shadow-md transition">
            <div class="text-green-800 text-sm font-semibold uppercase">Livrés</div>
            <div class="text-2xl font-bold text-green-900"><?= $stats['delivered'] ?></div>
        </a>
        <a href="/shipments?delayed=1" class="bg-red-50 border border-red-200 p-4 rounded shadow block hover:shadow-md transition">
            <div class="text-red-800 text-sm font-semibold uppercase">Retardés</div>
            <div class="text-2xl font-bold text-red-900"><?= $stats['delayed'] ?></div>
        </a>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="p-4 border-b flex justify-between items-center bg-slate-50">
        <h2 class="text-xl font-bold">8 derniers colis</h2>
        <a href="/shipments/new" class="bg-action text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Nouveau colis</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 text-sm text-slate-600">
                    <th class="p-3 border-b">N° Suivi</th>
                    <th class="p-3 border-b">Destinataire</th>
                    <th class="p-3 border-b">Trajet</th>
                    <th class="p-3 border-b">Statut</th>
                    <th class="p-3 border-b">Mise à jour</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $s): ?>
                <tr class="border-b hover:bg-slate-50">
                    <td class="p-3 font-mono text-sm">
                        <a href="/shipments/<?= $s['id'] ?>" class="text-action hover:underline font-bold"><?= escape_html($s['tracking_number']) ?></a>
                        <?php if ($s['is_demo']): ?>
                        <span class="ml-2 text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded">Démo</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-sm"><?= escape_html(format_anonymous_name($s['recipient_name'])) ?></td>
                    <td class="p-3 text-sm"><?= escape_html($s['origin_city']) ?> &rarr; <?= escape_html($s['city']) ?></td>
                    <td class="p-3 text-sm">
                        <?php
                        $label = get_status_labels()[$s['status']] ?? $s['status'];
                        $isDelayed = is_delayed($s['status'], $s['estimated_delivery_at']);
                        if ($isDelayed) echo '<span class="text-red-600 font-bold">Retardé</span><br>';
                        echo escape_html($label);
                        ?>
                    </td>
                    <td class="p-3 text-sm text-slate-500"><?= format_date($s['updated_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                <tr>
                    <td colspan="5" class="p-6 text-center text-slate-500">Aucun colis enregistré.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
