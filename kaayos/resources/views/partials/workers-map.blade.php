@php
    $workersGeo = $workers->items();
@endphp

<div id="workersMap" class="workers-map-container" data-workers="{{ json_encode($workersGeo) }}"></div>

<script>
(function() {
    var mapEl = document.getElementById('workersMap');
    if (!mapEl) return;

    var workersData = [];
    try { workersData = JSON.parse(mapEl.dataset.workers || '[]'); } catch(e) {}

    var map = null;
    var markersLayer = null;
    var mapInitialized = false;

    function loadLeaflet(cb) {
        if (typeof L !== 'undefined') { cb(); return; }
        if (!document.querySelector('link[href*="leaflet.css"]')) {
            var l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(l);
        }
        var s = document.createElement('script');
        s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        s.onload = cb;
        s.onerror = function() {
            mapEl.innerHTML = '<div class="map-error"><i class="fa-solid fa-map-location-dot"></i><p>Could not load the map. Please try again.</p></div>';
        };
        document.head.appendChild(s);
    }

    function createMarkerIcon() {
        return L.divIcon({
            className: 'worker-marker-icon',
            html: '<div class="worker-marker-pin"><i class="fa-solid fa-user-gear"></i></div>',
            iconSize: [36, 44],
            iconAnchor: [18, 44],
            popupAnchor: [0, -48]
        });
    }

    function starString(rating) {
        var full = Math.floor(rating);
        var half = rating % 1 >= 0.5 ? 1 : 0;
        var empty = 5 - full - half;
        var s = '';
        for (var i = 0; i < full; i++) s += '<i class="fa-solid fa-star"></i>';
        if (half) s += '<i class="fa-solid fa-star-half-stroke"></i>';
        for (var i = 0; i < empty; i++) s += '<i class="fa-regular fa-star"></i>';
        return s;
    }

    function buildPopup(w) {
        var avatarHtml = w.avatar
            ? '<img src="' + w.avatar + '" alt="' + w.name + '">'
            : '<div class="popup-initials">' + w.initials + '</div>';
        var verifiedBadge = w.verified
            ? '<span class="popup-verified"><i class="fa-solid fa-circle-check"></i></span>'
            : '';
        var rateHtml = w.price > 0 ? '<span class="popup-rate">₱' + w.price.toLocaleString() + '/hr</span>' : '';
        var reviewText = w.reviews > 0 ? w.reviews + ' review' + (w.reviews !== 1 ? 's' : '') : 'No reviews';

        return '<div class="worker-map-popup">'
            + '<div class="popup-header">'
            +   '<div class="popup-avatar">' + avatarHtml + '</div>'
            +   '<div class="popup-info">'
            +     '<div class="popup-name">' + w.name + verifiedBadge + '</div>'
            +     '<div class="popup-category">' + w.category + '</div>'
            +   '</div>'
            + '</div>'
            + '<div class="popup-rating">'
            +   '<span class="popup-stars">' + starString(w.rating) + '</span>'
            +   '<span class="popup-rating-num">' + w.rating.toFixed(1) + '</span>'
            +   '<span class="popup-reviews">' + reviewText + '</span>'
            + '</div>'
            + '<div class="popup-details">'
            +   '<span class="popup-location"><i class="fa-solid fa-location-dot"></i> ' + (w.distance || 'Tuy') + '</span>'
            +   rateHtml
            + '</div>'
            + '<a href="/workers/' + w.id + '" class="popup-btn">View Profile <i class="fa-solid fa-arrow-right"></i></a>'
            + '</div>';
    }

    function renderMarkers(workers) {
        if (!map || !markersLayer) return;
        markersLayer.clearLayers();
        var icon = createMarkerIcon();
        var bounds = [];

        workers.forEach(function(w) {
            var lat = parseFloat(w.latitude);
            var lng = parseFloat(w.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            var marker = L.marker([lat, lng], { icon: icon })
                .bindPopup(buildPopup(w), { maxWidth: 280, minWidth: 220 });
            markersLayer.addLayer(marker);
            bounds.push([lat, lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
    }

    window.initWorkerMap = function(workers) {
        if (mapInitialized) {
            renderMarkers(workers || workersData);
            setTimeout(function() { if (map) map.invalidateSize(); }, 100);
            return;
        }

        loadLeaflet(function() {
            try {
                map = L.map(mapEl, {
                    center: [14.02, 120.73],
                    zoom: 13,
                    zoomControl: true
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                markersLayer = L.layerGroup().addTo(map);
                mapInitialized = true;

                renderMarkers(workers || workersData);
                setTimeout(function() { map.invalidateSize(); }, 200);
            } catch(e) {
                mapEl.innerHTML = '<div class="map-error"><i class="fa-solid fa-map-location-dot"></i><p>Could not initialize the map.</p></div>';
            }
        });
    };

    window.updateWorkerMap = function(workers) {
        workersData = workers || [];
        if (mapInitialized) {
            renderMarkers(workersData);
        }
    };
})();
</script>
