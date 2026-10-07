<?php
require_once __DIR__ . '/api/bootstrap.php';
require_admin();

$search = $_GET['q'] ?? '';

// Obtenir une liste distincte de clients d'après les expéditions
$query = "
    SELECT 
        recipient_name as name, 
        recipient_email as email, 
        recipient_phone as phone,
        MAX(created_at) as last_order,
        COUNT(id) as total_shipments
    FROM shipments
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (recipient_name LIKE ? OR recipient_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " GROUP BY recipient_email, recipient_name, recipient_phone ORDER BY last_order DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$page_title = 'Administration';
require_once __DIR__ . '/templates/admin_layout.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-primary">Clients</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row gap-4 justify-between items-center bg-slate-50">
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="text" name="q" value="<?= escape_html($search) ?>" placeholder="Rechercher par nom ou email..." class="px-4 py-2 w-64 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition-colors">Chercher</button>
            <?php if ($search): ?>
                <a href="/customers" class="px-4 py-2 text-slate-500 hover:text-slate-700 underline flex items-center">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-sm border-b border-slate-100">
                    <th class="p-4 font-semibold">Nom</th>
                    <th class="p-4 font-semibold">Contact (Email / Tel)</th>
                    <th class="p-4 font-semibold text-center">Nb Colis</th>
                    <th class="p-4 font-semibold">Dernière activité</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">Aucun client trouvé.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-bold text-slate-800">
                                <?= escape_html($c['name']) ?>
                            </td>
                            <td class="p-4">
                                <div><a href="mailto:<?= escape_html($c['email']) ?>" class="text-accent hover:underline"><?= escape_html($c['email']) ?></a></div>
                                <?php if ($c['phone']): ?>
                                    <div class="text-xs text-slate-500"><?= escape_html($c['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center font-bold text-slate-700">
                                <?= (int)$c['total_shipments'] ?>
                            </td>
                            <td class="p-4 text-sm text-slate-600">
                                <?= date('d/m/Y', strtotime($c['last_order'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/templates/admin_footer.php'; ?>
