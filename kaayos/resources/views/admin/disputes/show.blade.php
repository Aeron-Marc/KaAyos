@extends('layouts.admin')

@section('title', 'Dispute Details')
@section('content')
<a href="{{ route('admin.disputes.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Disputes</a>

<div class="header">
    <div class="header-left">
        <h1>
            <i class="fa-solid fa-scale-balanced"></i>
            @if($dispute->type === 'worker_report') Worker Report @else Dispute @endif
            #{{ $dispute->id }}
        </h1>
        <p>
            @if($dispute->type === 'worker_report')
                Reported by {{ $dispute->raisedBy->name ?? 'N/A' }}
            @else
                Booking #{{ $dispute->booking_id }}
            @endif
        </p>
    </div>
    <div class="header-right">
        @if($dispute->type === 'worker_report')
            <span class="status-badge" style="background:#fef2f2;color:#991b1b;">Worker Report</span>
        @else
            <span class="status-badge" style="background:#eff6ff;color:#1e40af;">Booking Dispute</span>
        @endif
        <span class="status-badge status-{{ $dispute->status }}">{{ str_replace('_', ' ', ucfirst($dispute->status)) }}</span>
    </div>
</div>

<div class="layout-grid-2">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-circle-info"></i>
            @if($dispute->type === 'worker_report') Report @else Dispute @endif Details
        </div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">ID</span><span class="detail-value">#{{ $dispute->id }}</span></div>
            <div class="detail-row"><span class="detail-label">Type</span>
                <span class="detail-value">
                    @if($dispute->type === 'worker_report') Worker Report @else Booking Dispute @endif
                </span>
            </div>
            @if($dispute->type === 'worker_report')
                <div class="detail-row"><span class="detail-label">Reported Worker</span>
                    <span class="detail-value">{{ $dispute->reportedWorker->name ?? 'N/A' }}</span>
                </div>
            @endif
            <div class="detail-row"><span class="detail-label">Booking</span>
                <span class="detail-value">
                    @if($dispute->booking_id)
                        <a href="{{ route('admin.bookings.show', $dispute->booking_id) }}" style="color:var(--b6)">#{{ $dispute->booking_id }}</a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </span>
            </div>
            <div class="detail-row"><span class="detail-label">Raised By</span><span class="detail-value">{{ $dispute->raisedBy->name ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ str_replace('_', ' ', ucfirst($dispute->status)) }}</span></div>
            <div class="detail-row"><span class="detail-label">Created At</span><span class="detail-value">{{ $dispute->created_at->format('F d, Y \a\t g:i A') }}</span></div>
        </div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Reason</span></div>
            <p style="margin-top:8px;font-size:.95rem;color:var(--g9);line-height:1.6">{{ $dispute->reason }}</p>
        </div>
        @if($dispute->resolution_notes)
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Resolution Notes</span></div>
            <p style="margin-top:8px;font-size:.95rem;color:var(--g9);line-height:1.6">{{ $dispute->resolution_notes }}</p>
        </div>
        @endif
        @if($dispute->resolved_at)
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Resolved At</span><span class="detail-value">{{ $dispute->resolved_at->format('F d, Y \a\t g:i A') }}</span></div>
            @if($dispute->resolvedBy)
            <div class="detail-row"><span class="detail-label">Resolved By</span><span class="detail-value">{{ $dispute->resolvedBy->name }}</span></div>
            @endif
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title"><i class="fa-solid {{ $dispute->status === 'resolved' ? 'fa-check-circle' : 'fa-pen' }}" style="color:{{ $dispute->status === 'resolved' ? 'var(--s10)' : 'var(--b6)' }}"></i>
            {{ $dispute->status === 'resolved' ? 'Resolved' : 'Update Status' }}
        </div>

        @if($dispute->status !== 'resolved')
        <form method="POST" action="{{ route('admin.disputes.update', $dispute) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label for="status">Status <span style="color:var(--d10)">*</span></label>
                <select name="status" id="status" required>
                    <option value="open" {{ $dispute->status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="under_review" {{ $dispute->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
            <div class="form-group">
                <label for="resolution_notes">Resolution Notes</label>
                <textarea name="resolution_notes" id="resolution_notes" rows="4" placeholder="Add resolution notes (required when resolving)">{{ old('resolution_notes', $dispute->resolution_notes) }}</textarea>
                @error('resolution_notes') <div class="error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update</button>
        </form>
        @else
        <div class="info-box">
            <i class="fa-solid fa-check-circle" style="color:var(--s10)"></i>
            <span>This {{ $dispute->type === 'worker_report' ? 'report' : 'dispute' }} has been resolved and cannot be edited further.</span>
        </div>
        @endif
    </div>
</div>

@if($dispute->booking_id)
<div class="card">
    <div class="card-title"><i class="fa-solid fa-receipt"></i> Booking Information</div>
    <div class="detail-section">
        <div class="detail-row"><span class="detail-label">Booking ID</span><span class="detail-value"><a href="{{ route('admin.bookings.show', $dispute->booking_id) }}" style="color:var(--b6)">#{{ $dispute->booking_id }}</a></span></div>
        <div class="detail-row"><span class="detail-label">Service</span><span class="detail-value">{{ $dispute->booking->service_category }}</span></div>
        <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ str_replace('_', ' ', ucfirst($dispute->booking->status)) }}</span></div>
        <div class="detail-row"><span class="detail-label">Client</span><span class="detail-value">{{ $dispute->booking->client->name ?? 'N/A' }}</span></div>
        <div class="detail-row"><span class="detail-label">Worker</span><span class="detail-value">{{ $dispute->booking->worker->name ?? 'N/A' }}</span></div>
        <div class="detail-row"><span class="detail-label">Price</span><span class="detail-value">₱{{ number_format((float)$dispute->booking->price, 2) }}</span></div>
    </div>
</div>
@endif
@endsection
