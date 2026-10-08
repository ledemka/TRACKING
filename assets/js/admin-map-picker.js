// admin-map-picker.js
// Logique Leaflet cliquable pour l'admin avec Autocomplétion
document.addEventListener('DOMContentLoaded', () => {
    const mapContainers = document.querySelectorAll('.admin-map-picker');
    
    mapContainers.forEach(container => {
        const mapEl = container.querySelector('.map-element');
        const latInput = container.querySelector('.input-lat');
        const lngInput = container.querySelector('.input-lng');
        const clearBtn = container.querySelector('.btn-clear-map');
        
        // Cibles pour l'autocomplétion
        const targetAddress = container.dataset.targetAddress ? document.querySelector(container.dataset.targetAddress) : null;
        const targetZip = container.dataset.targetZip ? document.querySelector(container.dataset.targetZip) : null;
        const targetCity = container.dataset.targetCity ? document.querySelector(container.dataset.targetCity) : null;
        const targetCountry = container.dataset.targetCountry ? document.querySelector(container.dataset.targetCountry) : null;
        
        if (!mapEl || !latInput || !lngInput) return;
        
        let map = L.map(mapEl).setView([48.8566, 2.3522], 5);
        
        // Add geocoder search UI if API key is present
        if (window.HERE_BROWSER_API_KEY) {
            const searchContainer = document.createElement('div');
            searchContainer.className = 'relative mb-4 z-50';
            searchContainer.innerHTML = `
                <input type="text" class="border p-3 rounded w-full map-search-input shadow-sm" placeholder="Commencez à taper une adresse pour la rechercher...">
                <ul class="absolute z-[9999] bg-white border border-slate-200 w-full rounded shadow-lg mt-1 hidden map-search-results max-h-60 overflow-y-auto"></ul>
            `;
            container.insertBefore(searchContainer, mapEl);
            
            const searchInput = searchContainer.querySelector('.map-search-input');
            const resultsList = searchContainer.querySelector('.map-search-results');
            let debounceTimer;

            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                const query = searchInput.value.trim();
                
                if (query.length < 3) {
                    resultsList.classList.add('hidden');
                    return;
                }
                
                debounceTimer = setTimeout(() => {
                    fetch(`https://autocomplete.search.hereapi.com/v1/autocomplete?q=${encodeURIComponent(query)}&apiKey=${window.HERE_BROWSER_API_KEY}&limit=5`)
                        .then(res => res.json())
                        .then(data => {
                            resultsList.innerHTML = '';
                            if (data.items && data.items.length > 0) {
                                data.items.forEach(item => {
                                    const li = document.createElement('li');
                                    li.className = 'p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0';
                                    li.innerHTML = `<strong>${item.title}</strong>`;
                                    li.addEventListener('click', () => {
                                        resultsList.classList.add('hidden');
                                        searchInput.value = item.title;
                                        lookupAddress(item.id);
                                    });
                                    resultsList.appendChild(li);
                                });
                                resultsList.classList.remove('hidden');
                            } else {
                                resultsList.classList.add('hidden');
                            }
                        })
                        .catch(err => console.error(err));
                }, 300);
            });

            document.addEventListener('click', (e) => {
                if (!searchContainer.contains(e.target)) {
                    resultsList.classList.add('hidden');
                }
            });

            function lookupAddress(id) {
                fetch(`https://lookup.search.hereapi.com/v1/lookup?id=${id}&apiKey=${window.HERE_BROWSER_API_KEY}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.position) {
                            const lat = data.position.lat.toFixed(6);
                            const lng = data.position.lng.toFixed(6);
                            latInput.value = lat;
                            lngInput.value = lng;
                            updateMarker(lat, lng);
                            
                            // Auto-fill form fields
                            if (data.address) {
                                const addr = [];
                                if (data.address.houseNumber) addr.push(data.address.houseNumber);
                                if (data.address.street) addr.push(data.address.street);
                                
                                if (targetAddress && addr.length > 0) targetAddress.value = addr.join(' ');
                                if (targetZip && data.address.postalCode) targetZip.value = data.address.postalCode;
                                if (targetCity && data.address.city) targetCity.value = data.address.city;
                                if (targetCountry && data.address.countryName) targetCountry.value = data.address.countryName;
                            }
                        }
                    })
                    .catch(err => console.error("Erreur de lookup", err));
            }
        }
        
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
            map.setView([lat, lng], 13);
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
            // Reverse geocoding on map click
            if (window.HERE_BROWSER_API_KEY) {
                fetch(`https://revgeocode.search.hereapi.com/v1/revgeocode?at=${lat},${lng}&apiKey=${window.HERE_BROWSER_API_KEY}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.items && data.items.length > 0) {
                            const addr = data.items[0].address;
                            const streetParts = [];
                            if (addr.houseNumber) streetParts.push(addr.houseNumber);
                            if (addr.street) streetParts.push(addr.street);
                            
                            if (targetAddress && streetParts.length > 0) targetAddress.value = streetParts.join(' ');
                            if (targetZip && addr.postalCode) targetZip.value = addr.postalCode;
                            if (targetCity && addr.city) targetCity.value = addr.city;
                            if (targetCountry && addr.countryName) targetCountry.value = addr.countryName;
                            
                            const searchInput = container.querySelector('.map-search-input');
                            if (searchInput) searchInput.value = data.items[0].title;
                        }
                    })
                    .catch(err => console.error("Reverse geocoding err:", err));
            }
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
