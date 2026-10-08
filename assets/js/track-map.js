document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('track-map-container');
    if (!mapContainer) return;

    // Protection contre l'absence de Leaflet : la page reste lisible (légende serveur).
    if (typeof L === 'undefined') {
        console.warn('Leaflet non chargé.');
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Lecture et validation des valeurs numériques
    const toCoord = (value, max) => {
        const n = parseFloat(value);
        return Number.isFinite(n) && n >= -max && n <= max ? n : null;
    };
    const isPoint = (p) => Array.isArray(p) && p.length === 2
        && toCoord(p[0], 90) !== null && toCoord(p[1], 180) !== null;

    const lat = toCoord(mapContainer.dataset.lat, 90);
    const lng = toCoord(mapContainer.dataset.lng, 180);
    if (lat === null || lng === null) return;

    let trail = [];
    try {
        const parsed = JSON.parse(mapContainer.dataset.trail || '[]');
        if (Array.isArray(parsed)) trail = parsed.filter(isPoint);
    } catch (e) {
        trail = [];
    }

    const destLat = toCoord(mapContainer.dataset.destLat, 90);
    const destLng = toCoord(mapContainer.dataset.destLng, 180);
    const hasDestination = destLat !== null && destLng !== null;
    const isDelivered = mapContainer.dataset.delivered === '1';

    // Couleurs lues depuis les variables CSS (aucune couleur en dur ici)
    const rootStyles = getComputedStyle(document.documentElement);
    const traveledColor = rootStyles.getPropertyValue('--map-traveled').trim();
    const remainingColor = rootStyles.getPropertyValue('--map-remaining').trim();

    const map = L.map(mapContainer, {
        scrollWheelZoom: false,
        zoomControl: true,
        fadeAnimation: !prefersReducedMotion,
        zoomAnimation: !prefersReducedMotion,
        markerZoomAnimation: !prefersReducedMotion
    }).setView([lat, lng], 13);

    L.tileLayer(mapContainer.dataset.tileUrl, {
        maxZoom: 18,
        attribution: mapContainer.dataset.attribution
    }).addTo(map);

    const boundsPoints = [[lat, lng]];

    // Trajet parcouru : trait plein
    if (trail.length > 1) {
        L.polyline(trail, {
            color: traveledColor,
            weight: 4,
            opacity: 0.9,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(map);
        boundsPoints.push(...trail);
    }

    // Trajet restant (à vol d'oiseau) : pointillés + marqueur de destination
    if (hasDestination && !isDelivered) {
        L.polyline([[lat, lng], [destLat, destLng]], {
            color: remainingColor,
            weight: 3,
            opacity: 0.9,
            dashArray: '6 8',
            lineCap: 'round'
        }).addTo(map);

        const destIcon = L.divIcon({
            className: 'track-map-destination',
            html: '',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });
        const tooltip = document.createElement('span');
        tooltip.textContent = mapContainer.dataset.destLabel || '';
        L.marker([destLat, destLng], { icon: destIcon, title: tooltip.textContent })
            .bindTooltip(tooltip, { direction: 'top', offset: [0, -8] })
            .addTo(map);
        boundsPoints.push([destLat, destLng]);
    }

    // Position actuelle : pastille accent, anneau pulsant sauf si Livré
    const currentIcon = L.divIcon({
        className: isDelivered ? 'track-map-marker track-map-marker--static' : 'track-map-marker',
        html: '',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });
    L.marker([lat, lng], { icon: currentIcon, keyboard: false, zIndexOffset: 1000 }).addTo(map);

    if (boundsPoints.length > 1) {
        map.fitBounds(L.latLngBounds(boundsPoints), {
            padding: [40, 40],
            animate: !prefersReducedMotion
        });
    }
});
