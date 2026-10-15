document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('track-map-container');
    if (!mapContainer) return;

    // Protection contre l'absence de Leaflet : la page reste lisible (légende serveur).
    if (typeof L === 'undefined') {
        console.warn('Leaflet non chargé.');
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const escapeHtml = (str) => {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    };

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

    // Icon templates
    const createSvgIcon = (bgColor, svgPath) => L.divIcon({
        className: 'custom-map-icon',
        html: `<div style="width:28px;height:28px;background-color:${bgColor};border-radius:50%;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;">
                  <svg width="14" height="14" fill="white" viewBox="0 0 24 24"><path d="${svgPath}"/></svg>
               </div>`,
        iconSize: [28, 28],
        iconAnchor: [14, 28] // point towards the bottom center
    });

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
    
    // Icône Origine (Départ)
    if (trail.length > 0) {
        // Home icon path
        const originTitle = (window.I18N && window.I18N['map.origin_title']) 
            || mapContainer.dataset.originTitle 
            || "Départ";
        const originIcon = createSvgIcon('var(--color-primary, #0B1F3A)', 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z');
        L.marker(trail[0], { icon: originIcon, title: originTitle }).addTo(map);
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

        // Pin icon path
        const destIcon = createSvgIcon(remainingColor, 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z');
        const tooltip = document.createElement('span');
        tooltip.textContent = mapContainer.dataset.destLabel || '';
        L.marker([destLat, destLng], { icon: destIcon, title: tooltip.textContent })
            .bindTooltip(tooltip, { direction: 'top', offset: [0, -28] })
            .addTo(map);
        boundsPoints.push([destLat, destLng]);
    }

    // Position actuelle : pastille accent, icône selon le mode de transport, anneau pulsant sauf si Livré
    const hasCurrent = mapContainer.dataset.hasCurrent !== '0';
    if (hasCurrent) {
        const transportMode = mapContainer.dataset.transportMode || 'land';
        const transportLabel = mapContainer.dataset.transportLabel || 'Terrestre';
        const deliveredText = (window.I18N && window.I18N['map.delivered_title']) 
            || mapContainer.dataset.deliveredTitle 
            || 'Colis livré';
        const currentPosTemplate = (window.I18N && window.I18N['map.current_position']) 
            || mapContainer.dataset.currentPositionTemplate 
            || 'Position actuelle (:mode)';
        const currentPosLabel = currentPosTemplate.replace(':mode', transportLabel);
        const indicativeTrailText = (window.I18N && window.I18N['map.indicative_trail']) 
            || mapContainer.dataset.indicativeTrail 
            || "Tracé indicatif à vol d'oiseau";

        // Chemins SVG Material selon le mode de transport
        const iconPaths = {
            air: 'M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z', // Avion
            land: 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z', // Camion
            sea: 'M20 21c-1.39 0-2.78-.47-4-1.32-2.44 1.71-5.56 1.71-8 0C6.78 20.53 5.39 21 4 21H2v2h2c1.38 0 2.74-.35 4-.99 2.52 1.29 5.48 1.29 8 0 1.26.65 2.62.99 4 .99h2v-2h-2zM3.95 19H4c1.6 0 3.02-.88 4-2 .98 1.12 2.4 2 4 2s3.02-.88 4-2c.98 1.12 2.4 2 4 2h.05l1.89-6.68c.08-.26.06-.54-.06-.78s-.34-.42-.6-.47L20 13l-1.45-4.34c-.18-.54-.69-.91-1.26-.91H15V5c0-1.1-.9-2-2-2h-2c-1.1 0-2 .9-2 2v2.75L6.64 8.76c-.57.14-1 .61-1.14 1.18L4.04 13l-1.28.27c-.26.05-.48.23-.6.47s-.14.52-.06.78L3.95 19zM11 5h2v3h-2V5z' // Bateau
        };

        const currentPath = isDelivered 
            ? 'M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z' // Checkmark
            : (iconPaths[transportMode] || iconPaths.land);
        
        const animationStyle = isDelivered ? '' : 'animation: pulse 1.5s ease-in-out infinite;';
        
        const currentIcon = L.divIcon({
            className: 'custom-map-icon',
            html: `<div style="width:34px;height:34px;background-color:var(--color-accent, #F59E0B);border-radius:50%;border:4px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;position:relative;z-index:1000;${animationStyle}">
                      <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="${currentPath}"/></svg>
                   </div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 17] // Center anchor for current position
        });
        
        const currentMarker = L.marker([lat, lng], { 
            icon: currentIcon, 
            keyboard: false, 
            zIndexOffset: 1000,
            title: isDelivered ? deliveredText : currentPosLabel
        }).addTo(map);

        // Infobulle informative sans ambiguïté sur le caractère indicatif du tracé
        const tooltip = document.createElement('div');
        tooltip.style.fontSize = '12px';
        tooltip.style.fontWeight = '600';
        tooltip.innerHTML = isDelivered 
            ? escapeHtml(deliveredText)
            : `${escapeHtml(currentPosLabel)}<div style="font-size:10px;font-weight:normal;opacity:0.85;">${escapeHtml(indicativeTrailText)}</div>`;
        currentMarker.bindTooltip(tooltip, { direction: 'top', offset: [0, -20] });
    } else {
        // Just center map on destination (lat/lng will be set to dest in PHP)
        boundsPoints.push([lat, lng]);
    }

    if (boundsPoints.length > 1 || !hasCurrent) {
        map.fitBounds(L.latLngBounds(boundsPoints), {
            padding: [40, 40],
            animate: !prefersReducedMotion
        });
    }
});
