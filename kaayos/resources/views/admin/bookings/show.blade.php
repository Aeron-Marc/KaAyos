@extends('layouts.admin')

@section('title', 'Booking Details')
@section('content')
<a href="{{ route('admin.bookings.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Bookings</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-receipt"></i> Booking #{{ $booking->id }}</h1>
        <p>{{ $booking->service_category }}</p>
    </div>
    <div class="header-right">
        <span class="status-badge status-{{ $booking->status }}">{{ str_replace('_', ' ', ucfirst($booking->status)) }}</span>
    </div>
</div>

<div class="layout-grid-2">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-circle-info"></i> Booking Details</div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Booking Ref</span><span class="detail-value">{{ $booking->booking_ref ?? 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span></div>
            <div class="detail-row"><span class="detail-label">Service</span><span class="detail-value">{{ $booking->service_category }}</span></div>
            <div class="detail-row"><span class="detail-label">Scheduled At</span><span class="detail-value">{{ $booking->scheduled_at?->format('F d, Y \a\t g:i A') ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Location</span><span class="detail-value" style="text-align:right;max-width:60%">{{ $booking->address }}</span></div>
            <div class="detail-row"><span class="detail-label">Price</span><span class="detail-value">₱{{ number_format((float)$booking->price, 2) }}</span></div>
        </div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ str_replace('_', ' ', ucfirst($booking->status)) }}</span></div>
            @if($booking->completed_at)
            <div class="detail-row"><span class="detail-label">Completed At</span><span class="detail-value">{{ $booking->completed_at->format('F d, Y \a\t g:i A') }}</span></div>
            @endif
            @if($booking->cancelled_at)
            <div class="detail-row"><span class="detail-label">Cancelled At</span><span class="detail-value">{{ $booking->cancelled_at->format('F d, Y \a\t g:i A') }}</span></div>
            @endif
            @if($booking->cancellation_reason)
            <div class="detail-row"><span class="detail-label">Cancel Reason</span><span class="detail-value" style="text-align:right;max-width:60%">{{ $booking->cancellation_reason }}</span></div>
            @endif
        </div>
        @if($booking->notes)
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Notes</span><span class="detail-value" style="text-align:right;max-width:60%">{{ $booking->notes }}</span></div>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title"><i class="fa-solid fa-users"></i> Client & Worker</div>
        <div class="detail-section">
            <div class="detail-row" style="flex-direction:column;gap:4px">
                <span class="detail-label">Client</span>
                <div style="display:flex;align-items:center;gap:12px;width:100%">
                    <div class="user-initials" style="background:var(--b6);width:48px;height:48px;font-size:1.2rem">
                        {{ strtoupper(substr($booking->client->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($booking->client->last_name ?? 'N', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600" style="font-size:1rem">{{ $booking->client->name ?? 'N/A' }}</div>
                        <div class="text-sm text-muted">{{ $booking->client->email }}</div>
                        <div class="text-sm text-muted">{{ $booking->client->phone ?? 'No phone' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detail-section">
            <div class="detail-row" style="flex-direction:column;gap:4px">
                <span class="detail-label">Worker</span>
                <div style="display:flex;align-items:center;gap:12px;width:100%">
                    <div class="user-initials" style="background:var(--s10);width:48px;height:48px;font-size:1.2rem">
                        {{ strtoupper(substr($booking->worker->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($booking->worker->last_name ?? 'N', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600" style="font-size:1rem">{{ $booking->worker->name ?? 'N/A' }}</div>
                        <div class="text-sm text-muted">{{ $booking->worker->email }}</div>
                        <div class="text-sm text-muted">{{ $booking->worker->phone ?? 'No phone' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Created At</span><span class="detail-value">{{ $booking->created_at->format('F d, Y \a\t g:i A') }}</span></div>
        </div>
    </div>
</div>
<div style="margin-top:24px;border-top:1px solid var(--b2);padding-top:20px;display:flex;gap:10px;">
    @if(!in_array($booking->status, ['completed', 'cancelled']))
        <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
            @csrf
            <input type="hidden" name="reason" value="Cancelled by administrator.">
            <button type="submit" class="btn btn-solid" style="background:var(--r6);">
                <i class="fa-solid fa-ban"></i> Cancel Booking
            </button>
        </form>
    @endif
</div>
@endsection
