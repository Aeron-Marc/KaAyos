@extends('layouts.admin')

@section('title', 'Disputes')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-scale-balanced"></i> Disputes</h1>
        <p>Review and resolve booking disputes</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.disputes.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="status">Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left: auto;">
        <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}" style="width: 200px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($disputes->count())
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Booking</th>
                    <th>Raised By</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disputes as $dispute)
                <tr>
                    <td class="fw-600">#{{ $dispute->id }}</td>
                    <td><a href="{{ route('admin.bookings.show', $dispute->booking_id) }}" style="color:var(--b6)">#{{ $dispute->booking_id }}</a></td>
                    <td class="text-sm">{{ $dispute->raisedBy->name ?? 'N/A' }}</td>
                    <td class="text-sm" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $dispute->reason }}</td>
                    <td><span class="status-badge status-{{ $dispute->status }}">{{ str_replace('_', ' ', ucfirst($dispute->status)) }}</span></td>
                    <td class="text-sm text-muted">{{ $dispute->created_at->format('M d, Y') }}</td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $disputes->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-scale-balanced"></i></div>
            <div class="empty-state-title">No disputes found</div>
            <div class="empty-state-subtitle">All clear — no disputes have been filed.</div>
        </div>
    @endif
</div>
@endsection
