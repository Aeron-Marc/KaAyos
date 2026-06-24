@extends('layouts.worker')

@section('title', 'Job Requests')
@section('page_title', 'Job Requests')

@section('topbar_actions')
    <a href="{{ route('worker.schedule') }}" class="btn btn-outline">
        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> View Schedule
    </a>
@endsection

@section('content')

<div class="filter-pills">
    <button type="button" class="filter-pill active">All</button>
    <button type="button" class="filter-pill">Pending</button>
    <button type="button" class="filter-pill">Accepted</button>
    <button type="button" class="filter-pill">Completed</button>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Incoming Requests</div>
            <h2 class="section-title">Job Requests</h2>
        </div>
    </div>

    <table class="bookings-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Service</th>
                <th>Date &amp; Time</th>
                <th>Location</th>
                <th>Amount</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobRequests as $job)
                <tr>
                    <td><span class="booking-worker">{{ $job['client'] }}</span></td>
                    <td>{{ $job['service'] }}</td>
                    <td>{{ $job['date'] }}</td>
                    <td style="font-size:.82rem;color:var(--g4);">{{ $job['location'] }}</td>
                    <td>₱{{ number_format($job['price']) }}</td>
                    <td>
                        @php
                            $statusClass = match($job['status']) {
                                'Accepted' => 'status-active',
                                'Pending' => 'status-pending',
                                'Completed' => 'status-done',
                                default => 'status-cancelled',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $job['status'] }}</span>
                    </td>
                    <td>
                        @if($job['status'] === 'Pending')
                            <div style="display:flex;gap:6px;">
                                <button class="btn btn-solid" style="padding:6px 12px;font-size:.78rem;">
                                    <i class="fa-solid fa-check" aria-hidden="true"></i> Accept
                                </button>
                                <button class="btn btn-ghost" style="padding:6px 12px;font-size:.78rem;color:#c62828;border-color:#ef9a9a;">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i> Decline
                                </button>
                            </div>
                        @elseif($job['status'] === 'Accepted')
                            <a href="{{ route('worker.messages') }}" class="link-action">
                                <i class="fa-solid fa-comment" aria-hidden="true"></i> Message
                            </a>
                        @else
                            <span style="font-size:.82rem;color:var(--g4);">Done</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fa-regular fa-clipboard" aria-hidden="true"></i>
                            <h3>No job requests yet</h3>
                            <p>When a client books your service, it will appear here.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
