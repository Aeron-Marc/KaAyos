@extends('layouts.admin')

@section('title', 'Services')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-wrench"></i> Services</h1>
        <p>Manage services within each category</p>
    </div>
    <div class="header-right">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Service</a>
    </div>
</div>

<form method="GET" action="{{ route('admin.services.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group" style="margin-left: auto;">
        <input type="text" name="search" placeholder="Search services..." value="{{ request('search') }}" style="width: 200px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i></button>
</form>

<div class="table-container">
    @if($services->count())
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Base Price</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                <tr>
                    <td class="fw-600">{{ $service->name }}</td>
                    <td><span class="status-badge" style="background:var(--b0);color:var(--b7)">{{ $service->category->name ?? 'N/A' }}</span></td>
                    <td class="table-col-price">@if($service->base_price) ₱{{ number_format((float)$service->base_price, 2) }} @else <span class="text-muted">—</span> @endif</td>
                    <td>
                        @if($service->is_active)
                            <span class="status-badge status-active"><i class="fa-solid fa-check-circle"></i> Active</span>
                        @else
                            <span class="status-badge status-suspended"><i class="fa-solid fa-eye-slash"></i> Inactive</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div class="actions-cell" style="justify-content: center;">
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" style="display:inline" onsubmit="return confirm('Delete this service?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $services->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-wrench"></i></div>
            <div class="empty-state-title">No services found</div>
            <div class="empty-state-subtitle"><a href="{{ route('admin.services.create') }}" style="color:var(--b6)">Create your first service</a></div>
        </div>
    @endif
</div>
@endsection
