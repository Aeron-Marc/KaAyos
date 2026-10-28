@extends('layouts.client')

@section('title', 'Verify Identity')
@section('page_title', 'Verify Your Identity')

@section('content')

@php
    $user = auth()->user();
    $status = $user->clientIdentityStatus();
    $isPending = $status === 'pending';
    $canReplace = in_array($status, ['rejected', 'resubmission_required'], true);
    $latest = $user->latestClientIdentityVerification;
    $idTypes = config('kaayos.client_identity.id_types', []);
@endphp

@if(session('success'))
    <div class="info-banner info-banner-success" role="status">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="info-banner info-banner-error" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Status banner -->
<div class="status-banner status-{{ $status }}">
    <div class="status-banner-inner">
        @if($status === 'pending')
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            <span class="status-banner-title">{{ __('client.identity.status.pending') }}</span>
            <span class="status-banner-text">{{ __('client.identity.pending_description') }}</span>
        @elseif($status === 'verified')
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span class="status-banner-title">{{ __('client.identity.status.verified') }}</span>
            <span class="status-banner-text">{{ __('client.identity.verified_description') }}</span>
        @elseif($status === 'rejected')
            <i class="fa-solid fa-x-circle" aria-hidden="true"></i>
            <span class="status-banner-title">{{ __('client.identity.status.rejected') }}</span>
            <span class="status-banner-text">{{ $latest->rejection_reason ?? __('client.identity.rejected_description') }}</span>
        @elseif($status === 'resubmission_required')
            <i class="fa-solid fa-pen" aria-hidden="true"></i>
            <span class="status-banner-title">{{ __('client.identity.status.resubmission_required') }}</span>
            <span class="status-banner-text">{{ $latest->rejection_reason ?? __('client.identity.resubmission_description') }}</span>
        @else
            <i class="fa-regular fa-circle-dot" aria-hidden="true"></i>
            <span class="status-banner-title">{{ __('client.identity.status.not_submitted') }}</span>
            <span class="status-banner-text">{{ __('client.identity.not_submitted_description') }}</span>
        @endif
    </div>
</div>

<!-- Submission details (when a submission exists) -->
@if($latest)
    <div class="submission-meta">
        <div class="meta-row">
            <span class="meta-label">{{ __('client.identity.id_type') }}</span>
            <span class="meta-value">{{ ucfirst(str_replace('_', ' ', $latest->id_type)) }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">{{ __('client.identity.submitted_at') }}</span>
            <span class="meta-value">{{ $latest->submitted_at?->format('M d, Y \a\t g:i A') ?? 'N/A' }}</span>
        </div>
        @if($latest->reviewed_at)
            <div class="meta-row">
                <span class="meta-label">{{ __('client.identity.reviewed_at') }}</span>
                <span class="meta-value">{{ $latest->reviewed_at->format('M d, Y \a\t g:i A') }}</span>
            </div>
        @endif
    </div>
@endif

<!-- Upload form -->
@if(!$isPending)
    <form id="identity-form" method="POST" action="{{ route('client.account.identity.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-section">
            <h3 class="form-section-title">{{ __('client.identity.documents') }}</h3>

            <div class="upload-grid">
                <div class="upload-zone" id="zone-id">
                    <input type="file" name="id" id="file-id" accept=".jpg,.jpeg,.png,.pdf" required
                           {{ $isPending ? 'disabled' : '' }}
                           data-zone="id"
                           data-capture="environment">
                    <label class="upload-label" for="file-id" data-zone-label="id">
                        <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                        <span class="upload-title">{{ __('client.identity.upload_id_label') }}</span>
                        <span class="upload-subtitle">{{ __('client.identity.upload_id_subtitle') }}</span>
                        <span class="upload-hint" id="hint-id">JPG, PNG or PDF &middot; Original filename is not stored.</span>
                        <span class="upload-selected" id="selected-id"></span>
                    </label>
                </div>

                <div class="upload-zone" id="zone-selfie">
                    <input type="file" name="selfie" id="file-selfie" accept="image/*" required
                           {{ $isPending ? 'disabled' : '' }}
                           data-zone="selfie"
                           data-capture="user">
                    <label class="upload-label" for="file-selfie" data-zone-label="selfie">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <span class="upload-title">{{ __('client.identity.upload_selfie_label') }}</span>
                        <span class="upload-subtitle">{{ __('client.identity.upload_selfie_subtitle') }}</span>
                        <span class="upload-hint" id="hint-selfie">JPG, PNG or WEBP &middot; Minimum 400x400 pixels.</span>
                        <span class="upload-selected" id="selected-selfie"></span>
                    </label>
                </div>

                <div class="preview-panel">
                    <div class="preview-empty" id="preview-empty">
                        <i class="fa-solid fa-image" aria-hidden="true"></i>
                        <span>{{ __('client.identity.preview_empty') }}</span>
                    </div>

                    <div class="preview-grid" id="preview-grid" style="display:none;">
                        <div class="preview-item" id="preview-id-item">
                            <img id="preview-id-img" src="" alt="{{ __('client.identity.preview_id_label') }}">
                            <button type="button" class="preview-remove" data-zone="id" aria-label="{{ __('client.identity.remove_id') }}">
                                <i class="fa-solid fa-times" aria-hidden="true"></i>
                            </button>
                            <div class="preview-name" id="preview-id-name"></div>
                        </div>

                        <div class="preview-item" id="preview-selfie-item">
                            <img id="preview-selfie-img" src="" alt="{{ __('client.identity.preview_selfie_label') }}">
                            <button type="button" class="preview-remove" data-zone="selfie" aria-label="{{ __('client.identity.remove_selfie') }}">
                                <i class="fa-solid fa-times" aria-hidden="true"></i>
                            </button>
                            <div class="preview-name" id="preview-selfie-name"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="form-section-title">{{ __('client.identity.attestation') }}</h3>

            <div class="attestation-list">
                <label class="attestation-item">
                    <input type="checkbox" name="consent" id="consent" value="1" {{ old('consent') ? 'checked' : '' }} required {{ $isPending ? 'disabled' : '' }}>
                    <span class="attestation-mark"></span>
                    <span class="attestation-text">{{ __('client.identity.consent') }}</span>
                </label>

                <label class="attestation-item">
                    <input type="checkbox" name="age_attested" id="age_attested" value="1" {{ old('age_attested') ? 'checked' : '' }} required {{ $isPending ? 'disabled' : '' }}>
                    <span class="attestation-mark"></span>
                    <span class="attestation-text">{{ __('client.identity.age_attest') }}</span>
                </label>

                <p class="attestation-note">{{ __('client.identity.age_note') }}</p>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-solid btn-submit" {{ $isPending ? 'disabled' : '' }}>
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                <span class="submit-text">{{ $isPending ? __('client.identity.disabled_pending') : __('client.identity.submit') }}</span>
            </button>

            @if($canReplace)
                <p class="replace-hint">{{ __('client.identity.replace_hint') }}</p>
            @endif
        </div>
    </form>
@else
    <div class="pending-guard">
        <div class="pending-guard-inner">
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            <div>
                <span class="pending-title">{{ __('client.identity.disabled_pending') }}</span>
                <span class="pending-text">{{ __('client.identity.pending_review_desc') }}</span>
            </div>
        </div>
    </div>
@endif

<!-- Submission history -->
@if($latest)
    <div class="form-section history-section">
        <h3 class="form-section-title">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            {{ __('client.identity.history') }}
        </h3>

        @if($history->count())
            <div class="history-list">
                @foreach($history as $record)
                    <div class="history-item">
                        <div class="history-meta">
                            <span class="history-date">{{ $record->submitted_at?->format('M d, Y') }}</span>
                            <span class="history-type">{{ ucfirst(str_replace('_', ' ', $record->id_type)) }}</span>
                        </div>
                        <x-status-badge :status="$record->status" />
                        @if($record->rejection_reason)
                            <span class="history-reason">{{ $record->rejection_reason }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">{{ __('client.identity.history_empty') }}</p>
        @endif
    </div>
@endif

<!-- Privacy notice -->
<div class="privacy-notice">
    <div class="privacy-notice-inner">
        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        <div>
            <span class="privacy-title">{{ __('client.identity.privacy_notice_title') }}</span>
            <span class="privacy-text">
                {{ __('client.identity.privacy_body') }}
                <a href="{{ route('privacy') }}">{{ __('client.identity.privacy_link') }}</a>
                {{ __('client.identity.and') }}
                <a href="{{ route('contact') }}">{{ __('client.identity.contact_link') }}</a>.
            </span>
        </div>
    </div>
</div>

@push('styles')
<style>
.status-banner {
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 18px;
    border-radius:var(--radius);
    margin-bottom:20px;
    font-size:.95rem;
    line-height:1.5;
    animation:statusFade .35s ease;
}
@keyframes statusFade{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}

.status-banner-inner{display:flex;align-items:center;gap:10px;flex:1;}
.status-banner i{flex-shrink:0;width:20px;text-align:center;}
.status-banner-title{font-weight:700;color:var(--b9);display:block;}
.status-banner-text{color:var(--g6);}

.status-banner.status-pending{background:var(--orange-light);border-left:4px solid var(--orange);color:var(--orange-dark);}
.status-banner.status-pending i{color:var(--orange);}
.status-banner.status-verified{background:var(--green-light);border-left:4px solid var(--green);color:var(--green-dark);}
.status-banner.status-verified i{color:var(--green);}
.status-banner.status-rejected{background:var(--red-light);border-left:4px solid var(--red);color:var(--red-dark);}
.status-banner.status-rejected i{color:var(--red);}
.status-banner.status-resubmission_required{background:var(--orange-light);border-left:4px solid var(--orange);color:var(--orange-dark);}
.status-banner.status-resubmission_required i{color:var(--orange);}
.status-banner.status-not_submitted{background:#eef4fb;border-left:4px solid var(--b6);color:var(--b8);}
.status-banner.status-not_submitted i{color:var(--b6);}

.submission-meta{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;padding:14px 18px;background:var(--off);border:1px solid var(--g1);border-radius:var(--radius);}
.meta-row{display:flex;justify-content:space-between;gap:12px;font-size:.9rem;}
.meta-label{color:var(--g5);}
.meta-value{color:var(--b9);font-weight:600;}

.form-section{margin-bottom:20px;}
.form-section-title{font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:var(--g5);margin-bottom:12px;color:var(--b9);font-weight:600;}

/* Upload zones */
.upload-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;align-items:start;}
.upload-zone{position:relative;border:2px dashed var(--g2);border-radius:var(--radius-sm);background:var(--white);transition:border-color .2s,background .2s;min-height:170px;overflow:hidden;}
.upload-zone:hover{border-color:var(--b6);background:#f4f9ff;}
.upload-zone.has-file{border-color:var(--green);background:#dcfce7;}

.upload-label{display:flex;flex-direction:column;align-items:center;gap:8px;padding:22px 14px;cursor:pointer;width:100%;}
.upload-label i{font-size:1.5rem;color:var(--g5);}
.upload-title{font-weight:600;color:var(--b9);font-size:.9rem;}
.upload-subtitle{font-size:.78rem;color:var(--g6);margin-top:2px;}
.upload-hint{font-size:.72rem;color:var(--g4);margin-top:6px;}
.upload-selected{font-size:.75rem;color:var(--green);margin-top:6px;word-break:break-word;}

.upload-zone input{position:absolute;inset:0;opacity:0;z-index:2;cursor:pointer;width:100%;height:100%;}

/* Preview panel */
.preview-panel{border:2px dashed var(--g2);border-radius:var(--radius-sm);background:var(--white);min-height:170px;overflow:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.preview-empty{color:var(--g4);font-size:.85rem;text-align:center;}
.preview-empty i{font-size:1.75rem;color:var(--g3);margin-bottom:8px;}
.preview-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;width:100%;padding:12px;}
.preview-item{border:1px solid var(--g1);border-radius:var(--radius-sm);background:var(--off);position:relative;overflow:hidden;display:flex;flex-direction:column;}
.preview-item img{width:100%;height:110px;object-fit:cover;background:#fff;}
.preview-remove{position:absolute;top:8px;right:8px;width:24px;height:24px;border:none;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;cursor:pointer;font-size:.65rem;display:flex;align-items:center;justify-content:center;z-index:2;}
.preview-name{font-size:.7rem;color:var(--g5);padding:6px 8px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

/* Attestation */
.attestation-list{display:flex;flex-direction:column;gap:10px;}
.attestation-item{display:flex;align-items:flex-start;gap:10px;cursor:pointer;}
.attestation-item input{width:18px;height:18px;margin-top:.1rem;flex-shrink:0;}
.attestation-mark{width:18px;height:18px;border:2px solid var(--g2);border-radius:4px;flex-shrink:0;margin-top:.05rem;transition:border-color .2s,background .2s;}
.attestation-item input:checked + .attestation-mark{background:var(--b6);border-color:var(--b6);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffffff'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E");background-size:14px;background-repeat:no-repeat;background-position:center;}
.attestation-text{font-size:.92rem;color:var(--b9);line-height:1.4;}
.attestation-item input:disabled + .attestation-mark{border-color:var(--g2);background:#eee;cursor:not-allowed;}
.attestation-item input:disabled{opacity:0;}
.attestation-note{font-size:.8rem;color:var(--g4);margin-top:6px;}

/* Actions */
.form-actions{display:flex;flex-direction:column;gap:10px;margin-top:4px;}
.btn-submit{width:100%;padding:12px 20px;font-size:.95rem;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-submit i{font-size:1rem;}
.btn-submit:disabled{opacity:.6;cursor:not-allowed;}
.replace-hint{font-size:.8rem;color:var(--g4);margin-top:4px;text-align:center;}

/* Pending guard */
.pending-guard{border:2px dashed var(--g2);border-radius:var(--radius);background:var(--white);padding:24px;text-align:center;margin-bottom:20px;}
.pending-guard-inner{display:flex;align-items:center;gap:12px;justify-content:center;color:var(--g5);}
.pending-guard i{color:var(--orange);font-size:1.25rem;}
.pending-title{font-weight:700;color:var(--b9);display:block;font-size:.95rem;}
.pending-text{color:var(--g6);font-size:.88rem;}

/* History */
.history-list{display:flex;flex-direction:column;gap:8px;}
.history-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 14px;border:1px solid var(--g1);border-radius:var(--radius-sm);background:var(--white);}
.history-meta{display:flex;gap:10px;flex:1;min-width:0;}
.history-date{font-size:.82rem;color:var(--g5);white-space:nowrap;}
.history-type{font-size:.82rem;color:var(--b9);font-weight:600;white-space:nowrap;}
.history-reason{font-size:.82rem;color:var(--red-dark);white-space:nowrap;}

/* Privacy notice */
.privacy-notice{margin-top:4px;}
.privacy-notice-inner{display:flex;align-items:flex-start;gap:10px;padding:14px 18px;background:#f4f7fa;border:1px solid var(--g1);border-radius:var(--radius);}
.privacy-notice-inner i{color:var(--b6);flex-shrink:0;margin-top:2px;}
.privacy-notice-inner div{flex:1;}
.privacy-title{font-weight:700;color:var(--b9);font-size:.85rem;display:block;margin-bottom:4px;}
.privacy-text{font-size:.8rem;color:var(--g6);line-height:1.5;}
.privacy-text a{color:var(--b6);text-decoration:underline;}

/* Responsive */
@media(max-width:640px){
    .status-banner{padding:12px 16px;}
    .status-banner-title{font-size:.9rem;}
    .status-banner-text{font-size:.85rem;}
    .upload-grid{grid-template-columns:1fr;}
    .preview-grid{grid-template-columns:1fr;}
    .form-section-title{font-size:.8rem;}
    .history-item{flex-direction:column;align-items:flex-start;gap:6px;}
    .history-meta{flex-direction:column;align-items:flex-start;gap:4px;}
    .history-date{font-size:.78rem;}
    .history-type{font-size:.78rem;}
    .history-reason{font-size:.78rem;}
}
</style>
@endpush

@push('scripts')
<script>
(function(){
    var zones = ['id', 'selfie'];
    var previewImages = {};

    zones.forEach(function(zone){
        var input = document.getElementById('file-' + zone);
        var label = document.querySelector('[data-zone-label="' + zone + '"]');
        var selected = document.getElementById('selected-' + zone);
        var item = document.getElementById('preview-' + zone + '-item');
        var img = document.getElementById('preview-' + zone + '-img');
        var nameEl = document.getElementById('preview-' + zone + '-name');

        // Set capture attribute for mobile camera
        var capture = input.getAttribute('data-capture');
        if(capture) input.setAttribute('capture', capture);

        input.addEventListener('change', function(){
            var file = this.files[0];
            if(!file) return;

            // Show selected filename
            if(selected){
                selected.textContent = file.name + ' &middot; ' + (file.size/1024).toFixed(1) + ' KB';
                selected.style.display = 'block';
            }

            // Clamp parent zone styling
            var zoneEl = document.getElementById('zone-' + zone);
            if(zoneEl){
                zoneEl.classList.add('has-file');
                var i = zoneEl.querySelector('i');
                if(i) i.style.color = 'var(--green)';
            }

            // Image preview
            if(file.type.startsWith('image/')){
                var url = URL.createObjectURL(file);
                previewImages[zone] = url;
                img.src = url;
                if(nameEl) nameEl.textContent = file.name;
                if(item) item.style.display = 'flex';
                document.getElementById('preview-grid').style.display = 'grid';
                document.getElementById('preview-empty').style.display = 'none';
            } else {
                if(item) item.style.display = 'none';
                var grid = document.getElementById('preview-grid');
                var anyVisible = zones.some(function(z){ return document.getElementById('preview-' + z + '-item').style.display === 'flex'; });
                grid.style.display = anyVisible ? 'grid' : 'none';
                document.getElementById('preview-empty').style.display = anyVisible ? 'none' : 'block';
            }
        });

        // Remove preview
        var removeBtn = item ? item.querySelector('.preview-remove') : null;
        if(removeBtn){
            removeBtn.addEventListener('click', function(e){
                e.stopPropagation();
                input.value = '';
                if(selected) selected.style.display = 'none';
                if(item) item.style.display = 'none';
                var zoneEl = document.getElementById('zone-' + zone);
                if(zoneEl){
                    zoneEl.classList.remove('has-file');
                    var i = zoneEl.querySelector('i');
                    if(i) i.style.color = '';
                }
                document.getElementById('preview-grid').style.display = 'none';
                document.getElementById('preview-empty').style.display = 'block';
                delete previewImages[zone];
            });
        }
    });

    // Disable form if pending
    var pending = document.body.classList.contains('identity-pending') || false;
    if(pending){
        var form = document.getElementById('identity-form');
        if(form){
            form.querySelectorAll('input, select, button').forEach(function(el){ el.disabled = true; });
        }
    }
})();
</script>
@endpush

@endsection
