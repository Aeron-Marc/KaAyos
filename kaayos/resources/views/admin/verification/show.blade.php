@extends('layouts.admin')

@php
    $docByType = $verification->documents->keyBy('document_type');
    $allDocsSubmitted = collect($documentTypes)->every(fn($t) => $docByType->has($t['name']));
    $pendingCount = $verification->documents->where('status', 'pending')->count();
    $verifiedCount = $verification->documents->where('status', 'verified')->count();
    $rejectedCount = $verification->documents->where('status', 'rejected')->count();
    $totalTypes = count($documentTypes);
@endphp

@section('title', 'Verification — ' . ($verification->user->name ?? 'Unknown'))
@section('content')
<a href="{{ route('admin.verifications.index') }}" class="back-link">
    <i class="fa-solid fa-arrow-left"></i> Back to Verifications
</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-clipboard-check"></i> Worker Verification</h1>
        <p>{{ $verification->user->name ?? 'Unknown' }}</p>
    </div>
    <div>
        <span class="status-badge status-{{ $verification->status === 'verified' ? 'approved' : $verification->status }}">
            @if($verification->status === 'pending_review')<i class="fa-solid fa-hourglass-half"></i>
            @elseif($verification->status === 'under_review')<i class="fa-solid fa-magnifying-glass"></i>
            @elseif($verification->status === 'verified')<i class="fa-solid fa-check-circle"></i>
            @elseif($verification->status === 'changes_requested')<i class="fa-solid fa-rotate"></i>
            @elseif($verification->status === 'rejected')<i class="fa-solid fa-x-circle"></i>
            @else<i class="fa-solid fa-circle"></i>
            @endif
            {{ ucfirst(str_replace('_', ' ', $verification->status)) }}
        </span>
    </div>
</div>

@if($verification->status === 'pending_review')
<div class="action-bar" style="margin-bottom:28px;">
    <div>
        <span style="font-size:.85rem;font-weight:600;color:var(--g7);text-transform:uppercase;letter-spacing:.04em;">Application Status</span>
        <span style="display:block;font-size:1rem;font-weight:700;color:var(--b9);">Ready for Review</span>
    </div>
    <div class="page-actions">
        <form method="POST" action="{{ route('admin.verifications.review', $verification) }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-play"></i> Start Review
            </button>
        </form>
    </div>
</div>
@endif

<div class="layout-grid-2" style="grid-template-columns:1fr 1.5fr;">
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-title"><i class="fa-solid fa-id-card"></i> Worker Details</div>

            <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:20px;">
                <div style="width:72px;height:72px;border-radius:12px;background:linear-gradient(135deg,var(--b4),var(--b6));display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:700;flex-shrink:0;box-shadow:0 4px 12px rgba(26,111,196,.2)">
                    {{ strtoupper(substr($verification->user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($verification->user->last_name ?? 'N', 0, 1)) }}
                </div>
                <div>
                    <h2 style="font-size:1.3rem;font-weight:700;color:var(--b9);margin:0 0 4px 0">{{ $verification->user->name ?? 'Unknown' }}</h2>
                    <p style="font-size:.85rem;color:var(--g4);margin:0">
                        {{ $verification->user->workerProfile?->skills ? implode(', ', array_slice($verification->user->workerProfile->skills, 0, 3)) : 'No skills listed' }}
                        {{ $verification->user->workerProfile?->years_of_experience ? "— {$verification->user->workerProfile->years_of_experience} yrs" : '' }}
                    </p>
                </div>
            </div>

            <div class="detail-section" style="padding-bottom:16px;">
                <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">{{ $verification->user->email }}</span></div>
                <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">{{ $verification->user->phone ?? 'N/A' }}</span></div>
                <div class="detail-row"><span class="detail-label">Category</span><span class="detail-value">{{ $verification->user->service_category ?? 'N/A' }}</span></div>
                <div class="detail-row"><span class="detail-label">City</span><span class="detail-value">{{ $verification->user->city ?? 'N/A' }}</span></div>
                <div class="detail-row"><span class="detail-label">Submitted</span><span class="detail-value">{{ $verification->submitted_at?->format('M d, Y \a\t g:i A') ?? 'Not yet' }}</span></div>
            </div>

            <div class="detail-section" style="padding-bottom:0;border-bottom:none;">
                <div class="detail-row">
                    <span class="detail-label">Progress</span>
                    <span class="detail-value" style="font-weight:700;">{{ $verifiedCount }}/{{ $totalTypes }} verified</span>
                </div>
                <div style="margin-top:8px;height:10px;background:var(--g1);border-radius:5px;overflow:hidden;">
                    @php $pct = $totalTypes > 0 ? round(($verifiedCount / $totalTypes) * 100) : 0; @endphp
                    <div style="height:100%;width:{{ $pct }}%;background:{{ $pct === 100 ? 'var(--s10)' : 'var(--b6)' }};border-radius:5px;transition:width .4s;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:.75rem;color:var(--g4);">
                    <span>{{ $pendingCount }} pending</span>
                    <span>{{ $rejectedCount }} rejected</span>
                </div>
            </div>

            @if($verification->admin_notes && $verification->status === 'rejected')
            <div class="detail-section" style="margin-top:16px;padding-bottom:0;border-bottom:none;">
                <div class="detail-row"><span class="detail-label" style="color:var(--d10)">Rejection Reason</span><span class="detail-value" style="text-align:left;max-width:60%;color:var(--d10)">{{ $verification->admin_notes }}</span></div>
            </div>
            @endif

            @if($verification->reviewedBy)
            <div class="detail-section" style="margin-top:16px;padding-bottom:0;border-bottom:none;">
                <div class="detail-row"><span class="detail-label">Reviewed By</span><span class="detail-value">{{ $verification->reviewedBy->name }}</span></div>
                @if($verification->reviewed_at)
                <div class="detail-row"><span class="detail-label">Reviewed At</span><span class="detail-value">{{ $verification->reviewed_at->format('M d, Y \a\t g:i A') }}</span></div>
                @endif
                @if($verification->verified_at)
                <div class="detail-row"><span class="detail-label">Verified At</span><span class="detail-value">{{ $verification->verified_at->format('M d, Y \a\t g:i A') }}</span></div>
                @endif
            </div>
            @endif
        </div>

        @if($verification->events->count() > 0)
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-timeline"></i> Timeline</div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($verification->events->sortByDesc('created_at') as $event)
                <div style="display:flex;gap:12px;align-items:flex-start;padding-bottom:12px;border-bottom:1px solid var(--g1);">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.8rem;">
                        <i class="fa-solid {{ $event->event_type === 'verified' || $event->event_type === 'document_approved' || $event->event_type === 'bulk_approved' ? 'fa-check' : ($event->event_type === 'rejected' || $event->event_type === 'document_rejected' || $event->event_type === 'bulk_rejected' ? 'fa-xmark' : ($event->event_type === 'changes_requested' ? 'fa-rotate' : 'fa-circle')) }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.82rem;font-weight:600;color:var(--g9);">
                            {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                            @if($event->metadata && isset($event->metadata['document_type']))
                                — {{ str_replace('_', ' ', ucfirst($event->metadata['document_type'])) }}
                            @endif
                        </div>
                        <div style="font-size:.75rem;color:var(--g4);margin-top:2px;">
                            @if($event->actor)<span>{{ $event->actor->name }}</span> · @endif
                            <span>{{ $event->created_at->format('M d, Y \a\t g:i A') }}</span>
                            @if($event->old_status && $event->new_status)
                                · <span>{{ str_replace('_', ' ', $event->old_status) }} → {{ str_replace('_', ' ', $event->new_status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div>
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-list-check"></i> Required Documents</div>
            <p style="font-size:.82rem;color:var(--g4);margin-bottom:16px;">All {{ $totalTypes }} documents must be verified for the worker to be fully verified.</p>

            <div style="display:grid;grid-template-columns:1fr;gap:16px;">
                @foreach($documentTypes as $type)
                    @php
                        $doc = $docByType->get($type['name']);
                        $docStatus = $doc ? $doc->status : 'not_submitted';
                    @endphp
                    <div class="document-card">
                        <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:10px;">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.95rem;">
                                <i class="fa-solid {{ $type['icon'] }}"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:.85rem;font-weight:600;color:var(--b9);">{{ $type['name'] }}</div>
                            </div>
                            <span class="status-badge status-{{ $docStatus === 'verified' ? 'approved' : $docStatus }}" style="font-size:.7rem;padding:3px 8px;flex-shrink:0;">
                                @if($docStatus === 'pending')<i class="fa-solid fa-hourglass-half"></i>
                                @elseif($docStatus === 'verified')<i class="fa-solid fa-check-circle"></i>
                                @elseif($docStatus === 'rejected')<i class="fa-solid fa-x-circle"></i>
                                @else<i class="fa-solid fa-circle"></i>
                                @endif
                                {{ $docStatus === 'not_submitted' ? 'Not submitted' : ucfirst($docStatus) }}
                            </span>
                        </div>

                        @if($doc && $doc->file_path)
                            @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
                            @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                                <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $type['name'] }}" class="preview-doc-img" style="cursor:pointer;margin-bottom:10px;" onclick="openLightbox(this.src)">
                            @elseif($ext === 'pdf')
                                <div style="width:100%;aspect-ratio:3/4;background:linear-gradient(135deg,var(--b0),rgba(26,111,196,.03));border:2px solid var(--g1);border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--b6);margin-bottom:10px;">
                                    <div style="font-size:2.8rem;margin-bottom:8px;color:var(--d10)"><i class="fa-solid fa-file-pdf"></i></div>
                                    <div style="font-size:.72rem;font-weight:600;text-align:center;padding:0 16px;word-break:break-all;color:var(--g7)">{{ Str::limit(basename($doc->file_path), 30) }}</div>
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" style="margin-top:8px;font-size:.75rem;font-weight:600;color:var(--b6);text-decoration:none;">
                                        <i class="fa-solid fa-external-link"></i> View PDF
                                    </a>
                                </div>
                            @else
                                <div style="width:100%;aspect-ratio:3/4;background:linear-gradient(135deg,var(--b0),rgba(26,111,196,.03));border:2px solid var(--g1);border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--b6);margin-bottom:10px;">
                                    <div style="font-size:2.8rem;margin-bottom:8px"><i class="fa-solid fa-file"></i></div>
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" style="font-size:.75rem;font-weight:600;color:var(--b6);text-decoration:none;"><i class="fa-solid fa-download"></i> Download</a>
                                </div>
                            @endif
                        @else
                            <div class="document-placeholder" style="margin-bottom:10px;">
                                <div class="document-placeholder-icon"><i class="fa-solid fa-file-circle-exclamation"></i></div>
                                <div class="document-placeholder-text">Not yet uploaded</div>
                            </div>
                        @endif

                        @if($doc && $doc->rejection_reason)
                            <div style="background:rgba(239,68,68,.06);border-left:3px solid var(--d10);border-radius:6px;padding:10px 12px;margin-bottom:10px;font-size:.8rem;color:var(--d8);">
                                <strong>Reason:</strong> {{ $doc->rejection_reason }}
                            </div>
                        @endif

                        @if(in_array($verification->status, ['pending_review', 'under_review', 'changes_requested']) && $docStatus === 'pending')
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <form method="POST" action="{{ route('admin.verifications.documents.approve', [$verification, $doc]) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs" onclick="return confirm('Approve this document?')">
                                        <i class="fa-solid fa-check"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger btn-xs" onclick="showQuickReject('{{ $verification->id }}', '{{ $doc->id }}')">
                                    <i class="fa-solid fa-xmark"></i> Reject
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($pendingCount > 1 && in_array($verification->status, ['pending_review', 'under_review']))
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--g1);">
                <div style="font-size:.82rem;font-weight:600;color:var(--g7);margin-bottom:12px;">
                    <i class="fa-solid fa-layer-group"></i> Batch Actions ({{ $pendingCount }} pending)
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('admin.verifications.approveAll', $verification) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve all {{ $pendingCount }} pending documents?')">
                            <i class="fa-solid fa-check-double"></i> Approve All
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('batch-reject-form').style.display='block'">
                        <i class="fa-solid fa-xmark"></i> Reject All
                    </button>
                </div>
            </div>

            <div id="batch-reject-form" style="display:none;margin-top:16px;">
                <div class="card" style="padding:20px;">
                    <div class="card-title" style="font-size:1rem;margin-bottom:12px;"><i class="fa-solid fa-xmark" style="color:var(--d10)"></i> Reject All Pending</div>
                    <form method="POST" action="{{ route('admin.verifications.rejectAll', $verification) }}">
                        @csrf
                        <div class="form-group">
                            <label for="batch_rejection_reason">Rejection Reason <span style="color:var(--d10)">*</span></label>
                            <textarea name="rejection_reason" id="batch_rejection_reason" rows="3" required></textarea>
                        </div>
                        <div class="page-actions">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-x-circle"></i> Confirm</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('batch-reject-form').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        @if($verifiedCount === $totalTypes && $verification->status !== 'verified')
        <div class="action-bar" style="margin-top:20px;">
            <div>
                <span style="font-size:.82rem;font-weight:600;color:var(--g7);text-transform:uppercase;letter-spacing:.04em;">All documents verified</span>
                <span style="display:block;font-size:.95rem;font-weight:700;color:var(--b9);">Mark worker as fully verified</span>
            </div>
            <form method="POST" action="{{ route('admin.verifications.approveAll', $verification) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Complete verification for this worker?')">
                    <i class="fa-solid fa-check-circle"></i> Complete Verification
                </button>
            </form>
        </div>
        @endif

        @if(in_array($verification->status, ['pending_review', 'under_review', 'changes_requested']))
        <div style="margin-top:20px;display:flex;gap:12px;justify-content:flex-end;">
            <button type="button" class="btn btn-warning" onclick="document.getElementById('request-changes-form').style.display='block'">
                <i class="fa-solid fa-rotate"></i> Request Changes
            </button>
            <button type="button" class="btn btn-danger" onclick="document.getElementById('permanent-reject-form').style.display='block'">
                <i class="fa-solid fa-ban"></i> Reject Permanently
            </button>
        </div>

        <div id="request-changes-form" style="display:none;margin-top:12px;">
            <div class="card" style="padding:20px;">
                <form method="POST" action="{{ route('admin.verifications.requestChanges', $verification) }}">
                    @csrf
                    <p style="font-size:.85rem;color:var(--g7);margin-bottom:12px;">This will set the application to "Changes Requested" and notify the worker to re-upload rejected documents.</p>
                    <div class="page-actions">
                        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-rotate"></i> Confirm Request Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('request-changes-form').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="permanent-reject-form" style="display:none;margin-top:12px;">
            <div class="card" style="padding:20px;">
                <div class="card-title" style="font-size:1rem;margin-bottom:12px;"><i class="fa-solid fa-ban" style="color:var(--d10)"></i> Reject Permanently</div>
                <form method="POST" action="{{ route('admin.verifications.reject', $verification) }}">
                    @csrf
                    <div class="form-group">
                        <label for="reject_reason">Reason <span style="color:var(--d10)">*</span></label>
                        <textarea name="reason" id="reject_reason" rows="3" required></textarea>
                    </div>
                    <div class="page-actions">
                        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Confirm Reject</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('permanent-reject-form').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<div id="lightbox" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);z-index:9999;justify-content:center;align-items:center;" onclick="closeLightbox(event)">
    <button style="position:absolute;top:20px;right:30px;background:none;border:none;color:#fff;font-size:2.5rem;cursor:pointer;z-index:10000;" onclick="closeLightbox()">&times;</button>
    <img id="lightbox-img" src="" alt="Preview" style="max-width:90%;max-height:90%;border-radius:8px;box-shadow:0 8px 40px rgba(0,0,0,.5);object-fit:contain;">
</div>

<div id="quick-reject-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9998;justify-content:center;align-items:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;max-width:480px;width:90%;box-shadow:0 8px 40px rgba(0,0,0,.2);" onclick="event.stopPropagation()">
        <div class="card-title" style="font-size:1.1rem;margin-bottom:16px;"><i class="fa-solid fa-xmark" style="color:var(--d10)"></i> Reject Document</div>
        <form id="quick-reject-form" method="POST" action="">
            @csrf
            <div class="form-group">
                <label for="quick_rejection_reason">Rejection Reason <span style="color:var(--d10)">*</span></label>
                <textarea name="rejection_reason" id="quick_rejection_reason" rows="3" required></textarea>
            </div>
            <div class="page-actions">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-x-circle"></i> Confirm</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('quick-reject-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .document-card {
        border: 2px solid var(--g1);
        border-radius: 12px;
        padding: 14px;
        transition: all .2s;
    }
    .document-card:hover {
        border-color: var(--b4);
        box-shadow: 0 2px 8px rgba(26,111,196,.08);
    }
    .btn-xs {
        padding: 4px 10px;
        font-size: .75rem;
        border-radius: 5px;
    }
    .preview-doc-img {
        transition: opacity .2s;
    }
    .preview-doc-img:hover {
        opacity: .85;
    }
</style>
@endpush

@push('scripts')
<script>
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = '';
        }
    });
    function showQuickReject(verificationId, docId) {
        const form = document.getElementById('quick-reject-form');
        form.action = '/admin/verifications/' + verificationId + '/documents/' + docId + '/reject';
        document.getElementById('quick-reject-modal').style.display = 'flex';
    }
    document.getElementById('quick-reject-modal')?.addEventListener('click', function() {
        this.style.display = 'none';
    });
</script>
@endpush