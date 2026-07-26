@extends('layouts.client')

@section('title', 'Find Workers')
@section('page_title', 'Find Workers')

@section('topbar_actions')
    <button type="button" class="btn btn-ghost">
        <i class="fa-solid fa-sliders" aria-hidden="true"></i> Filters
    </button>
@endsection

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
    <form action="{{ route('client.workers') }}" class="search-row">
        <div class="search-field">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, skill, or service…" aria-label="Search workers">
        </div>
        <button type="submit" class="btn btn-solid">Search</button>
    </form>

    <div class="cat-dropdown" id="clientCatDropdown">
        <button class="cat-dropdown-trigger">
            <span id="clientCatLabel">
                @if(request('category'))
                    @php $selCat = collect($categories)->firstWhere('id', request('category')); @endphp
                    <i class="fa-solid {{ $selCat['icon'] ?? 'fa-wrench' }}"></i> {{ $selCat['name'] ?? 'Category' }}
                @else
                    <i class="fa-solid fa-th"></i> All Categories
                @endif
            </span>
            <i class="fa-solid fa-chevron-down cat-chev"></i>
        </button>
        <div class="cat-dropdown-menu" id="clientCatMenu">
            <div class="cat-menu-header">Filter by Category</div>
            <a href="{{ route('client.workers') }}" class="cat-option {{ !request('category') ? 'active' : '' }}">
                <span class="cat-option-icon"><i class="fa-solid fa-th"></i></span>
                <span class="cat-option-label">All Categories</span>
            </a>
            <div class="cat-menu-divider"></div>
            @foreach($categories as $cat)
                <a href="{{ route('client.workers', ['category' => $cat['id']]) }}"
                   class="cat-option {{ request('category') === $cat['id'] ? 'active' : '' }}">
                    <span class="cat-option-icon"><i class="fa-solid {{ $cat['icon'] ?? 'fa-wrench' }}"></i></span>
                    <span class="cat-option-label">{{ $cat['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

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
    if(!dd||!menu) return;
    trigger.addEventListener('click', function(e){
        e.stopPropagation();
        menu.classList.toggle('open');
        var chev = trigger.querySelector('.cat-chev');
        if(chev) chev.style.transform = menu.classList.contains('open') ? 'rotate(180deg)' : '';
    });
    document.addEventListener('click', function(){
        menu.classList.remove('open');
        var chev = trigger?.querySelector('.cat-chev');
        if(chev) chev.style.transform = '';
    });
})();
</script>
@endsection
