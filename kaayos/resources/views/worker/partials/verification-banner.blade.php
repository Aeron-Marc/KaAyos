@php
    $docs = collect($documents ?? []);
    $pendingCount = $docs->where('status', 'Pending')->count();
    $rejectedCount = $docs->where('status', 'Rejected')->count();
@endphp

@if($pendingCount > 0 || $rejectedCount > 0)
<style>
    .verification-banner {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding: 14px 18px;
        margin-bottom: 18px;
        border-radius: 12px;
        border: 1px solid #fcd34d;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    .verification-banner.rejected-only {
        border-color: #fca5a5;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }
    .verification-banner .vb-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #f59e0b;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .verification-banner.rejected-only .vb-icon {
        background: #dc2626;
    }
    .verification-banner .vb-body {
        flex: 1;
        min-width: 220px;
    }
    .verification-banner .vb-body strong {
        display: block;
        font-size: .92rem;
        color: #92400e;
        margin-bottom: 2px;
    }
    .verification-banner.rejected-only .vb-body strong {
        color: #991b1b;
    }
    .verification-banner .vb-body span {
        font-size: .82rem;
        color: #78350f;
        line-height: 1.5;
    }
    .verification-banner.rejected-only .vb-body span {
        color: #7f1d1d;
    }
    .verification-banner .vb-link {
        padding: 7px 14px;
        border-radius: 8px;
        border: 1px solid #b45309;
        color: #92400e;
        font-size: .8rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: all .15s;
    }
    .verification-banner .vb-link:hover {
        background: #b45309;
        color: #fff;
    }
    .verification-banner.rejected-only .vb-link {
        border-color: #b91c1c;
        color: #991b1b;
    }
    .verification-banner.rejected-only .vb-link:hover {
        background: #b91c1c;
        color: #fff;
    }
</style>

@if($pendingCount > 0)
    <div class="verification-banner" role="status">
        <div class="vb-icon"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></div>
        <div class="vb-body">
            <strong>Verification in review — {{ $pendingCount }} {{ Str::plural('document', $pendingCount) }} pending</strong>
            <span>
                Our team is reviewing your submission. Verification typically takes <strong>1–2 business days</strong>.
                You'll be notified as soon as your status changes — no need to resubmit.
            </span>
        </div>
        <a href="{{ route('worker.documents') }}" class="vb-link">
            <i class="fa-solid fa-file-shield" aria-hidden="true"></i> View status
        </a>
    </div>
@elseif($rejectedCount > 0)
    <div class="verification-banner rejected-only" role="alert">
        <div class="vb-icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></div>
        <div class="vb-body">
            <strong>{{ $rejectedCount }} {{ Str::plural('document', $rejectedCount) }} {{ $rejectedCount > 1 ? 'were' : 'was' }} rejected</strong>
            <span>
                Please review the admin notes and upload a new copy. Re-verified documents are usually reviewed within
                <strong>1–2 business days</strong>.
            </span>
        </div>
        <a href="{{ route('worker.documents') }}" class="vb-link">
            <i class="fa-solid fa-upload" aria-hidden="true"></i> Re-upload
        </a>
    </div>
@endif
@endif
