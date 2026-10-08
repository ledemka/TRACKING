<?php
// templates/track_map.php

$map_tile_url = getenv('MAP_TILE_URL') ?: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
$map_attribution = getenv('MAP_ATTRIBUTION') ?: "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors";

$has_current_pos = ($map_center_lat !== null && $map_center_lng !== null);

// Valeurs numériques formatées explicitement
$attr_lat = $has_current_pos ? sprintf('%.6F', $map_center_lat) : sprintf('%.6F', $map_dest_lat);
$attr_lng = $has_current_pos ? sprintf('%.6F', $map_center_lng) : sprintf('%.6F', $map_dest_lng);
$trail_json = htmlspecialchars(json_encode($map_trail_points), ENT_QUOTES, 'UTF-8');
$osm_link = "https://www.openstreetmap.org/?mlat={$attr_lat}&mlon={$attr_lng}#map=15/{$attr_lat}/{$attr_lng}";

// Ligne de distances : uniquement si la destination est connue.
$distance_parts = [];
if ($map_dest_lat !== null) {
    if ($map_show_traveled && $map_traveled_km >= 1) {
        $distance_parts[] = 'Parcouru ≈ ' . $map_traveled_km . ' km';
    }
    if ($map_show_remaining) {
        $distance_parts[] = 'Restant ≈ ' . $map_remaining_km . ' km';
    }
}
?>

<div class="mb-12 border border-slate-100 rounded-2xl overflow-hidden shadow-sm bg-white">
    <div class="p-5 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <div>
            <h2 class="text-lg font-bold text-slate-800">
                <?= $has_current_pos ? 'Dernière position connue' : 'Destination' ?>
            </h2>
            <p class="text-sm font-medium text-slate-600">
                <?= escape_html($has_current_pos ? $map_last_location_name : ($shipment['city'] ?? '')) ?>
            </p>
        </div>
        <?php if ($has_current_pos && $map_last_updated): ?>
        <div class="text-sm text-slate-500 font-semibold bg-white px-3 py-1.5 rounded-lg border border-slate-200">
            Mise à jour : <?= format_date($map_last_updated, 'd M Y à H:i') ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="track-map-container" 
         class="w-full h-64 sm:h-80 bg-slate-100 relative focus:outline-none focus:ring-2 focus:ring-action" 
         tabindex="0"
         aria-label="Carte affichant la position du colis"
         data-lat="<?= $attr_lat ?>" 
         data-lng="<?= $attr_lng ?>" 
         data-has-current="<?= $has_current_pos ? '1' : '0' ?>"
         data-trail="<?= $trail_json ?>"
         data-delivered="<?= $map_is_delivered ? '1' : '0' ?>"
<?php if ($map_show_remaining): ?>
         data-dest-lat="<?= sprintf('%.2F', $map_dest_lat) ?>"
         data-dest-lng="<?= sprintf('%.2F', $map_dest_lng) ?>"
         data-dest-label="<?= escape_html('Destination : ' . ($shipment['city'] ?? '')) ?>"
<?php endif; ?>
         data-tile-url="<?= escape_html($map_tile_url) ?>"
         data-attribution="<?= escape_html($map_attribution) ?>">
        <noscript>
            <p class="w-full h-full flex items-center justify-center p-4 text-center text-sm text-slate-600">
                La carte nécessite JavaScript. La position et la légende restent indiquées ci-dessus et ci-dessous.
            </p>
        </noscript>
    </div>

    <div class="px-5 py-4 bg-slate-50 border-t border-slate-100">
        <ul class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-700" aria-label="Légende de la carte">
<?php if ($map_show_traveled): ?>
            <li class="flex items-center gap-2">
                <span class="track-map-legend-line track-map-legend-line--traveled" aria-hidden="true"></span>
                Trajet parcouru
            </li>
<?php endif; ?>
<?php if ($map_show_remaining): ?>
            <li class="flex items-center gap-2">
                <span class="track-map-legend-line track-map-legend-line--remaining" aria-hidden="true"></span>
                Trajet restant (estimation à vol d'oiseau)
            </li>
<?php endif; ?>
            <li class="flex items-center gap-2">
                <span class="track-map-legend-dot" aria-hidden="true"></span>
                Position actuelle
            </li>
        </ul>
<?php if ($distance_parts): ?>
        <p class="mt-2 text-sm font-semibold text-slate-600"><?= escape_html(implode(' · ', $distance_parts)) ?> (à vol d'oiseau)</p>
<?php endif; ?>
        <p class="mt-3 text-center">
            <a href="<?= escape_html($osm_link) ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-action hover:text-blue-800 transition-colors">
                Voir sur OpenStreetMap
            </a>
        </p>
    </div>
</div>

<link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css">
<script src="/assets/vendor/leaflet/leaflet.js" defer></script>
<script src="/assets/js/track-map.js" defer></script>
