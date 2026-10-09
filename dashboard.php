<?php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/api/lib/shipments.php';

require_admin();

$stats = get_dashboard_stats();

// Filtering logic (Restaurée comme dans l'Option 1)
$q = $_GET['q'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 15;
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

$whereClause = implode(' AND ', $where);

global $pdo;
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM shipments WHERE $whereClause");
$stmtCount->execute($params);
$totalResults = $stmtCount->fetchColumn();

$totalPages = ceil($totalResults / $limit) ?: 1;
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$stmt = $pdo->prepare("SELECT * FROM shipments WHERE $whereClause ORDER BY updated_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$shipments = $stmt->fetchAll();

$page_title = "Dashboard";
require __DIR__ . '/templates/admin_header.php';
?>
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800 tracking-tight mb-6">Tableau de Bord</h1>

    <!-- Grille 5 colonnes garanties sur Desktop -->
    <div class="flex flex-wrap lg:grid lg:grid-cols-5 gap-2 sm:gap-3">
        <?php $q_param = $q !== '' ? '&q=' . urlencode($q) : ''; ?>
        
        <!-- Total -->
        <a href="/dashboard<?= $q !== '' ? '?q='.urlencode($q) : '' ?>" class="flex-1 min-w-[140px] flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3 rounded-xl shadow-sm hover:shadow-md transition-all border <?= ($statusFilter === '') ? 'bg-slate-100 border-slate-300 ring-1 ring-slate-300' : 'bg-slate-50 border-slate-200 hover:border-slate-300' ?>">
            <div class="flex items-center gap-2 min-w-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider truncate">Total</span>
            </div>
            <div class="ml-2 text-sm sm:text-base font-bold text-slate-900 bg-white px-2 py-0.5 rounded-md shadow-sm border border-slate-100"><?= $stats['total'] ?></div>
        </a>

        <!-- Expédiés -->
        <a href="/dashboard?status=<?= STATUS_SHIPPED ?><?= $q_param ?>" class="flex-1 min-w-[140px] flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3 rounded-xl shadow-sm hover:shadow-md transition-all border <?= ($statusFilter === STATUS_SHIPPED) ? 'bg-blue-100 border-blue-300 ring-1 ring-blue-300' : 'bg-blue-50 border-blue-200 hover:border-blue-300' ?>">
            <div class="flex items-center gap-2 min-w-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l3 3v8a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2z"></path></svg>
                <span class="text-[10px] sm:text-xs font-bold text-blue-700 uppercase tracking-wider truncate">Expédiés</span>
            </div>
            <div class="ml-2 text-sm sm:text-base font-bold text-blue-900 bg-white px-2 py-0.5 rounded-md shadow-sm border border-blue-100"><?= $stats['shipped'] ?></div>
        </a>

        <!-- En cours -->
        <a href="/dashboard?status=<?= STATUS_OUT_FOR_DELIVERY ?><?= $q_param ?>" class="flex-1 min-w-[140px] flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3 rounded-xl shadow-sm hover:shadow-md transition-all border <?= ($statusFilter === STATUS_OUT_FOR_DELIVERY) ? 'bg-amber-100 border-amber-300 ring-1 ring-amber-300' : 'bg-amber-50 border-amber-200 hover:border-amber-300' ?>">
            <div class="flex items-center gap-2 min-w-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                <span class="text-[10px] sm:text-xs font-bold text-amber-700 uppercase tracking-wider truncate">En cours</span>
            </div>
            <div class="ml-2 text-sm sm:text-base font-bold text-amber-900 bg-white px-2 py-0.5 rounded-md shadow-sm border border-amber-100"><?= $stats['out_for_delivery'] ?></div>
        </a>

        <!-- Livrés -->
        <a href="/dashboard?status=<?= STATUS_DELIVERED ?><?= $q_param ?>" class="flex-1 min-w-[140px] flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3 rounded-xl shadow-sm hover:shadow-md transition-all border <?= ($statusFilter === STATUS_DELIVERED) ? 'bg-green-100 border-green-300 ring-1 ring-green-300' : 'bg-green-50 border-green-200 hover:border-green-300' ?>">
            <div class="flex items-center gap-2 min-w-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[10px] sm:text-xs font-bold text-green-700 uppercase tracking-wider truncate">Livrés</span>
            </div>
            <div class="ml-2 text-sm sm:text-base font-bold text-green-900 bg-white px-2 py-0.5 rounded-md shadow-sm border border-green-100"><?= $stats['delivered'] ?></div>
        </a>

        <!-- Retardés -->
        <a href="/dashboard?status=<?= STATUS_DELAYED ?><?= $q_param ?>" class="flex-1 min-w-[140px] flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3 rounded-xl shadow-sm hover:shadow-md transition-all border <?= ($statusFilter === STATUS_DELAYED) ? 'bg-red-100 border-red-300 ring-1 ring-red-300' : 'bg-red-50 border-red-200 hover:border-red-300' ?>">
            <div class="flex items-center gap-2 min-w-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-[10px] sm:text-xs font-bold text-red-700 uppercase tracking-wider truncate">Retardés</span>
            </div>
            <div class="ml-2 text-sm sm:text-base font-bold text-red-900 bg-white px-2 py-0.5 rounded-md shadow-sm border border-red-100"><?= $stats['delayed'] ?></div>
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-elevation-1 border border-slate-200 overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50">
        <h2 class="text-lg font-bold text-slate-800 flex items-center">
            <?php
            if ($statusFilter === STATUS_DELAYED) echo "Colis — Retardés";
            elseif ($statusFilter === STATUS_SHIPPED) echo "Colis — Expédiés";
            elseif ($statusFilter === STATUS_OUT_FOR_DELIVERY) echo "Colis — En cours";
            elseif ($statusFilter === STATUS_DELIVERED) echo "Colis — Livrés";
            else echo "Tous les colis";
            ?>
            <span class="text-sm font-normal text-slate-500 ml-2 bg-slate-200 px-2 py-0.5 rounded-full"><?= $totalResults ?></span>
            <?php if ($statusFilter || $q): ?>
                <a href="/dashboard" class="ml-4 text-xs font-normal text-slate-400 hover:text-action hover:underline">Effacer</a>
            <?php endif; ?>
        </h2>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <form method="GET" action="/dashboard" class="flex w-full sm:w-auto gap-2">
                <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= escape_html($statusFilter) ?>"><?php endif; ?>
                
                <div class="relative flex-grow sm:w-56 md:w-80">
                    <input type="text" name="q" value="<?= escape_html($q) ?>" placeholder="Numéro de suivis" class="w-full pl-3 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-action focus:border-action outline-none">
                </div>
                <button type="submit" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm">Chercher</button>
            </form>
            <a href="/shipments/new" class="bg-action text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 shadow-sm transition-all whitespace-nowrap w-full sm:w-auto text-center">Nouveau colis</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <th class="p-4 font-semibold">N° Suivi</th>
                    <th class="p-4 font-semibold">Destinataire</th>
                    <th class="p-4 font-semibold">Trajet</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold">Mise à jour</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shipments as $s): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors">
                    <td class="p-4 font-mono text-sm">
                        <a href="/shipments/<?= $s['id'] ?>" class="text-action hover:underline font-bold"><?= escape_html($s['tracking_number']) ?></a>
                        <?php if ($s['is_demo']): ?>
                        <span class="ml-2 text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md font-sans">Démo</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-sm text-slate-700"><?= escape_html(format_anonymous_name($s['recipient_name'])) ?></td>
                    <td class="p-4 text-sm text-slate-600"><?= escape_html($s['origin_city']) ?> &rarr; <span class="font-medium text-slate-800"><?= escape_html($s['city']) ?></span></td>
                    <td class="p-4 text-sm">
                        <?= render_status_badge($s['status']) ?>
                    </td>
                    <td class="p-4 text-sm text-slate-500"><?= format_date($s['updated_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($shipments)): ?>
                <tr>
                    <td colspan="5" class="p-12 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        <p class="text-slate-500 font-medium">Aucun colis trouvé.</p>
                        <?php if ($q || $statusFilter): ?>
                        <a href="/dashboard" class="text-action hover:underline text-sm mt-2 inline-block">Effacer les filtres</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between bg-white gap-4">
        <div class="text-sm text-slate-500">
            Affichage de <span class="font-medium text-slate-900"><?= $totalResults == 0 ? 0 : $offset + 1 ?></span> à <span class="font-medium text-slate-900"><?= min($offset + $limit, $totalResults) ?></span> sur <span class="font-medium text-slate-900"><?= $totalResults ?></span> résultats
        </div>
        <div class="flex gap-1 overflow-x-auto pb-1 max-w-full">
            <?php 
            $query_params = [];
            if ($q !== '') $query_params['q'] = $q;
            if ($statusFilter !== '') $query_params['status'] = $statusFilter;
            $base_qs = http_build_query($query_params);
            $base_url = "/dashboard" . ($base_qs ? "?$base_qs&" : "?");
            ?>
            
            <?php if ($page > 1): ?>
                <a href="<?= $base_url ?>page=<?= $page - 1 ?>" class="px-3 py-1.5 border border-slate-200 rounded text-sm text-slate-600 hover:bg-slate-50 whitespace-nowrap">Précédent</a>
            <?php else: ?>
                <span class="px-3 py-1.5 border border-slate-100 rounded text-sm text-slate-400 cursor-not-allowed whitespace-nowrap">Précédent</span>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i === $page) {
                    echo '<span class="px-3 py-1.5 border border-action bg-action text-white rounded text-sm font-medium whitespace-nowrap">'.$i.'</span>';
                } else {
                    echo '<a href="'.$base_url.'page='.$i.'" class="px-3 py-1.5 border border-slate-200 rounded text-sm text-slate-600 hover:bg-slate-50 whitespace-nowrap">'.$i.'</a>';
                }
            }
            ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="<?= $base_url ?>page=<?= $page + 1 ?>" class="px-3 py-1.5 border border-slate-200 rounded text-sm text-slate-600 hover:bg-slate-50 whitespace-nowrap">Suivant</a>
            <?php else: ?>
                <span class="px-3 py-1.5 border border-slate-100 rounded text-sm text-slate-400 cursor-not-allowed whitespace-nowrap">Suivant</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
