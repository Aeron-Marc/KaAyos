@extends('layouts.client')

@section('title', 'Bookings')
@section('page_title', 'Bookings')

@section('topbar_actions')
    <a href="{{ route('client.workers') }}" class="btn btn-solid">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> New Booking
    </a>
@endsection

@section('content')

<div class="filter-pills">
    <button type="button" class="filter-pill active">All</button>
    <button type="button" class="filter-pill">Active</button>
    <button type="button" class="filter-pill">Pending</button>
    <button type="button" class="filter-pill">Completed</button>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Booking Management</div>
            <h2 class="section-title">Your Scheduled Jobs</h2>
        </div>
    </div>

    <table class="bookings-table">
        <thead>
            <tr>
                <th>Worker</th>
                <th>Service</th>
                <th>Date &amp; Time</th>
                <th>Status</th>
                <th>Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td><span class="booking-worker">{{ $booking['worker'] }}</span></td>
                    <td>{{ $booking['service'] }}</td>
                    <td>{{ $booking['date'] }}</td>
                    <td>
                        @php
                            $statusClass = match($booking['status']) {
                                'Active' => 'status-active',
                                'Pending' => 'status-pending',
                                'Done' => 'status-done',
                                default => 'status-cancelled',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $booking['status'] }}</span>
                    </td>
                    <td>₱{{ number_format($booking['price']) }}</td>
                    <td>
                        <a href="{{ route('client.messages') }}" class="link-action">Message</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
