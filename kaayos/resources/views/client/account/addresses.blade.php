@extends('layouts.client')

@section('title', 'Addresses')
@section('page_title', 'Addresses')

@section('content')

@php
    $allBarangays = \App\Support\TuyBarangays::allBarangays();
@endphp

<style>
    /* Standalone form groups (not in a .form-row) need their own rhythm */
    .add-addr-form > .form-group,
    #editAddressModal .form-group { margin-bottom: 12px; }
    #editAddressModal .form-actions { margin-top: 4px; }
</style>

@if(session('success'))
    <div style="background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fef2f2;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.88rem;">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="welcome-banner">
    <h2>Saved Addresses</h2>
    <p>Save the places you book most often. Your default address is preselected whenever you book a worker.</p>
</div>

<div class="card-panel" style="margin-bottom:20px;">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">My Addresses</div>
            <h2 class="section-title">Saved Addresses</h2>
        </div>
        <span style="font-size:.82rem;color:var(--g4);font-weight:500;">
            {{ $addresses->count() }} saved
        </span>
    </div>

    @forelse($addresses as $address)
        <div style="display:flex;align-items:center;gap:16px;padding:18px 22px;border-bottom:1px solid var(--g1);flex-wrap:wrap;" id="address-row-{{ $address->id }}">
            <div style="width:44px;height:44px;border-radius:10px;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;">
                <i class="fa-solid {{ $address->is_default ? 'fa-house-chimney' : 'fa-location-dot' }}" aria-hidden="true"></i>
            </div>
            <div style="flex:1;min-width:220px;">
                <div style="font-size:.9rem;font-weight:600;color:var(--b9);display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    {{ $address->label }}
                    @if($address->is_default)
                        <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:600;">Default</span>
                    @endif
                </div>
                <div style="font-size:.82rem;color:var(--g4);margin-top:2px;">
                    {{ $address->fullAddress() }}
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap;">
                <button type="button" class="btn btn-outline" style="padding:6px 12px;font-size:.8rem;"
                        onclick='openEditAddress({{ json_encode([
                            'id' => $address->id,
                            'action' => route('client.account.addresses.update', $address),
                            'label' => $address->label,
                            'house_no' => $address->house_no,
                            'barangay' => $address->barangay,
                            'latitude' => $address->latitude,
                            'longitude' => $address->longitude,
                            'is_default' => (bool) $address->is_default,
                        ]) }})'>
                    Edit
                </button>
                @unless($address->is_default)
                    <form method="POST" action="{{ route('client.account.addresses.default', $address) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:.8rem;">Make default</button>
                    </form>
                @endunless
                <form method="POST" action="{{ route('client.account.addresses.destroy', $address) }}" style="display:inline;"
                      onsubmit="return confirm('Remove this saved address?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost" style="padding:6px 12px;font-size:.8rem;color:#dc2626;">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div style="padding:32px 22px;text-align:center;color:var(--g4);font-size:.88rem;">
            <i class="fa-solid fa-map-location-dot" style="font-size:1.6rem;margin-bottom:8px;display:block;color:var(--g5);" aria-hidden="true"></i>
            No saved addresses yet. Add one below.
        </div>
    @endforelse
</div>

<div class="form-section">
    <h3 class="form-section-title">Add New Address</h3>

    <form method="POST" action="{{ route('client.account.addresses.store') }}" class="add-addr-form">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label for="addr_label">Label</label>
                <input type="text" id="addr_label" name="label" maxlength="30" placeholder="e.g. Home, Office" value="{{ old('label') }}" required>
            </div>
            <div class="form-group">
                <label for="addr_house_no">House No. / Street</label>
                <input type="text" id="addr_house_no" name="house_no" placeholder="e.g. 123 Mabini St" value="{{ old('house_no') }}" required>
            </div>
        </div>
        <div class="form-group">
            <label for="addr_barangay">Barangay</label>
            <select id="addr_barangay" name="barangay" required>
                <option value="" disabled selected>Select barangay…</option>
                @foreach($allBarangays as $barangay)
                    <option value="{{ $barangay }}" @selected(old('barangay') === $barangay)>{{ $barangay }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline" id="gpsBtn" onclick="fillFromGps('addr')" style="width:100%;margin-top:8px;">
                <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Use my location
            </button>
            <p id="gpsStatus" style="font-size:.78rem;color:var(--g4);margin-top:6px;display:none;"></p>
        </div>
        <input type="hidden" name="latitude" id="addr_latitude" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="addr_longitude" value="{{ old('longitude') }}">
        <div class="form-group" style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" id="addr_is_default" name="is_default" value="1" @checked(old('is_default') || $addresses->isEmpty())>
            <label for="addr_is_default" style="margin:0;">Set as my default address</label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-solid">Save address</button>
        </div>
    </form>
</div>

{{-- Edit modal --}}
<div id="editAddressModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:1200;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)closeEditAddress()">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:520px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="margin:0;font-size:1rem;color:var(--b9);">Edit Address</h3>
            <button type="button" onclick="closeEditAddress()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--g4);">&times;</button>
        </div>
        <form method="POST" id="editAddressForm">
            @csrf @method('PUT')
            <div class="form-group">
                <label for="edit_label">Label</label>
                <input type="text" id="edit_label" name="label" maxlength="30" required>
            </div>
            <div class="form-group">
                <label for="edit_house_no">House No. / Street</label>
                <input type="text" id="edit_house_no" name="house_no" required>
            </div>
            <div class="form-group">
                <label for="edit_barangay">Barangay</label>
                <select id="edit_barangay" name="barangay" required>
                    <option value="" disabled>Select barangay…</option>
                    @foreach($allBarangays as $barangay)
                        <option value="{{ $barangay }}">{{ $barangay }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-outline" onclick="fillFromGps('edit')" style="width:100%;margin-bottom:12px;">
                <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Use my location
            </button>
            <input type="hidden" name="latitude" id="edit_latitude">
            <input type="hidden" name="longitude" id="edit_longitude">
            <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="edit_is_default" name="is_default" value="1">
                <label for="edit_is_default" style="margin:0;">Set as my default address</label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-solid">Save changes</button>
                <button type="button" class="btn btn-ghost" onclick="closeEditAddress()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAddress(data) {
    document.getElementById('editAddressForm').action = data.action;
    document.getElementById('edit_label').value = data.label;
    document.getElementById('edit_house_no').value = data.house_no;
    document.getElementById('edit_barangay').value = data.barangay;
    document.getElementById('edit_latitude').value = data.latitude || '';
    document.getElementById('edit_longitude').value = data.longitude || '';
    document.getElementById('edit_is_default').checked = !!data.is_default;
    document.getElementById('editAddressModal').style.display = 'flex';
}

function closeEditAddress() {
    document.getElementById('editAddressModal').style.display = 'none';
}

function fillFromGps(prefix) {
    var status = prefix === 'addr' ? document.getElementById('gpsStatus') : null;
    if (!navigator.geolocation) {
        if (status) { status.textContent = 'Geolocation is not supported by this browser.'; status.style.display = 'block'; status.style.color = '#dc2626'; }
        return;
    }
    if (status) { status.textContent = 'Getting your location…'; status.style.display = 'block'; status.style.color = 'var(--g4)'; }

    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude, lng = pos.coords.longitude;
        fetch('/api/location/reverse', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ latitude: lat, longitude: lng })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var brgySel = document.getElementById(prefix + '_barangay');
            if (res.barangay && brgySel) brgySel.value = res.barangay;
            document.getElementById(prefix + '_latitude').value = lat.toFixed(7);
            document.getElementById(prefix + '_longitude').value = lng.toFixed(7);
            if (status) { status.textContent = 'Location set to Brgy. ' + (res.barangay || '—'); status.style.color = '#166534'; }
        })
        .catch(function() {
            if (status) { status.textContent = 'Could not read your location.'; status.style.color = '#dc2626'; }
        });
    }, function() {
        if (status) { status.textContent = 'Location permission denied.'; status.style.color = '#dc2626'; }
    });
}
</script>

@endsection
