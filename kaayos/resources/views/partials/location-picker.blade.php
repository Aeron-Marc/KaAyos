{{--
    Shared "Residential Address" location picker.
    GPS-first with interactive map + manual Tuy barangay fallback.
    Works on both the client and worker profile pages (auth-protected).
--}}
@php
    $lpUser = auth()->user();
    $lpBarangays = class_exists(\App\Support\TuyBarangays::class)
        ? \App\Support\TuyBarangays::allBarangays()
        : ($barangays ?? []);
    $lpInitial = [
        'latitude'  => $lpUser->latitude,
        'longitude' => $lpUser->longitude,
        'barangay'  => $lpUser->barangay,
        'street'    => $lpUser->street_address,
        'source'    => $lpUser->location_source,
        'region'    => $lpUser->region ?? 'Calabarzon',
        'province'  => $lpUser->province ?? 'Batangas',
        'city'      => $lpUser->city_municipality ?? 'Tuy',
        'residence' => $lpUser->residence,
    ];
@endphp

@push('styles')
<style>
.loc-picker .loc-current {
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    background: var(--off); border: 1.5px solid var(--g1); border-radius: var(--radius-sm);
    padding: 12px 16px; margin-bottom: 14px;
}
.loc-residence { font-size: .9rem; color: var(--g7); margin: 0; font-weight: 600; }
.loc-source-badge {
    display: inline-flex; align-items: center; gap: 6px; font-size: .72rem; font-weight: 700;
    padding: 4px 10px; border-radius: 100px; letter-spacing: .3px; text-transform: uppercase;
}
.loc-source-badge.is-gps { background: var(--b0); color: var(--b6); }
.loc-source-badge.is-manual { background: #fef3d0; color: #a07b10; }
.loc-source-badge.is-none { background: var(--g1); color: var(--g4); }
.loc-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.loc-map { height: 300px; border-radius: var(--radius-sm); border: 1.5px solid var(--g1); z-index: 0; }
.loc-panel {
    margin-top: 14px; padding: 14px 16px; border: 1.5px solid var(--g1);
    border-radius: var(--radius-sm); background: var(--white);
}
.loc-detected-title { font-size: .82rem; font-weight: 700; color: var(--b9); margin: 0 0 10px; }
.loc-addr-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px 14px; margin: 0 0 4px; }
.loc-addr-list dt { font-size: .72rem; color: var(--g4); font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
.loc-addr-list dd { font-size: .86rem; color: var(--g7); font-weight: 600; margin: 2px 0 0; }
.loc-hint { font-size: .82rem; color: var(--g4); margin: 0 0 12px; }
.loc-error {
    margin-top: 14px; padding: 12px 16px; border-radius: var(--radius-sm);
    background: #fde0de; border: 1.5px solid #f5b9b4; color: #a32d2d; font-size: .84rem;
    display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
}
.loc-error p { margin: 0; }
.loc-error .btn { flex-shrink: 0; }
</style>
@endpush

<div class="form-section loc-picker" id="locPicker">
    <h3 class="form-section-title">Residential Address</h3>

    <div class="loc-current">
        <p class="loc-residence" id="locResidence">
            {{ $lpInitial['residence'] }}
        </p>
        <span class="loc-source-badge {{ $lpInitial['source'] ? 'is-' . $lpInitial['source'] : 'is-none' }}" id="locSourceBadge">
            @if($lpInitial['source'] === 'gps')
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i> GPS
            @elseif($lpInitial['source'] === 'manual')
                <i class="fa-solid fa-pen" aria-hidden="true"></i> Manual
            @else
                <i class="fa-solid fa-minus" aria-hidden="true"></i> Not set
            @endif
        </span>
    </div>

    <div class="loc-actions">
        <button type="button" class="btn btn-solid" id="locGpsBtn">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i> Use My Current Location
        </button>
        <button type="button" class="btn btn-ghost" id="locManualBtn">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Enter Address Manually
        </button>
    </div>

    {{-- GPS panel --}}
    <div class="loc-panel" id="locGpsPanel" style="display:none">
        <div id="locMap" class="loc-map"></div>

        <div id="locDetected" style="display:none;margin-top:14px;">
            <p class="loc-detected-title"><i class="fa-solid fa-map-pin" aria-hidden="true"></i> Detected Address</p>
            <dl class="loc-addr-list">
                <div><dt>Barangay</dt><dd id="locAddrBarangay"></dd></div>
                <div><dt>City/Municipality</dt><dd id="locAddrCity"></dd></div>
                <div><dt>Province</dt><dd id="locAddrProvince"></dd></div>
                <div><dt>Region</dt><dd id="locAddrRegion"></dd></div>
            </dl>
            <div class="form-group" style="margin-top:12px;">
                <label for="locStreet">Street / Landmark (optional)</label>
                <input type="text" id="locStreet" value="{{ $lpInitial['street'] }}" placeholder="e.g. Purok 3, Malakas St.">
            </div>
            <div class="loc-actions" style="margin-top:14px;">
                <button type="button" class="btn btn-outline" id="locAdjustBtn">
                    <i class="fa-solid fa-location-arrow" aria-hidden="true"></i> Adjust Pin
                </button>
                <button type="button" class="btn btn-solid" id="locConfirmBtn">
                    <i class="fa-solid fa-check" aria-hidden="true"></i> Confirm Location
                </button>
            </div>
        </div>

        <div class="loc-error" id="locGpsError" style="display:none;"></div>
    </div>

    {{-- Manual panel --}}
    <div class="loc-panel" id="locManualPanel" style="display:none">
        <p class="loc-hint">Select your barangay in <strong>Tuy, Batangas</strong>:</p>
        <div class="form-group">
            <label for="locManualBarangay">Barangay</label>
            <select id="locManualBarangay">
                <option value="">Select barangay...</option>
                @foreach($lpBarangays as $lpB)
                    <option value="{{ $lpB }}" {{ $lpInitial['barangay'] === $lpB ? 'selected' : '' }}>{{ $lpB }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin-top:10px;">
            <label for="locManualStreet">Street / Landmark (optional)</label>
            <input type="text" id="locManualStreet" value="{{ $lpInitial['street'] }}" placeholder="e.g. Purok 3, Malakas St.">
        </div>
        <div class="loc-actions" style="margin-top:14px;">
            <button type="button" class="btn btn-solid" id="locManualSaveBtn">
                <i class="fa-solid fa-check" aria-hidden="true"></i> Save Address
            </button>
        </div>
    </div>

    {{-- Fallback message --}}
    <div class="loc-error" id="locFallback" style="display:none;">
        <p>We couldn't determine your location. You can enter your barangay manually instead.</p>
        <button type="button" class="btn btn-ghost" id="locFallbackManualBtn">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Enter Address Manually
        </button>
    </div>

    {{-- Synced state (hidden) --}}
    <input type="hidden" id="locLatitude" value="{{ $lpInitial['latitude'] ?? '' }}">
    <input type="hidden" id="locLongitude" value="{{ $lpInitial['longitude'] ?? '' }}">
    <input type="hidden" id="locBarangay" value="{{ $lpInitial['barangay'] ?? '' }}">
    <input type="hidden" id="locStreetAddress" value="{{ $lpInitial['street'] ?? '' }}">
    <input type="hidden" id="locSource" value="{{ $lpInitial['source'] ?? '' }}">
</div>

@push('scripts')
<script>
(function () {
    var initial = @json($lpInitial);
    var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var token = window.authToken || '{{ auth()->user()->createToken('location-picker')->plainTextToken }}';

    function headers(json) {
        var h = { Accept: 'application/json', Authorization: 'Bearer ' + token };
        if (json) h['Content-Type'] = 'application/json';
        return h;
    }

    var els = {
        gpsBtn: document.getElementById('locGpsBtn'),
        manualBtn: document.getElementById('locManualBtn'),
        gpsPanel: document.getElementById('locGpsPanel'),
        manualPanel: document.getElementById('locManualPanel'),
        map: document.getElementById('locMap'),
        detected: document.getElementById('locDetected'),
        gpsError: document.getElementById('locGpsError'),
        fallback: document.getElementById('locFallback'),
        addr: {
            barangay: document.getElementById('locAddrBarangay'),
            city: document.getElementById('locAddrCity'),
            province: document.getElementById('locAddrProvince'),
            region: document.getElementById('locAddrRegion')
        },
        street: document.getElementById('locStreet'),
        manualBarangay: document.getElementById('locManualBarangay'),
        manualStreet: document.getElementById('locManualStreet'),
        confirmBtn: document.getElementById('locConfirmBtn'),
        manualSaveBtn: document.getElementById('locManualSaveBtn'),
        fallbackManualBtn: document.getElementById('locFallbackManualBtn'),
        lat: document.getElementById('locLatitude'),
        lng: document.getElementById('locLongitude'),
        barangay: document.getElementById('locBarangay'),
        streetAddress: document.getElementById('locStreetAddress'),
        source: document.getElementById('locSource'),
        residence: document.getElementById('locResidence'),
        sourceBadge: document.getElementById('locSourceBadge')
    };

    var map = null;
    var marker = null;
    var current = { lat: null, lng: null };

    function toast(type, message) {
        var container = document.getElementById('toastContainer');
        if (!container) return;
        var icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation' };
        var div = document.createElement('div');
        div.className = 'toast toast-' + type;
        div.innerHTML = '<i class="fa-solid ' + (icons[type] || icons.error) + '"></i><span>' + message + '</span>';
        container.appendChild(div);
        setTimeout(function () { div.remove(); }, 3500);
    }

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
        s.onerror = function () { showGpsError('Could not load the map. Please try entering your address manually.'); };
        document.head.appendChild(s);
    }

    function showPanel(name) {
        els.gpsPanel.style.display = name === 'gps' ? '' : 'none';
        els.manualPanel.style.display = name === 'manual' ? '' : 'none';
        els.fallback.style.display = name === 'fallback' ? '' : 'none';
    }

    function showGpsError(message) {
        els.gpsError.textContent = message;
        els.gpsError.style.display = 'flex';
        els.detected.style.display = 'none';
    }

    function clearGpsError() {
        els.gpsError.style.display = 'none';
    }

    function reverseGeocode(lat, lng) {
        clearGpsError();
        fetch('/api/location/reverse', {
            method: 'POST',
            headers: headers(true),
            body: JSON.stringify({ latitude: lat, longitude: lng })
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data || !res.data.barangay) {
                    showGpsError("We couldn't determine your location. You can enter your barangay manually instead.");
                    els.fallbackManualBtn.style.display = '';
                    return;
                }
                els.addr.barangay.textContent = res.data.barangay;
                els.addr.city.textContent = res.data.city_municipality;
                els.addr.province.textContent = res.data.province;
                els.addr.region.textContent = res.data.region;
                els.barangay.value = res.data.barangay;
                els.detected.style.display = '';
                showPanel('gps');
            })
            .catch(function () {
                showGpsError('Reverse geocoding failed. You can enter your barangay manually instead.');
                els.fallbackManualBtn.style.display = '';
            });
    }

    function initMap(lat, lng) {
        loadLeaflet(function () {
            try {
                if (map) map.remove();
                map = L.map(els.map, { zoomControl: true }).setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    var p = marker.getLatLng();
                    current.lat = p.lat.toFixed(7);
                    current.lng = p.lng.toFixed(7);
                    els.lat.value = current.lat;
                    els.lng.value = current.lng;
                    reverseGeocode(current.lat, current.lng);
                });
                current.lat = lat;
                current.lng = lng;
                els.lat.value = lat;
                els.lng.value = lng;
                setTimeout(function () { if (map) map.invalidateSize(); }, 200);
            } catch (e) {
                showGpsError('Could not initialize the map. You can enter your barangay manually instead.');
            }
        });
    }
    function useCurrentLocation() {
        if (!navigator.geolocation) {
            showPanel('fallback');
            els.fallbackManualBtn.style.display = '';
            return;
        }
        showPanel('gps');
        els.gpsError.style.display = 'none';
        els.detected.style.display = 'none';
        els.gpsError.textContent = 'Requesting your location\u2026';
        els.gpsError.style.display = 'flex';

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                var lat = pos.coords.latitude.toFixed(7);
                var lng = pos.coords.longitude.toFixed(7);
                current.lat = lat;
                current.lng = lng;
                els.lat.value = lat;
                els.lng.value = lng;
                initMap(lat, lng);
                reverseGeocode(lat, lng);
            },
            function (err) {
                var message = "We couldn't determine your location. You can enter your barangay manually instead.";
                if (err && err.code === 1) {
                    message = 'Location permission was denied. You can enter your barangay manually instead.';
                } else if (err && err.code === 2) {
                    message = 'Your location is currently unavailable. You can enter your barangay manually instead.';
                } else if (err && err.code === 3) {
                    message = 'The location request timed out. You can enter your barangay manually instead.';
                }
                showGpsError(message);
                els.fallbackManualBtn.style.display = '';
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
        );
    }

    function saveLocation(payload) {
        var btn = (payload.location_source === 'gps') ? els.confirmBtn : els.manualSaveBtn;
        var original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving\u2026';

        fetch('/api/location', {
            method: 'POST',
            headers: headers(true),
            body: JSON.stringify(payload)
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok) {
                    var msg = (res.data && res.data.message) ? res.data.message : 'Could not save your location.';
                    if (res.data && res.data.errors && res.data.errors.barangay) {
                        msg = res.data.errors.barangay[0];
                    }
                    throw new Error(msg);
                }
                els.lat.value = res.data.latitude;
                els.lng.value = res.data.longitude;
                els.barangay.value = res.data.barangay;
                els.streetAddress.value = res.data.street_address || '';
                els.source.value = res.data.location_source;
                els.residence.textContent = res.data.residence;
                els.sourceBadge.className = 'loc-source-badge is-' + res.data.location_source;
                els.sourceBadge.innerHTML = res.data.location_source === 'gps'
                    ? '<i class="fa-solid fa-location-dot" aria-hidden="true"></i> GPS'
                    : '<i class="fa-solid fa-pen" aria-hidden="true"></i> Manual';
                toast('success', res.data.message || 'Location saved.');
            })
            .catch(function (err) { toast('error', err.message); })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = original;
            });
    }

    els.gpsBtn.addEventListener('click', useCurrentLocation);

    els.manualBtn.addEventListener('click', function () {
        showPanel('manual');
    });

    els.fallbackManualBtn.addEventListener('click', function () {
        showPanel('manual');
    });

    els.confirmBtn.addEventListener('click', function () {
        if (els.lat.value === '' || els.lng.value === '' || !els.barangay.value) {
            toast('error', 'Please wait for the location to be detected before confirming.');
            return;
        }
        saveLocation({
            latitude: els.lat.value,
            longitude: els.lng.value,
            barangay: els.barangay.value,
            street_address: els.street.value,
            region: initial.region,
            province: initial.province,
            city_municipality: initial.city,
            location_source: 'gps'
        });
    });

    els.manualSaveBtn.addEventListener('click', function () {
        var barangay = els.manualBarangay.value;
        if (!barangay) {
            toast('error', 'Please select your barangay.');
            return;
        }
        saveLocation({
            latitude: els.lat.value || null,
            longitude: els.lng.value || null,
            barangay: barangay,
            street_address: els.manualStreet.value,
            region: initial.region,
            province: initial.province,
            city_municipality: initial.city,
            location_source: 'manual'
        });
        els.barangay.value = barangay;
    });

    if (initial.latitude && initial.longitude) {
        current.lat = initial.latitude;
        current.lng = initial.longitude;
    }
})();
</script>
@endpush
