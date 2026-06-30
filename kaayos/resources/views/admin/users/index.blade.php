@extends('layouts.admin')

@section('title', 'Users')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-users"></i> Users</h1>
        <p>Manage all platform users</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.users.index') }}" class="filters-bar">
    <div class="filter-group">
        <label for="role">Role:</label>
        <select name="role" id="role" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
            <option value="worker" {{ request('role') === 'worker' ? 'selected' : '' }}>Worker</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
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
    @if($users->count())
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-initials" style="background: {{ $user->role === 'admin' ? 'var(--d10)' : ($user->role === 'worker' ? 'var(--s10)' : 'var(--b6)') }}">
                                {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="user-cell-info">
                                <div class="user-cell-name">{{ $user->name }}</div>
                                <div class="user-cell-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="status-badge status-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                    <td class="text-sm text-muted">{{ $user->city ?? 'N/A' }}</td>
                    <td>
                        @if($user->suspended_at)
                            <span class="status-badge status-suspended"><i class="fa-solid fa-ban"></i> Suspended</span>
                        @else
                            <span class="status-badge status-active"><i class="fa-solid fa-check-circle"></i> Active</span>
                        @endif
                    </td>
                    <td class="text-sm text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                    <td style="text-align: center;">
                        <div class="actions-cell" style="justify-content: center;">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                            @if(!$user->isAdmin())
                                @if($user->suspended_at)
                                    <form method="POST" action="{{ route('admin.users.reactivate', $user) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Reactivate {{ $user->name }}?')"><i class="fa-solid fa-rotate-left"></i> Reactivate</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('suspend-{{ $user->id }}').style.display='block'"><i class="fa-solid fa-ban"></i> Suspend</button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $users->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-users-slash"></i></div>
            <div class="empty-state-title">No users found</div>
            <div class="empty-state-subtitle">Try adjusting your search or filter criteria.</div>
        </div>
    @endif
</div>

@foreach($users as $user)
@if(!$user->isAdmin() && !$user->suspended_at)
<div id="suspend-{{ $user->id }}" style="display:none;margin-top:16px;">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-ban" style="color:var(--d10)"></i> Suspend User: {{ $user->name }}</div>
        <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
            @csrf
            <div class="form-group">
                <label for="reason-{{ $user->id }}">Suspension Reason <span style="color:var(--d10)">*</span></label>
                <textarea name="reason" id="reason-{{ $user->id }}" rows="3" placeholder="Why is this user being suspended?" required></textarea>
            </div>
            <div class="page-actions">
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Confirm Suspension</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('suspend-{{ $user->id }}').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach
@endsection
