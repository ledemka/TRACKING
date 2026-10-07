<?php
require_once __DIR__ . '/api/bootstrap.php';

$ip = $_SERVER['REMOTE_ADDR'];
rate_limit_hit('track', $ip, 30, 300); // 30 requêtes par 5 minutes

$raw_id = $_GET['id'] ?? '';
$tracking_id = strtoupper(trim($raw_id));

$error = '';
$shipment = null;
$events = [];

if ($tracking_id) {
    if (!preg_match('/^[A-Z0-9-]{3,40}$/', $tracking_id)) {
        $error = "Numéro de suivi introuvable. Vérifiez votre numéro et réessayez.";
        http_response_code(404);
    } else {
        $stmt = $pdo->prepare("SELECT s.*, c.name as carrier_name FROM shipments s LEFT JOIN carriers c ON s.carrier_id = c.id WHERE s.tracking_number = ?");
        $stmt->execute([$tracking_id]);
        $shipment = $stmt->fetch();
        
        if (!$shipment) {
            $error = "Numéro de suivi introuvable. Vérifiez votre numéro et réessayez.";
            http_response_code(404);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM tracking_events WHERE shipment_id = ? ORDER BY occurred_at DESC");
            $stmt->execute([$shipment['id']]);
            $events = $stmt->fetchAll();
        }
    }
} else {
    header("Location: /");
    exit;
}

require_once __DIR__ . '/templates/public_header.php';

// Fonction d'aide pour la barre de statut (3 étapes)
function get_step_status($current_status, $step_status) {
    $order = [STATUS_SHIPPED => 1, STATUS_OUT_FOR_DELIVERY => 2, STATUS_DELIVERED => 3];
    $c = $order[$current_status] ?? 0;
    $s = $order[$step_status] ?? 0;
    if ($c === 3) return 'completed';
    if ($s < $c) return 'completed';
    if ($s == $c) return 'current';
    return 'pending';
}
?>

<main class="flex-grow flex flex-col py-12 px-4 bg-surface">
    <div class="max-w-3xl mx-auto w-full">
        
        <?php if ($error): ?>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 text-center">
                <div class="w-16 h-16 mx-auto bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 mb-2">Colis introuvable</h1>
                <p class="text-slate-600 mb-8"><?= escape_html($error) ?></p>
                
                <form action="/track" method="GET" class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                    <input type="text" name="id" placeholder="N° de suivi" class="flex-grow px-5 py-3 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none code-tracking text-center sm:text-left" required value="<?= escape_html($raw_id) ?>">
                    <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-800 transition-colors shadow-sm">
                        Rechercher
                    </button>
                </form>
            </div>
        <?php else: ?>
        
            <?php if ($shipment['is_demo']): ?>
                <!-- Bandeau information bleu pour demo -->
                <div class="bg-blue-50 text-blue-800 p-4 rounded-lg border border-blue-100 mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">Données de démonstration</span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="p-6 md:p-8 border-b border-slate-100">
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
                        <div>
                            <p class="text-sm text-slate-500 font-medium mb-1">Numéro de suivi</p>
                            <h1 class="text-2xl md:text-3xl font-bold text-primary code-tracking"><?= escape_html($shipment['tracking_number']) ?></h1>
                        </div>
                        <?php
                            $s_style = 'bg-slate-100 text-slate-600'; // default
                            $s_label = get_status_labels()[$shipment['status']] ?? 'Inconnu';
                            if ($shipment['status'] === STATUS_SHIPPED) $s_style = 'bg-status-shipped-bg text-status-shipped-text border border-status-shipped-bg';
                            if ($shipment['status'] === STATUS_OUT_FOR_DELIVERY) $s_style = 'bg-status-delivery-bg text-status-delivery-text border border-status-delivery-bg';
                            if ($shipment['status'] === STATUS_DELIVERED) $s_style = 'bg-status-delivered-bg text-status-delivered-text border border-status-delivered-bg';
                        ?>
                        <div class="inline-flex items-center justify-center px-4 py-2 rounded-full font-bold text-sm <?= $s_style ?> shadow-sm">
                            <?= escape_html($s_label) ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-xl border border-slate-100">
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Destinataire</p>
                            <p class="font-medium text-slate-800"><?= escape_html(mask_name($shipment['recipient_name'])) ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Transporteur</p>
                            <p class="font-medium text-slate-800"><?= escape_html($shipment['carrier_name'] ?: 'Non spécifié') ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Itinéraire</p>
                            <p class="font-medium text-slate-800 flex items-center gap-2">
                                <?= escape_html($shipment['origin_city']) ?>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                <?= escape_html($shipment['city']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Livraison estimée</p>
                            <p class="font-medium text-slate-800"><?= $shipment['estimated_delivery_at'] ? format_date($shipment['estimated_delivery_at'], 'd/m/Y') : 'Non estimée' ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Date d'expédition</p>
                            <p class="font-medium text-slate-800"><?= $shipment['shipped_at'] ? format_date($shipment['shipped_at'], 'd/m/Y') : 'Non expédié' ?></p>
                        </div>
                    </div>
                </div>

                <!-- Barre 3 étapes -->
                <div class="p-6 md:p-8 bg-slate-50 border-b border-slate-100">
                    <div class="relative max-w-lg mx-auto">
                        <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-200 -translate-y-1/2 rounded-full"></div>
                        <div class="relative flex justify-between">
                            <?php 
                            $steps = [
                                STATUS_SHIPPED => 'Expédié',
                                STATUS_OUT_FOR_DELIVERY => 'En cours',
                                STATUS_DELIVERED => 'Livré'
                            ];
                            foreach ($steps as $key => $label): 
                                $state = get_step_status($shipment['status'], $key);
                                $bgClass = $state === 'completed' ? 'bg-primary border-primary text-white' : ($state === 'current' ? 'bg-accent border-accent text-white' : 'bg-white border-slate-300 text-slate-400');
                            ?>
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-8 h-8 rounded-full border-2 <?= $bgClass ?> flex items-center justify-center z-10 transition-colors shadow-sm">
                                    <?php if ($state === 'completed'): ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <?php elseif ($state === 'current'): ?>
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    <?php endif; ?>
                                </div>
                                <span class="text-xs font-semibold <?= $state !== 'pending' ? 'text-slate-800' : 'text-slate-400' ?>"><?= escape_html($label) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Historique (Timeline) -->
                <div class="p-6 md:p-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-6">Historique d'expédition</h3>
                    
                    <?php if (empty($events)): ?>
                        <p class="text-slate-500 text-center py-4">Aucun événement enregistré.</p>
                    <?php else: ?>
                        <div class="relative pl-4 space-y-8 before:absolute before:inset-y-2 before:left-[15px] before:w-0.5 before:bg-slate-100">
                            <?php foreach ($events as $index => $event): 
                                $isLast = ($index === 0); // Ordre DESC, donc index 0 = le plus récent = point coloré
                                $dotClass = $isLast ? 'bg-primary shadow-[0_0_0_4px_rgba(11,31,58,0.1)]' : 'bg-slate-300';
                            ?>
                                <div class="relative pl-8">
                                    <div class="absolute left-[-5px] top-1.5 w-3 h-3 rounded-full <?= $dotClass ?>"></div>
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1">
                                        <div>
                                            <p class="font-bold <?= $isLast ? 'text-slate-800' : 'text-slate-600' ?>"><?= escape_html($event['label']) ?></p>
                                            <?php if ($event['location']): ?>
                                                <p class="text-sm text-slate-500 mt-1 flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    <?= escape_html($event['location']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-slate-400 font-medium whitespace-nowrap pt-1">
                                            <?= format_date($event['occurred_at'], 'd M Y à H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="text-center mt-6">
                 <a href="/" class="text-sm font-semibold text-primary hover:text-accent transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Faire une nouvelle recherche
                 </a>
            </div>
            
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
