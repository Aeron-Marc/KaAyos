@extends('layouts.admin')

@section('title', __('admin.disputes.title'))
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i> {{ __('admin.disputes.title') }}</h1>
        <p>{{ __('admin.disputes.subtitle') }}</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.disputes.index') }}" class="filters-bar" role="search">
    <div class="filter-group">
        <label for="type">{{ __('admin.disputes.type') }}</label>
        <select name="type" id="type">
            <option value="">{{ __('admin.disputes.all_types') }}</option>
            <option value="booking_dispute" @selected(request('type') === 'booking_dispute')>{{ __('admin.disputes.booking_dispute') }}</option>
            <option value="worker_report" @selected(request('type') === 'worker_report')>{{ __('admin.disputes.worker_report') }}</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="status">{{ __('admin.disputes.status') }}</label>
        <select name="status" id="status">
            <option value="">{{ __('admin.disputes.all_statuses') }}</option>
            <option value="unresolved" @selected(request('status') === 'unresolved')>{{ __('admin.disputes.unresolved') }}</option>
            <option value="open" @selected(request('status') === 'open')>{{ __('status.open') }}</option>
            <option value="under_review" @selected(request('status') === 'under_review')>{{ __('status.under_review') }}</option>
            <option value="resolved" @selected(request('status') === 'resolved')>{{ __('status.resolved') }}</option>
        </select>
    </div>
    <div class="filter-group" style="margin-left:auto;">
        <label for="search" class="visually-hidden">{{ __('admin.disputes.search_label') }}</label>
        <input type="search" name="search" id="search" placeholder="{{ __('admin.disputes.search_placeholder') }}" value="{{ request('search') }}" style="width:200px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        {{ __('admin.common.apply') }}
    </button>
    @if(request()->hasAny(['type', 'status', 'search']))
        <a href="{{ route('admin.disputes.index') }}" class="btn btn-secondary btn-sm">{{ __('admin.common.reset') }}</a>
    @endif
</form>

<div class="table-container">
    @if($disputes->count())
        <table>
            <caption class="visually-hidden">{{ __('admin.disputes.caption') }}</caption>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.disputes.id') }}</th>
                    <th scope="col">{{ __('admin.disputes.type') }}</th>
                    <th scope="col">{{ __('admin.disputes.booking') }}</th>
                    <th scope="col">{{ __('admin.disputes.raised_by') }}</th>
                    <th scope="col">{{ __('admin.disputes.reason') }}</th>
                    <th scope="col">{{ __('admin.disputes.status') }}</th>
                    <th scope="col">{{ __('admin.disputes.date') }}</th>
                    <th scope="col" style="text-align:center;">{{ __('admin.disputes.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disputes as $dispute)
                    <tr>
                        <td class="fw-600">#{{ $dispute->id }}</td>
                        <td>
                            <span class="status-badge {{ $dispute->type === 'worker_report' ? 'status-suspended' : 'status-under_review' }}">
                                {{ $dispute->type === 'worker_report' ? __('admin.disputes.worker_report') : __('admin.disputes.booking_dispute') }}
                            </span>
                        </td>
                        <td>
                            @if($dispute->booking_id)
                                <a href="{{ route('admin.bookings.show', $dispute->booking_id) }}" style="color:var(--b6)">#{{ $dispute->booking_id }}</a>
                            @else
                                <span class="text-muted">{{ __('admin.common.not_available') }}</span>
                            @endif
                        </td>
                        <td class="text-sm">{{ $dispute->raisedBy->name ?? __('admin.common.not_available') }}</td>
                        <td class="text-sm" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $dispute->reason }}">{{ $dispute->reason }}</td>
                        <td><x-status-badge :status="$dispute->status" /></td>
                        <td class="text-sm text-muted"><x-datetime :value="$dispute->created_at" /></td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                {{ __('admin.common.view') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $disputes->links() }}</div>
    @else
        <x-empty-state icon="fa-scale-balanced" :title="__('admin.disputes.empty')" :subtitle="__('admin.disputes.empty_sub')" />
    @endif
</div>
@endsection
