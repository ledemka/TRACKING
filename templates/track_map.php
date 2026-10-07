<?php
// templates/track_map.php

$map_tile_url = getenv('MAP_TILE_URL') ?: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
$map_attribution = getenv('MAP_ATTRIBUTION') ?: "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors";

$trail_json = htmlspecialchars(json_encode($map_trail_points), ENT_QUOTES, 'UTF-8');
$osm_link = "https://www.openstreetmap.org/?mlat={$map_center_lat}&mlon={$map_center_lng}#map=15/{$map_center_lat}/{$map_center_lng}";
?>

<div class="mb-12 border border-slate-100 rounded-2xl overflow-hidden shadow-sm bg-white">
    <div class="p-5 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Dernière position connue</h2>
            <p class="text-sm font-medium text-slate-600">
                <?= escape_html($map_last_location_name) ?>
            </p>
        </div>
        <div class="text-sm text-slate-500 font-semibold bg-white px-3 py-1.5 rounded-lg border border-slate-200">
            Mise à jour : <?= format_date($map_last_updated, 'd M Y à H:i') ?>
        </div>
    </div>
    
    <div id="track-map-container" 
         class="w-full h-64 sm:h-80 bg-slate-100 relative focus:outline-none focus:ring-2 focus:ring-action" 
         tabindex="0"
         aria-label="Carte affichant la dernière position connue du colis"
         data-lat="<?= escape_html($map_center_lat) ?>" 
         data-lng="<?= escape_html($map_center_lng) ?>" 
         data-trail="<?= $trail_json ?>"
         data-tile-url="<?= escape_html($map_tile_url) ?>"
         data-attribution="<?= escape_html($map_attribution) ?>">
         
        <noscript>
            <div class="w-full h-full flex items-center justify-center bg-slate-100">
                <a href="<?= escape_html($osm_link) ?>" target="_blank" rel="noopener noreferrer" class="text-action font-bold hover:underline">
                    Voir sur OpenStreetMap
                </a>
            </div>
        </noscript>
    </div>
    
    <div class="p-3 bg-slate-50 text-center border-t border-slate-100">
        <a href="<?= escape_html($osm_link) ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-action hover:text-blue-800 transition-colors">
            Ouvrir dans OpenStreetMap
        </a>
    </div>
</div>

<link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css">
<script src="/assets/vendor/leaflet/leaflet.js" defer></script>
<script src="/assets/js/track-map.js" defer></script>
