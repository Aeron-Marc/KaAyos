@extends('layouts.admin')

@section('title', 'Workers')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-briefcase"></i> Workers</h1>
        <p>View all registered workers and their categories</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.workers.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label for="status">Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left: auto;">
        <input type="text" name="search" placeholder="Search name or email..." value="{{ request('search') }}" style="width: 220px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($workers->count())
        <table>
            <thead>
                <tr>
                    <th>Worker</th>
                    <th>Service Category</th>
                    <th>Services</th>
                    <th>Hourly Rate</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workers as $worker)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-initials" style="background:var(--s10)">
                                {{ strtoupper(substr($worker->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($worker->last_name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="user-cell-info">
                                <div class="user-cell-name">{{ $worker->name }}</div>
                                <div class="user-cell-email">{{ $worker->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php
                            $cats = $worker->providerServices->pluck('service.category.name')->filter()->unique();
                        @endphp
                        @if($cats->count())
                            @foreach($cats as $cat)
                                <span class="status-badge" style="background:var(--b0);color:var(--b7);margin-bottom:4px;display:inline-block">{{ $cat }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">{{ $worker->service_category ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $services = $worker->providerServices->pluck('service.name')->filter();
                        @endphp
                        @if($services->count())
                            <span class="text-sm text-muted">{{ $services->take(3)->join(', ') }}{{ $services->count() > 3 ? '...' : '' }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="table-col-price">
                        @if($worker->workerProfile && $worker->workerProfile->hourly_rate)
                            ₱{{ number_format((float)$worker->workerProfile->hourly_rate, 2) }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($worker->suspended_at)
                            <span class="status-badge status-suspended"><i class="fa-solid fa-ban"></i> Suspended</span>
                        @else
                            <span class="status-badge status-active"><i class="fa-solid fa-check-circle"></i> Active</span>
                        @endif
                    </td>
                    <td class="text-sm text-muted">{{ $worker->created_at->format('M d, Y') }}</td>
                    <td style="text-align: center;">
                        <div class="actions-cell" style="justify-content: center;">
                            <a href="{{ route('admin.users.show', $worker) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $workers->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-briefcase-slash"></i></div>
            <div class="empty-state-title">No workers found</div>
            <div class="empty-state-subtitle">Try adjusting your search or filter criteria.</div>
        </div>
    @endif
</div>
@endsection
