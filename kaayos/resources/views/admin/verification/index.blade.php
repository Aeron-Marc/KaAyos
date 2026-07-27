@extends('layouts.admin')

@php
    $totalTypes = $totalTypes ?? 4;
@endphp

@section('title', 'Worker Verifications')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-clipboard-check"></i> Worker Verifications</h1>
        <p>Review and approve service provider verification applications</p>
    </div>
    <div class="header-right">
        <span class="status-badge status-pending"><i class="fa-solid fa-hourglass-half"></i> {{ $statusCounts['pending_review'] ?? 0 }} pending review</span>
        <span class="status-badge status-verified" style="background:rgba(251,191,36,.1);color:var(--y8)"><i class="fa-solid fa-rotate"></i> {{ $statusCounts['changes_requested'] ?? 0 }} changes req.</span>
    </div>
</div>

<div class="quick-stats" style="margin-bottom:24px;">
    <div class="stat-item">
        <div class="stat-item-label">Pending Review</div>
        <div class="stat-item-value">{{ $statusCounts['pending_review'] ?? 0 }}</div>
    </div>
    <div class="stat-item" style="border-left-color:var(--y9);">
        <div class="stat-item-label">Changes Requested</div>
        <div class="stat-item-value">{{ $statusCounts['changes_requested'] ?? 0 }}</div>
    </div>
    <div class="stat-item" style="border-left-color:var(--b4);">
        <div class="stat-item-label">Under Review</div>
        <div class="stat-item-value">{{ $statusCounts['under_review'] ?? 0 }}</div>
    </div>
    <div class="stat-item" style="border-left-color:var(--g4);">
        <div class="stat-item-label">Incomplete</div>
        <div class="stat-item-value">{{ $statusCounts['pending_documents'] ?? 0 }}</div>
    </div>
</div>

<form method="GET" action="{{ route('admin.verifications.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="status">Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
            <option value="changes_requested" {{ request('status') === 'changes_requested' ? 'selected' : '' }}>Changes Requested</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="pending_documents" {{ request('status') === 'pending_documents' ? 'selected' : '' }}>Incomplete</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left: auto;">
        <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}" style="width: 200px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($verifications->count())
        <table>
            <thead>
                <tr>
                    <th>Worker</th>
                    <th>Submitted</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Reviewed By</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($verifications as $v)
                @php
                    $docStatuses = $v->documents->pluck('status');
                    $verifiedCount = $docStatuses->filter(fn($s) => $s === 'verified')->count();
                    $pendingCount = $docStatuses->filter(fn($s) => $s === 'pending')->count();
                    $rejectedCount = $docStatuses->filter(fn($s) => $s === 'rejected')->count();
                    $pct = $totalTypes > 0 ? round(($verifiedCount / $totalTypes) * 100) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-initials" style="background:linear-gradient(135deg,var(--b4),var(--b6));">
                                {{ strtoupper(substr($v->user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($v->user->last_name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="user-cell-info">
                                <div class="user-cell-name">{{ $v->user->name ?? 'Unknown' }}</div>
                                <div class="user-cell-email">{{ $v->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-muted">
                        {{ $v->submitted_at ? $v->submitted_at->format('M d, Y') : '—' }}
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;max-width:120px;height:8px;background:var(--g1);border-radius:4px;overflow:hidden;">
                                <div style="height:100%;width:{{ $pct }}%;background:{{ $pct === 100 ? 'var(--s10)' : 'var(--b6)' }};border-radius:4px;transition:width .3s;"></div>
                            </div>
                            <span style="font-size:.82rem;font-weight:600;color:var(--g7);white-space:nowrap;">
                                {{ $verifiedCount }}/{{ $totalTypes }}
                            </span>
                        </div>
                        @if($pendingCount > 0)
                            <div style="font-size:.75rem;color:var(--y8);margin-top:2px;">{{ $pendingCount }} pending</div>
                        @endif
                        @if($rejectedCount > 0)
                            <div style="font-size:.75rem;color:var(--d8);margin-top:2px;">{{ $rejectedCount }} rejected</div>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge status-{{ $v->status === 'verified' ? 'approved' : $v->status }}">
                            @if($v->status === 'pending_review')<i class="fa-solid fa-hourglass-half"></i>
                            @elseif($v->status === 'under_review')<i class="fa-solid fa-magnifying-glass"></i>
                            @elseif($v->status === 'verified')<i class="fa-solid fa-check-circle"></i>
                            @elseif($v->status === 'changes_requested')<i class="fa-solid fa-rotate"></i>
                            @elseif($v->status === 'rejected')<i class="fa-solid fa-x-circle"></i>
                            @else<i class="fa-solid fa-circle"></i>
                            @endif
                            {{ ucfirst(str_replace('_', ' ', $v->status)) }}
                        </span>
                    </td>
                    <td class="text-sm text-muted">
                        {{ $v->reviewedBy?->name ?? '—' }}
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.verifications.show', $v) }}" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-magnifying-glass"></i> Review
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $verifications->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="empty-state-title">No verification applications found</div>
            <div class="empty-state-subtitle">Try adjusting your search or filter criteria.</div>
        </div>
    @endif
</div>
@endsection