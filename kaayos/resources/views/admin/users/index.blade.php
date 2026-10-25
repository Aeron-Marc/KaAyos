@extends('layouts.admin')

@section('title', __('admin.users.title'))
@section('skeleton')
    <div class="sp-title-row">
        <div class="ad-sk ad-sk-title" style="width:120px;"></div>
    </div>
    <div class="sp-filter-bar">
        <div class="ad-sk" style="height:38px;width:60px;border-radius:8px;"></div>
        <div class="ad-sk" style="height:38px;width:140px;border-radius:8px;"></div>
        <div class="ad-sk" style="height:38px;width:60px;border-radius:8px;"></div>
        <div class="ad-sk" style="height:38px;width:140px;border-radius:8px;"></div>
        <div class="ad-sk ad-sk-search" style="width:220px;"></div>
        <div class="ad-sk" style="height:38px;width:38px;border-radius:8px;"></div>
    </div>
    <div class="ad-sk-panel">
        @for($i = 0; $i < 6; $i++)
            <div class="ad-sk ad-sk-row"></div>
        @endfor
    </div>
@endsection
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-users" aria-hidden="true"></i> {{ __('admin.users.title') }}</h1>
        <p>{{ __('admin.users.subtitle') }}</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.users.index') }}" class="filters-bar" role="search">
    <div class="filter-group">
        <label for="role">{{ __('admin.users.role') }}</label>
        <select name="role" id="role">
            <option value="">{{ __('admin.users.all_roles') }}</option>
            <option value="client" @selected(request('role') === 'client')>{{ __('admin.users.role_client') }}</option>
            <option value="worker" @selected(request('role') === 'worker')>{{ __('admin.users.role_worker') }}</option>
            <option value="admin" @selected(request('role') === 'admin')>{{ __('admin.users.role_admin') }}</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="status">{{ __('admin.users.status') }}</label>
        <select name="status" id="status">
            <option value="">{{ __('admin.users.all_statuses') }}</option>
            <option value="active" @selected(request('status') === 'active')>{{ __('status.active') }}</option>
            <option value="suspended" @selected(request('status') === 'suspended')>{{ __('status.suspended') }}</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left:auto;">
        <label for="search" class="visually-hidden">{{ __('admin.users.search_label') }}</label>
        <input type="search" name="search" id="search" placeholder="{{ __('admin.users.search_placeholder') }}" value="{{ request('search') }}" style="width:220px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        {{ __('admin.common.apply') }}
    </button>
    @if(request()->hasAny(['role', 'status', 'search']))
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">{{ __('admin.common.reset') }}</a>
    @endif
</form>

<div class="table-container">
    @if($users->count())
        <table>
            <caption class="visually-hidden">{{ __('admin.users.caption') }}</caption>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.users.user') }}</th>
                    <th scope="col">{{ __('admin.users.role') }}</th>
                    <th scope="col">{{ __('admin.users.city') }}</th>
                    <th scope="col">{{ __('admin.users.status') }}</th>
                    <th scope="col">{{ __('admin.users.joined') }}</th>
                    <th scope="col" style="text-align:center;">{{ __('admin.users.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-initials" style="background: {{ $user->role === 'admin' ? 'var(--d10)' : ($user->role === 'worker' ? 'var(--s10)' : 'var(--b6)') }}" aria-hidden="true">
                                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? 'N', 0, 1)) }}
                                </div>
                                <div class="user-cell-info">
                                    <div class="user-cell-name">{{ $user->name }}</div>
                                    <div class="user-cell-email">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="status-badge status-{{ $user->role }}">{{ __('admin.users.role_'.$user->role) }}</span></td>
                        <td class="text-sm text-muted">{{ $user->city ?? __('admin.common.not_available') }}</td>
                        <td>
                            @if($user->suspended_at)
                                <span class="status-badge status-suspended"><i class="fa-solid fa-ban" aria-hidden="true"></i> {{ __('status.suspended') }}</span>
                            @else
                                <span class="status-badge status-active"><i class="fa-solid fa-check-circle" aria-hidden="true"></i> {{ __('status.active') }}</span>
                            @endif
                        </td>
                        <td class="text-sm text-muted"><x-datetime :value="$user->created_at" format="M j, Y" /></td>
                        <td style="text-align:center;">
                            <div class="actions-cell" style="justify-content:center;">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    {{ __('admin.common.view') }}
                                </a>
                                @if(!$user->isAdmin())
                                    @if($user->suspended_at)
                                        <form method="POST" action="{{ route('admin.users.reactivate', $user) }}" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                                {{ __('admin.users.reactivate') }}
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-warning btn-sm" data-suspend-target="suspend-{{ $user->id }}" aria-haspopup="dialog">
                                            <i class="fa-solid fa-ban" aria-hidden="true"></i>
                                            {{ __('admin.users.suspend') }}
                                        </button>
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
        <x-empty-state icon="fa-users-slash" :title="__('admin.users.empty')" :subtitle="__('admin.users.empty_sub')" />
    @endif
</div>

@foreach($users as $user)
    @if(!$user->isAdmin() && !$user->suspended_at)
        <dialog id="suspend-{{ $user->id }}" class="admin-dialog" aria-labelledby="suspend-title-{{ $user->id }}">
            <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                @csrf
                <div class="card-title" id="suspend-title-{{ $user->id }}">
                    <i class="fa-solid fa-ban" style="color:var(--d10)" aria-hidden="true"></i>
                    {{ __('admin.users.suspend_title', ['name' => $user->name]) }}
                </div>
                <div class="form-group">
                    <label for="reason-{{ $user->id }}">{{ __('admin.users.suspension_reason') }} <span style="color:var(--d10)">*</span></label>
                    <textarea name="reason" id="reason-{{ $user->id }}" rows="3" placeholder="{{ __('admin.users.suspension_placeholder') }}" required></textarea>
                </div>
                <div class="page-actions">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-ban" aria-hidden="true"></i>
                        {{ __('admin.users.confirm_suspension') }}
                    </button>
                    <button type="button" class="btn btn-secondary" data-dialog-close>{{ __('admin.common.cancel') }}</button>
                </div>
            </form>
        </dialog>
    @endif
@endforeach

@push('scripts')
<script>
document.querySelectorAll('[data-suspend-target]').forEach(function (button) {
    button.addEventListener('click', function () {
        var dialog = document.getElementById(button.dataset.suspendTarget);
        if (dialog) {
            dialog.showModal();
            dialog.querySelector('textarea')?.focus();
        }
    });
});
document.querySelectorAll('.admin-dialog').forEach(function (dialog) {
    dialog.querySelectorAll('[data-dialog-close]').forEach(function (button) {
        button.addEventListener('click', function () { dialog.close(); });
    });
    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) dialog.close();
    });
});
</script>
@endpush
@endsection
