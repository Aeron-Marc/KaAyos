@extends('layouts.admin')

@section('title', 'Client Identity Details')
@section('content')

<a href="{{ route('admin.client-verifications.index') }}" class="back-link">
    <i class="fa-solid fa-arrow-left"></i> Back to Client Verifications
</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-user-check"></i> Client Identity Details</h1>
        <p>Review government ID and selfie submission</p>
    </div>
    <div>
        <span class="status-badge status-{{ $verification->status }}">
            @if($verification->isPending())<i class="fa-solid fa-hourglass-half"></i>
            @elseif($verification->isVerified())<i class="fa-solid fa-check-circle"></i>
            @elseif($verification->isRejected())<i class="fa-solid fa-x-circle"></i>
            @elseif($verification->needsResubmission())<i class="fa-solid fa-pen"></i>
            @endif
            {{ __('client.identity.status.'.$verification->status) }}
        </span>
    </div>
</div>

<div style="display:flex;flex-direction:column;gap:28px;margin-bottom:28px">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-user"></i> Client Details</div>

        <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;">
            <div style="width:80px;height:80px;border-radius:12px;background:linear-gradient(135deg,var(--b4),var(--b6));display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.2rem;font-weight:700;flex-shrink:0;box-shadow:0 4px 12px rgba(26,111,196,.2)">
                {{ strtoupper(substr($verification->user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($verification->user->last_name ?? 'N', 0, 1)) }}
            </div>
            <div>
                <h2 style="font-size:1.4rem;font-weight:700;color:var(--b9);margin:0 0 4px 0">{{ $verification->user->name ?? 'Unknown' }}</h2>
                <p style="font-size:.9rem;color:var(--g4);margin:0">{{ $verification->user->email ?? __('admin.common.not_available') }}</p>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Full Name</span><span class="detail-value">{{ $verification->user->name ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Email Address</span><span class="detail-value">{{ $verification->user->email ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Phone Number</span><span class="detail-value">{{ $verification->user->phone ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">ID Type</span><span class="detail-value">{{ ucfirst(str_replace('_', ' ', $verification->id_type)) }}</span></div>
            <div class="detail-row"><span class="detail-label">Date Submitted</span><span class="detail-value">{{ $verification->submitted_at?->format('F d, Y \a\t g:i A') ?? 'N/A' }}</span></div>
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
        <div class="card-title"><i class="fa-solid fa-file-invoice"></i> Submitted Documents</div>

        <div class="info-box">
            <i class="fa-solid fa-info-circle"></i>
            <span>Documents streamed through a controlled route with restrictive headers. Original filenames are not stored.</span>
        </div>

        <div class="document-grid">
            @php
                $docs = [
                    'id' => ['label' => 'Government ID', 'path' => $verification->id_document_path, 'icon' => 'fa-id-card'],
                    'selfie' => ['label' => 'Selfie', 'path' => $verification->selfie_path, 'icon' => 'fa-user'],
                ];
            @endphp
            @foreach($docs as $key => $doc)
            @php
                $ext = $doc['path'] ? strtolower(pathinfo($doc['path'], PATHINFO_EXTENSION)) : null;
                $isImage = $ext ? in_array($ext, ['jpg','jpeg','png','webp']) : false;
                $isPdf = $ext === 'pdf';
                $src = $doc['path'] ? route('admin.client-verifications.document', [$verification, $key]) : null;
            @endphp
            <div>
                <label class="document-label"><i class="fa-solid {{ $doc['icon'] }}"></i> {{ $doc['label'] }}</label>

                @if($doc['path'])
                    @if($isImage)
                        <img src="{{ $src }}" alt="{{ $doc['label'] }}" class="preview-doc-img" style="cursor:zoom-in" data-doc-src="{{ $src }}" data-doc-title="{{ $doc['label'] }}" data-doc-is-image="1">
                    @elseif($isPdf)
                        <div style="border:2px solid var(--g1);border-radius:12px;background:#fff;overflow:hidden;aspect-ratio:3/4">
                            <iframe src="{{ $src }}" title="{{ $doc['label'] }}" style="width:100%;height:100%;border:none;background:#fff"></iframe>
                        </div>
                        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
                            <a href="{{ $src }}" download class="btn btn-secondary btn-xs"><i class="fa-solid fa-download"></i> Download</a>
                        </div>
                    @endif

                    <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center">
                        <span class="status-badge status-{{ $verification->status === 'verified' ? 'verified' : $verification->status }}" style="font-size:.75rem;padding:4px 8px">{{ ucfirst($verification->status) }}</span>
                        <span class="text-sm text-muted">{{ $verification->created_at?->format('M d') ?? 'N/A' }}</span>
                    </div>

                    @if($verification->isPending())
                    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;border-top:1px dashed var(--g1);padding-top:10px">
                        <form method="POST" action="{{ route('admin.client-verifications.approve', $verification) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-xs" onclick="if(!confirm('Approve this client identity?'))return false;this.disabled=true;this.form.submit();">
                                <i class="fa-solid fa-check-circle"></i> Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-xs" onclick="document.getElementById('reject-{{ $key }}').style.display='block'">
                            <i class="fa-solid fa-x-circle"></i> Reject
                        </button>
                        <button type="button" class="btn btn-warning btn-xs" onclick="document.getElementById('resubmit-{{ $key }}').style.display='block'">
                            <i class="fa-solid fa-pen"></i> Request Resubmission
                        </button>
                    </div>

                    <div id="reject-{{ $key }}" style="display:none;margin-top:10px;border-top:1px dashed var(--d8);padding-top:10px">
                        <form method="POST" action="{{ route('admin.client-verifications.reject', $verification) }}">
                            @csrf
                            <div class="form-group">
                                <label for="rejection_reason_{{ $key }}">Rejection Reason <span style="color:var(--d10)">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason_{{ $key }}" rows="2" placeholder="Explain why this submission is being rejected..." required></textarea>
                                @error('rejection_reason') <div class="error">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label for="notes_{{ $key }}">Private Notes (optional)</label>
                                <textarea name="notes" id="notes_{{ $key }}" rows="1" placeholder="Internal notes..."></textarea>
                            </div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <button type="submit" class="btn btn-danger btn-xs" onclick="this.disabled=true;this.form.submit();"><i class="fa-solid fa-x-circle"></i> Confirm Rejection</button>
                                <button type="button" class="btn btn-secondary btn-xs" onclick="document.getElementById('reject-{{ $key }}').style.display='none'">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <div id="resubmit-{{ $key }}" style="display:none;margin-top:10px;border-top:1px dashed var(--y8);padding-top:10px">
                        <form method="POST" action="{{ route('admin.client-verifications.request-resubmission', $verification) }}">
                            @csrf
                            <div class="form-group">
                                <label for="rejection_reason_{{ $key }}">Explanation for Resubmission <span style="color:var(--d10)">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason_{{ $key }}" rows="2" placeholder="Tell the client what needs to be corrected..." required></textarea>
                                @error('rejection_reason') <div class="error">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label for="notes_{{ $key }}">Private Notes (optional)</label>
                                <textarea name="notes" id="notes_{{ $key }}" rows="1" placeholder="Internal notes..."></textarea>
                            </div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <button type="submit" class="btn btn-warning btn-xs" onclick="this.disabled=true;this.form.submit();"><i class="fa-solid fa-pen"></i> Request Resubmission</button>
                                <button type="button" class="btn btn-secondary btn-xs" onclick="document.getElementById('resubmit-{{ $key }}').style.display='none'">Cancel</button>
                            </div>
                        </form>
                    </div>
                    @endif
                @else
                    <div class="document-placeholder">
                        <div class="document-placeholder-icon"><i class="fa-solid {{ $doc['icon'] }}"></i></div>
                        <div class="document-placeholder-text">{{ $doc['label'] }}</div>
                        <div class="document-placeholder-subtext">No document submitted.</div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Submission History</div>

        @if($history->count())
            <table>
                <thead>
                    <tr>
                        <th scope="col">{{ __('client.identity.submitted_at') }}</th>
                        <th scope="col">{{ __('client.identity.status') }}</th>
                        <th scope="col">{{ __('client.identity.id_type') }}</th>
                        @if($history->where('rejection_reason', '!=', null)->count())
                            <th scope="col">{{ __('client.identity.rejection_reason') }}</th>
                        @endif
                        <th scope="col">{{ __('admin.client_verifications.reviewed_by') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $record)
                        <tr>
                            <td class="text-sm">{{ $record->submitted_at?->format('M d, Y H:i') }}</td>
                            <td><x-status-badge :status="$record->status" /></td>
                            <td class="text-sm">{{ ucfirst(str_replace('_', ' ', $record->id_type)) }}</td>
                            @if($history->where('rejection_reason', '!=', null)->count())
                                <td class="text-sm">{{ $record->rejection_reason ?? '—' }}</td>
                            @endif
                            <td class="text-sm">{{ $record->reviewedBy?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">{{ __('client.identity.history_empty') }}</p>
        @endif
    </div>
</div>

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
.doc-viewer-close{width:36px;height:36px;border-radius:8px;border:none;background:var(--off);color:var(--g7);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center}
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
