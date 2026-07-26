@extends('layouts.worker')

@section('title', 'Earnings')
@section('page_title', 'Earnings')

@section('skeleton')
    <div class="sp-stats">
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:60px;"></div><div class="skeleton skeleton-text-sm" style="width:90px;"></div></div>
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:60px;"></div><div class="skeleton skeleton-text-sm" style="width:90px;"></div></div>
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:60px;"></div><div class="skeleton skeleton-text-sm" style="width:90px;"></div></div>
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:60px;"></div><div class="skeleton skeleton-text-sm" style="width:90px;"></div></div>
    </div>
    <div class="sp-panel">
        <div class="sp-title-row">
            <div class="skeleton skeleton-title" style="width:140px;"></div>
            <div class="skeleton skeleton-button" style="width:100px;"></div>
        </div>
        <div class="sp-table-header">
            <div class="skeleton skeleton-text" style="height:16px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;width:80px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;width:70px;"></div>
        </div>
        <div class="skeleton skeleton-table-row"></div>
        <div class="skeleton skeleton-table-row"></div>
        <div class="skeleton skeleton-table-row"></div>
        <div class="skeleton skeleton-table-row"></div>
        <div class="skeleton skeleton-table-row"></div>
    </div>
@endsection

@section('content')

<div class="stats-grid">
    <div class="stat-card accent">
        <div class="stat-icon"><i class="fa-solid fa-coins" aria-hidden="true"></i></div>
        <div class="stat-value">₱{{ number_format($earnings['total']) }}</div>
        <div class="stat-label">Total Earnings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-calendar-week" aria-hidden="true"></i></div>
        <div class="stat-value">₱{{ number_format($earnings['this_month']) }}</div>
        <div class="stat-label">This Month</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-clock" aria-hidden="true"></i></div>
        <div class="stat-value">₱{{ number_format($earnings['pending_payout']) }}</div>
        <div class="stat-label">Pending Payout</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></div>
        <div class="stat-value">₱{{ number_format($earnings['avg_per_job']) }}</div>
        <div class="stat-label">Avg per Job</div>
    </div>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Transaction History</div>
            <h2 class="section-title">Payouts</h2>
        </div>
        <a href="{{ route('worker.earnings.export') }}" class="btn btn-ghost" style="font-size:.82rem;">
            <i class="fa-solid fa-download" aria-hidden="true"></i> Export
        </a>
    </div>

    <table class="bookings-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Client</th>
                <th>Job</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($earnings['payouts'] as $payout)
                <tr>
                    <td>{{ $payout['date'] }}</td>
                    <td><span class="booking-worker">{{ $payout['client'] }}</span></td>
                    <td>{{ $payout['job'] }}</td>
                    <td>₱{{ number_format($payout['amount']) }}</td>
                    <td>
                        @php
                            $statusClass = $payout['status'] === 'Completed' ? 'status-done' : 'status-pending';
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $payout['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fa-regular fa-coins" aria-hidden="true"></i>
                            <h3>No earnings yet</h3>
                            <p>Complete jobs to start earning and track your payouts here.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
