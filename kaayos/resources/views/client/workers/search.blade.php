@extends('layouts.client')

@section('title', 'Find Workers')
@section('page_title', 'Find Workers')

@php
    $activeFilterCount = 0;
    if (!empty($filters['area'])) $activeFilterCount++;
    if (!empty($filters['sort']) && $filters['sort'] !== 'rating') $activeFilterCount++;
    $preserved = array_filter([
        'area' => $filters['area'] ?? '',
        'sort' => $filters['sort'] ?? '',
        'q'    => $filters['q'] ?? '',
    ]);
    $clearPreserved = array_filter([
        'q'        => $filters['q'] ?? '',
        'category' => $filters['category'] ?? '',
    ]);
    $preservedQuery = $preserved ? '?' . http_build_query($preserved) : '';
    $clearQuery = $clearPreserved ? '?' . http_build_query($clearPreserved) : '';
@endphp

@section('skeleton')
    <div class="skeleton" style="height:52px;border-radius:var(--radius);margin-bottom:16px;"></div>
    <div class="sp-tabs">
        <div class="skeleton" style="height:32px;width:50px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:80px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:70px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:100px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:60px;border-radius:99px;"></div>
    </div>
    <div class="skeleton skeleton-title" style="width:180px;margin-bottom:16px;"></div>
    <div class="sp-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
        <div class="skeleton" style="height:240px;border-radius:var(--radius);"></div>
        <div class="skeleton" style="height:240px;border-radius:var(--radius);"></div>
        <div class="skeleton" style="height:240px;border-radius:var(--radius);"></div>
        <div class="skeleton" style="height:240px;border-radius:var(--radius);"></div>
    </div>
@endsection

@section('content')

<div class="search-filter-bar">
    <form action="{{ route('client.workers') }}" class="search-row" method="GET">
        <div class="search-field">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search by name, skill, or service…" aria-label="Search workers">
        </div>
        <input type="hidden" name="category" value="{{ $filters['category'] }}">
        <input type="hidden" name="area" value="{{ $filters['area'] }}">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <button type="submit" class="btn btn-solid">Search</button>
    </form>

    <div class="cat-dropdown" id="clientCatDropdown">
        <button class="cat-dropdown-trigger">
            <span id="clientCatLabel">
                @if($filters['category'])
                    @php $selCat = collect($categories)->firstWhere('id', $filters['category']); @endphp
                    <i class="fa-solid {{ $selCat['icon'] ?? 'fa-wrench' }}"></i> {{ $selCat['name'] ?? 'Category' }}
                @else
                    <i class="fa-solid fa-th"></i> All Categories
                @endif
            </span>
            <i class="fa-solid fa-chevron-down cat-chev"></i>
        </button>
        <div class="cat-dropdown-menu" id="clientCatMenu">
            <div class="cat-menu-header">Filter by Category</div>
            <a href="{{ route('client.workers') . $preservedQuery }}" class="cat-option {{ !$filters['category'] ? 'active' : '' }}">
                <span class="cat-option-icon"><i class="fa-solid fa-th"></i></span>
                <span class="cat-option-label">All Categories</span>
            </a>
            <div class="cat-menu-divider"></div>
            @foreach($categories as $cat)
                @php
                    $catQuery = '?' . http_build_query(array_merge(['category' => $cat['id']], $preserved));
                @endphp
                <a href="{{ route('client.workers') . $catQuery }}"
                   class="cat-option {{ $filters['category'] === $cat['id'] ? 'active' : '' }}">
                    <span class="cat-option-icon"><i class="fa-solid {{ $cat['icon'] ?? 'fa-wrench' }}"></i></span>
                    <span class="cat-option-label">{{ $cat['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <button type="button" class="btn btn-ghost" id="filtersToggleBtn" aria-expanded="false" aria-controls="filterPanel">
        <i class="fa-solid fa-sliders" aria-hidden="true"></i> Filters
        @if($activeFilterCount > 0)
            <span class="filter-badge">{{ $activeFilterCount }}</span>
        @endif
    </button>
</div>

<form action="{{ route('client.workers') }}" method="GET" id="filterPanel" class="filter-panel">
    <input type="hidden" name="q" value="{{ $filters['q'] }}">
    <input type="hidden" name="category" value="{{ $filters['category'] }}">

    <div class="filter-row">
        <div class="filter-field">
            <label for="filterArea">Service Area (Barangay)</label>
            <select name="area" id="filterArea" class="filter-select">
                <option value="">All barangays</option>
                @foreach($areas as $area)
                    <option value="{{ $area }}" {{ $filters['area'] === $area ? 'selected' : '' }}>{{ $area }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-field">
            <label for="filterSort">Sort by</label>
            <select name="sort" id="filterSort" class="filter-select">
                <option value="rating" {{ $filters['sort'] === 'rating' ? 'selected' : '' }}>Highest Rating</option>
                <option value="price_low" {{ $filters['sort'] === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ $filters['sort'] === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="reviews" {{ $filters['sort'] === 'reviews' ? 'selected' : '' }}>Most Reviews</option>
                <option value="exp" {{ $filters['sort'] === 'exp' ? 'selected' : '' }}>Most Experienced</option>
            </select>
        </div>

        <div class="filter-actions">
            @if($activeFilterCount > 0)
                <a href="{{ route('client.workers') . $clearQuery }}" class="btn btn-ghost filter-clear">
                    <i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Clear
                </a>
            @endif
            <button type="submit" class="btn btn-solid">
                <i class="fa-solid fa-check" aria-hidden="true"></i> Apply
            </button>
        </div>
    </div>
</form>

@if($activeFilterCount > 0)
    <div class="active-filters">
        <span class="active-filters-label">Active filters:</span>
        @if(!empty($filters['area']))
            <span class="active-filter-tag">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $filters['area'] }}
                <a href="{{ route('client.workers') . ('?' . http_build_query(array_filter(['q' => $filters['q'], 'category' => $filters['category'], 'sort' => $filters['sort']]))) }}" class="active-filter-x" aria-label="Remove area filter">&times;</a>
            </span>
        @endif
        @if(!empty($filters['sort']) && $filters['sort'] !== 'rating')
            <span class="active-filter-tag">
                <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                {{ ['price_low' => 'Price: Low→High', 'price_high' => 'Price: High→Low', 'reviews' => 'Most Reviews', 'exp' => 'Most Experienced'][$filters['sort']] ?? 'Sort' }}
                <a href="{{ route('client.workers') . ('?' . http_build_query(array_filter(['q' => $filters['q'], 'category' => $filters['category'], 'area' => $filters['area']]))) }}" class="active-filter-x" aria-label="Remove sort filter">&times;</a>
            </span>
        @endif
    </div>
@endif

<div class="section-header">
    <div>
        <div class="eyebrow">Trabahadors</div>
        <h2 class="section-title">{{ count($workers) }} worker(s) found</h2>
    </div>
</div>

@if(empty($workers))
    <div class="card-panel">
        <div class="empty-state">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <h3>No workers found</h3>
            <p>Try a different category or broaden your search.</p>
        </div>
    </div>
@else
    <div class="workers-grid">
        @include('client.partials.worker-cards', ['workers' => $workers])
    </div>
@endif

<script>
(function(){
    var dd = document.getElementById('clientCatDropdown');
    var menu = document.getElementById('clientCatMenu');
    var trigger = dd?.querySelector('.cat-dropdown-trigger');
    if (dd && menu && trigger) {
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            menu.classList.toggle('open');
            var chev = trigger.querySelector('.cat-chev');
            if (chev) chev.style.transform = menu.classList.contains('open') ? 'rotate(180deg)' : '';
        });
        document.addEventListener('click', function(){
            menu.classList.remove('open');
            var chev = trigger?.querySelector('.cat-chev');
            if (chev) chev.style.transform = '';
        });
    }

    var toggleBtn = document.getElementById('filtersToggleBtn');
    var panel = document.getElementById('filterPanel');
    if (toggleBtn && panel) {
        toggleBtn.addEventListener('click', function(e){
            e.stopPropagation();
            var isOpen = panel.classList.toggle('open');
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
        document.addEventListener('click', function(e){
            if (panel.contains(e.target) || toggleBtn.contains(e.target)) return;
            panel.classList.remove('open');
            toggleBtn.setAttribute('aria-expanded', 'false');
        });
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && panel.classList.contains('open')) {
                panel.classList.remove('open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.focus();
            }
        });
    }
})();
</script>
@endsection