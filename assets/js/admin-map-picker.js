// admin-map-picker.js
// Logique Leaflet cliquable pour l'admin
document.addEventListener('DOMContentLoaded', () => {
    const mapContainers = document.querySelectorAll('.admin-map-picker');
    
    mapContainers.forEach(container => {
        const mapEl = container.querySelector('.map-element');
        const latInput = container.querySelector('.input-lat');
        const lngInput = container.querySelector('.input-lng');
        const clearBtn = container.querySelector('.btn-clear-map');
        
        if (!mapEl || !latInput || !lngInput) return;
        
        let map = L.map(mapEl).setView([48.8566, 2.3522], 5);
        
        // La tile layer utilise les variables injectées
        L.tileLayer(window.MAP_TILE_URL || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: window.MAP_MAX_ZOOM || 19,
            attribution: window.MAP_ATTRIBUTION || '&copy; OpenStreetMap'
        }).addTo(map);
        
        let marker = null;
        
        function updateMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
            map.setView([lat, lng]);
        }
        
        // Initial state
        if (latInput.value && lngInput.value) {
            updateMarker(parseFloat(latInput.value), parseFloat(lngInput.value));
        }
        
        // Click on map
        map.on('click', (e) => {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            latInput.value = lat;
            lngInput.value = lng;
            updateMarker(lat, lng);
        });
        
        // Manual input
        latInput.addEventListener('change', () => {
            if (latInput.value && lngInput.value) {
                updateMarker(parseFloat(latInput.value), parseFloat(lngInput.value));
            }
        });
        
        lngInput.addEventListener('change', () => {
            if (latInput.value && lngInput.value) {
                updateMarker(parseFloat(latInput.value), parseFloat(lngInput.value));
            }
        });
        
        // Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                latInput.value = '';
                lngInput.value = '';
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
            });
        }
    });
});
