@extends('layouts.worker')

@section('title', 'Claims')
@section('page_title', 'Claims')

@section('content')

<div class="welcome-banner">
    <h2>Claims Against You</h2>
    <p>Reports and disputes filed about your work. Submit a counter-claim so an admin can review your side before a decision is made.</p>
</div>

@if(session('success'))
    <div style="display:flex;align-items:center;gap:9px;background:#d6f5e8;color:#1a6852;border:1px solid #a7e8d0;border-radius:10px;padding:11px 15px;font-size:.87rem;font-weight:500;margin-top:16px;">
        <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="display:flex;align-items:center;gap:9px;background:#fde0de;color:#a32d2d;border:1px solid #f5c2ba;border-radius:10px;padding:11px 15px;font-size:.87rem;font-weight:500;margin-top:16px;">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@forelse($claims as $claim)
<div class="card-panel" style="margin-top:16px;">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">
                @if($claim->type === 'worker_report') Worker Report @else Booking Dispute @endif
                &middot; {{ $claim->created_at->format('M d, Y') }}
            </div>
            <h2 class="section-title">
                #{{ $claim->id }} — {{ $claim->booking->service_category ?? ('Booking #' . $claim->booking_id) }}
            </h2>
        </div>
        @if($claim->status === 'resolved')
            <span class="status-badge status-done"><i class="fa-solid fa-check-circle" aria-hidden="true"></i> Resolved</span>
        @elseif($claim->status === 'under_review')
            <span class="status-badge" style="background:#dbeafe;color:#1e40af;"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Under Review</span>
        @else
            <span class="status-badge status-pending"><i class="fa-solid fa-clock" aria-hidden="true"></i> Open</span>
        @endif
    </div>

    <div style="padding:4px 22px 0;display:flex;flex-wrap:wrap;gap:16px;font-size:.82rem;color:var(--g4);">
        <span><i class="fa-solid fa-user" aria-hidden="true"></i> Reported by {{ $claim->raisedBy->name ?? 'N/A' }}</span>
        @if($claim->booking)
            <span><i class="fa-solid fa-receipt" aria-hidden="true"></i> Booking #{{ $claim->booking_id }}</span>
            <span><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Client: {{ $claim->booking->client->name ?? 'N/A' }}</span>
        @endif
    </div>

    <div style="padding:16px 22px 0;">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#a32d2d;margin-bottom:6px;">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> The claim
        </div>
        <div style="background:#fef6f5;border:1px solid #f5c2ba;border-left:3px solid #dc2626;border-radius:9px;padding:13px 16px;font-size:.9rem;color:var(--g9);line-height:1.6;white-space:pre-wrap;">{{ $claim->reason }}</div>
    </div>

    <div style="padding:16px 22px 4px;">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#1e40af;margin-bottom:6px;">
            <i class="fa-solid fa-reply" aria-hidden="true"></i> Your counter-claim
        </div>

        @if($claim->hasCounterClaim())
            <div style="background:#f4f8ff;border:1px solid #bfdbfe;border-left:3px solid #2563eb;border-radius:9px;padding:13px 16px;font-size:.9rem;color:var(--g9);line-height:1.6;white-space:pre-wrap;">{{ $claim->counter_claim }}</div>
            <div style="font-size:.76rem;color:var(--g4);margin-top:6px;">
                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                Submitted {{ $claim->counter_claim_submitted_at?->format('M d, Y \a\t g:i A') ?? 'earlier' }}
            </div>
        @else
            <div style="background:var(--off);border:1.5px dashed var(--g1);border-radius:9px;padding:13px 16px;font-size:.87rem;color:var(--g4);">
                No counter-claim submitted yet. Tell your side of the story below.
            </div>
        @endif

        @if($claim->status !== 'resolved')
            <div style="margin-top:12px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('counter-form-{{ $claim->id }}').style.display='block';this.style.display='none';">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    {{ $claim->hasCounterClaim() ? 'Edit counter-claim' : 'Submit counter-claim' }}
                </button>
            </div>

            <div id="counter-form-{{ $claim->id }}" style="display:none;margin-top:12px;">
                <form method="POST" action="{{ route('worker.claims.counter', $claim) }}">
                    @csrf
                    <textarea name="counter_claim" rows="5" required minlength="10" maxlength="5000"
                        placeholder="Explain what happened from your perspective (at least 10 characters)..."
                        style="width:100%;border:1.5px solid var(--g1);border-radius:9px;padding:12px 15px;font-family:inherit;font-size:.9rem;color:var(--g9);background:#fff;outline:none;box-sizing:border-box;white-space:pre-wrap;">{{ old('counter_claim', $claim->counter_claim) }}</textarea>
                    @error('counter_claim')
                        <div style="color:#a32d2d;font-size:.8rem;margin-top:5px;"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</div>
                    @enderror
                    <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-solid"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> {{ $claim->hasCounterClaim() ? 'Update counter-claim' : 'Submit counter-claim' }}</button>
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('counter-form-{{ $claim->id }}').style.display='none';">Cancel</button>
                    </div>
                </form>
            </div>
        @else
            <div style="display:flex;align-items:center;gap:8px;background:var(--off);border:1px solid var(--g1);border-radius:9px;padding:10px 14px;font-size:.82rem;color:var(--g4);margin-top:12px;">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                This dispute has been resolved — no further counter-claims can be submitted.
            </div>
        @endif
    </div>
</div>
@empty
<div class="empty-state" style="margin-top:24px;">
    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
    <h3>No claims against you</h3>
    <p>No reports or disputes name you right now. Keep up the good work.</p>
</div>
@endforelse

@if($claims->hasPages())
    <div style="margin-top:20px;">{{ $claims->links() }}</div>
@endif

@endsection
