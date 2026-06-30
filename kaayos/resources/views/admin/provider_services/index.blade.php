@extends('layouts.admin')

@section('title', 'Provider Services')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-handshake"></i> Provider Services</h1>
        <p>View which providers offer which services</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.provider-services.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="service_id">Service:</label>
        <select name="service_id" id="service_id" onchange="this.form.submit()">
            <option value="">All Services</option>
            @foreach($services as $svc)
                <option value="{{ $svc->id }}" {{ request('service_id') == $svc->id ? 'selected' : '' }}>{{ $svc->name }} ({{ $svc->category->name ?? 'N/A' }})</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group" style="margin-left: auto;">
        <input type="text" name="search" placeholder="Search provider..." value="{{ request('search') }}" style="width: 200px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($providerServices->count())
        <table>
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Service</th>
                    <th>Category</th>
                    <th>Custom Price</th>
                    <th>Available</th>
                </tr>
            </thead>
            <tbody>
                @foreach($providerServices as $ps)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-initials" style="background:var(--s10)">{{ strtoupper(substr($ps->user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($ps->user->last_name ?? 'N', 0, 1)) }}</div>
                            <div class="user-cell-info">
                                <div class="user-cell-name">{{ $ps->user->name ?? 'Unknown' }}</div>
                                <div class="user-cell-email">{{ $ps->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="fw-600">{{ $ps->service->name ?? 'N/A' }}</td>
                    <td><span class="status-badge" style="background:var(--b0);color:var(--b7)">{{ $ps->service->category->name ?? 'N/A' }}</span></td>
                    <td class="table-col-price">
                        @if($ps->custom_price)
                            ₱{{ number_format((float)$ps->custom_price, 2) }}
                        @else
                            <span class="text-muted">Default</span>
                        @endif
                    </td>
                    <td>
                        @if($ps->is_available)
                            <span class="status-badge status-active"><i class="fa-solid fa-check-circle"></i> Yes</span>
                        @else
                            <span class="status-badge status-suspended"><i class="fa-solid fa-x-circle"></i> No</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $providerServices->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-handshake-slash"></i></div>
            <div class="empty-state-title">No provider services found</div>
            <div class="empty-state-subtitle">Providers haven't linked any services yet.</div>
        </div>
    @endif
</div>
@endsection
