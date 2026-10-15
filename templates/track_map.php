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

$current_transport_mode = $shipment['transport_mode'] ?? TRANSPORT_MODE_LAND;
$current_transport_label = get_transport_mode_label($current_transport_mode);

// Libellé de trajet restant adapté au mode de transport pour ne pas induire en erreur sur les routes réelles
if ($current_transport_mode === TRANSPORT_MODE_AIR) {
    $remaining_legend_text = __('map.remaining_air');
    $distance_disclaimer = __('map.disclaimer_air');
} elseif ($current_transport_mode === TRANSPORT_MODE_SEA) {
    $remaining_legend_text = __('map.remaining_sea');
    $distance_disclaimer = __('map.disclaimer_sea');
} else {
    $remaining_legend_text = __('map.remaining_land');
    $distance_disclaimer = __('map.disclaimer_land');
}

// Ligne de distances : uniquement si la destination est connue.
$distance_parts = [];
if ($map_dest_lat !== null) {
    if ($map_show_traveled && $map_traveled_km >= 1) {
        $distance_parts[] = __('map.distance_traveled', ['distance' => $map_traveled_km]);
    }
    if ($map_show_remaining) {
        $distance_parts[] = __('map.distance_remaining', ['distance' => $map_remaining_km]);
    }
}
?>

<div class="mb-12 border border-slate-100 rounded-2xl overflow-hidden shadow-sm bg-white">
    <div class="p-5 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <div>
            <h2 class="text-lg font-bold text-slate-800">
                <?= $has_current_pos ? escape_html(__('map.last_known_position')) : escape_html(__('map.destination')) ?>
            </h2>
            <p class="text-sm font-medium text-slate-600">
                <?= escape_html($has_current_pos ? $map_last_location_name : ($shipment['city'] ?? '')) ?>
                <span class="inline-block text-xs font-semibold text-slate-500 ml-2 px-2 py-0.5 rounded bg-white border border-slate-200">
                    <?= escape_html(__('track.transport_prefix') . ' ' . mb_strtolower($current_transport_label)) ?>
                </span>
            </p>
        </div>
        <?php if ($has_current_pos && $map_last_updated): ?>
        <div class="text-sm text-slate-500 font-semibold bg-white px-3 py-1.5 rounded-lg border border-slate-200">
            <?= escape_html(__('map.updated_at', ['date' => format_date($map_last_updated, 'd M Y à H:i')])) ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="track-map-container" 
         class="w-full h-64 sm:h-80 bg-slate-100 relative focus:outline-none focus:ring-2 focus:ring-action" 
         tabindex="0"
         aria-label="<?= __attr('map.last_known_position') ?>"
         data-lat="<?= $attr_lat ?>" 
         data-lng="<?= $attr_lng ?>" 
         data-has-current="<?= $has_current_pos ? '1' : '0' ?>"
         data-trail="<?= $trail_json ?>"
         data-delivered="<?= $map_is_delivered ? '1' : '0' ?>"
         data-transport-mode="<?= escape_html($current_transport_mode) ?>"
         data-transport-label="<?= escape_html($current_transport_label) ?>"
         data-origin-title="<?= __attr('map.origin_title') ?>"
         data-delivered-title="<?= __attr('map.delivered_title') ?>"
         data-indicative-trail="<?= __attr('map.indicative_trail') ?>"
         data-current-position-template="<?= __attr('map.current_position') ?>"
<?php if ($map_show_remaining): ?>
         data-dest-lat="<?= sprintf('%.2F', $map_dest_lat) ?>"
         data-dest-lng="<?= sprintf('%.2F', $map_dest_lng) ?>"
         data-dest-label="<?= __attr('track.destination_label') ?> : <?= escape_html($shipment['city'] ?? '') ?>"
<?php endif; ?>
         data-tile-url="<?= escape_html($map_tile_url) ?>"
         data-attribution="<?= escape_html($map_attribution) ?>">
        <noscript>
            <p class="w-full h-full flex items-center justify-center p-4 text-center text-sm text-slate-600">
                <?= escape_html(__('map.noscript')) ?>
            </p>
        </noscript>
    </div>

    <div class="px-5 py-4 bg-slate-50 border-t border-slate-100">
        <ul class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-700" aria-label="<?= __attr('map.traveled_route') ?>">
<?php if ($map_show_traveled): ?>
            <li class="flex items-center gap-2">
                <span class="track-map-legend-line track-map-legend-line--traveled" aria-hidden="true"></span>
                <?= escape_html(__('map.traveled_route')) ?>
            </li>
<?php endif; ?>
<?php if ($map_show_remaining): ?>
            <li class="flex items-center gap-2">
                <span class="track-map-legend-line track-map-legend-line--remaining" aria-hidden="true"></span>
                <?= escape_html($remaining_legend_text) ?>
            </li>
<?php endif; ?>
            <li class="flex items-center gap-2">
                <span class="track-map-legend-dot" aria-hidden="true"></span>
                <?= escape_html(__('map.current_position', ['mode' => mb_strtolower($current_transport_label)])) ?>
            </li>
        </ul>
<?php if ($distance_parts): ?>
        <p class="mt-2 text-xs font-semibold text-slate-500">
            <?= escape_html(implode(' · ', $distance_parts)) ?> (<?= escape_html($distance_disclaimer) ?>)
        </p>
<?php endif; ?>
        <p class="mt-3 text-center">
            <a href="<?= escape_html($osm_link) ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-action hover:text-blue-800 transition-colors">
                <?= escape_html(__('map.view_osm')) ?>
            </a>
        </p>
    </div>
</div>

<link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css">
<script src="/assets/vendor/leaflet/leaflet.js" defer></script>
<script src="/assets/js/track-map.js" defer></script>
