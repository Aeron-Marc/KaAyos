@extends('layouts.admin')

@section('title', 'Dashboard')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-gauge-high"></i> Dashboard Overview</h1>
        <p>Welcome back! Here's your platform overview.</p>
    </div>
</div>

<div class="quick-stats">
    <div class="stat-item">
        <div class="stat-item-label">Last Updated</div>
        <div class="stat-item-value">Just now</div>
    </div>
    <div class="stat-item">
        <div class="stat-item-label">Platform Status</div>
        <div class="stat-item-value" style="color: var(--s10);"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i>Healthy</div>
    </div>
    <div class="stat-item">
        <div class="stat-item-label">Active Users</div>
        <div class="stat-item-value">{{ $activeUsers }}</div>
    </div>
</div>

<div class="metrics-grid">
    <div class="metric-card accent-blue">
        <div class="metric-label"><i class="fa-solid fa-users" style="margin-right: 6px;"></i>Total Users</div>
        <div class="metric-value">{{ number_format($totalUsers) }}</div>
        <div class="metric-change">
            <i class="fa-solid fa-circle"></i>
            <span>{{ $totalClients }} clients / {{ $totalProviders }} workers</span>
        </div>
    </div>

    <div class="metric-card accent-green">
        <div class="metric-label"><i class="fa-solid fa-calendar-check" style="margin-right: 6px;"></i>Active Bookings</div>
        <div class="metric-value">{{ number_format($activeBookings) }}</div>
        <div class="metric-change">
            <i class="fa-solid fa-circle"></i>
            <span>{{ $completedBookings }} completed / {{ $cancelledBookings }} cancelled</span>
        </div>
    </div>

    <div class="metric-card accent-orange">
        <div class="metric-label"><i class="fa-solid fa-hourglass-half" style="margin-right: 6px;"></i>Pending Verifications</div>
        <div class="metric-value">{{ number_format($pendingVerifications) }}</div>
        <div class="metric-change {{ $pendingVerifications > 0 ? 'negative' : '' }}">
            <i class="fa-solid {{ $pendingVerifications > 0 ? 'fa-exclamation-circle' : 'fa-check-circle' }}"></i>
            <span>{{ $pendingVerifications > 0 ? 'Awaiting review' : 'All clear' }}</span>
        </div>
    </div>

    <div class="metric-card accent-red">
        <div class="metric-label"><i class="fa-solid fa-chart-line" style="margin-right: 6px;"></i>Platform Revenue</div>
        <div class="metric-value">₱{{ number_format($totalRevenue, 0) }}</div>
        <div class="metric-change">
            <i class="fa-solid fa-circle"></i>
            <span>From {{ $completedBookings }} completed bookings</span>
        </div>
    </div>

    <div class="metric-card accent-purple">
        <div class="metric-label"><i class="fa-solid fa-scale-balanced" style="margin-right: 6px;"></i>Disputes</div>
        <div class="metric-value">{{ number_format($totalDisputes) }}</div>
        <div class="metric-change {{ $openDisputes > 0 ? 'negative' : '' }}">
            <i class="fa-solid fa-circle"></i>
            <span>{{ $openDisputes }} open</span>
        </div>
    </div>
</div>

<div class="layout-grid-2">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-user-plus"></i> Recent Users</div>
        @if($recentUsers->count())
            <table>
                <thead>
                    <tr><th>Name</th><th>Role</th><th>Joined</th></tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $u)
                    <tr>
                        <td><div class="user-cell"><div class="user-initials" style="background:var(--b6)">{{ strtoupper(substr($u->first_name, 0, 1)) }}{{ strtoupper(substr($u->last_name, 0, 1)) }}</div><div class="user-cell-info"><div class="user-cell-name">{{ $u->name }}</div><div class="user-cell-email">{{ $u->email }}</div></div></div></td>
                        <td><span class="status-badge status-{{ $u->role }}">{{ ucfirst($u->role) }}</span></td>
                        <td class="text-sm text-muted">{{ $u->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><div class="empty-state-icon"><i class="fa-solid fa-users"></i></div><div class="empty-state-title">No users yet</div></div>
        @endif
    </div>

    <div class="card">
        <div class="card-title"><i class="fa-solid fa-calendar"></i> Recent Bookings</div>
        @if($recentBookings->count())
            <table>
                <thead>
                    <tr><th>Client</th><th>Worker</th><th>Status</th><th>Price</th></tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $b)
                    <tr>
                        <td class="text-sm">{{ $b->client->name ?? 'N/A' }}</td>
                        <td class="text-sm">{{ $b->worker->name ?? 'N/A' }}</td>
                        <td><span class="status-badge status-{{ $b->status }}">{{ str_replace('_', ' ', ucfirst($b->status)) }}</span></td>
                        <td class="table-col-price">₱{{ number_format((float)$b->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><div class="empty-state-icon"><i class="fa-solid fa-calendar"></i></div><div class="empty-state-title">No bookings yet</div></div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-title"><i class="fa-solid fa-list-check"></i> Quick Actions</div>
    <div class="page-actions">
        <a href="{{ route('admin.verification.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Review Pending Verifications
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-file-export"></i> View Reports
        </a>
    </div>
</div>
@endsection
