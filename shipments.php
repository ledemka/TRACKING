<?php
require_once __DIR__ . '/api/bootstrap.php';
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

<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Gestion des colis</h1>
        <p class="text-slate-500 mt-1">Gérez, recherchez et suivez l'ensemble de vos expéditions.</p>
    </div>
    <a href="/shipments/new" class="bg-action text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 shadow-elevation-1 transition-all whitespace-nowrap">Nouveau colis</a>
</div>

<form method="GET" action="/shipments" class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-8 flex flex-col lg:flex-row gap-4 lg:items-end">
    <div class="flex-grow">
        <label for="q" class="block text-sm font-semibold text-slate-700 mb-1.5">Recherche</label>
        <div class="relative">
            <input type="text" name="q" id="q" value="<?= escape_html($q) ?>" placeholder="Numéro de suivis" class="w-full pl-3 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-action focus:border-action outline-none transition-colors">
        </div>
    </div>
    <div class="w-full lg:w-56">
        <label for="status" class="block text-sm font-semibold text-slate-700 mb-1.5">Statut</label>
        <select name="status" id="status" class="w-full border border-slate-200 p-2 rounded-lg text-sm focus:ring-2 focus:ring-action focus:border-action outline-none bg-white">
            <option value="">Tous les statuts</option>
            <?php foreach(get_status_labels() as $k => $v): ?>
                <option value="<?= $k ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= escape_html($v) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-center gap-3 w-full lg:w-auto pt-2 lg:pt-0">
        <button type="submit" class="flex-1 lg:flex-none bg-action text-white px-6 py-2 rounded-lg text-sm font-semibold hover:opacity-90 shadow-sm transition-all text-center">Filtrer</button>
        <?php if ($q || $statusFilter): ?>
            <a href="/shipments" class="text-sm text-slate-500 hover:text-action hover:underline transition-colors px-2 whitespace-nowrap">Réinitialiser</a>
        <?php endif; ?>
    </div>
</form>

<div class="bg-white rounded-xl shadow-elevation-1 border border-slate-200 overflow-hidden mb-8">
    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h2 class="text-lg font-bold text-slate-800">Colis</h2>
        <span class="text-sm font-medium text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm"><?= $total ?> résultats</span>
    </div>
    
    <div class="overflow-x-auto hidden md:block">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <th class="p-4 font-semibold">N° Suivi</th>
                    <th class="p-4 font-semibold">Destinataire</th>
                    <th class="p-4 font-semibold">Trajet</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($shipments as $s): ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="p-4 font-mono text-sm">
                        <a href="/shipments/<?= $s['id'] ?>" class="text-action hover:underline font-bold"><?= escape_html($s['tracking_number']) ?></a>
                        <?php if ($s['is_demo']): ?>
                        <span class="ml-2 text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md font-sans">Démo</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-sm text-slate-700 font-medium"><?= escape_html(format_anonymous_name($s['recipient_name'])) ?></td>
                    <td class="p-4 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <span><?= escape_html($s['origin_city']) ?></span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            <span class="font-medium text-slate-800"><?= escape_html($s['city']) ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-sm">
                        <?= render_status_badge($s['status']) ?>
                    </td>
                    <td class="p-4 text-sm flex gap-3 justify-end items-center">
                        <a href="/shipments/<?= $s['id'] ?>" class="text-slate-500 hover:text-action transition-colors flex items-center gap-1 font-medium" aria-label="Modifier le colis <?= escape_html($s['tracking_number']) ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Modifier
                        </a>
                        <button class="text-slate-400 hover:text-red-600 transition-colors flex items-center gap-1 btn-delete-prompt font-medium" data-dialog="delete-dialog-<?= $s['id'] ?>" aria-label="Supprimer le colis <?= escape_html($s['tracking_number']) ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Supprimer
                        </button>
                        
                        <dialog id="delete-dialog-<?= $s['id'] ?>" class="p-6 rounded-xl shadow-2xl border-0 backdrop:bg-slate-800/60 w-full max-w-md">
                            <h3 class="text-xl font-bold mb-2 text-slate-800">Supprimer le colis ?</h3>
                            <p class="mb-6 text-slate-500 text-sm">Le colis <strong class="font-mono text-slate-700"><?= escape_html($s['tracking_number']) ?></strong> et tout son historique seront effacés définitivement.</p>
                            <form method="POST" action="/shipments" class="delete-form-js-modal">
                                <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                                <label class="flex items-center gap-3 mb-8 p-3 bg-red-50 rounded-lg border border-red-100 cursor-pointer">
                                    <input type="checkbox" name="confirm_delete" required class="confirm-checkbox w-4 h-4 text-red-600 bg-white border-red-300 rounded focus:ring-red-500">
                                    <span class="text-sm font-bold text-red-800">Je confirme la suppression</span>
                                </label>
                                <div class="flex justify-end gap-3">
                                    <button type="button" class="btn-close-dialog px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition-colors">Annuler</button>
                                    <button type="submit" class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 shadow-sm transition-colors">Supprimer</button>
                                </div>
                            </form>
                        </dialog>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($shipments)): ?>
                <tr>
                    <td colspan="5" class="p-12 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        <p class="text-slate-500 font-medium">Aucun colis ne correspond à vos critères.</p>
                        <?php if ($q || $statusFilter): ?>
                        <a href="/shipments" class="text-action hover:underline text-sm mt-2 inline-block">Effacer les filtres</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile view -->
    <div class="md:hidden flex flex-col">
        <?php foreach($shipments as $s): ?>
        <div class="p-4 border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition-colors">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <a href="/shipments/<?= $s['id'] ?>" class="font-mono text-action font-bold hover:underline text-sm"><?= escape_html($s['tracking_number']) ?></a>
                    <?php if ($s['is_demo']): ?>
                    <span class="ml-2 text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-md font-sans uppercase font-bold tracking-wider">Démo</span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <?= render_status_badge($s['status']) ?>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-2 mb-4">
                <div class="flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-slate-700 font-medium truncate"><?= escape_html(format_anonymous_name($s['recipient_name'])) ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="truncate"><?= escape_html($s['origin_city']) ?> <span class="text-slate-400 mx-1">&rarr;</span> <strong class="text-slate-800"><?= escape_html($s['city']) ?></strong></span>
                </div>
            </div>
            
            <div class="flex gap-4 pt-3 border-t border-slate-100">
                <a href="/shipments/<?= $s['id'] ?>" class="flex-1 flex items-center justify-center gap-2 bg-slate-50 text-slate-700 hover:bg-slate-100 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Modifier
                </a>
                <button class="flex-1 flex items-center justify-center gap-2 bg-red-50 text-red-600 hover:bg-red-100 px-3 py-2 rounded-lg text-sm font-medium transition-colors btn-delete-prompt" data-dialog="delete-dialog-mob-<?= $s['id'] ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Supprimer
                </button>
                
                <dialog id="delete-dialog-mob-<?= $s['id'] ?>" class="p-5 rounded-xl shadow-2xl border-0 backdrop:bg-slate-800/60 w-[calc(100%-2rem)] max-w-sm m-auto">
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Supprimer ce colis ?</h3>
                    <p class="mb-5 text-slate-500 text-sm">Action irréversible.</p>
                    <form method="POST" action="/shipments" class="delete-form-js-modal">
                        <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                        <label class="flex items-center gap-3 mb-6 p-3 bg-red-50 rounded-lg border border-red-100">
                            <input type="checkbox" name="confirm_delete" required class="confirm-checkbox w-4 h-4 text-red-600 border-red-300 rounded">
                            <span class="text-sm font-bold text-red-800">Je confirme</span>
                        </label>
                        <div class="flex flex-col gap-2">
                            <button type="submit" class="w-full py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">Supprimer</button>
                            <button type="button" class="w-full py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 btn-close-dialog">Annuler</button>
                        </div>
                    </form>
                </dialog>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($shipments)): ?>
        <div class="p-8 text-center bg-slate-50 border-t border-slate-100">
            <p class="text-slate-500 font-medium text-sm">Aucun colis trouvé.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between bg-slate-50/50 gap-4">
        <div class="text-sm text-slate-500">
            Affichage de <span class="font-medium text-slate-900"><?= $total == 0 ? 0 : $offset + 1 ?></span> à <span class="font-medium text-slate-900"><?= min($offset + $limit, $total) ?></span> sur <span class="font-medium text-slate-900"><?= $total ?></span>
        </div>
        <div class="flex gap-1 overflow-x-auto pb-1 max-w-full">
            <?php 
            $query_params = [];
            if ($q !== '') $query_params['q'] = $q;
            if ($statusFilter !== '') $query_params['status'] = $statusFilter;
            $base_qs = http_build_query($query_params);
            $base_url = "/shipments" . ($base_qs ? "?$base_qs&" : "?");
            ?>
            
            <?php if ($page > 1): ?>
                <a href="<?= $base_url ?>page=<?= $page - 1 ?>" class="px-3 py-1.5 border border-slate-200 rounded text-sm text-slate-600 bg-white hover:bg-slate-50 whitespace-nowrap shadow-sm">Précédent</a>
            <?php else: ?>
                <span class="px-3 py-1.5 border border-slate-100 rounded text-sm text-slate-400 bg-slate-50 cursor-not-allowed whitespace-nowrap">Précédent</span>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i === $page) {
                    echo '<span class="px-3 py-1.5 border border-action bg-action text-white rounded text-sm font-medium shadow-sm whitespace-nowrap">'.$i.'</span>';
                } else {
                    echo '<a href="'.$base_url.'page='.$i.'" class="px-3 py-1.5 border border-slate-200 rounded text-sm text-slate-600 bg-white hover:bg-slate-50 shadow-sm whitespace-nowrap">'.$i.'</a>';
                }
            }
            ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="<?= $base_url ?>page=<?= $page + 1 ?>" class="px-3 py-1.5 border border-slate-200 rounded text-sm text-slate-600 bg-white hover:bg-slate-50 whitespace-nowrap shadow-sm">Suivant</a>
            <?php else: ?>
                <span class="px-3 py-1.5 border border-slate-100 rounded text-sm text-slate-400 bg-slate-50 cursor-not-allowed whitespace-nowrap">Suivant</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
