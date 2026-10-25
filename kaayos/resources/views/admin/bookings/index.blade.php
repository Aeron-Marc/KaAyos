@extends('layouts.admin')

@section('title', __('admin.bookings.title'))
@section('skeleton')
    <div class="sp-title-row">
        <div class="ad-sk ad-sk-title" style="width:140px;"></div>
    </div>
    <div class="sp-filter-bar">
        <div class="ad-sk" style="height:38px;width:120px;border-radius:8px;"></div>
        <div class="ad-sk" style="height:38px;width:130px;border-radius:8px;"></div>
        <div class="ad-sk" style="height:38px;width:200px;border-radius:8px;"></div>
        <div class="ad-sk" style="height:38px;width:38px;border-radius:8px;"></div>
    </div>
    <div class="ad-sk-panel">
        @for($i = 0; $i < 5; $i++)
            <div class="ad-sk ad-sk-row"></div>
        @endfor
    </div>
@endsection
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> {{ __('admin.bookings.title') }}</h1>
        <p>{{ __('admin.bookings.subtitle') }}</p>
    </div>
</div>

@if(request('date_basis') === 'outcome')
    <div class="info-box">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span>{{ __('admin.bookings.outcome_basis_notice') }}</span>
    </div>
@endif

<form method="GET" action="{{ route('admin.bookings.index') }}" class="filters-bar" role="search">
    @if(request('date_basis') === 'outcome')
        <input type="hidden" name="date_basis" value="outcome">
    @endif
    <div class="filter-group">
        <label for="status">{{ __('admin.bookings.status') }}</label>
        <select name="status" id="status">
            <option value="">{{ __('admin.bookings.all_statuses') }}</option>
            @foreach(['new', 'accepted', 'en_route', 'in_progress', 'completed', 'cancelled', 'declined', 'active', 'stuck', 'unassigned', 'other'] as $option)
                <option value="{{ $option }}" @selected(request('status') === $option)>{{ $option === 'other' ? __('status.other') : __('status.'.$option) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label for="date_from">{{ __('admin.bookings.from') }}</label>
        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
    </div>
    <div class="filter-group">
        <label for="date_to">{{ __('admin.bookings.to') }}</label>
        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
    </div>
    <div class="filter-group" style="margin-left:auto;">
        <label for="search" class="visually-hidden">{{ __('admin.bookings.search_label') }}</label>
        <input type="search" name="search" id="search" placeholder="{{ __('admin.bookings.search_placeholder') }}" value="{{ request('search') }}" style="width:220px;">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-search" aria-hidden="true"></i>
        {{ __('admin.common.apply') }}
    </button>
    @if(request()->hasAny(['status', 'date_from', 'date_to', 'search', 'date_basis']))
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">{{ __('admin.common.reset') }}</a>
    @endif
</form>

<div class="table-container">
    @if($bookings->count())
        <table>
            <caption class="visually-hidden">{{ __('admin.bookings.caption') }}</caption>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.bookings.id') }}</th>
                    <th scope="col">{{ __('admin.bookings.client') }}</th>
                    <th scope="col">{{ __('admin.bookings.worker') }}</th>
                    <th scope="col">{{ __('admin.bookings.service') }}</th>
                    <th scope="col">{{ __('admin.bookings.schedule') }}</th>
                    <th scope="col" class="table-col-price">{{ __('admin.bookings.price') }}</th>
                    <th scope="col">{{ __('admin.bookings.status') }}</th>
                    <th scope="col" style="text-align:center;">{{ __('admin.bookings.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                    <tr>
                        <td class="fw-600">#{{ $booking->id }}</td>
                        <td class="text-sm">{{ $booking->client->name ?? __('admin.common.not_available') }}</td>
                        <td class="text-sm">{{ $booking->worker->name ?? __('admin.common.not_available') }}</td>
                        <td class="text-sm">{{ $booking->service_category ?: __('admin.dash.categories.uncategorized') }}</td>
                        <td class="text-sm text-muted"><x-datetime :value="$booking->scheduled_at" format="M j, Y g:i A" /></td>
                        <td class="table-col-price"><x-money :value="$booking->price" /></td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                {{ __('admin.common.view') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $bookings->links() }}</div>
    @else
        <x-empty-state icon="fa-calendar-xmark" :title="__('admin.bookings.empty')" :subtitle="__('admin.bookings.empty_sub')" />
    @endif
</div>
@endsection
