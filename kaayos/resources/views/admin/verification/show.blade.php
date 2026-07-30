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

<div class="layout-grid-2">
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
            <div class="detail-row"><span class="detail-label">Email Address</span><span class="detail-value">{{ $verification->user->email }}</span></div>
            <div class="detail-row"><span class="detail-label">Phone Number</span><span class="detail-value">{{ $verification->user->phone ?? 'N/A' }}</span></div>
        </div>

        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Service Category</span><span class="detail-value">{{ $verification->user->service_category ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">City</span><span class="detail-value">{{ $verification->user->city ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Date Submitted</span><span class="detail-value">{{ $verification->created_at->format('F d, Y \a\t g:i A') }}</span></div>
        </div>

        @if($verification->admin_notes)
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Admin Notes</span><span class="detail-value" style="text-align:left;max-width:60%">{{ $verification->admin_notes }}</span></div>
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
            <span>Document upload history for this provider.</span>
        </div>

        <div class="document-grid">
            @forelse($documents as $doc)
            <div>
                <label class="document-label">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</label>
                @if($doc->file_path)
                    <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $doc->document_type }}" class="preview-doc-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none;width:100%;aspect-ratio:3/4;background:linear-gradient(135deg,var(--b0),rgba(26,111,196,.05));border:2px dashed var(--b4);border-radius:12px;flex-direction:column;align-items:center;justify-content:center;color:var(--b6)">
                        <div style="font-size:2.8rem;margin-bottom:8px"><i class="fa-solid fa-file"></i></div>
                        <div style="font-size:.85rem;font-weight:600;text-align:center;padding:0 12px">{{ $doc->document_type }}</div>
                    </div>
                @else
                    <div class="document-placeholder">
                        <div class="document-placeholder-icon"><i class="fa-solid fa-file"></i></div>
                        <div class="document-placeholder-text">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</div>
                        <div class="document-placeholder-subtext">No file uploaded</div>
                    </div>
                @endif
                <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center">
                    <span class="status-badge status-{{ $doc->status === 'verified' ? 'approved' : $doc->status }}" style="font-size:.75rem;padding:4px 8px">{{ ucfirst($doc->status) }}</span>
                    <span class="text-sm text-muted">{{ $doc->created_at->format('M d') }}</span>
                </div>
            </div>
            @empty
            <div style="grid-column:1/-1" class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-file-circle-exclamation"></i></div>
                <div class="empty-state-title">No documents found</div>
            </div>
            @endforelse
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
            <button type="submit" class="btn btn-success" onclick="return confirm('Approve this verification?')">
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
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-x-circle"></i> Confirm Rejection</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-form').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
