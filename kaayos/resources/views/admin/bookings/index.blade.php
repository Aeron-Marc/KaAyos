@extends('layouts.admin')

@section('title', 'Bookings')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-calendar-check"></i> Bookings</h1>
        <p>View all platform bookings</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.bookings.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="status">Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="date_from">From:</label>
        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
    </div>
    <div class="filter-group">
        <label for="date_to">To:</label>
        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
    </div>
    <div class="filter-group" style="margin-left: auto;">
        <input type="text" name="search" placeholder="Search client or provider..." value="{{ request('search') }}" style="width: 220px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($bookings->count())
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Worker</th>
                    <th>Service</th>
                    <th>Schedule</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                <tr>
                    <td class="fw-600">#{{ $booking->id }}</td>
                    <td class="text-sm">{{ $booking->client->name ?? 'N/A' }}</td>
                    <td class="text-sm">{{ $booking->worker->name ?? 'N/A' }}</td>
                    <td class="text-sm">{{ $booking->service_category }}</td>
                    <td class="text-sm text-muted">{{ $booking->scheduled_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                    <td class="table-col-price">₱{{ number_format((float)$booking->price, 2) }}</td>
                    <td><span class="status-badge status-{{ $booking->status }}">{{ str_replace('_', ' ', ucfirst($booking->status)) }}</span></td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $bookings->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-calendar-xmark"></i></div>
            <div class="empty-state-title">No bookings found</div>
            <div class="empty-state-subtitle">Try adjusting your search or filter criteria.</div>
        </div>
    @endif
</div>
@endsection
