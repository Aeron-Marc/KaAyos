@extends('layouts.admin')

@section('title', 'Client Identity Verifications')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-id-card" aria-hidden="true"></i> {{ __('admin.client_verifications.title') }}</h1>
        <p>{{ __('admin.client_verifications.subtitle') }}</p>
    </div>
    <div class="header-right">
        <span class="status-badge status-pending">
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            {{ __('admin.client_verifications.pending_clients', ['count' => $pendingCount]) }}
        </span>
    </div>
</div>

<form method="GET" action="{{ route('admin.client-verifications.index') }}" class="filters-bar" role="search">
    <div class="filter-group">
        <label for="status">{{ __('admin.client_verifications.status') }}</label>
        <select name="status" id="status">
            <option value="">{{ __('admin.disputes.all_statuses') }}</option>
            <option value="pending" @selected(request('status') === 'pending')>{{ __('client.identity.status.pending') }}</option>
            <option value="verified" @selected(request('status') === 'verified')>{{ __('client.identity.status.verified') }}</option>
            <option value="rejected" @selected(request('status') === 'rejected')>{{ __('client.identity.status.rejected') }}</option>
            <option value="resubmission_required" @selected(request('status') === 'resubmission_required')>{{ __('client.identity.status.resubmission_required') }}</option>
            <option value="not_submitted" @selected(request('status') === 'not_submitted')>{{ __('client.identity.status.not_submitted') }}</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left:auto;">
        <label for="search" class="visually-hidden">{{ __('admin.client_verifications.search_label') }}</label>
        <input type="search" name="search" id="search" placeholder="{{ __('admin.client_verifications.search_placeholder') }}" value="{{ request('search') }}" style="width:240px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        {{ __('admin.common.apply') }}
    </button>
    @if(request()->hasAny(['status', 'search']))
        <a href="{{ route('admin.client-verifications.index') }}" class="btn btn-secondary btn-sm">{{ __('admin.common.reset') }}</a>
    @endif
</form>

<div class="table-container">
    @if($paginated->count())
        <table>
            <caption class="visually-hidden">{{ __('admin.client_verifications.caption') }}</caption>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.client_verifications.client') }}</th>
                    <th scope="col">{{ __('admin.client_verifications.status') }}</th>
                    <th scope="col">{{ __('admin.client_verifications.id_type') }}</th>
                    <th scope="col">{{ __('admin.client_verifications.submitted') }}</th>
                    <th scope="col" style="text-align:center;">{{ __('admin.client_verifications.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paginated as $client)
                @php
                    $latest = $client->latestClientIdentityVerification;
                    $status = $latest
                        ? ($latest->isPending() ? 'pending' : ($latest->isVerified() ? 'verified' : ($latest->isRejected() ? 'rejected' : 'resubmission_required')))
                        : 'not_submitted';
                @endphp
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-initials" style="background:linear-gradient(135deg,var(--b4),var(--b6));" aria-hidden="true">
                                {{ strtoupper(substr($client->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($client->last_name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="user-cell-info">
                                <div class="user-cell-name">{{ $client->name }}</div>
                                <div class="user-cell-email">{{ $client->email ?? __('admin.common.not_available') }}</div>
                            </div>
                        </div>
                    </td>
                    <td><x-status-badge :status="$status" /></td>
                    <td class="text-sm text-muted">
                        {{ $latest ? ucfirst(str_replace('_', ' ', $latest->id_type)) : __('admin.common.not_available') }}
                    </td>
                    <td class="text-sm text-muted">
                        @if($latest)
                            <x-datetime :value="$latest->submitted_at" format="M j, Y H:i" />
                        @else
                            {{ __('admin.common.not_available') }}
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <a href="{{ route('admin.client-verifications.show', $latest ?? $client) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            {{ __('admin.client_verifications.review') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $paginated->links() }}</div>
    @else
        <x-empty-state icon="fa-id-card" :title="__('admin.client_verifications.empty')" :subtitle="__('admin.client_verifications.empty_sub')" />
    @endif
</div>
@endsection
