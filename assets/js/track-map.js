document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('track-map-container');
    if (!mapContainer) return;

    // Protection contre l'absence de Leaflet
    if (typeof L === 'undefined') {
        console.warn('Leaflet non chargé.');
        return;
    }

    // Récupérer la préférence de mouvement réduit
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const lat = parseFloat(mapContainer.getAttribute('data-lat'));
    const lng = parseFloat(mapContainer.getAttribute('data-lng'));
    const tileUrl = mapContainer.getAttribute('data-tile-url');
    const attribution = mapContainer.getAttribute('data-attribution');
    
    let trail = [];
    try {
        trail = JSON.parse(mapContainer.getAttribute('data-trail') || '[]');
    } catch (e) {
        console.error('Erreur de parsing data-trail', e);
    }

    if (isNaN(lat) || isNaN(lng)) return;

    // Initialisation
    const map = L.map('track-map-container', {
        scrollWheelZoom: false, // Désactivé pour l'accessibilité mobile/desktop
        zoomControl: true, // Toujours actif
        fadeAnimation: !prefersReducedMotion,
        zoomAnimation: !prefersReducedMotion,
        markerZoomAnimation: !prefersReducedMotion
    }).setView([lat, lng], 13);

    L.tileLayer(tileUrl, {
        maxZoom: 18,
        attribution: attribution
    }).addTo(map);

    // Marqueur personnalisé via classe CSS
    const customIcon = L.divIcon({
        className: 'track-map-marker',
        html: '',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    L.marker([lat, lng], { icon: customIcon }).addTo(map);

    // Tracé de l'historique
    if (trail.length > 1) {
        const rootStyles = getComputedStyle(document.documentElement);
        const trailColor = rootStyles.getPropertyValue('--map-trail-color').trim() || '#1d4ed8';

        const polyline = L.polyline(trail, {
            color: trailColor,
            weight: 3,
            dashArray: '5, 10',
            opacity: 0.6,
            lineCap: 'round'
        }).addTo(map);
        
        // Ajustement de la vue pour englober tout le trajet
        if (!prefersReducedMotion) {
            map.fitBounds(polyline.getBounds(), { padding: [50, 50], animate: true });
        } else {
            map.fitBounds(polyline.getBounds(), { padding: [50, 50], animate: false });
        }
    }
});
