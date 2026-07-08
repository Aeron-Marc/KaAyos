@extends('layouts.worker')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@push('styles')
<style>
.avatar-upload-wrap { position: relative; display: inline-block; cursor: pointer; }
.avatar-upload-overlay {
    position: absolute; inset: 0; border-radius: 50%;
    background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .75rem; font-weight: 600; opacity: 0; transition: opacity .2s;
    text-align: center; line-height: 1.3; padding: 4px;
}
.avatar-upload-wrap:hover .avatar-upload-overlay { opacity: 1; }
.portfolio-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px;
}
.portfolio-card {
    background: var(--white); border: 1.5px solid var(--g1); border-radius: var(--radius); overflow: hidden;
}
.portfolio-card img {
    width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block;
}
.portfolio-card .portfolio-caption {
    padding: 8px 12px; font-size: .82rem; color: var(--g7);
}
.portfolio-card .portfolio-actions {
    padding: 6px 12px 10px; display: flex; gap: 6px;
}
.portfolio-upload-card {
    border: 2px dashed var(--g1); border-radius: var(--radius);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    aspect-ratio: 4/3; cursor: pointer; transition: all .2s; color: var(--g4);
    background: var(--off);
}
.portfolio-upload-card:hover { border-color: var(--b4); color: var(--b6); background: var(--b0); }
.portfolio-upload-card i { font-size: 2rem; margin-bottom: 8px; }
.portfolio-upload-card span { font-size: .82rem; font-weight: 600; }

.doc-card {
    display: flex; align-items: center; gap: 14px; padding: 16px 20px;
    border-bottom: 1px solid var(--g1); transition: background .15s;
}
.doc-card:last-child { border-bottom: none; }
.doc-icon {
    width: 44px; height: 44px; border-radius: 10px; background: var(--b0);
    color: var(--b6); display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 1.1rem;
}
.doc-info { flex: 1; min-width: 0; }
.doc-info .doc-name { font-size: .9rem; font-weight: 600; color: var(--b9); }
.doc-info .doc-desc { font-size: .82rem; color: var(--g4); margin-top: 2px; }
.doc-file { font-size: .75rem; color: var(--g4); margin-top: 2px; }
.doc-file i { margin-right: 4px; }
.status-badge { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: 4px 10px; border-radius: 100px; }
.status-verified { background: #d6f5e8; color: #1a6852; }
.status-pending { background: #fef3d0; color: #a07b10; }
.status-not-submitted { background: #fde0de; color: #a32d2d; }
.doc-upload-btn { padding: 5px 12px; font-size: .78rem; }
.alert-success {
    background: #d6f5e8; color: #1a6852; padding: 12px 16px; border-radius: var(--radius-sm);
    font-size: .875rem; font-weight: 500; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
}
.barangay-grid {
    display: flex; flex-wrap: wrap; gap: 8px;
}
.barangay-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border: 1.5px solid var(--g1); border-radius: 100px;
    background: var(--white); cursor: pointer; transition: all .15s;
    font-size: .85rem; font-weight: 500; color: var(--g6); user-select: none;
}
.barangay-chip:hover { border-color: var(--b3); background: var(--b0); color: var(--b7); }
.barangay-chip.selected { border-color: var(--b5); background: var(--b1); color: var(--b8); font-weight: 600; }
.barangay-chip.selected::before {
    content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
    font-size: .7rem; color: var(--b6);
}

.tag-input-wrap {
    border: 1.5px solid var(--g1); border-radius: var(--radius-sm);
    padding: 6px 10px; background: var(--white); transition: border-color .15s;
    min-height: 42px;
}
.tag-input-wrap:focus-within { border-color: var(--b4); }
.tag-list {
    display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
}
.tag-chip {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--b0); color: var(--b8); font-size: .82rem;
    padding: 4px 8px; border-radius: 100px; font-weight: 500;
    line-height: 1.3;
}
.tag-remove {
    cursor: pointer; font-size: 1rem; color: var(--b5); line-height: 1;
    margin-left: 2px;
}
.tag-remove:hover { color: #c62828; }
.tag-field {
    border: none; outline: none; font-size: .85rem; padding: 4px 2px;
    min-width: 180px; flex: 1; background: transparent; color: var(--b9);
}
.tag-field::placeholder { color: var(--g3); }

.avail-grid {
    display: flex; flex-direction: column; gap: 8px; margin-top: 4px;
}
.avail-row {
    display: flex; align-items: center; gap: 12px;
    padding: 8px 12px; border-radius: var(--radius-sm);
    background: var(--off); transition: background .15s;
}
.avail-row:has(.avail-checkbox:checked) {
    background: var(--b0);
}
.avail-day-label {
    display: flex; align-items: center; gap: 8px; cursor: pointer;
    min-width: 110px; user-select: none;
}
.avail-checkbox {
    width: 18px; height: 18px; accent-color: var(--b5); cursor: pointer;
}
.avail-day-name {
    font-size: .88rem; font-weight: 500; color: var(--b9);
}
.avail-times {
    display: flex; align-items: center; gap: 8px;
}
.avail-times input[type="time"] {
    border: 1px solid var(--g1); border-radius: var(--radius-sm);
    padding: 5px 10px; font-size: .85rem; background: var(--white);
    color: var(--b9); outline: none; width: 110px;
}
.avail-times input[type="time"]:focus {
    border-color: var(--b4);
}
.avail-sep {
    color: var(--g4); font-weight: 500;
}
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        {{ session('success') }}
    </div>
@endif

<div class="profile-grid">

    <aside>
        <div class="profile-sidebar-card">
            <form method="POST" action="{{ route('worker.profile.avatar') }}" enctype="multipart/form-data" id="avatar-form">
                @csrf
                <div class="avatar-upload-wrap">
                    @if(auth()->user()->avatar)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar) }}"
                             alt="" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 14px;display:block;">
                    @else
                        <div class="profile-big-avatar">
                            {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                        </div>
                    @endif
                    <div class="avatar-upload-overlay">
                        <i class="fa-solid fa-camera" style="font-size:1.2rem;" aria-hidden="true"></i>
                    </div>
                    <input type="file" name="avatar" accept="image/*" style="display:none;" id="avatar-input">
                </div>
            </form>
            <h3>{{ auth()->user()->name ?? 'User' }}</h3>
            <p>{{ auth()->user()->city ?: 'Location not set' }}</p>
            <span class="profile-role-tag">Trabahador</span>

            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--g1);">
                <div style="display:flex;justify-content:center;gap:20px;">
                    <div>
                        <div style="font-size:1.3rem;font-weight:700;color:var(--b9);">{{ $stats[2]['value'] ?? '0.0' }}</div>
                        <div style="font-size:.72rem;color:var(--g4);">★ Rating</div>
                    </div>
                    <div>
                        <div style="font-size:1.3rem;font-weight:700;color:var(--b9);">{{ $stats[3]['value'] ?? 0 }}</div>
                        <div style="font-size:.72rem;color:var(--g4);">Jobs Done</div>
                    </div>
                    <div>
                        <div style="font-size:1.3rem;font-weight:700;color:var(--b9);">{{ $stats[1]['value'] ?? 0 }}</div>
                        <div style="font-size:.72rem;color:var(--g4);">Active</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div>

        <form method="POST" action="{{ route('worker.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name"
                               value="{{ old('first_name', auth()->user()->first_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name"
                               value="{{ old('last_name', auth()->user()->last_name) }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <p class="form-value">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone"
                               value="{{ old('phone', auth()->user()->phone) }}">
                    </div>
                </div>

                <div class="form-section" style="margin-top:12px;padding-top:16px;border-top:1px solid var(--g1);">
                    <h3 style="font-size:.95rem;font-weight:600;margin-bottom:10px;">Change Email</h3>
                    <p style="font-size:.82rem;color:var(--g4);margin-bottom:12px;">
                        You can change your email once every 30 days. A verification code will be sent to confirm.
                    </p>
                    <button type="button" class="btn btn-outline" id="email-change-btn">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i> Change Email
                    </button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City / Municipality</label>
                        <input type="text" id="city" name="city"
                               value="{{ old('city', auth()->user()->city) }}" placeholder="e.g. Tuy, Batangas">
                    </div>
                    <div class="form-group">
                        <label for="language">Language Preference</label>
                        <select id="language" name="language">
                            <option value="English" {{ auth()->user()->language === 'English' ? 'selected' : '' }}>English</option>
                            <option value="Filipino" {{ auth()->user()->language === 'Filipino' ? 'selected' : '' }}>Filipino</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Professional Information</h3>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="review-textarea" placeholder="Tell clients about yourself, your experience, and the services you offer…">{{ old('bio', $workerProfile->bio) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Skills</label>
                    <div class="tag-input-wrap">
                        <div class="tag-list" id="skills-tag-list">
                            @if($workerProfile->skills)
                                @foreach($workerProfile->skills as $skill)
                                    <span class="tag-chip" data-value="{{ $skill }}">{{ $skill }}<span class="tag-remove" data-value="{{ $skill }}">&times;</span></span>
                                @endforeach
                            @endif
                            <input type="text" class="tag-field" placeholder="Type and press Enter to add">
                        </div>
                    </div>
                    <input type="hidden" name="skills" id="skills"
                           value="{{ old('skills', $workerProfile->skills ? implode(',', $workerProfile->skills) : '') }}">
                </div>
                <div class="form-group" style="margin-top:12px;">
                    <label>Spoken Languages</label>
                    <div class="tag-input-wrap">
                        <div class="tag-list" id="languages-tag-list">
                            @if($workerProfile->spoken_languages)
                                @foreach($workerProfile->spoken_languages as $lang)
                                    <span class="tag-chip" data-value="{{ $lang }}">{{ $lang }}<span class="tag-remove" data-value="{{ $lang }}">&times;</span></span>
                                @endforeach
                            @endif
                            <input type="text" class="tag-field" placeholder="Type and press Enter to add">
                        </div>
                    </div>
                    <input type="hidden" name="spoken_languages" id="spoken_languages"
                           value="{{ old('spoken_languages', $workerProfile->spoken_languages ? implode(',', $workerProfile->spoken_languages) : '') }}">
                </div>
                <div class="form-row" style="margin-top:12px;">
                    <div class="form-group">
                        <label for="service_category">Primary Category</label>
                        <select id="service_category" name="service_category">
                            <option value="">Select category</option>
                            @foreach(['Plumbing', 'Electrical', 'Cleaning', 'Carpentry', 'Painting', 'Aircon', 'Landscaping', 'General Repair'] as $cat)
                                <option value="{{ $cat }}" {{ old('service_category', auth()->user()->service_category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="years_of_experience">Years of Experience</label>
                        <input type="number" id="years_of_experience" name="years_of_experience" min="0" max="100"
                               value="{{ old('years_of_experience', $workerProfile->years_of_experience) }}" placeholder="e.g. 5">
                    </div>
                </div>
                <div class="form-group">
                    <label for="hourly_rate">Hourly Rate (₱)</label>
                    <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="0.01"
                           value="{{ old('hourly_rate', $workerProfile->hourly_rate) }}" placeholder="e.g. 400">
                </div>
            </div>

            <div class="form-section">
                <h3>Availability</h3>
                @php
                    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $availability = old('availability')
                        ? json_decode(old('availability'), true)
                        : ($workerProfile->availability ?? []);
                    $availMap = [];
                    foreach ($availability as $a) {
                        $availMap[$a['day']] = $a;
                    }
                @endphp
                <div class="avail-grid" id="avail-grid">
                    @foreach($daysOfWeek as $day)
                        @php $a = $availMap[$day] ?? ['active' => false, 'start' => '08:00', 'end' => '17:00']; @endphp
                        <div class="avail-row" data-day="{{ $day }}">
                            <label class="avail-day-label">
                                <input type="checkbox" class="avail-checkbox" {{ $a['active'] ? 'checked' : '' }}>
                                <span class="avail-day-name">{{ $day }}</span>
                            </label>
                            <div class="avail-times" style="{{ $a['active'] ? '' : 'display:none;' }}">
                                <input type="time" class="avail-start" value="{{ $a['start'] ?? '08:00' }}">
                                <span class="avail-sep">—</span>
                                <input type="time" class="avail-end" value="{{ $a['end'] ?? '17:00' }}">
                            </div>
                        </div>
                    @endforeach
                </div>
                <input type="hidden" name="availability" id="availability-input"
                       value="{{ old('availability', $workerProfile->availability ? json_encode($workerProfile->availability) : '') }}">
                <button type="button" class="btn btn-ghost" id="apply-all-times" style="margin-top:8px;font-size:.82rem;">
                    <i class="fa-solid fa-copy" aria-hidden="true"></i> Apply same time to all active days
                </button>
            </div>

            <div class="form-section">
                <h3>Service Coverage</h3>
                <p style="font-size:.82rem;color:var(--g4);margin-bottom:12px;">Select the barangays in <strong>Tuy, Batangas</strong> where you offer services:</p>

                <input type="hidden" name="service_areas" id="service_areas_input"
                       value="{{ old('service_areas', $workerProfile->service_areas ? implode(',', $workerProfile->service_areas) : '') }}">

                @php
                    $selectedAreas = old('service_areas', $workerProfile->service_areas)
                        ? (is_array($workerProfile->service_areas) ? $workerProfile->service_areas : explode(',', $workerProfile->service_areas))
                        : [];
                    $selectedAreas = array_map('trim', $selectedAreas);
                    $tuyBarangays = ['Acle','Bayudbud','Bolbok','Burgos','Dalima','Dao','Guinhawa','Lumbangan','Luna','Luntal','Magahis','Malibu','Mataywanac','Palincaro','Putol','Rillo','Rizal','Sabang','San Jose','Talon','Toong','Tuyon-Tuyon'];
                @endphp

                <div class="barangay-grid">
                    @foreach($tuyBarangays as $barangay)
                        <label class="barangay-chip {{ in_array($barangay, $selectedAreas) ? 'selected' : '' }}">
                            <input type="checkbox" value="{{ $barangay }}"
                                   {{ in_array($barangay, $selectedAreas) ? 'checked' : '' }}
                                   class="barangay-checkbox" style="display:none;">
                            <span>{{ $barangay }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="form-row" style="margin-top:16px;">
                    <div class="form-group">
                        <label for="service_radius">Service Radius (km)</label>
                        <input type="number" id="service_radius" name="service_radius" min="0" max="500"
                               value="{{ old('service_radius', $workerProfile->service_radius) }}" placeholder="e.g. 25">
                    </div>
                    <div class="form-group">
                        <label for="service_zone">Service Zone (comma-separated)</label>
                        <input type="text" id="service_zone" name="service_zone" placeholder="e.g. Zone 1, Zone 2, Zone 3"
                               value="{{ old('service_zone', $workerProfile->service_zone ? implode(', ', $workerProfile->service_zone) : '') }}">
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn btn-solid">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
                </button>
                <a href="{{ route('worker.profile') }}" class="btn btn-ghost">Cancel</a>
            </div>

        </form>

        {{-- Work Portfolio --}}
        <div class="form-section" style="margin-top:20px;">
            <h3>Work Portfolio</h3>

            <div class="portfolio-grid">
                @forelse($portfolios as $item)
                    <div class="portfolio-card">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($item->photo_path) }}" alt="Work photo">
                        @if($item->caption)
                            <div class="portfolio-caption">{{ $item->caption }}</div>
                        @endif
                        <div class="portfolio-actions">
                            <form method="POST" action="{{ route('worker.profile.portfolio.delete', $item->id) }}" onsubmit="return confirm('Remove this photo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost" style="padding:4px 10px;font-size:.75rem;color:#c62828;border-color:#ef9a9a;">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                @endforelse

                <form method="POST" action="{{ route('worker.profile.portfolio') }}" enctype="multipart/form-data" class="portfolio-upload-card" id="portfolio-form">
                    @csrf
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    <span>Add Photo</span>
                    <input type="file" name="photo" accept="image/*" style="display:none;" id="portfolio-input">
                    <input type="text" name="caption" placeholder="Add a caption…" style="margin-top:8px;border:none;background:transparent;text-align:center;font-size:.78rem;width:100%;outline:none;color:var(--g7);" onclick="event.stopPropagation();">
                </form>
            </div>
        </div>

        {{-- Documents --}}
        <div class="form-section" style="margin-top:20px;">
            <h3>Documents</h3>
            <p style="font-size:.82rem;color:var(--g4);margin-bottom:16px;">Upload the required documents for verification. Verified documents help build trust with clients.</p>

            @forelse($documents as $doc)
                <div class="doc-card">
                    <div class="doc-icon">
                        <i class="fa-solid {{ $doc['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <div class="doc-info">
                        <div class="doc-name">{{ $doc['name'] }}</div>
                        <div class="doc-desc">{{ $doc['description'] }}</div>
                        @if($doc['file'])
                            <div class="doc-file"><i class="fa-solid fa-paperclip" aria-hidden="true"></i> {{ $doc['file'] }}</div>
                        @endif
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        @php
                            $statusClass = match($doc['status']) {
                                'Verified' => 'status-verified',
                                'Pending' => 'status-pending',
                                default => 'status-not-submitted',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}" style="display:inline-flex;margin-bottom:4px;">
                            @if($doc['status'] === 'Verified')
                                <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
                            @elseif($doc['status'] === 'Pending')
                                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                            @else
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            @endif
                            {{ $doc['status'] }}
                        </span>
                        <div style="margin-top:4px;">
                            <form method="POST" action="{{ route('worker.profile.document') }}" enctype="multipart/form-data" style="display:inline;">
                                @csrf
                                <input type="hidden" name="document_type" value="{{ $doc['name'] }}">
                                <input type="file" name="file" accept=".jpeg,.png,.jpg,.pdf" style="display:none;" class="doc-file-input" data-doc="{{ $doc['name'] }}">
                                <button type="button" class="btn btn-outline doc-upload-btn" onclick="this.closest('form').querySelector('.doc-file-input').click();">
                                    <i class="fa-solid fa-upload" aria-hidden="true"></i>
                                    {{ $doc['file'] ? 'Replace' : 'Upload' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa-regular fa-file" aria-hidden="true"></i>
                    <h3>No documents</h3>
                    <p>Upload your IDs and clearances to get verified.</p>
                </div>
            @endforelse
        </div>

    </div>

</div>

@endsection

{{-- Email Change Modal --}}
<div id="email-change-modal" class="modal-overlay" style="display:none;" role="presentation">
    <div class="otp-modal" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
        <div class="otp-modal-header">
            <div class="otp-modal-icon">
                <i class="fa-solid fa-envelope" id="ec-icon" aria-hidden="true"></i>
            </div>
            <h2 id="ec-title">Change Email Address</h2>
            <p id="ec-subtitle">Current: <strong>{{ auth()->user()->email }}</strong></p>
        </div>

        {{-- Step 1: Form --}}
        <div id="ec-step-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="ec-new-email">New Email</label>
                    <input type="email" id="ec-new-email">
                </div>
                <div class="form-group">
                    <label for="ec-confirm-email">Confirm New Email</label>
                    <input type="email" id="ec-confirm-email">
                </div>
            </div>
            <div class="form-group">
                <label for="ec-password">Current Password</label>
                <input type="password" id="ec-password" placeholder="Enter your current password">
            </div>
            <div id="ec-error-form" class="field-error otp-error" style="display:none;"></div>
            <div class="otp-actions">
                <button type="button" class="btn btn-solid" id="ec-send-btn">Send verification code</button>
                <button type="button" class="btn btn-ghost" id="ec-cancel-btn">Cancel</button>
            </div>
        </div>

        {{-- Step 2: OTP --}}
        <div id="ec-step-otp" style="display:none;">
            <p id="ec-otp-sent-to" style="text-align:center;color:var(--g5);margin-bottom:16px;font-size:.9rem;"></p>
            <div class="otp-inputs" id="ec-otp-inputs">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input ec-otp-digit" data-idx="0">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input ec-otp-digit" data-idx="1">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input ec-otp-digit" data-idx="2">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input ec-otp-digit" data-idx="3">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input ec-otp-digit" data-idx="4">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input ec-otp-digit" data-idx="5">
            </div>
            <div id="ec-error-otp" class="field-error otp-error" style="display:none;"></div>
            <div class="otp-actions">
                <button type="button" class="btn btn-solid" id="ec-verify-btn">Verify & change email</button>
                <button type="button" class="btn btn-ghost" id="ec-back-btn">Back</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.authToken = "{{ auth()->user()->createToken('worker-profile-page')->plainTextToken }}";

// Email Change Modal
(function() {
    const modal = document.getElementById('email-change-modal');
    const openBtn = document.getElementById('email-change-btn');
    const cancelBtn = document.getElementById('ec-cancel-btn');
    const backBtn = document.getElementById('ec-back-btn');
    const sendBtn = document.getElementById('ec-send-btn');
    const verifyBtn = document.getElementById('ec-verify-btn');

    const stepForm = document.getElementById('ec-step-form');
    const stepOtp  = document.getElementById('ec-step-otp');
    const ecIcon   = document.getElementById('ec-icon');
    const ecTitle  = document.getElementById('ec-title');
    const ecSubtitle = document.getElementById('ec-subtitle');
    const ecOtpSentTo = document.getElementById('ec-otp-sent-to');

    const errForm = document.getElementById('ec-error-form');
    const errOtp  = document.getElementById('ec-error-otp');

    const newEmailInput = document.getElementById('ec-new-email');
    const confirmEmailInput = document.getElementById('ec-confirm-email');
    const passwordInput = document.getElementById('ec-password');
    const otpInputs = document.querySelectorAll('.ec-otp-digit');

    let currentNewEmail = '';
    let loading = false;

    function showError(container, msg) {
        container.textContent = msg;
        container.style.display = 'block';
    }

    function hideError(container) {
        container.textContent = '';
        container.style.display = 'none';
    }

    function resetModal() {
        stepForm.style.display = 'block';
        stepOtp.style.display = 'none';
        ecIcon.className = 'fa-solid fa-envelope';
        ecTitle.textContent = 'Change Email Address';
        ecSubtitle.innerHTML = 'Current: <strong>{{ auth()->user()->email }}</strong>';
        newEmailInput.value = '';
        confirmEmailInput.value = '';
        passwordInput.value = '';
        otpInputs.forEach(inp => inp.value = '');
        hideError(errForm);
        hideError(errOtp);
        loading = false;
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send verification code';
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify & change email';
    }

    function openModal() {
        resetModal();
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    openBtn?.addEventListener('click', openModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', closeModal);

    backBtn?.addEventListener('click', function() {
        stepForm.style.display = 'block';
        stepOtp.style.display = 'none';
        ecIcon.className = 'fa-solid fa-envelope';
        ecTitle.textContent = 'Change Email Address';
        ecSubtitle.innerHTML = 'Current: <strong>{{ auth()->user()->email }}</strong>';
        hideError(errOtp);
    });

    // OTP digit handling
    otpInputs.forEach((input, idx) => {
        input.addEventListener('input', function(e) {
            const val = e.target.value;
            if (val && !/^\d$/.test(val)) { this.value = ''; return; }
            if (val && idx < 5) otpInputs[idx + 1].focus();
            hideError(errOtp);
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                otpInputs[idx - 1].focus();
            }
        });
    });

    // Paste support for OTP
    document.getElementById('ec-otp-inputs')?.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        if (!pasted) return;
        otpInputs.forEach((inp, i) => { inp.value = pasted[i] || ''; });
        const focusIdx = Math.min(pasted.length, 5);
        otpInputs[focusIdx]?.focus();
    });

    function getAuthHeaders() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + window.authToken,
        };
    }

    // Send OTP
    sendBtn?.addEventListener('click', async function() {
        const newEmail = newEmailInput.value.trim();
        const confirmEmail = confirmEmailInput.value.trim();
        const password = passwordInput.value;

        if (!newEmail || !confirmEmail || !password) {
            showError(errForm, 'Please fill in all fields.');
            return;
        }
        if (newEmail !== confirmEmail) {
            showError(errForm, 'Emails do not match.');
            return;
        }

        loading = true;
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending…';
        hideError(errForm);

        try {
            const res = await fetch('/email-otp/send', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({
                    new_email: newEmail,
                    new_email_confirmation: confirmEmail,
                    current_password: password,
                }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to send code.');

            currentNewEmail = newEmail;
            stepForm.style.display = 'none';
            stepOtp.style.display = 'block';
            ecIcon.className = 'fa-solid fa-shield-check';
            ecTitle.textContent = 'Verify the code';
            ecOtpSentTo.textContent = 'Enter the code sent to ' + newEmail + '. It expires in 10 minutes.';
            setTimeout(() => otpInputs[0]?.focus(), 50);
        } catch (err) {
            showError(errForm, err.message);
        } finally {
            loading = false;
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send verification code';
        }
    });

    // Verify OTP
    verifyBtn?.addEventListener('click', async function() {
        const otp = Array.from(otpInputs).map(inp => inp.value).join('');
        if (otp.length !== 6) {
            showError(errOtp, 'Enter the 6-digit code.');
            return;
        }

        loading = true;
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Verifying…';
        hideError(errOtp);

        try {
            const res = await fetch('/email-otp/verify', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({ otp }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Verification failed.');

            // Update the displayed email in the profile
            const emailDisplay = document.querySelector('.form-value');
            if (emailDisplay) emailDisplay.textContent = currentNewEmail;

            closeModal();
            alert('Email changed successfully.');
        } catch (err) {
            showError(errOtp, err.message);
            otpInputs.forEach(inp => inp.value = '');
            setTimeout(() => otpInputs[0]?.focus(), 50);
        } finally {
            loading = false;
            verifyBtn.disabled = false;
            verifyBtn.textContent = 'Verify & change email';
        }
    });
})();

document.getElementById('avatar-input')?.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        document.getElementById('avatar-form').submit();
    }
});

document.querySelectorAll('.portfolio-upload-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
            document.getElementById('portfolio-input').click();
        }
    });
});

document.getElementById('portfolio-input')?.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        document.getElementById('portfolio-form').submit();
    }
});

document.querySelectorAll('.doc-file-input').forEach(input => {
    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            this.closest('form').submit();
        }
    });
});

// Barangay coverage chips
const hiddenInput = document.getElementById('service_areas_input');

document.querySelectorAll('.barangay-chip').forEach(chip => {
    chip.addEventListener('click', function(e) {
        const cb = this.querySelector('.barangay-checkbox');
        cb.checked = !cb.checked;
        this.classList.toggle('selected', cb.checked);
        updateBarangayInput();
    });
});

function updateBarangayInput() {
    const checked = document.querySelectorAll('.barangay-checkbox:checked');
    hiddenInput.value = Array.from(checked).map(cb => cb.value).join(',');
}

// Tag Input Component
function initTagInput(wrapEl) {
    const list = wrapEl.querySelector('.tag-list');
    const field = list.querySelector('.tag-field');
    const hidden = wrapEl.parentElement.querySelector('input[type="hidden"]');

    function syncHidden() {
        const chips = list.querySelectorAll('.tag-chip');
        hidden.value = Array.from(chips).map(c => c.dataset.value).join(',');
    }

    function addTag(text) {
        text = text.trim();
        if (!text) return;
        const existing = list.querySelectorAll('.tag-chip');
        for (const chip of existing) {
            if (chip.dataset.value.toLowerCase() === text.toLowerCase()) return;
        }
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.dataset.value = text;
        chip.innerHTML = text + '<span class="tag-remove">&times;</span>';
        chip.querySelector('.tag-remove').addEventListener('click', function () {
            chip.remove();
            syncHidden();
        });
        list.insertBefore(chip, field);
        field.value = '';
        syncHidden();
    }

    field.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(this.value);
        }
    });
    field.addEventListener('paste', function (e) {
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        if (pasted.includes(',')) {
            e.preventDefault();
            pasted.split(',').forEach(function (s) { addTag(s); });
        }
    });
    // Initialize existing chips
    list.querySelectorAll('.tag-chip').forEach(function (chip) {
        chip.querySelector('.tag-remove').addEventListener('click', function () {
            chip.remove();
            syncHidden();
        });
    });
}

document.querySelectorAll('.tag-input-wrap').forEach(initTagInput);

// Availability component
(function () {
    const grid = document.getElementById('avail-grid');
    const hiddenInput = document.getElementById('availability-input');
    if (!grid) return;

    function serializeAvailability() {
        const rows = grid.querySelectorAll('.avail-row');
        const data = [];
        rows.forEach(function (row) {
            const cb = row.querySelector('.avail-checkbox');
            const start = row.querySelector('.avail-start');
            const end = row.querySelector('.avail-end');
            data.push({
                day: row.dataset.day,
                active: cb.checked,
                start: cb.checked ? start.value : null,
                end: cb.checked ? end.value : null,
            });
        });
        hiddenInput.value = JSON.stringify(data);
    }

    grid.addEventListener('change', function (e) {
        if (e.target.classList.contains('avail-checkbox')) {
            const row = e.target.closest('.avail-row');
            const times = row.querySelector('.avail-times');
            const start = row.querySelector('.avail-start');
            const end = row.querySelector('.avail-end');
            times.style.display = e.target.checked ? '' : 'none';
            if (!e.target.checked) {
                start.value = '08:00';
                end.value = '17:00';
            }
            serializeAvailability();
        }
        if (e.target.classList.contains('avail-start') || e.target.classList.contains('avail-end')) {
            serializeAvailability();
        }
    });

    // Apply same time to all active days
    document.getElementById('apply-all-times')?.addEventListener('click', function () {
        const firstActive = grid.querySelector('.avail-row:has(.avail-checkbox:checked)');
        if (!firstActive) return;
        const refStart = firstActive.querySelector('.avail-start').value;
        const refEnd = firstActive.querySelector('.avail-end').value;
        grid.querySelectorAll('.avail-row:has(.avail-checkbox:checked)').forEach(function (row) {
            row.querySelector('.avail-start').value = refStart;
            row.querySelector('.avail-end').value = refEnd;
        });
        serializeAvailability();
    });
})();
</script>
@endpush
