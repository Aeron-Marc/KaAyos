@extends('layouts.client')

@section('title', 'Find Workers')
@section('page_title', 'Find Workers')

@section('topbar_actions')
    <button type="button" class="btn btn-ghost">
        <i class="fa-solid fa-sliders" aria-hidden="true"></i> Filters
    </button>
@endsection

@section('content')

<form action="{{ route('client.workers') }}" class="search-row">
    <div class="search-field">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, skill, or service…" aria-label="Search workers">
    </div>
    <div class="search-field" style="max-width:220px;flex:none;">
        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
        <input type="text" name="location" placeholder="Your barangay" aria-label="Location">
    </div>
    <button type="submit" class="btn btn-solid">Search</button>
</form>

<div class="filter-pills">
    <a href="{{ route('client.workers') }}" class="filter-pill {{ !request('category') ? 'active' : '' }}">All</a>
    @foreach($categories as $cat)
        <a href="{{ route('client.workers', ['category' => $cat['id']]) }}"
           class="filter-pill {{ request('category') === $cat['id'] ? 'active' : '' }}">
            {{ $cat['name'] }}
        </a>
    @endforeach
</div>

<div class="section-header">
    <div>
        <div class="eyebrow">Verified Trabahadors</div>
        <h2 class="section-title">{{ count($workers) }} workers near you</h2>
    </div>
    <span style="font-size:.84rem;color:var(--g4);">
        <i class="fa-solid fa-robot" aria-hidden="true"></i> AI-ranked by skill &amp; distance
    </span>
</div>

@php
    $filtered = collect($workers);
    if (request('category')) {
        $filtered = $filtered->filter(fn ($w) => strtolower($w['category']) === request('category') || str_contains(strtolower($w['category']), request('category')));
    }
@endphp

@if($filtered->isEmpty())
    <div class="card-panel">
        <div class="empty-state">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <h3>No workers found</h3>
            <p>Try a different category or broaden your search area.</p>
        </div>
    </div>
@else
    <div class="workers-grid">
        @include('client.partials.worker-cards', ['workers' => $filtered->values()->all()])
    </div>
@endif

@endsection
