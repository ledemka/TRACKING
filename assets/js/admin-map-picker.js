// admin-map-picker.js
// Logique Leaflet cliquable pour l'admin avec barre de recherche & autocomplétion
document.addEventListener('DOMContentLoaded', () => {
    const mapContainers = document.querySelectorAll('.admin-map-picker');
    
    // Fournisseur : 'osm' par défaut (OpenStreetMap / Nominatim), ou 'here' si configuré explicitement
    const forcedProvider = (window.GEOCODING_PROVIDER || '').toLowerCase().trim();
    const rawHereKey = window.HERE_BROWSER_API_KEY;
    const preferHere = (forcedProvider === 'here') && Boolean(rawHereKey && String(rawHereKey).trim() !== '' && rawHereKey !== 'null' && rawHereKey !== 'false');

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

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
        const targetLocation = container.dataset.targetLocation ? document.querySelector(container.dataset.targetLocation) : null;
        
        if (!mapEl || !latInput || !lngInput) return;
        
        // 1. Barre de recherche d'autocomplétion (positionnement explicite avec Vanilla CSS)
        const i18n = window.I18N || {};
        const searchPlaceholder = i18n['js.search_placeholder'] || "Rechercher une adresse ou une ville (ex: 19 Boulevard, Paris)...";
        const clearSearchTitle = i18n['js.clear_search'] || "Effacer la recherche";

        const searchContainer = document.createElement('div');
        searchContainer.className = 'map-search-container';
        searchContainer.style.cssText = 'position: relative; margin-bottom: 12px; z-index: 1001; width: 100%;';
        searchContainer.innerHTML = `
            <div style="position: relative; display: flex; align-items: center; width: 100%;">
                <span class="material-symbols-outlined" style="position: absolute; left: 12px; color: #94a3b8; pointer-events: none; font-size: 20px; user-select: none;">search</span>
                <input type="text" class="map-search-input" style="width: 100%; box-sizing: border-box; padding: 10px 36px 10px 38px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #ffffff; color: #1e293b; outline: none; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);" placeholder="${escapeHtml(searchPlaceholder)}">
                <button type="button" class="btn-clear-search" style="position: absolute; right: 10px; display: none; background: transparent; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 9999px; line-height: 1;" title="${escapeHtml(clearSearchTitle)}">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">close</span>
                </button>
            </div>
            <ul class="map-search-results" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; width: 100%; box-sizing: border-box; z-index: 99999; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.18), 0 6px 12px -2px rgba(0, 0, 0, 0.08); max-height: 250px; overflow-y: auto; margin: 0; padding: 0; list-style: none;"></ul>
        `;
        container.insertBefore(searchContainer, mapEl);

        const searchInput = searchContainer.querySelector('.map-search-input');
        const clearSearchBtn = searchContainer.querySelector('.btn-clear-search');
        const resultsList = searchContainer.querySelector('.map-search-results');
        let debounceTimer = null;

        // Effets focus sur l'input
        searchInput.addEventListener('focus', () => {
            searchInput.style.borderColor = '#171717';
            searchInput.style.boxShadow = '0 0 0 2px #FFE500';
        });
        searchInput.addEventListener('blur', () => {
            searchInput.style.borderColor = '#cbd5e1';
            searchInput.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
        });

        // 2. Initialisation Leaflet
        const initialLat = latInput.value ? parseFloat(latInput.value) : 48.8566;
        const initialLng = lngInput.value ? parseFloat(lngInput.value) : 2.3522;
        const initialZoom = (latInput.value && lngInput.value) ? 13 : 5;

        const map = L.map(mapEl).setView([initialLat, initialLng], initialZoom);
        
        L.tileLayer(window.MAP_TILE_URL || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: window.MAP_MAX_ZOOM || 19,
            attribution: window.MAP_ATTRIBUTION || '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        let marker = null;
        
        function updateMarker(lat, lng) {
            const numLat = parseFloat(lat);
            const numLng = parseFloat(lng);
            if (isNaN(numLat) || isNaN(numLng)) return;

            if (marker) {
                marker.setLatLng([numLat, numLng]);
            } else {
                marker = L.marker([numLat, numLng]).addTo(map);
            }
            map.setView([numLat, numLng], 13);
        }
        
        // État initial si coordonnées présentes
        if (latInput.value && lngInput.value) {
            updateMarker(latInput.value, lngInput.value);
        }

        // Appliquer les données aux champs cibles
        function applyAddressData(data) {
            if (targetAddress && data.street) targetAddress.value = data.street;
            if (targetZip && data.zip) targetZip.value = data.zip;
            if (targetCity && data.city) targetCity.value = data.city;
            if (targetCountry && data.country) targetCountry.value = data.country;
            if (targetLocation && data.location) targetLocation.value = data.location;
        }

        // Extraction adresse OpenStreetMap (Nominatim)
        function parseNominatimAddress(item) {
            const addr = item.address || {};
            const streetParts = [];
            if (addr.house_number) streetParts.push(addr.house_number);
            if (addr.road) streetParts.push(addr.road);
            
            let street = streetParts.join(' ');
            if (!street && item.display_name) {
                street = item.display_name.split(',')[0].trim();
            }
            
            const zip = addr.postcode || '';
            const city = addr.city || addr.town || addr.village || addr.municipality || addr.city_district || '';
            const country = addr.country || '';
            const location = city || street || country || '';

            return { street, zip, city, country, location };
        }

        function showResults() {
            resultsList.style.display = 'block';
        }

        function hideResults() {
            resultsList.style.display = 'none';
        }

        // Recherche Nominatim (OpenStreetMap - Sans clé, immédiat et universel)
        function searchNominatim(query) {
            const searchingText = i18n['js.searching'] || 'Recherche en cours...';
            const noResultsText = i18n['js.no_results'] || 'Aucune adresse trouvée';
            const connErrorText = i18n['js.conn_error'] || 'Erreur de connexion cartographique';

            resultsList.innerHTML = `<li style="padding: 12px; font-size: 13px; color: #64748b; font-style: italic;">${escapeHtml(searchingText)}</li>`;
            showResults();

            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`;
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(items => {
                    resultsList.innerHTML = '';
                    if (Array.isArray(items) && items.length > 0) {
                        items.forEach(item => {
                            const parsed = parseNominatimAddress(item);
                            const mainText = parsed.street || item.display_name.split(',')[0];
                            const subText = item.display_name;
                            
                            const li = document.createElement('li');
                            li.style.cssText = 'padding: 10px 14px; cursor: pointer; display: flex; align-items: flex-start; gap: 10px; border-bottom: 1px solid #f1f5f9; background: #ffffff; transition: background 0.15s ease; text-align: left;';
                            li.innerHTML = `
                                <span class="material-symbols-outlined" style="color: #94a3b8; font-size: 18px; margin-top: 2px; flex-shrink: 0; user-select: none;">location_on</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p style="font-weight: 600; color: #1e293b; font-size: 14px; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(mainText)}</p>
                                    <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(subText)}</p>
                                </div>
                            `;
                            li.addEventListener('mouseenter', () => li.style.background = '#f8fafc');
                            li.addEventListener('mouseleave', () => li.style.background = '#ffffff');

                            li.addEventListener('click', () => {
                                const lat = parseFloat(item.lat).toFixed(6);
                                const lng = parseFloat(item.lon).toFixed(6);
                                latInput.value = lat;
                                lngInput.value = lng;
                                updateMarker(lat, lng);
                                searchInput.value = item.display_name;
                                clearSearchBtn.style.display = 'block';
                                applyAddressData(parsed);
                                hideResults();
                            });
                            resultsList.appendChild(li);
                        });
                        showResults();
                    } else {
                        resultsList.innerHTML = `<li style="padding: 12px; font-size: 13px; color: #64748b; font-style: italic;">${escapeHtml(noResultsText)}</li>`;
                        showResults();
                    }
                })
                .catch(err => {
                    console.error("Erreur Nominatim:", err);
                    resultsList.innerHTML = `<li style="padding: 12px; font-size: 13px; color: #ef4444;">${escapeHtml(connErrorText)}</li>`;
                    showResults();
                });
        }

        // Recherche HERE (utilisé si configuré explicitement)
        function searchHere(query) {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 1500);

            const url = `https://autocomplete.search.hereapi.com/v1/autocomplete?q=${encodeURIComponent(query)}&apiKey=${rawHereKey}&limit=5`;
            fetch(url, { signal: controller.signal })
                .then(res => {
                    clearTimeout(timeoutId);
                    if (!res.ok) throw new Error('HERE HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    resultsList.innerHTML = '';
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            const li = document.createElement('li');
                            li.style.cssText = 'padding: 10px 14px; cursor: pointer; display: flex; align-items: flex-start; gap: 10px; border-bottom: 1px solid #f1f5f9; background: #ffffff; transition: background 0.15s ease; text-align: left;';
                            li.innerHTML = `
                                <span class="material-symbols-outlined" style="color: #94a3b8; font-size: 18px; margin-top: 2px; flex-shrink: 0; user-select: none;">location_on</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p style="font-weight: 600; color: #1e293b; font-size: 14px; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(item.title)}</p>
                                </div>
                            `;
                            li.addEventListener('mouseenter', () => li.style.background = '#f8fafc');
                            li.addEventListener('mouseleave', () => li.style.background = '#ffffff');

                            li.addEventListener('click', () => {
                                hideResults();
                                searchInput.value = item.title;
                                clearSearchBtn.style.display = 'block';
                                lookupHereAddress(item.id, item.title);
                            });
                            resultsList.appendChild(li);
                        });
                        showResults();
                    } else {
                        searchNominatim(query);
                    }
                })
                .catch(err => {
                    clearTimeout(timeoutId);
                    console.warn("HERE indisponible, bascule sur Nominatim:", err);
                    searchNominatim(query);
                });
        }

        // Lookup HERE
        function lookupHereAddress(id, defaultTitle) {
            fetch(`https://lookup.search.hereapi.com/v1/lookup?id=${id}&apiKey=${rawHereKey}`)
                .then(res => res.json())
                .then(data => {
                    if (data.position) {
                        const lat = data.position.lat.toFixed(6);
                        const lng = data.position.lng.toFixed(6);
                        latInput.value = lat;
                        lngInput.value = lng;
                        updateMarker(lat, lng);
                        
                        if (data.address) {
                            const addr = data.address;
                            const streetParts = [];
                            if (addr.houseNumber) streetParts.push(addr.houseNumber);
                            if (addr.street) streetParts.push(addr.street);
                            
                            const street = streetParts.join(' ') || defaultTitle || '';
                            const zip = addr.postalCode || '';
                            const city = addr.city || '';
                            const country = addr.countryName || '';
                            const location = city || street || country;
                            
                            applyAddressData({ street, zip, city, country, location });
                        }
                    }
                })
                .catch(err => console.error("Erreur HERE lookup:", err));
        }

        // Reverse Geocoding OpenStreetMap
        function reverseGeocodeNominatim(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
                .then(res => res.json())
                .then(data => {
                    if (!data) return;
                    const parsed = parseNominatimAddress(data);
                    applyAddressData(parsed);
                    if (data.display_name) {
                        searchInput.value = data.display_name;
                        clearSearchBtn.style.display = 'block';
                    }
                })
                .catch(err => console.error("Erreur reverse geocoding:", err));
        }

        // Événement saisie recherche
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = searchInput.value.trim();

            if (query.length > 0) {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
            
            if (query.length < 3) {
                hideResults();
                return;
            }
            
            debounceTimer = setTimeout(() => {
                if (preferHere) {
                    searchHere(query);
                } else {
                    searchNominatim(query);
                }
            }, 300);
        });

        // Bouton effacer recherche
        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            hideResults();
            searchInput.focus();
        });

        // Fermer la liste déroulante au clic en dehors
        document.addEventListener('click', (e) => {
            if (!searchContainer.contains(e.target)) {
                hideResults();
            }
        });

        // Clic sur la carte -> Place le marqueur et géocode inversé
        map.on('click', (e) => {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            latInput.value = lat;
            lngInput.value = lng;
            updateMarker(lat, lng);
            reverseGeocodeNominatim(lat, lng);
        });
        
        // Saisie manuelle des coordonnées
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
        
        // Bouton effacer la position
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                latInput.value = '';
                lngInput.value = '';
                searchInput.value = '';
                clearSearchBtn.style.display = 'none';
                hideResults();
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
            });
        }
    });
});
