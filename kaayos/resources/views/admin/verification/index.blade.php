@extends('layouts.admin')

@section('title', 'Worker Verifications')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-clipboard-check"></i> Worker Verifications</h1>
        <p>Review and approve pending service provider applications</p>
    </div>
    <div class="header-right">
        <span class="status-badge status-pending"><i class="fa-solid fa-hourglass-half"></i> {{ $pendingCount }} pending</span>
    </div>
</div>

<form method="GET" action="{{ route('admin.verification.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="status">Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="not_submitted" {{ request('status') === 'not_submitted' ? 'selected' : '' }}>Not Submitted</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left: auto;">
        <input type="text" name="search" placeholder="Search provider name or email..." value="{{ request('search') }}" style="width: 220px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($workers->count())
        <table>
            <thead>
                <tr>
                    <th>Provider Name</th>
                    <th>Documents</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workers as $worker)
                @php
                    $docs = $worker->workerDocuments;
                    $status = $docs->isEmpty()
                        ? 'not_submitted'
                        : ($docs->contains('status', 'pending') ? 'pending'
                            : ($docs->contains('status', 'rejected') ? 'rejected' : 'verified'));
                    $reviewDoc = $docs->firstWhere('status', 'pending') ?? $docs->first();
                @endphp
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-initials" style="background: linear-gradient(135deg, var(--b4), var(--b6));">
                                {{ strtoupper(substr($worker->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($worker->last_name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="user-cell-info">
                                <div class="user-cell-name">{{ $worker->name }}</div>
                                <div class="user-cell-email">{{ $worker->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-muted">
                        {{ $docs->count() ? $docs->count() . ' doc' . ($docs->count() > 1 ? 's' : '') . ' / ' . count(\App\Support\WorkerDocuments::types()) . ' required' : '—' }}
                    </td>
                    <td class="text-sm text-muted">
                        {{ $docs->count() ? $docs->first()->created_at->format('M d, Y H:i') : ($worker->created_at ? $worker->created_at->format('M d, Y H:i') : 'N/A') }}
                    </td>
                    <td>
                        <span class="status-badge status-{{ $status === 'verified' ? 'approved' : $status }}">
                            @if($status === 'pending')<i class="fa-solid fa-hourglass-half"></i>
                            @elseif($status === 'verified')<i class="fa-solid fa-check-circle"></i>
                            @elseif($status === 'rejected')<i class="fa-solid fa-x-circle"></i>
                            @else<i class="fa-solid fa-circle"></i>
                            @endif
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        @if($reviewDoc)
                            <a href="{{ route('admin.verification.show', $reviewDoc) }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-magnifying-glass"></i> Review
                            </a>
                        @else
                            <a href="{{ route('admin.users.show', $worker) }}" class="btn btn-secondary btn-sm">
                                <i class="fa-regular fa-user"></i> View Worker
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $workers->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="empty-state-title">No workers found</div>
            <div class="empty-state-subtitle">Try adjusting your search or filter criteria.</div>
        </div>
    @endif
</div>
@endsection
