@if(auth()->user()->role === 'worker')
    @extends('layouts.worker')
@else
    @extends('layouts.client')
@endif

@section('title', 'My Testimonials')
@section('page_title', 'My Testimonials')

@section('skeleton')
    <div class="skeleton skeleton-title" style="width:200px;margin-bottom:20px;"></div>
    <div class="sp-panel">
        <div style="display:flex;gap:16px;margin-bottom:16px;">
            <div class="skeleton skeleton-avatar"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:40%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:30%;"></div>
            </div>
        </div>
        <div class="skeleton skeleton-text"></div>
        <div class="skeleton skeleton-text-sm"></div>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div style="background:rgba(16,185,129,.1);color:#047857;padding:14px 20px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:.9rem;font-weight:500;">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
@endif

<div class="testimonials-header">
    <div class="testimonials-stats">
        <div class="stat-pill"><i class="fa-solid fa-clock"></i> {{ $pendingCount }} Pending</div>
        <div class="stat-pill approved"><i class="fa-solid fa-circle-check"></i> {{ $approvedCount }} Approved</div>
    </div>
    <a href="{{ route('testimonials.create') }}" class="btn-new-testimonial">
        <i class="fa-solid fa-plus"></i> New Testimonial
    </a>
</div>

@if($testimonials->count())
    <div class="testimonials-list">
        @foreach($testimonials as $testimonial)
            <div class="testimonial-item {{ $testimonial->status }}">
                <div class="testimonial-header">
                    <div class="testimonial-status">
                        @if($testimonial->status === 'pending')
                            <span class="status-badge status-pending"><i class="fa-solid fa-clock"></i> Pending Review</span>
                        @elseif($testimonial->status === 'approved')
                            <span class="status-badge status-approved"><i class="fa-solid fa-circle-check"></i> Approved</span>
                        @elseif($testimonial->status === 'rejected')
                            <span class="status-badge status-rejected"><i class="fa-solid fa-xmark"></i> Rejected</span>
                        @endif
                    </div>
                    <span class="testimonial-date">{{ $testimonial->created_at->format('M d, Y') }}</span>
                </div>
                <div class="testimonial-stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $testimonial->rating)
                            <i class="fa-solid fa-star"></i>
                        @else
                            <i class="fa-regular fa-star" style="color:#D1D5DB;"></i>
                        @endif
                    @endfor
                </div>
                <p class="testimonial-content">{{ $testimonial->content }}</p>
                @if($testimonial->status === 'rejected' && $testimonial->admin_notes)
                    <div class="rejection-reason">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $testimonial->admin_notes }}</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div style="margin-top:20px;">
        {{ $testimonials->links() }}
    </div>
@else
    <div class="empty-state" style="text-align:center;padding:60px 20px;">
        <div style="font-size:3rem;color:var(--g4);margin-bottom:16px;"><i class="fa-solid fa-quote-left"></i></div>
        <div style="font-size:1.1rem;font-weight:600;color:var(--g9);margin-bottom:8px;">No testimonials yet</div>
        <div style="font-size:.9rem;color:var(--g4);margin-bottom:24px;">Share your experience with KaAyos and help others discover the platform.</div>
        <a href="{{ route('testimonials.create') }}" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;background:var(--b6);color:#fff;text-decoration:none;font-size:.9rem;font-weight:600;transition:all .18s;">
            <i class="fa-solid fa-plus"></i> Write Your First Testimonial
        </a>
    </div>
@endif

@push('styles')
<style>
    .testimonials-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
    .testimonials-stats { display: flex; gap: 8px; }
    .stat-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: .82rem; font-weight: 600; background: rgba(245,158,11,.1); color: #D97706; }
    .stat-pill.approved { background: rgba(16,185,129,.1); color: #047857; }
    .btn-new-testimonial { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; background: var(--b6); color: #fff; text-decoration: none; font-size: .88rem; font-weight: 600; transition: all .18s; }
    .btn-new-testimonial:hover { background: var(--b7); transform: translateY(-1px); }
    .testimonials-list { display: flex; flex-direction: column; gap: 14px; }
    .testimonial-item { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.05); border: 1px solid rgba(0,0,0,.05); }
    .testimonial-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .testimonial-date { font-size: .78rem; color: var(--g4); }
    .testimonial-stars { margin-bottom: 10px; }
    .testimonial-stars i { color: #F59E0B; font-size: .9rem; margin-right: 1px; }
    .testimonial-content { font-size: .9rem; color: var(--g7); line-height: 1.6; margin: 0; }
    .rejection-reason { margin-top: 12px; background: rgba(239,68,68,.06); border: 1px solid rgba(239,68,68,.15); border-radius: 8px; padding: 10px 14px; font-size: .82rem; color: #B91C1C; display: flex; align-items: flex-start; gap: 8px; }
    .rejection-reason i { margin-top: 2px; flex-shrink: 0; }
    .testimonial-item.rejected { border-left: 3px solid #EF4444; }
    .testimonial-item.pending { border-left: 3px solid #F59E0B; }
    .testimonial-item.approved { border-left: 3px solid #10B981; }
</style>
@endpush
@endsection
