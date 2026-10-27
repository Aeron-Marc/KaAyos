@extends('layouts.public')

@section('title', 'Find Workers – KaAyos')
@section('meta_description', 'Search PESO-verified plumbers, electricians, carpenters and cleaners in Tuy, Batangas. AI-matched results ranked by skill, rating and distance.')

@push('styles')
<style>
.btn-outline{background:transparent;color:var(--b6);border:1.5px solid var(--b4);font-size:.875rem;font-weight:600;padding:8px 18px;border-radius:7px;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:7px}
.btn-outline:hover{background:var(--b0)}
.btn-outline-dark{background:transparent;color:var(--b9);border:1.5px solid var(--g1);border-radius:7px;font-weight:600;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:7px;padding:12px 0;font-size:.95rem;justify-content:center}
.btn-outline-dark:hover{background:var(--b0)}

.page-header{background:var(--b9);padding:48px 5% 40px;text-align:center}
.page-header h1{font-size:clamp(1.8rem,3vw,2.4rem);font-weight:700;color:#fff;margin-bottom:8px}
.page-header p{font-size:.93rem;color:rgba(255,255,255,.6)}

.search-section{background:#fff;padding:24px 5%;margin:0 auto;max-width:1100px;border-radius:0 0 14px 14px;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.search-bar{display:flex;gap:12px;max-width:800px;margin:0 auto}
.search-bar .input-wrap{position:relative;flex:1}
.search-bar .input-wrap i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--g4);font-size:1rem;pointer-events:none}
.search-bar .input-wrap input{width:100%;border:1.5px solid var(--g1);border-radius:9px;padding:13px 18px 13px 42px;font-size:1rem;color:var(--g9);background:var(--off);outline:none;font-family:inherit;transition:border-color .18s;box-sizing:border-box}
.search-bar .input-wrap input:focus{border-color:var(--b4)}
.search-bar .input-wrap input::placeholder{color:var(--g4)}
.search-bar .input-wrap.loc-input{flex:0 0 240px}
.search-bar .input-wrap.loc-input input{padding-left:44px}
.location-notice{margin:14px auto 0;max-width:800px;background:#fde8d8;border:1.5px solid #f5c2a0;color:#a04d1a;border-radius:9px;padding:10px 14px;font-size:.85rem;display:flex;align-items:center;gap:8px}
.location-hint{font-size:.88rem;color:var(--g4);margin:0 0 18px}
.location-hint i{color:var(--b6)}
.location-hint strong{color:var(--b8)}
.privacy-note{margin:12px auto 0;max-width:800px;font-size:.8rem;color:var(--g4);display:flex;align-items:baseline;gap:7px;justify-content:center;text-align:left}
.privacy-note i{color:var(--b6);transform:translateY(1px)}
.privacy-note em{color:var(--g7);font-style:italic}

.layout{display:flex;gap:28px;max-width:1200px;margin:0 auto;padding:32px 5%}
.sidebar{flex:0 0 220px}
.sidebar h3{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--b8);margin-bottom:14px}
.cat-list{display:flex;flex-direction:column;gap:6px}
.cat-link{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:9px;font-size:.88rem;font-weight:500;color:var(--g7);transition:all .18s;border:1.5px solid transparent}
.cat-link:hover{background:var(--b0);color:var(--b6);border-color:var(--b2)}
.cat-link.active{background:var(--b0);color:var(--b6);border-color:var(--b4);font-weight:600}
.cat-link .cat-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.cat-link .cat-count{font-size:.76rem;color:var(--g4);margin-left:auto}

.main-content{flex:1;min-width:0}
.result-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:8px}
.result-header h2{font-size:1.15rem;font-weight:600;color:var(--b9)}
.result-header .count{font-size:.88rem;color:var(--g4)}
.result-header .clear-link{font-size:.84rem;color:var(--b6);font-weight:500;display:inline-flex;align-items:center;gap:4px}
.result-header .clear-link:hover{text-decoration:underline}

.worker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}
.worker-card{background:var(--white);border:1.5px solid var(--g1);border-radius:16px;padding:20px 22px;cursor:pointer;transition:all .25s;display:flex;flex-direction:column;height:100%;box-shadow:0 2px 8px rgba(4,44,83,.06)}
.worker-card:hover{border-color:var(--b4);box-shadow:0 8px 28px rgba(0,0,0,.1);transform:translateY(-4px)}
.w-card-top{display:flex;gap:14px;align-items:flex-start}
.w-avatar{width:60px;height:60px;border-radius:12px;object-fit:cover;flex-shrink:0}
.w-initials{background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700}
.w-meta{flex:1;min-width:0}
.w-name-row{display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
.w-name{font-size:.95rem;font-weight:600;color:var(--b9)}
.w-trade{font-size:.8rem;color:var(--b6);font-weight:500;margin-top:1px}
.w-rating{display:flex;align-items:center;gap:4px;background:var(--b0);padding:4px 10px;border-radius:7px;font-size:.82rem;font-weight:600;color:var(--b8);white-space:nowrap;flex-shrink:0}
.w-rating i{color:#f59e0b;font-size:.72rem}
.w-details-row{display:flex;align-items:center;gap:14px;margin-top:8px;font-size:.82rem;color:var(--g4)}
.w-details-row .w-price{font-weight:600;color:var(--b7);margin-left:auto}
.w-skills{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.w-skill-tag{padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:500;background:var(--off);color:var(--g7);border:1px solid var(--g1)}
.w-works{margin-top:10px;padding-top:10px;border-top:1px solid var(--g1)}
.w-works-row{display:flex;gap:8px}
.w-work-thumb{width:64px;height:64px;border-radius:9px;background:var(--off);background-size:cover;background-position:center;border:1.5px solid var(--g1);display:flex;align-items:center;justify-content:center;color:var(--g4);font-size:1.1rem;transition:all .2s;cursor:pointer;flex-shrink:0}
.w-work-thumb:hover{transform:scale(1.08);box-shadow:0 4px 12px rgba(0,0,0,.1);border-color:var(--b4)}
.w-work-sample{background:var(--b0);color:var(--b2);border-style:dashed;font-size:1rem}
.w-card-actions{display:flex;gap:10px;margin-top:auto;padding-top:16px;border-top:1px solid var(--g1)}
.w-card-actions .btn{flex:1;justify-content:center;padding:11px 0;font-size:.84rem}
.w-card-actions .btn-outline{flex:1;justify-content:center;padding:11px 0;font-size:.84rem}

.pagination{display:flex;justify-content:center;gap:6px;margin-top:36px;flex-wrap:wrap}
.pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:.88rem;font-weight:500;border:1.5px solid var(--g1);transition:all .18s}
.pagination a:hover{background:var(--b0);border-color:var(--b4);color:var(--b6)}
.pagination .active{background:var(--b6);color:#fff;border-color:var(--b6)}

.empty-state{text-align:center;padding:60px 20px}
.empty-state i{font-size:3rem;color:var(--g1);margin-bottom:16px}
.empty-state h3{font-size:1.2rem;color:var(--g9);margin-bottom:8px}
.empty-state p{font-size:.9rem;color:var(--g4);max-width:360px;margin:0 auto}

@media(max-width:768px){
.layout{flex-direction:column}
.search-bar{flex-wrap:wrap}
.search-bar .input-wrap,.search-bar .input-wrap.loc-input{width:100%!important;flex:1 1 100%!important}
.search-bar .btn{width:100%;justify-content:center}
.sidebar{flex:none;overflow-x:auto;display:flex;flex-wrap:wrap;gap:10px}
.sidebar h3{width:100;margin-bottom:6px}
.cat-list{flex-direction:row;flex-wrap:wrap;gap:4px}
.cat-link{padding:8px 12px;font-size:.82rem}
}
@media(max-width:480px){
.worker-grid{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')

<div class="page-header">
  <h1>Find a Trabahador</h1>
  <p>Browse verified skilled workers in Tuy, Batangas</p>
</div>

<div class="search-section">
  <form class="search-bar" action="/search" method="GET">
    <div class="input-wrap">
      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      <input type="text" name="q" id="nlQuery" placeholder="e.g. leaking pipe, cleaning, painting..." value="{{ $query ?? '' }}" aria-label="Search workers" autocomplete="off">
    </div>
    <div class="input-wrap loc-input">
      <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
      <input type="text" name="location" list="barangayList" placeholder="Your barangay (e.g. Luna, Bolbok…)" value="{{ $location ?? '' }}" aria-label="Location" autocomplete="off">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search</button>
  </form>
  <p class="privacy-note"><i class="fa-solid fa-lock" aria-hidden="true"></i> Only your barangay is used to find nearby workers — never your exact address. You can also just describe the problem, like <em>"my sink is leaking in Bolbok."</em></p>
  <datalist id="barangayList">
    @foreach(\App\Support\TuyBarangays::allBarangays() as $barangayName)
      <option value="{{ $barangayName }}"></option>
    @endforeach
    <option value="Tuy, Batangas"></option>
  </datalist>
  @if(!empty($locationNotice))
    <div class="location-notice"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> {{ $locationNotice }}</div>
  @endif
</div>

<div class="layout">
  <aside class="sidebar">
    <h3>Categories</h3>
    <div class="cat-list">
      <a href="/search{{ $query ? '?q=' . urlencode($query) : '' }}{{ $location ? ($query ? '&' : '?') . 'location=' . urlencode($location) : '' }}" class="cat-link {{ !$category ? 'active' : '' }}">
        <span class="cat-icon" style="background:var(--b0);color:var(--b6)"><i class="fa-solid fa-grid-2"></i></span>
        All Services
      </a>
      @foreach($categories as $cat)
        @php
          $url = '/search?category=' . urlencode($cat->slug);
          if ($query) $url .= '&q=' . urlencode($query);
          if ($location) $url .= '&location=' . urlencode($location);
        @endphp
        <a href="{{ $url }}" class="cat-link {{ $category === $cat->slug ? 'active' : '' }}">
          <span class="cat-icon" style="background:var(--b0);color:var(--b6)"><i class="fa-solid {{ $cat->icon ?: 'fa-wrench' }}"></i></span>
          {{ $cat->name }}
        </a>
      @endforeach
    </div>
  </aside>

  <div class="main-content">
    <div class="result-header">
      <h2>
        @if($category)
          {{ ucfirst($category) }} Workers
        @else
          All Workers
        @endif
      </h2>
      <div>
        @if($category || $query || $location)
          <a href="/search" class="clear-link"><i class="fa-solid fa-xmark"></i> Clear filters</a>
        @endif
        <span class="count">{{ $workers->total() }} result{{ $workers->total() !== 1 ? 's' : '' }}</span>
      </div>
    </div>

    @if($locationBarangay)
      <p class="location-hint"><i class="fa-solid fa-map-pin" aria-hidden="true"></i> Showing workers near <strong>Brgy. {{ $locationBarangay }}, Tuy, Batangas</strong> — sorted by approximate distance. Exact addresses are never shared.</p>
    @endif

    @if($workers->count() > 0)
      <div class="worker-grid">
        @foreach($workers as $w)
          <a href="{{ route('workers.public.show', $w['id']) }}" class="worker-card">
            <div class="w-card-top">
              @if($w['avatar'])
                <img src="{{ $w['avatar'] }}" alt="{{ $w['name'] }}" class="w-avatar">
              @else
                <div class="w-avatar w-initials">{{ $w['initials'] }}</div>
              @endif
              <div class="w-meta">
                <div class="w-name-row">
                  <div>
                    <div class="w-name">{{ $w['name'] }}</div>
                    <div class="w-trade">{{ $w['category'] }}</div>
                  </div>
                  <div class="w-rating">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                    {{ number_format($w['rating'], 1) }}
                  </div>
                </div>
                <div class="w-details-row">
                  <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> @if($w['distance_km'] !== null){{ $w['distance_km'] }} km away@else{{ $w['distance'] }}@endif</span>
                  @if($w['reviews'] > 0)
                    <span><i class="fa-regular fa-comment"></i> {{ $w['reviews'] }}</span>
                  @endif
                  @if($w['price'] > 0)
                    <span class="w-price">₱{{ number_format($w['price']) }}/hr</span>
                  @endif
                </div>
              </div>
            </div>
            @if(!empty($w['skills']) && count($w['skills']) > 0)
              <div class="w-skills">
                @foreach(array_slice($w['skills'], 0, 3) as $skill)
                  <span class="w-skill-tag">{{ $skill }}</span>
                @endforeach
              </div>
            @endif
            @if(!empty($w['works']))
              <div class="w-works">
                <div class="w-works-row">
                  @for($i = 0; $i < min(3, count($w['works'])); $i++)
                    @if($w['works'][$i]['photo'])
                      <div class="w-work-thumb" style="background-image:url('{{ $w["works"][$i]["photo"] }}')" title="{{ $w['works'][$i]['caption'] ?? '' }}"></div>
                    @else
                      <div class="w-work-thumb w-work-sample"><i class="fa-solid fa-camera"></i></div>
                    @endif
                  @endfor
                </div>
              </div>
            @endif
            <div class="w-card-actions">
              <span class="btn btn-outline" data-href="{{ route('workers.public.show', $w['id']) }}" onclick="event.stopPropagation();event.preventDefault();window.location.href=this.dataset.href"><i class="fa-regular fa-user" aria-hidden="true"></i> View Profile</span>
            </div>
          </a>
        @endforeach
      </div>

      <div class="pagination">
        {{ $workers->links('vendor.pagination.tailwind') }}
      </div>
    @else
      <div class="empty-state">
        <i class="fa-solid fa-users-slash"></i>
        <h3>No matching services/workers found</h3>
        <p>Try a nearby barangay, a simpler term like <em>"plumber"</em> or <em>"cleaning"</em>, or clear your filters. We're adding new verified workers every week.</p>
        <a href="{{ url('/search') }}" class="btn btn-primary" style="margin-top: 16px; display: inline-flex;"><i class="fa-solid fa-rotate-left"></i> Reset Filters</a>
      </div>
    @endif
  </div>
</div>

@endsection

@push('scripts')
<script>
/* ROTATING NATURAL-LANGUAGE PLACEHOLDERS */
(function(){
  var el = document.getElementById('nlQuery');
  if(!el || el.value) return;
  var prompts = [
    'Try: "I need a plumber"',
    'Try: "My sink is leaking"',
    'Try: "Aircon cleaning in Bolbok"',
    'Try: "Electrician near Luna"'
  ];
  var i = 0;
  setInterval(function(){
    i = (i + 1) % prompts.length;
    el.placeholder = prompts[i];
  }, 3000);
})();
</script>
@endpush
