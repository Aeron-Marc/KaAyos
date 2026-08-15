@extends('layouts.admin')

@section('title', 'Verification Details')
@section('content')
<a href="{{ route('admin.verification.index') }}" class="back-link">
    <i class="fa-solid fa-arrow-left"></i> Back to Verifications
</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-user-check"></i> Verification Details</h1>
        <p>Review worker registration and supporting documents</p>
    </div>
    <div>
        <span class="status-badge status-{{ $verification->status === 'verified' ? 'approved' : $verification->status }}">
            @if($verification->status === 'pending')<i class="fa-solid fa-hourglass-half"></i>
            @elseif($verification->status === 'verified')<i class="fa-solid fa-check-circle"></i>
            @elseif($verification->status === 'rejected')<i class="fa-solid fa-x-circle"></i>
            @endif
            {{ ucfirst($verification->status) }}
        </span>
    </div>
</div>

<div style="display:flex;flex-direction:column;gap:28px;margin-bottom:28px">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-id-card"></i> Worker Registration Details</div>

        <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;">
            <div style="width:80px;height:80px;border-radius:12px;background:linear-gradient(135deg,var(--b4),var(--b6));display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.2rem;font-weight:700;flex-shrink:0;box-shadow:0 4px 12px rgba(26,111,196,.2)">
                {{ strtoupper(substr($verification->user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($verification->user->last_name ?? 'N', 0, 1)) }}
            </div>
            <div>
                <h2 style="font-size:1.4rem;font-weight:700;color:var(--b9);margin:0 0 4px 0">{{ $verification->user->name ?? 'Unknown' }}</h2>
                <p style="font-size:.9rem;color:var(--g4);margin:0">
                    {{ $verification->user->workerProfile?->skills ? implode(', ', array_slice($verification->user->workerProfile->skills, 0, 3)) : 'No skills listed' }}
                    {{ $verification->user->workerProfile?->years_of_experience ? "— {$verification->user->workerProfile->years_of_experience} years experience" : '' }}
                </p>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Full Name</span><span class="detail-value">{{ $verification->user->name ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Email Address</span><span class="detail-value">{{ $verification->user->email ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Phone Number</span><span class="detail-value">{{ $verification->user->phone ?? 'N/A' }}</span></div>
        </div>

        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Service Category</span><span class="detail-value">{{ $verification->user->service_category ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">City</span><span class="detail-value">{{ $verification->user->city ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Date Submitted</span><span class="detail-value">{{ $verification->created_at?->format('F d, Y \a\t g:i A') ?? 'N/A' }}</span></div>
        </div>

        @if($verification->admin_notes)
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Admin Notes</span><span class="detail-value" style="text-align:left;max-width:60%;white-space:pre-line">{{ $verification->admin_notes }}</span></div>
            @if($verification->reviewedBy)
            <div class="detail-row"><span class="detail-label">Reviewed By</span><span class="detail-value">{{ $verification->reviewedBy->name }}</span></div>
            @endif
            @if($verification->reviewed_at)
            <div class="detail-row"><span class="detail-label">Reviewed At</span><span class="detail-value">{{ $verification->reviewed_at->format('F d, Y \a\t g:i A') }}</span></div>
            @endif
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title"><i class="fa-solid fa-file-invoice"></i> Supporting Documents</div>

        <div class="info-box">
            <i class="fa-solid fa-info-circle"></i>
            <span>Approve or reject each submitted document individually. Documents not yet submitted are shown as placeholders.</span>
        </div>

        <div class="document-grid">
            @foreach($slots as $slot)
            @php
                $doc = $slot['doc'];
                $ext = $doc && $doc->file_path ? strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)) : null;
                $isImage = $ext ? in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) : false;
                $isPdf = $ext === 'pdf';
                $src = $doc && $doc->file_path ? asset('storage/' . $doc->file_path) : null;
            @endphp
            <div>
                <label class="document-label"><i class="fa-solid {{ $slot['icon'] }}"></i> {{ $slot['name'] }}</label>

                @if($doc && $doc->file_path)
                    @if($isImage)
                        <img src="{{ $src }}" alt="{{ $slot['name'] }}" class="preview-doc-img" style="cursor:zoom-in" loading="lazy" data-doc-src="{{ $src }}" data-doc-title="{{ $slot['name'] }}" data-doc-is-image="1">
                    @elseif($isPdf)
                        <iframe src="{{ $src }}" title="{{ $slot['name'] }}" class="preview-doc-img" style="border:2px solid var(--g1);border-radius:12px;background:#fff" loading="lazy"></iframe>
                        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
                            <button type="button" class="btn btn-primary btn-xs" data-doc-src="{{ $src }}" data-doc-title="{{ $slot['name'] }}" data-doc-is-image="0"><i class="fa-solid fa-eye"></i> Open</button>
                            <a href="{{ $src }}" download class="btn btn-secondary btn-xs"><i class="fa-solid fa-download"></i> Download</a>
                        </div>
                    @else
                        <div class="document-placeholder">
                            <div class="document-placeholder-icon"><i class="fa-solid fa-file"></i></div>
                            <div class="document-placeholder-text">{{ $slot['name'] }}</div>
                            <div class="document-placeholder-subtext">Preview unavailable</div>
                        </div>
                        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
                            <button type="button" class="btn btn-primary btn-xs" data-doc-src="{{ $src }}" data-doc-title="{{ $slot['name'] }}" data-doc-is-image="0"><i class="fa-solid fa-eye"></i> Open</button>
                            <a href="{{ $src }}" download class="btn btn-secondary btn-xs"><i class="fa-solid fa-download"></i> Download</a>
                        </div>
                    @endif

                    <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center">
                        <span class="status-badge status-{{ $doc->status === 'verified' ? 'approved' : $doc->status }}" style="font-size:.75rem;padding:4px 8px">{{ ucfirst($doc->status) }}</span>
                        <span class="text-sm text-muted">{{ $doc->created_at?->format('M d') ?? 'N/A' }}</span>
                    </div>

                    @if($doc->status === 'pending')
                    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;border-top:1px dashed var(--g1);padding-top:10px">
                        <form method="POST" action="{{ route('admin.verification.approve', $doc) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-xs" onclick="if(!confirm('Approve this document?'))return false;this.disabled=true;this.form.submit();">
                                <i class="fa-solid fa-check-circle"></i> Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-xs" onclick="document.getElementById('reject-doc-{{ $doc->id }}').style.display='block'">
                            <i class="fa-solid fa-x-circle"></i> Reject
                        </button>
                    </div>

                    <div id="reject-doc-{{ $doc->id }}" style="display:none;margin-top:10px;border-top:1px dashed var(--d8);padding-top:10px">
                        <form method="POST" action="{{ route('admin.verification.reject', $doc) }}">
                            @csrf
                            <div class="form-group">
                                <label for="rejection_reason_{{ $doc->id }}">Rejection Reason <span style="color:var(--d10)">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason_{{ $doc->id }}" rows="2" placeholder="Explain why this document is being rejected..." required></textarea>
                                @error('rejection_reason') <div class="error">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label for="notes_{{ $doc->id }}">Private Notes (optional)</label>
                                <textarea name="notes" id="notes_{{ $doc->id }}" rows="1" placeholder="Internal notes..."></textarea>
                            </div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <button type="submit" class="btn btn-danger btn-xs" onclick="this.disabled=true;this.form.submit();"><i class="fa-solid fa-x-circle"></i> Confirm Rejection</button>
                                <button type="button" class="btn btn-secondary btn-xs" onclick="document.getElementById('reject-doc-{{ $doc->id }}').style.display='none'">Cancel</button>
                            </div>
                        </form>
                    </div>
                    @endif
                @else
                    <div class="document-placeholder">
                        <div class="document-placeholder-icon"><i class="fa-solid {{ $slot['icon'] }}"></i></div>
                        <div class="document-placeholder-text">{{ $slot['name'] }}</div>
                        <div class="document-placeholder-subtext">No document submitted.</div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

@if($verification->status === 'pending')
<div class="action-bar">
    <div>
        <span style="font-size:.85rem;font-weight:600;color:var(--g7);text-transform:uppercase;letter-spacing:.04em;">Application Status</span>
        <span style="display:block;font-size:1rem;font-weight:700;color:var(--b9);">Ready for Decision</span>
    </div>
    <div class="page-actions">
        <form method="POST" action="{{ route('admin.verification.approve', $verification) }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-success" onclick="if(!confirm('Approve this verification?'))return false;this.disabled=true;this.form.submit();">
                <i class="fa-solid fa-check-circle"></i> Approve Verification
            </button>
        </form>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('reject-form').style.display='block'">
            <i class="fa-solid fa-x-circle"></i> Reject Application
        </button>
    </div>
</div>

<div id="reject-form" style="display:none;margin-top:20px;">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-xmark" style="color:var(--d10)"></i> Reject Application</div>
        <form method="POST" action="{{ route('admin.verification.reject', $verification) }}">
            @csrf
            <div class="form-group">
                <label for="rejection_reason">Rejection Reason <span style="color:var(--d10)">*</span></label>
                <textarea name="rejection_reason" id="rejection_reason" rows="3" placeholder="Explain why this application is being rejected..." required></textarea>
                @error('rejection_reason') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="notes">Private Notes (optional)</label>
                <textarea name="notes" id="notes" rows="2" placeholder="Internal notes..."></textarea>
            </div>
            <div class="page-actions">
                <button type="submit" class="btn btn-danger" onclick="this.disabled=true;this.form.submit();"><i class="fa-solid fa-x-circle"></i> Confirm Rejection</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-form').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- DOCUMENT VIEWER MODAL -->
<div id="docViewer" class="doc-viewer" onclick="closeDocViewer()">
    <div class="doc-viewer-panel" onclick="event.stopPropagation()">
        <div class="doc-viewer-head">
            <span class="doc-viewer-title" id="docViewerTitle">Document</span>
            <div class="doc-viewer-actions">
                <a class="btn btn-secondary btn-xs" id="docViewerDownload" href="#" download><i class="fa-solid fa-download"></i> Download</a>
                <button type="button" class="doc-viewer-close" onclick="closeDocViewer()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <div class="doc-viewer-body" id="docViewerBody"></div>
    </div>
</div>

@push('styles')
<style>
    .doc-viewer{position:fixed;inset:0;background:rgba(11,20,30,.75);z-index:1000;display:none;align-items:center;justify-content:center;padding:24px;backdrop-filter:blur(2px)}
    .doc-viewer.active{display:flex}
    .doc-viewer-panel{background:#fff;border-radius:16px;max-width:96vw;width:100%;height:92vh;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4)}
    .doc-viewer-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;border-bottom:1px solid var(--g1)}
    .doc-viewer-title{font-size:1rem;font-weight:600;color:var(--b9);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .doc-viewer-actions{display:flex;align-items:center;gap:10px;flex-shrink:0}
    .doc-viewer-close{width:36px;height:36px;border-radius:8px;border:none;background:var(--off);color:var(--g7);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .18s}
    .doc-viewer-close:hover{background:var(--d10);color:#fff}
    .doc-viewer-body{flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;background:var(--off);padding:16px}
    .doc-viewer-body iframe{width:100%;height:100%;border:none;border-radius:8px;background:#fff}
    .doc-viewer-body img{max-width:100%;max-height:100%;object-fit:contain;border-radius:8px}
    @media(max-width:768px){.doc-viewer-panel{height:85vh}}
</style>
@endpush
@push('scripts')
<script>
function openDocViewer(src, title, isImage) {
    var body = document.getElementById('docViewerBody');
    var titleEl = document.getElementById('docViewerTitle');
    var dl = document.getElementById('docViewerDownload');
    if (!body) return;
    body.innerHTML = '';
    if (isImage) {
        var img = document.createElement('img');
        img.src = src;
        img.alt = title || 'Document';
        body.appendChild(img);
    } else {
        var frame = document.createElement('iframe');
        frame.src = src;
        frame.title = title || 'Document';
        body.appendChild(frame);
    }
    titleEl.textContent = title || 'Document';
    if (dl) dl.href = src;
    document.getElementById('docViewer').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeDocViewer() {
    var viewer = document.getElementById('docViewer');
    if (!viewer) return;
    viewer.classList.remove('active');
    var body = document.getElementById('docViewerBody');
    if (body) body.innerHTML = '';
    document.body.style.overflow = '';
}
document.addEventListener('click', function(e) {
    var el = e.target.closest('[data-doc-src]');
    if (!el) return;
    openDocViewer(el.getAttribute('data-doc-src'),
                  el.getAttribute('data-doc-title'),
                  el.getAttribute('data-doc-is-image') === '1');
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDocViewer();
});
</script>
@endpush
@endsection
