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
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', auth()->user()->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone"
                               value="{{ old('phone', auth()->user()->phone) }}">
                    </div>
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
                    <label for="skills">Skills (comma-separated)</label>
                    <input type="text" id="skills" name="skills" placeholder="e.g. Plumbing, Electrical, Carpentry"
                           value="{{ old('skills', $workerProfile->skills ? implode(', ', $workerProfile->skills) : '') }}">
                    <div class="skill-tags" style="margin-top:8px;">
                        @if($workerProfile->skills)
                            @foreach($workerProfile->skills as $skill)
                                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;">{{ $skill }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="form-group" style="margin-top:12px;">
                    <label for="spoken_languages">Spoken Languages (comma-separated)</label>
                    <input type="text" id="spoken_languages" name="spoken_languages" placeholder="e.g. Filipino, English, Cebuano"
                           value="{{ old('spoken_languages', $workerProfile->spoken_languages ? implode(', ', $workerProfile->spoken_languages) : '') }}">
                    <div class="skill-tags" style="margin-top:8px;">
                        @if($workerProfile->spoken_languages)
                            @foreach($workerProfile->spoken_languages as $lang)
                                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;">{{ $lang }}</span>
                            @endforeach
                        @endif
                    </div>
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="available_days">Available Days</label>
                        <select id="available_days" name="available_days">
                            <option value="">Select schedule</option>
                            @foreach(['Monday — Friday', 'Monday — Saturday', 'All Week', 'Weekends Only'] as $days)
                                <option value="{{ $days }}" {{ old('available_days', $workerProfile->available_days) === $days ? 'selected' : '' }}>{{ $days }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="preferred_hours">Preferred Hours</label>
                        <select id="preferred_hours" name="preferred_hours">
                            <option value="">Select hours</option>
                            @foreach(['Morning (8 AM — 12 PM)', 'Full Day (8 AM — 5 PM)', 'Afternoon (1 PM — 5 PM)', 'Flexible'] as $hours)
                                <option value="{{ $hours }}" {{ old('preferred_hours', $workerProfile->preferred_hours) === $hours ? 'selected' : '' }}>{{ $hours }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Service Coverage</h3>
                <div class="form-group">
                    <label for="service_areas">Service Areas (comma-separated)</label>
                    <input type="text" id="service_areas" name="service_areas" placeholder="e.g. Tuy, Batangas, Nasugbu, Batangas, Balayan, Batangas"
                           value="{{ old('service_areas', $workerProfile->service_areas ? implode(', ', $workerProfile->service_areas) : '') }}">
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                        @if($workerProfile->service_areas)
                            @foreach($workerProfile->service_areas as $area)
                                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;background:var(--b0);border-color:var(--b1);color:var(--b6);">
                                    <i class="fa-solid fa-check" aria-hidden="true" style="margin-right:4px;"></i> {{ $area }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="form-row" style="margin-top:12px;">
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

@push('scripts')
<script>
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
</script>
@endpush
