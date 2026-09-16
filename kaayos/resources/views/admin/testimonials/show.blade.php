@extends('layouts.admin')

@section('title', 'View Testimonial')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.testimonials.index') }}">Testimonials</a>
    <span>/</span>
    <span>View Testimonial</span>
</div>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-quote-left"></i> Testimonial Details</h1>
        <p>View this testimonial</p>
    </div>
</div>

<div class="layout-grid-2">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-quote-left"></i> Testimonial</div>

        <div class="detail-section">
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    @if($testimonial->status === 'pending')
                        <span class="status-badge status-pending">Pending Review</span>
                    @elseif($testimonial->status === 'approved')
                        <span class="status-badge status-approved">Approved</span>
                    @elseif($testimonial->status === 'rejected')
                        <span class="status-badge status-rejected">Rejected</span>
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Rating</span>
                <span class="detail-value">
                    <div style="display:flex;gap:3px;">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $testimonial->rating)
                                <i class="fa-solid fa-star" style="color:#F59E0B;font-size:1rem;"></i>
                            @else
                                <i class="fa-regular fa-star" style="color:#D1D5DB;font-size:1rem;"></i>
                            @endif
                        @endfor
                    </div>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Submitted</span>
                <span class="detail-value">{{ $testimonial->created_at->format('F d, Y \a\t h:i A') }}</span>
            </div>
        </div>

        <div class="detail-section" style="border-bottom:none;">
            <div class="detail-label" style="margin-bottom:12px;">Testimonial Content</div>
            <div style="background:var(--off);border-radius:10px;padding:18px;border:1px solid var(--g1);">
                <p style="font-size:.95rem;color:var(--g7);line-height:1.7;margin:0;font-style:italic;">
                    "{{ $testimonial->content }}"
                </p>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-user"></i> User Info</div>
            <div class="detail-section" style="border-bottom:none;">
                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ $testimonial->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Role</span>
                    <span class="detail-value">{{ $testimonial->role }}</span>
                </div>
                @if($testimonial->user)
                    <div class="detail-row">
                        <span class="detail-label">Account</span>
                        <span class="detail-value">
                            <a href="{{ route('admin.users.show', $testimonial->user) }}" style="color:var(--b6);text-decoration:none;font-weight:600;">
                                {{ $testimonial->user->email }}
                            </a>
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <a href="{{ route('admin.testimonials.index') }}" class="back-link" style="display:inline-flex;margin-top:16px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Testimonials
        </a>
    </div>
</div>

@endsection
