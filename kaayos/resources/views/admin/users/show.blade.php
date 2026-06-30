@extends('layouts.admin')

@section('title', 'User Details')
@section('content')
<a href="{{ route('admin.users.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Users</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-user"></i> {{ $user->name }}</h1>
        <p>{{ $user->email }} — {{ ucfirst($user->role) }}</p>
    </div>
    <div class="header-right">
        @if($user->suspended_at)
            <span class="status-badge status-suspended"><i class="fa-solid fa-ban"></i> Suspended</span>
        @else
            <span class="status-badge status-active"><i class="fa-solid fa-check-circle"></i> Active</span>
        @endif
    </div>
</div>

<div class="layout-grid-2">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-id-card"></i> Account Details</div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">{{ $user->name }}</span></div>
            <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">{{ $user->email }}</span></div>
            <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">{{ $user->phone ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Role</span><span class="detail-value">{{ ucfirst($user->role) }}</span></div>
            <div class="detail-row"><span class="detail-label">City</span><span class="detail-value">{{ $user->city ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Joined</span><span class="detail-value">{{ $user->created_at->format('F d, Y') }}</span></div>
        </div>
        @if($user->suspended_at)
        <div class="detail-section" style="border-color:var(--d10)">
            <div class="detail-row"><span class="detail-label" style="color:var(--d10)">Suspended At</span><span class="detail-value" style="color:var(--d10)">{{ $user->suspended_at->format('F d, Y \a\t g:i A') }}</span></div>
            @if($user->suspended_reason)
            <div class="detail-row"><span class="detail-label">Reason</span><span class="detail-value" style="text-align:left;max-width:60%">{{ $user->suspended_reason }}</span></div>
            @endif
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title"><i class="fa-solid fa-calendar-check"></i> Booking History</div>
        @if($bookings->count())
            <table>
                <thead>
                    <tr><th>ID</th><th>With</th><th>Status</th><th>Price</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @foreach($bookings as $b)
                    <tr>
                        <td>#{{ $b->id }}</td>
                        <td class="text-sm">{{ $user->role === 'client' ? $b->worker->name : $b->client->name }}</td>
                        <td><span class="status-badge status-{{ $b->status }}">{{ str_replace('_', ' ', ucfirst($b->status)) }}</span></td>
                        <td class="table-col-price">₱{{ number_format((float)$b->price, 2) }}</td>
                        <td class="text-sm text-muted">{{ $b->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">{{ $bookings->links() }}</div>
        @else
            <div class="empty-state"><div class="empty-state-icon"><i class="fa-solid fa-calendar"></i></div><div class="empty-state-title">No bookings yet</div></div>
        @endif
    </div>
</div>
@endsection
