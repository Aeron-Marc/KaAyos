@extends('layouts.admin')

@section('title', __('admin.verification.title'))
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> {{ __('admin.verification.title') }}</h1>
        <p>{{ __('admin.verification.subtitle') }}</p>
    </div>
    <div class="header-right">
        <span class="status-badge status-pending">
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            {{ __('admin.verification.pending_workers', ['count' => $pendingCount]) }}
        </span>
    </div>
</div>

<form method="GET" action="{{ route('admin.verification.index') }}" class="filters-bar" role="search">
    <div class="filter-group">
        <label for="status">{{ __('admin.verification.status') }}</label>
        <select name="status" id="status">
            <option value="">{{ __('admin.disputes.all_statuses') }}</option>
            <option value="pending" @selected(request('status') === 'pending')>{{ __('admin.verification.pending_review') }}</option>
            <option value="verified" @selected(request('status') === 'verified')>{{ __('status.verified') }}</option>
            <option value="rejected" @selected(request('status') === 'rejected')>{{ __('status.rejected') }}</option>
            <option value="not_submitted" @selected(request('status') === 'not_submitted')>{{ __('status.not_submitted') }}</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left:auto;">
        <label for="search" class="visually-hidden">{{ __('admin.verification.search_label') }}</label>
        <input type="search" name="search" id="search" placeholder="{{ __('admin.verification.search_placeholder') }}" value="{{ request('search') }}" style="width:220px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        {{ __('admin.common.apply') }}
    </button>
    @if(request()->hasAny(['status', 'search']))
        <a href="{{ route('admin.verification.index') }}" class="btn btn-secondary btn-sm">{{ __('admin.common.reset') }}</a>
    @endif
</form>

<div class="table-container">
    @if($workers->count())
        <table>
            <caption class="visually-hidden">{{ __('admin.verification.caption') }}</caption>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.verification.provider') }}</th>
                    <th scope="col">{{ __('admin.verification.documents') }}</th>
                    <th scope="col">{{ __('admin.verification.submitted') }}</th>
                    <th scope="col">{{ __('admin.verification.status') }}</th>
                    <th scope="col" style="text-align:center;">{{ __('admin.verification.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workers as $worker)
                    @php
                        $docs = $worker->workerDocuments;
                        $status = $docs->isEmpty()
                            ? 'not_submitted'
                            : ($docs->contains('status', 'pending') ? 'pending'
                                : ($docs->contains('status', 'rejected') ? 'rejected'
                                    : ($docs->contains('status', 'not_submitted') ? 'not_submitted' : 'verified')));
                        $reviewDoc = $docs->firstWhere('status', 'pending') ?? $docs->first();
                        $required = count(\App\Support\WorkerDocuments::types());
                    @endphp
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-initials" style="background:linear-gradient(135deg,var(--b4),var(--b6));" aria-hidden="true">
                                    {{ strtoupper(substr($worker->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($worker->last_name ?? 'N', 0, 1)) }}
                                </div>
                                <div class="user-cell-info">
                                    <div class="user-cell-name">{{ $worker->name }}</div>
                                    <div class="user-cell-email">{{ $worker->email ?? __('admin.common.not_available') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-muted">
                            {{ $docs->count() ? __('admin.verification.document_count', ['submitted' => $docs->count(), 'required' => $required]) : __('admin.common.not_available') }}
                        </td>
                        <td class="text-sm text-muted">
                            @if($docs->count())
                                <x-datetime :value="$docs->first()->created_at" format="M j, Y H:i" />
                            @else
                                <x-datetime :value="$worker->created_at" format="M j, Y H:i" />
                            @endif
                        </td>
                        <td><x-status-badge :status="$status" /></td>
                        <td style="text-align:center;">
                            @if($reviewDoc)
                                <a href="{{ route('admin.verification.show', $reviewDoc) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                    {{ __('admin.verification.review') }}
                                </a>
                            @else
                                <a href="{{ route('admin.users.show', $worker) }}" class="btn btn-secondary btn-sm">
                                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                                    {{ __('admin.verification.view_worker') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $workers->links() }}</div>
    @else
        <x-empty-state icon="fa-clipboard-check" :title="__('admin.verification.empty')" :subtitle="__('admin.verification.empty_sub')" />
    @endif
</div>
@endsection
