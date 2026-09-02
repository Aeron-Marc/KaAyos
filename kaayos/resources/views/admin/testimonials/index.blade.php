@extends('layouts.admin')

@section('title', 'Testimonials')
@section('skeleton')
    <div class="sp-title-row">
        <div class="ad-sk ad-sk-title" style="width:220px;"></div>
    </div>
    <div class="ad-sk-panel">
        <div class="ad-sk" style="height:40px;width:100%;margin-bottom:16px;border-radius:8px;"></div>
        <div class="ad-sk ad-sk-row"></div>
        <div class="ad-sk ad-sk-row"></div>
        <div class="ad-sk ad-sk-row"></div>
        <div class="ad-sk ad-sk-row"></div>
    </div>
@endsection

@section('content')

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-quote-left"></i> Testimonials</h1>
        <p>Review and manage user testimonials for the landing page</p>
    </div>
</div>

<div class="filters-bar">
    <div class="filter-group">
        <label>Status</label>
        <select onchange="window.location.href='{{ route('admin.testimonials.index') }}?status='+this.value">
            <option value="">All</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
    </div>
    <div class="filter-group">
        <form method="GET" action="{{ route('admin.testimonials.index') }}" style="display:flex;gap:8px;align-items:center;">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" placeholder="Search testimonials..." value="{{ request('search') }}" style="min-width:220px;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Search</button>
        </form>
    </div>
    @if($pendingCount > 0)
        <div style="margin-left:auto;">
            <span class="status-badge status-pending"><i class="fa-solid fa-clock"></i> {{ $pendingCount }} Pending</span>
        </div>
    @endif
</div>

<div class="table-container">
    @if($testimonials->count())
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Testimonial</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testimonials as $testimonial)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-initials" style="background:{{ $testimonial->user && $testimonial->user->role === 'worker' ? '#7C3AED' : '#1A6FC4' }}">
                                    {{ $testimonial->avatar_initials }}
                                </div>
                                <div class="user-cell-info">
                                    <div class="user-cell-name">{{ $testimonial->name }}</div>
                                    <div class="user-cell-email">{{ $testimonial->role }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;gap:2px;">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $testimonial->rating)
                                        <i class="fa-solid fa-star" style="color:#F59E0B;font-size:.75rem;"></i>
                                    @else
                                        <i class="fa-regular fa-star" style="color:#D1D5DB;font-size:.75rem;"></i>
                                    @endif
                                @endfor
                            </div>
                        </td>
                        <td>
                            <div style="max-width:300px;">
                                <p style="margin:0;font-size:.88rem;color:var(--g7);line-height:1.4;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                    {{ $testimonial->content }}
                                </p>
                            </div>
                        </td>
                        <td>
                            @if($testimonial->status === 'pending')
                                <span class="status-badge status-pending">Pending</span>
                            @elseif($testimonial->status === 'approved')
                                <span class="status-badge status-approved">Approved</span>
                            @elseif($testimonial->status === 'rejected')
                                <span class="status-badge status-rejected">Rejected</span>
                            @endif
                        </td>
                        <td class="text-sm text-muted">{{ $testimonial->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('admin.testimonials.show', $testimonial) }}" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                                @if($testimonial->status === 'pending')
                                    <form method="POST" action="{{ route('admin.testimonials.approve', $testimonial) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Approve</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-quote-left"></i></div>
            <div class="empty-state-title">No testimonials found</div>
            <div class="empty-state-subtitle">Testimonials submitted by users will appear here.</div>
        </div>
    @endif
</div>

<div style="margin-top:20px;">
    {{ $testimonials->links() }}
</div>

@endsection
