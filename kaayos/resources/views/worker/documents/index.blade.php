@extends('layouts.worker')

@section('title', 'Documents')
@section('page_title', 'Documents')

@section('content')

<div class="welcome-banner">
    <h2>Document Submission</h2>
    <p>Submit the required documents to verify your identity and credentials. Verified workers get more booking requests and higher trust from clients.</p>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Requirements</div>
            <h2 class="section-title">Required Documents</h2>
        </div>
        <span style="font-size:.82rem;color:var(--g4);font-weight:500;">
            {{ collect($documents)->where('status', 'Verified')->count() }} of {{ count($documents) }} verified
        </span>
    </div>

    @forelse($documents as $doc)
        <div style="display:flex;align-items:center;gap:16px;padding:18px 22px;border-bottom:1px solid var(--g1);transition:background .15s;">
            <div style="width:44px;height:44px;border-radius:10px;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;">
                <i class="fa-solid {{ $doc['icon'] }}" aria-hidden="true"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.9rem;font-weight:600;color:var(--b9);">{{ $doc['name'] }}</div>
                <div style="font-size:.82rem;color:var(--g4);margin-top:2px;">{{ $doc['description'] }}</div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                @php
                    $docStatusClass = match($doc['status']) {
                        'Verified' => 'status-done',
                        'Pending' => 'status-pending',
                        default => 'status-cancelled',
                    };
                @endphp
                <span class="status-badge {{ $docStatusClass }}" style="display:inline-flex;margin-bottom:4px;">
                    @if($doc['status'] === 'Verified')
                        <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
                    @elseif($doc['status'] === 'Pending')
                        <i class="fa-solid fa-clock" aria-hidden="true"></i>
                    @else
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    @endif
                    {{ $doc['status'] }}
                </span>
                @if($doc['status'] !== 'Verified')
                    <div>
                        <button type="button" class="btn btn-outline" style="padding:5px 12px;font-size:.78rem;">
                            <i class="fa-solid fa-upload" aria-hidden="true"></i>
                            {{ $doc['file'] ? 'Replace' : 'Upload' }}
                        </button>
                    </div>
                @elseif($doc['file'])
                    <div style="font-size:.75rem;color:var(--g4);">
                        <i class="fa-solid fa-paperclip" aria-hidden="true"></i> {{ $doc['file'] }}
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fa-regular fa-file" aria-hidden="true"></i>
            <h3>No documents required</h3>
            <p>There are no document requirements at this time.</p>
        </div>
    @endforelse
</div>

<div class="card-panel" style="margin-top:20px;">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Note</div>
            <h2 class="section-title">Verification Process</h2>
        </div>
    </div>
    <div style="padding:18px 22px;font-size:.875rem;color:var(--g7);line-height:1.7;">
        <p style="margin-bottom:8px;">
            <i class="fa-solid fa-circle-check" style="color:var(--b6);margin-right:6px;" aria-hidden="true"></i>
            Documents are reviewed within 1–3 business days.
        </p>
        <p style="margin-bottom:8px;">
            <i class="fa-solid fa-circle-check" style="color:var(--b6);margin-right:6px;" aria-hidden="true"></i>
            You'll receive a notification once each document is verified.
        </p>
        <p>
            <i class="fa-solid fa-circle-check" style="color:var(--b6);margin-right:6px;" aria-hidden="true"></i>
            Fully verified workers appear higher in search results and receive a "Verified" badge.
        </p>
    </div>
</div>

@endsection
