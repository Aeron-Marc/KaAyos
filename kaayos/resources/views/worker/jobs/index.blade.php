@extends('layouts.worker')

@section('title', 'Job Requests')
@section('page_title', 'Job Requests')

@section('topbar_actions')
    <a href="{{ route('worker.schedule') }}" class="btn btn-outline">
        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> View Schedule
    </a>
@endsection

@php
    $filterLabels = [
        ''               => 'All',
        'new'            => 'New',
        'accepted'       => 'Accepted',
        'en_route'       => 'En Route',
        'in_progress'    => 'In Progress',
        'completed'      => 'Completed',
    ];

    $nextStepMap = [
        'new'         => ['status' => \App\Models\Booking::STATUS_ACCEPTED,    'label' => 'Accept this job request'],
        'accepted'    => ['status' => \App\Models\Booking::STATUS_EN_ROUTE,    'label' => 'Mark as En Route'],
        'en_route'    => ['status' => \App\Models\Booking::STATUS_IN_PROGRESS, 'label' => 'Start this job'],
        'in_progress' => ['status' => \App\Models\Booking::STATUS_COMPLETED,   'label' => 'Mark as Completed'],
    ];
@endphp

@section('content')

<div class="filter-pills" id="job-filters">
    @foreach($filterLabels as $val => $label)
        <button type="button"
                class="filter-pill {{ ($activeFilter ?? '') === $val ? 'active' : '' }}"
                data-filter="{{ $val }}">
            {{ $label }}
        </button>
    @endforeach
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
            @forelse($jobRequests as $i => $job)
                <tr class="job-row" data-status="{{ $job['raw_status'] }}" data-index="{{ $i }}">
                    <td><span class="booking-worker">{{ $job['client'] }}</span></td>
                    <td>{{ $job['service'] }}</td>
                    <td>{{ $job['date'] }}</td>
                    <td style="font-size:.82rem;color:var(--g4);">{{ $job['location'] }}</td>
                    <td>₱{{ number_format($job['price']) }}</td>
                    <td>
                        @php
                            $statusClass = match($job['raw_status']) {
                                'new'        => 'status-pending',
                                'accepted'   => 'status-active',
                                'en_route'   => 'status-active',
                                'in_progress'=> 'status-active',
                                'completed'  => 'status-done',
                                default      => 'status-cancelled',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $job['status'] }}</span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <button type="button"
                                    class="btn btn-outline"
                                    style="padding:6px 10px;font-size:.78rem;"
                                    onclick="showInfoModal({{ $i }})"
                                    title="View details">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                            @if(in_array($job['raw_status'], ['new', 'accepted', 'en_route', 'in_progress']))
                                <button type="button"
                                        class="btn btn-solid"
                                        style="padding:6px 12px;font-size:.78rem;"
                                        onclick="showConfirmModal({{ $i }})">
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    {{ $job['raw_status'] === 'new' ? 'Accept' : 'Next' }}
                                </button>
                            @else
                                <span style="font-size:.82rem;color:var(--g4);">Done</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="empty-row">
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

{{-- Info Modal --}}
<div id="infoModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="infoModalTitle">Job Details</h3>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body" id="infoModalBody">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Close</button>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div id="confirmModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="confirmModalTitle">Confirm Action</h3>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body" id="confirmModalBody">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Cancel</button>
            <form id="confirmForm" method="POST" action="" style="display:inline;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="confirmStatus" value="">
                <button type="submit" class="btn btn-solid" id="confirmSubmit">Confirm</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.45); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.modal-box {
    background: #fff; border-radius: 14px; width: 90%; max-width: 520px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: modalIn .2s ease;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px 0; font-weight: 600; font-size: 1.05rem;
}
.modal-close {
    background: none; border: none; font-size: 1.5rem; cursor: pointer;
    color: var(--g4); line-height: 1;
}
.modal-close:hover { color: var(--g8); }
.modal-body { padding: 16px 22px; max-height: 60vh; overflow-y: auto; }
.modal-footer {
    display: flex; gap: 10px; justify-content: flex-end;
    padding: 0 22px 18px;
}
.detail-grid {
    display: grid; grid-template-columns: 110px 1fr; gap: 10px 14px; font-size: .88rem;
}
.detail-label { color: var(--g5); font-weight: 500; }
.detail-value { color: var(--b9); }
</style>
@endpush

@push('scripts')
<script>
const jobs = @json($jobRequests);

function closeModals(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('infoModal').style.display = 'none';
    document.getElementById('confirmModal').style.display = 'none';
}

function showInfoModal(index) {
    const job = jobs[index];
    if (!job) return;

    const desc = job.description || 'No details provided.';

    document.getElementById('infoModalTitle').textContent = 'Job Details';
    document.getElementById('infoModalBody').innerHTML = `
        <div class="detail-grid">
            <span class="detail-label">Reference</span>
            <span class="detail-value">${job.booking_ref}</span>

            <span class="detail-label">Client</span>
            <span class="detail-value">${job.client}</span>

            <span class="detail-label">Email</span>
            <span class="detail-value">${job.client_email || 'N/A'}</span>

            <span class="detail-label">Phone</span>
            <span class="detail-value">${job.client_phone || 'N/A'}</span>

            <span class="detail-label">Service</span>
            <span class="detail-value">${job.service}</span>

            <span class="detail-label">Description</span>
            <span class="detail-value">${desc}</span>

            <span class="detail-label">Schedule</span>
            <span class="detail-value">${job.date}</span>

            <span class="detail-label">Location</span>
            <span class="detail-value">${job.location}</span>

            <span class="detail-label">Amount</span>
            <span class="detail-value" style="font-weight:600;">₱${Number(job.price).toLocaleString()}</span>

            <span class="detail-label">Status</span>
            <span class="detail-value">${job.status}</span>

            <span class="detail-label">Requested</span>
            <span class="detail-value">${job.created}</span>
        </div>
    `;

    document.getElementById('infoModal').style.display = 'flex';
}

function showConfirmModal(index) {
    const job = jobs[index];
    if (!job) return;

    const step = {
        'new':         { action: 'accept',    label: 'Accept',    verb: 'accepting' },
        'accepted':    { action: 'en_route',  label: 'Mark as En Route', verb: 'marking as en route' },
        'en_route':    { action: 'in_progress', label: 'Start',  verb: 'starting' },
        'in_progress': { action: 'completed', label: 'Complete', verb: 'completing' },
    }[job.raw_status];

    if (!step) return;

    const titles = {
        'new':         'Accept Job Request',
        'accepted':    'Mark as En Route',
        'en_route':    'Start Job',
        'in_progress': 'Complete Job',
    };

    document.getElementById('confirmModalTitle').textContent = titles[job.raw_status] || 'Confirm';
    document.getElementById('confirmModalBody').innerHTML = `
        <p style="margin:0 0 12px;color:var(--g6);">
            You are about to <strong>${step.verb}</strong> the following job:
        </p>
        <div class="detail-grid">
            <span class="detail-label">Client</span>
            <span class="detail-value">${job.client}</span>
            <span class="detail-label">Service</span>
            <span class="detail-value">${job.service}</span>
            <span class="detail-label">Schedule</span>
            <span class="detail-value">${job.date}</span>
            <span class="detail-label">Amount</span>
            <span class="detail-value">₱${Number(job.price).toLocaleString()}</span>
        </div>
        <p style="margin:14px 0 0;font-size:.82rem;color:var(--g4);">This action cannot be undone.</p>
    `;

    document.getElementById('confirmForm').action = '{{ route('worker.jobs.status', '__ID__') }}'.replace('__ID__', job.id);
    document.getElementById('confirmStatus').value = step.action;
    document.getElementById('confirmSubmit').textContent = step.label;

    document.getElementById('confirmModal').style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', function () {
    const pills = document.querySelectorAll('#job-filters .filter-pill');
    const rows = document.querySelectorAll('.job-row');
    const emptyRow = document.getElementById('empty-row');

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            let visible = 0;

            rows.forEach(row => {
                const match = !filter || row.dataset.status === filter;
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (emptyRow) {
                emptyRow.style.display = visible === 0 ? '' : 'none';
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModals();
    });
});
</script>
@endpush
