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

$page_title = 'Suivi d\'expédition en direct';
$page_description = "Accédez au suivi en direct de votre colis : localisation sur carte, avancement des étapes et historique des événements.";
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

<main class="flex-grow flex flex-col py-10 sm:py-14 px-4 sm:px-6 bg-[#F4F3ED] track-page-container">
    <div class="max-w-4xl mx-auto w-full">
        
        <!-- Fil d'Ariane épuré -->
        <nav class="mb-6 flex items-center gap-2 text-xs font-semibold text-[#6B6B6B]" aria-label="Fil d'Ariane">
            <a href="/" class="hover:text-[#171717] transition-colors focus-brand-light rounded">Accueil</a>
            <span class="text-slate-400" aria-hidden="true">/</span>
            <span class="text-[#171717]" aria-current="page">Suivi d'expédition</span>
        </nav>

        <?php if (!$shipment): ?>
            <!-- ÉTAT INITIAL OU ERREUR DE RECHERCHE -->
            <section class="max-w-2xl mx-auto text-center py-6 sm:py-10">
                <!-- Badge repère visuel de marque -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#171717] text-white text-xs font-bold tracking-wide mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#FFE500]" aria-hidden="true"></span>
                    <span>SUIVI EN TEMPS RÉEL</span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#171717] tracking-tight mb-4">
                    Suivez votre expédition
                </h1>
                <p class="text-[#6B6B6B] text-base sm:text-lg mb-8 max-w-lg mx-auto">
                    Entrez votre référence de colis pour accéder en direct à la localisation, aux étapes et à l'historique d'acheminement.
                </p>
                
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl font-medium text-sm flex items-center justify-center gap-2 shadow-sm" role="alert">
                        <svg class="w-5 h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span><?= escape_html($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Carte de recherche stylisée -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-black/5 shadow-sm">
                    <form id="track-form" action="/track" method="GET" class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-grow relative text-left">
                            <label for="tracking_id" class="sr-only">Numéro de suivi</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </span>
                                <input type="text" 
                                       name="id" 
                                       id="tracking_id"
                                       placeholder="Ex. COLIS-2026-00125" 
                                       class="w-full pl-12 pr-4 py-4 rounded-xl border <?= $empty_id_error ? 'border-red-500 focus:border-red-500 ring-2 ring-red-100' : 'border-slate-200 focus:border-[#171717] focus:ring-2 focus:ring-[#FFE500]' ?> outline-none code-tracking text-base sm:text-lg font-medium text-[#171717] bg-[#F4F3ED]/40 transition-all placeholder:text-slate-400"
                                       value="<?= escape_html($raw_id ?? '') ?>"
                                       aria-describedby="format-help <?= $empty_id_error ? 'empty-error' : '' ?>"
                                       <?= ($error || $empty_id_error) ? 'autofocus' : '' ?>>
                            </div>
                            <?php if ($empty_id_error): ?>
                                <p id="empty-error" class="text-red-600 text-xs sm:text-sm font-semibold mt-2 flex items-center gap-1.5" role="alert">
                                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span><?= escape_html($empty_id_error) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>
                        <button type="submit" id="track-submit-btn" class="bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] px-7 py-4 rounded-xl font-bold transition-all shadow-sm text-base flex items-center justify-center min-w-[170px] focus-brand-light">
                            <span id="track-submit-text" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Rechercher</span>
                            </span>
                        </button>
                    </form>
                    <p id="format-help" class="text-[#6B6B6B] text-xs mt-4">Format : lettres majuscules, chiffres et tirets</p>
                    <div id="track-status-container" class="mt-2 text-[#171717] font-semibold text-sm h-6"></div>
                </div>
            </section>
        <?php else: ?>
            <!-- ÉTAT DE RÉSULTAT DU SUIVI -->
            <section class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-black/5 p-6 sm:p-10 animate-fade-in">
                
                <!-- En-tête de carte logistique -->
                <header class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8 pb-6 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#6B6B6B] mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#FFE500]" aria-hidden="true"></span>
                            <span>Expédition certifiée</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                            Colis <span class="code-tracking text-[#171717] bg-[#F4F3ED] px-3 py-1 rounded-lg border border-black/5 text-xl sm:text-2xl"><?= escape_html($shipment['tracking_number']) ?></span>
                        </h1>
                    </div>
                    <div class="self-start sm:self-center">
                        <?= render_status_badge($shipment['status']) ?>
                    </div>
                </header>
                
                <?php if ($shipment['status'] === STATUS_DELAYED): ?>
                    <div class="mb-8 p-4 sm:p-5 bg-red-50 border border-red-200 text-red-800 rounded-xl font-bold flex items-center gap-3 shadow-sm">
                        <svg class="w-6 h-6 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Votre colis est retardé. Nos équipes mettent tout en œuvre pour son acheminement.</span>
                    </div>
                <?php endif; ?>
                
                <!-- Grille Informations Publiques -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-[#F4F3ED] p-5 sm:p-6 rounded-2xl border border-black/5 mb-8">
                    <div class="bg-white p-4 rounded-xl border border-black/5">
                        <p class="text-xs text-[#6B6B6B] font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#171717]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Destinataire</span>
                        </p>
                        <p class="font-bold text-[#171717] text-base"><?= escape_html(mask_name($shipment['recipient_name'])) ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-black/5">
                        <p class="text-xs text-[#6B6B6B] font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#171717]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Trajet</span>
                        </p>
                        <p class="font-bold text-[#171717] text-base flex items-center gap-2 flex-wrap">
                            <span><?= escape_html($shipment['origin_city']) ?></span>
                            <svg class="w-4 h-4 text-[#FFE500] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            <span><?= escape_html($shipment['city']) ?></span>
                        </p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-black/5">
                        <p class="text-xs text-[#6B6B6B] font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#171717]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Expédition</span>
                        </p>
                        <p class="font-bold text-[#171717] text-base"><?= $shipment['shipped_at'] ? format_date($shipment['shipped_at'], 'd M Y') : 'Non expédié' ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-black/5">
                        <p class="text-xs text-[#6B6B6B] font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#171717]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Livraison estimée</span>
                        </p>
                        <p class="font-bold text-[#171717] text-base"><?= $shipment['estimated_delivery_at'] ? format_date($shipment['estimated_delivery_at'], 'd M Y') : 'Non estimée' ?></p>
                    </div>
                </div>

                <?php if ($map_center_lat !== null || $map_dest_lat !== null): ?>
                    <?php require_once __DIR__ . '/templates/track_map.php'; ?>
                <?php endif; ?>

                <!-- Stepper de Progression Moderne -->
                <div class="mb-12 pt-2 animate-slide-up-stepper">
                    <div class="text-center mb-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B]">Progression de l'acheminement</p>
                    </div>
                    <ol class="relative flex justify-between w-full max-w-2xl mx-auto before:absolute before:top-4 before:left-6 before:right-6 before:h-1 before:bg-slate-200 before:z-0">
                        <?php 
                        $steps = [
                            STATUS_SHIPPED => 'Expédié',
                            STATUS_OUT_FOR_DELIVERY => 'En cours de livraison',
                            STATUS_DELIVERED => 'Livré'
                        ];
                        foreach ($steps as $key => $label): 
                            $step_state = get_step_status_label($progressed_status, $key);
                            if ($step_state === 'Terminé') {
                                $circleClass = 'bg-[#171717] border-[#171717] text-white shadow-sm';
                                $textClass = 'text-[#171717] font-bold';
                            } elseif ($step_state === 'En cours') {
                                $circleClass = 'bg-[#FFE500] border-[#171717] text-[#171717] ring-4 ring-[#FFE500]/30 animate-pulse-subtle shadow-md';
                                $textClass = 'text-[#171717] font-extrabold';
                            } else {
                                $circleClass = 'bg-white border-slate-300 text-slate-300';
                                $textClass = 'text-slate-400 font-medium';
                            }
                        ?>
                        <li class="relative z-10 flex flex-col items-center gap-2.5 bg-white px-2 sm:px-4" <?= $step_state === 'En cours' ? 'aria-current="step"' : '' ?>>
                            <div class="w-8 h-8 rounded-full border-2 <?= $circleClass ?> flex items-center justify-center transition-all">
                                <?php if ($step_state === 'Terminé'): ?>
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <?php elseif ($step_state === 'En cours'): ?>
                                    <span class="w-2.5 h-2.5 bg-[#171717] rounded-full" aria-hidden="true"></span>
                                <?php else: ?>
                                    <span class="w-2 h-2 bg-slate-200 rounded-full" aria-hidden="true"></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-center flex flex-col max-w-[110px] sm:max-w-none">
                                <span class="sr-only"><?= escape_html($step_state) ?> :</span>
                                <span class="text-xs sm:text-sm <?= $textClass ?>"><?= escape_html($label) ?></span>
                                <span class="text-[11px] text-[#6B6B6B] font-medium hidden sm:block"><?= escape_html($step_state) ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>

                <!-- Historique du Suivi -->
                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#171717]" aria-hidden="true"></span>
                            <h2 class="text-xl font-extrabold text-[#171717]">Historique des événements</h2>
                        </div>
                        <span class="text-xs font-semibold text-[#6B6B6B] bg-[#F4F3ED] px-3 py-1 rounded-full border border-black/5">
                            <?= count($events) ?> <?= count($events) > 1 ? 'mises à jour' : 'mise à jour' ?>
                        </span>
                    </div>
                    
                    <?php if (empty($events)): ?>
                        <p class="text-[#6B6B6B] py-5 bg-[#F4F3ED] rounded-xl px-5 text-sm font-medium">Aucun événement enregistré pour le moment.</p>
                    <?php else: ?>
                        <div class="relative pl-6 space-y-6 before:absolute before:inset-y-2 before:left-[11px] before:w-0.5 before:bg-slate-200 animate-slide-up-timeline">
                            <?php foreach ($events as $index => $event): 
                                $isLast = ($index === 0);
                                $dotClass = $isLast ? 'bg-[#FFE500] border-2 border-[#171717] ring-4 ring-[#FFE500]/25' : 'bg-slate-300';
                            ?>
                                <div class="relative pl-6">
                                    <div class="absolute left-[-17px] top-1.5 w-3.5 h-3.5 rounded-full <?= $dotClass ?>" aria-hidden="true"></div>
                                    <div class="p-4 rounded-xl border <?= $isLast ? 'bg-white border-[#171717]/15 shadow-sm' : 'bg-[#F4F3ED]/60 border-black/5' ?> flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                                        <div>
                                            <p class="font-bold text-base sm:text-lg <?= $isLast ? 'text-[#171717]' : 'text-slate-700' ?>"><?= escape_html($event['label']) ?></p>
                                            <?php if ($event['location']): ?>
                                                <p class="text-xs sm:text-sm text-[#6B6B6B] mt-1.5 flex items-center gap-1.5 font-medium">
                                                    <svg class="w-4 h-4 text-[#171717] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span><?= escape_html($event['location']) ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs sm:text-sm text-[#6B6B6B] font-semibold sm:text-right pt-0.5 bg-white px-3 py-1 rounded-lg border border-black/5 whitespace-nowrap self-start">
                                            <?= format_date($event['occurred_at'], 'd M Y à H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Action pour rechercher un autre colis -->
                <div class="mt-12 text-center pt-8 border-t border-slate-100">
                     <a href="/track" class="inline-flex items-center gap-2 px-6 py-3.5 bg-[#171717] text-white hover:bg-black rounded-xl font-bold transition-all shadow-sm focus-brand-light">
                        <svg class="w-4 h-4 text-[#FFE500]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Rechercher un autre colis</span>
                     </a>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script src="/assets/js/track.js" defer></script>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
