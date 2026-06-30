@extends('layouts.admin')

@section('title', 'Reports')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-file-chart-column"></i> Reports</h1>
        <p>Generate and export platform activity reports</p>
    </div>
</div>

<div class="card" style="margin-bottom:24px">
    <div class="card-title"><i class="fa-solid fa-sliders"></i> Report Parameters</div>
    <form method="GET" action="{{ route('admin.reports.index') }}">
        <div class="form-row">
            <div class="form-group">
                <label for="type">Report Type</label>
                <select name="type" id="type">
                    <option value="bookings" {{ $reportType === 'bookings' ? 'selected' : '' }}>Bookings Report</option>
                    <option value="payments" {{ $reportType === 'payments' ? 'selected' : '' }}>Payments / Revenue Report</option>
                    <option value="verifications" {{ $reportType === 'verifications' ? 'selected' : '' }}>Verification Activity Report</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date_from">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="form-group">
                <label for="date_to">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}">
            </div>
        </div>
        <div class="page-actions" style="margin-top:8px">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Generate Report</button>
            @if($reportData)
            <a href="{{ route('admin.reports.export', ['type' => $reportType, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success"><i class="fa-solid fa-download"></i> Export CSV</a>
            @endif
        </div>
    </form>
</div>

@if($reportData)
<div class="metrics-grid" style="margin-bottom:24px">
    @if($reportType === 'bookings')
        <div class="metric-card accent-blue">
            <div class="metric-label">Total Bookings</div>
            <div class="metric-value">{{ $reportData['summary']['total'] }}</div>
        </div>
        <div class="metric-card accent-green">
            <div class="metric-label">Completed</div>
            <div class="metric-value">{{ $reportData['summary']['completed'] }}</div>
        </div>
        <div class="metric-card accent-red">
            <div class="metric-label">Cancelled</div>
            <div class="metric-value">{{ $reportData['summary']['cancelled'] }}</div>
        </div>
        <div class="metric-card accent-orange">
            <div class="metric-label">Active</div>
            <div class="metric-value">{{ $reportData['summary']['active'] }}</div>
        </div>
    @elseif($reportType === 'payments')
        <div class="metric-card accent-green">
            <div class="metric-label">Completed Bookings</div>
            <div class="metric-value">{{ $reportData['summary']['total_completed'] }}</div>
        </div>
        <div class="metric-card accent-red">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">₱{{ number_format($reportData['summary']['total_revenue'], 2) }}</div>
        </div>
        <div class="metric-card accent-blue">
            <div class="metric-label">Avg Booking Value</div>
            <div class="metric-value">₱{{ number_format($reportData['summary']['average_booking_value'], 2) }}</div>
        </div>
    @elseif($reportType === 'verifications')
        <div class="metric-card accent-blue">
            <div class="metric-label">Total Submissions</div>
            <div class="metric-value">{{ $reportData['summary']['total'] }}</div>
        </div>
        <div class="metric-card accent-orange">
            <div class="metric-label">Pending</div>
            <div class="metric-value">{{ $reportData['summary']['pending'] }}</div>
        </div>
        <div class="metric-card accent-green">
            <div class="metric-label">Verified</div>
            <div class="metric-value">{{ $reportData['summary']['verified'] }}</div>
        </div>
        <div class="metric-card accent-red">
            <div class="metric-label">Rejected</div>
            <div class="metric-value">{{ $reportData['summary']['rejected'] }}</div>
        </div>
    @endif
</div>

<div class="table-container">
    @if(count($reportData['rows']))
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($reportData['rows'][0]) as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['rows'] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td class="text-sm">{{ $cell }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-file-circle-exclamation"></i></div>
            <div class="empty-state-title">No data found</div>
            <div class="empty-state-subtitle">No records match the selected period.</div>
        </div>
    @endif
</div>
@else
<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fa-solid fa-chart-simple"></i></div>
        <div class="empty-state-title">Select report parameters</div>
        <div class="empty-state-subtitle">Choose a report type and date range, then click "Generate Report".</div>
    </div>
</div>
@endif
@endsection
