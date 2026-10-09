<?php
require_once __DIR__ . '/api/bootstrap.php';

$ip = $_SERVER['REMOTE_ADDR'];
rate_limit_hit('track', $ip, 30, 300); // 30 requêtes par 5 minutes

$raw_id = $_GET['id'] ?? null;
$tracking_id = $raw_id !== null ? strtoupper(trim($raw_id)) : '';

$error = '';
$empty_id_error = '';
$shipment = null;
$events = [];

// Détermination des états :
// 1. $raw_id === null : État initial (pas de ?id=)
// 2. $raw_id !== null mais vide : Erreur champ vide
// 3. $raw_id fourni mais invalide : Erreur 404 "Numéro de suivi introuvable..."
// 4. Valide : Affichage

if ($raw_id !== null) {
    if ($tracking_id === '') {
        $empty_id_error = "Veuillez renseigner votre numéro de suivi.";
    } elseif (!preg_match('/^[A-Z0-9-]{3,40}$/', $tracking_id)) {
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
            
            $map_center_lat = null;
            $map_center_lng = null;
            $map_last_location_name = '';
            $map_last_updated = '';
            $map_trail_points = [];
            
            foreach ($events as $e) {
                if ($e['latitude'] !== null && $e['longitude'] !== null) {
                    $lat = (float)$e['latitude'];
                    $lng = (float)$e['longitude'];
                    if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                        array_unshift($map_trail_points, [$lat, $lng]);
                        if ($map_center_lat === null) {
                            $map_center_lat = $lat;
                            $map_center_lng = $lng;
                            $map_last_location_name = $e['location'] ?: $e['label'];
                            $map_last_updated = $e['occurred_at'];
                        }
                    }
                }
            }
            if (count($map_trail_points) > 50) {
                $map_trail_points = array_slice($map_trail_points, -50);
            }

            // Trajet parcouru : au moins 2 points localisés, sinon aucun trait ni distance.
            $map_traveled_km = 0;
            $map_show_traveled = count($map_trail_points) >= 2;
            if ($map_show_traveled) {
                for ($i = 1, $n = count($map_trail_points); $i < $n; $i++) {
                    $map_traveled_km += haversine_km($map_trail_points[$i - 1][0], $map_trail_points[$i - 1][1], $map_trail_points[$i][0], $map_trail_points[$i][1]);
                }
            }
            $map_traveled_km = (int) round($map_traveled_km);
            if (!$map_show_traveled) {
                $map_trail_points = [];
            }

            // Destination : validée, arrondie à 2 décimales pour la sortie publique.
            $map_is_delivered = ($shipment['status'] === STATUS_DELIVERED);
            $map_dest_lat = null;
            $map_dest_lng = null;
            $map_remaining_km = 0;
            $map_show_remaining = false;
            $raw_dest_lat = $shipment['destination_lat'] ?? null;
            $raw_dest_lng = $shipment['destination_lng'] ?? null;
            if (is_numeric($raw_dest_lat) && is_numeric($raw_dest_lng)) {
                $d_lat = (float) $raw_dest_lat;
                $d_lng = (float) $raw_dest_lng;
                if ($d_lat >= -90 && $d_lat <= 90 && $d_lng >= -180 && $d_lng <= 180) {
                    $map_dest_lat = round($d_lat, 2);
                    $map_dest_lng = round($d_lng, 2);
                    
                    if ($map_center_lat !== null) {
                        $dist = haversine_km($map_center_lat, $map_center_lng, $map_dest_lat, $map_dest_lng);
                        if (!$map_is_delivered && $dist > 1) {
                            $map_show_remaining = true;
                            $map_remaining_km = (int) round($dist);
                        }
                    }
                }
            }
            // Calculate progressed status for the stepper
            $progressed_status = STATUS_SHIPPED;
            if ($shipment['status'] === STATUS_DELAYED) {
                foreach ($events as $e) {
                    if (in_array($e['status'], [STATUS_SHIPPED, STATUS_OUT_FOR_DELIVERY, STATUS_DELIVERED])) {
                        $progressed_status = $e['status'];
                        break;
                    }
                }
            } else {
                $progressed_status = $shipment['status'];
            }
        }
    }
}

/**
 * Distance à vol d'oiseau (km) entre deux points, formule de haversine.
 */
function haversine_km(float $lat1, float $lng1, float $lat2, float $lng2): float {
    $r = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    return 2 * $r * asin(min(1.0, sqrt($a)));
}

require_once __DIR__ . '/templates/public_header.php';

function get_step_status_label($progressed_status, $step_status) {
    $order = [STATUS_SHIPPED => 1, STATUS_OUT_FOR_DELIVERY => 2, STATUS_DELIVERED => 3];
    $p = $order[$progressed_status] ?? 1;
    $s = $order[$step_status] ?? 0;
    $is_delayed = (($GLOBALS['shipment']['status'] ?? '') === STATUS_DELAYED);
    if ($p === 3 && !$is_delayed) return 'Terminé';
    if ($s < $p) return 'Terminé';
    if ($s == $p) return 'En cours';
    return 'À venir';
}
?>

<main class="flex-grow flex flex-col py-12 px-4 bg-surface track-page-container">
    <div class="max-w-4xl mx-auto w-full">
        
        <?php if (!$shipment): ?>
            <!-- ÉTAT INITIAL OU ERREUR -->
            <section class="text-center mb-12">
                <h1 class="text-3xl font-bold text-primary mb-3">Suivez votre colis</h1>
                <p class="text-slate-500 mb-8">Entrez votre numéro de suivi pour connaître l'avancement de votre expédition.</p>
                
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg font-medium" role="alert">
                        <?= escape_html($error) ?>
                    </div>
                <?php endif; ?>

                <form id="track-form" action="/track" method="GET" class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                    <div class="flex-grow relative">
                        <input type="text" 
                               name="id" 
                               id="tracking_id"
                               placeholder="Numéro de suivi" 
                               class="w-full px-5 py-4 rounded-xl border <?= $empty_id_error ? 'border-red-500' : 'border-slate-300' ?> focus:border-action focus:ring-2 focus:ring-action/20 outline-none code-tracking text-lg shadow-sm transition-shadow"
                               value="<?= escape_html($raw_id ?? '') ?>"
                               aria-describedby="format-help <?= $empty_id_error ? 'empty-error' : '' ?>"
                               <?= ($error || $empty_id_error) ? 'autofocus' : '' ?>>
                        <?php if ($empty_id_error): ?>
                            <p id="empty-error" class="absolute -bottom-6 left-0 text-red-600 text-sm" role="alert"><?= escape_html($empty_id_error) ?></p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" id="track-submit-btn" class="bg-action text-white px-8 py-4 rounded-xl font-bold hover:bg-blue-700 transition-colors whitespace-nowrap shadow-sm text-lg flex items-center justify-center min-w-[160px]">
                        <span id="track-submit-text">Rechercher</span>
                    </button>
                </form>
                <p id="format-help" class="text-slate-400 text-sm mt-8">Lettres, chiffres et tirets</p>
                <div id="track-status-container" class="mt-4 text-primary font-medium h-6"></div>
            </section>
        <?php else: ?>
            <!-- ÉTAT DE RÉSULTAT -->
            <section class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 animate-fade-in">
                
                <!-- En-tête de carte -->
                <header class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8 pb-6 border-b border-slate-100">
                    <div>
                        <h1 class="text-2xl font-bold text-primary">Colis <span class="code-tracking"><?= escape_html($shipment['tracking_number']) ?></span></h1>
                    </div>
                    <div class="scale-125 origin-right">
                        <?= render_status_badge($shipment['status']) ?>
                    </div>
                </header>
                
                <?php if ($shipment['status'] === STATUS_DELAYED): ?>
                    <div class="mb-8 p-4 bg-status-delayed-bg border border-status-delayed-border text-status-delayed-text rounded-xl font-bold flex items-center gap-3">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Votre colis est retardé.</span>
                    </div>
                <?php endif; ?>
                
                <!-- Grille Informations Publiques -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-8">
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Destinataire</p>
                        <p class="font-medium text-slate-800"><?= escape_html(mask_name($shipment['recipient_name'])) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Trajet</p>
                        <p class="font-medium text-slate-800 flex items-center gap-2">
                            <?= escape_html($shipment['origin_city']) ?>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            <?= escape_html($shipment['city']) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Date d'expédition</p>
                        <p class="font-medium text-slate-800"><?= $shipment['shipped_at'] ? format_date($shipment['shipped_at'], 'd M Y') : 'Non expédié' ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Livraison estimée</p>
                        <p class="font-medium text-slate-800"><?= $shipment['estimated_delivery_at'] ? format_date($shipment['estimated_delivery_at'], 'd M Y') : 'Non estimée' ?></p>
                    </div>
                </div>

                <?php if ($map_center_lat !== null || $map_dest_lat !== null): ?>
                    <?php require_once __DIR__ . '/templates/track_map.php'; ?>
                <?php endif; ?>

                <!-- Stepper de Progression -->
                <div class="mb-12 animate-slide-up-stepper">
                    <ol class="relative flex justify-between w-full max-w-2xl mx-auto before:absolute before:top-4 before:left-0 before:w-full before:h-1 before:bg-slate-200 before:-z-10 rounded-full">
                        <?php 
                        $steps = [
                            STATUS_SHIPPED => 'Expédié',
                            STATUS_OUT_FOR_DELIVERY => 'En cours de livraison',
                            STATUS_DELIVERED => 'Livré'
                        ];
                        foreach ($steps as $key => $label): 
                            $step_state = get_step_status_label($progressed_status, $key);
                            $bgClass = $step_state === 'Terminé' ? 'bg-primary border-primary text-white' : ($step_state === 'En cours' ? 'bg-accent border-accent text-white animate-pulse-subtle' : 'bg-white border-slate-300 text-slate-300');
                        ?>
                        <li class="flex flex-col items-center gap-3 bg-white px-2" <?= $step_state === 'En cours' ? 'aria-current="step"' : '' ?>>
                            <div class="w-8 h-8 rounded-full border-2 <?= $bgClass ?> flex items-center justify-center transition-colors shadow-sm">
                                <?php if ($step_state === 'Terminé'): ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <?php elseif ($step_state === 'En cours'): ?>
                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-center flex flex-col">
                                <span class="sr-only"><?= escape_html($step_state) ?> :</span>
                                <span class="text-xs font-bold <?= $step_state !== 'À venir' ? 'text-primary' : 'text-slate-400' ?>"><?= escape_html($label) ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>

                <!-- Historique du Suivi -->
                <div>
                    <h3 class="text-xl font-bold text-primary mb-6">Historique du suivi</h3>
                    
                    <?php if (empty($events)): ?>
                        <p class="text-slate-500 py-4 bg-slate-50 rounded-xl px-4">Aucun événement enregistré pour le moment.</p>
                    <?php else: ?>
                        <div class="relative pl-6 space-y-8 before:absolute before:inset-y-2 before:left-[11px] before:w-0.5 before:bg-slate-200 animate-slide-up-timeline">
                            <?php foreach ($events as $index => $event): 
                                $isLast = ($index === 0);
                                $dotClass = $isLast ? 'bg-primary shadow-[0_0_0_4px_rgba(11,31,58,0.1)]' : 'bg-slate-300';
                            ?>
                                <div class="relative pl-6">
                                    <div class="absolute left-[-17px] top-1.5 w-3 h-3 rounded-full <?= $dotClass ?>"></div>
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                                        <div>
                                            <p class="font-bold text-lg <?= $isLast ? 'text-primary' : 'text-slate-700' ?>"><?= escape_html($event['label']) ?></p>
                                            <?php if ($event['location']): ?>
                                                <p class="text-sm text-slate-500 mt-2 flex items-center gap-1 font-medium">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    <?= escape_html($event['location']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-slate-500 font-semibold sm:text-right pt-1 bg-slate-50 px-3 py-1 rounded-md">
                                            <?= format_date($event['occurred_at'], 'd M Y à H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-12 text-center pt-8 border-t border-slate-100">
                     <a href="/track" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-primary rounded-xl font-bold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Rechercher un autre colis
                     </a>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script src="/assets/js/track.js" defer></script>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
