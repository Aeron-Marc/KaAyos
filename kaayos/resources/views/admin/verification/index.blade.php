@extends('layouts.admin')

@section('title', 'Worker Verifications')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-clipboard-check"></i> Worker Verifications</h1>
        <p>Review and approve pending service provider applications</p>
    </div>
    <div class="header-right">
        <span class="status-badge status-pending"><i class="fa-solid fa-hourglass-half"></i> {{ $documents->where('status', 'pending')->count() }} pending</span>
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
        <input type="text" name="search" placeholder="Search provider name..." value="{{ request('search') }}" style="width: 200px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($documents->count())
        <table>
            <thead>
                <tr>
                    <th>Provider Name</th>
                    <th>Document Type</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-initials" style="background: linear-gradient(135deg, var(--b4), var(--b6));">
                                {{ strtoupper(substr($doc->user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($doc->user->last_name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="user-cell-info">
                                <div class="user-cell-name">{{ $doc->user->name ?? 'Unknown' }}</div>
                                <div class="user-cell-email">{{ $doc->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</td>
                    <td class="text-sm text-muted">{{ $doc->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        <span class="status-badge status-{{ $doc->status === 'verified' ? 'approved' : $doc->status }}">
                            @if($doc->status === 'pending')<i class="fa-solid fa-hourglass-half"></i>
                            @elseif($doc->status === 'verified')<i class="fa-solid fa-check-circle"></i>
                            @elseif($doc->status === 'rejected')<i class="fa-solid fa-x-circle"></i>
                            @else<i class="fa-solid fa-circle"></i>
                            @endif
                            {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.verification.show', $doc) }}" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-magnifying-glass"></i> Review
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $documents->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="empty-state-title">No verification documents found</div>
            <div class="empty-state-subtitle">Try adjusting your search or filter criteria.</div>
        </div>
    @endif
</div>
@endsection
