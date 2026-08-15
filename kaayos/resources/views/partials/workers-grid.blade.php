@if($workers->count())
    <div class="worker-grid fade-up" id="workerGrid">
      @foreach($workers as $w)
        <a href="{{ route('workers.public.show', $w['id']) }}" class="worker-card" data-category="{{ strtolower($w['category']) }}">
          <div class="w-card-top">
            @if($w['avatar'])
              <img src="{{ $w['avatar'] }}" alt="{{ $w['name'] }}" class="w-avatar" loading="lazy">
            @else
              <div class="w-avatar w-initials">{{ $w['initials'] }}</div>
            @endif
            <div class="w-meta">
              <div class="w-name-row">
                <div>
                  <div class="w-name">{{ $w['name'] }}</div>
                  <div class="w-trade">{{ $w['category'] }} <span class="peso-badge"><i class="fa-solid fa-certificate"></i> PESO</span></div>
                </div>
                <div class="w-rating">
                  <i class="fa-solid fa-star" aria-hidden="true"></i>
                  {{ number_format($w['rating'], 1) }}
                </div>
              </div>
              <div class="w-details-row">
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $w['distance'] }}</span>
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
          @if(!empty($w['works']) && count(array_filter(array_column($w['works'],'photo'))) > 0)
            <div class="w-works">
              <div class="w-works-row">
                @php $photos = array_filter(array_column($w['works'],'photo')); @endphp
                @foreach(array_slice($photos, 0, 3) as $photo)
                  <div class="w-work-thumb" style="background-image:url('{{ $photo }}')" title="Work sample"></div>
                @endforeach
              </div>
            </div>
          @endif

          <div class="w-card-actions">
            <span class="btn-outline-card" data-href="{{ route('workers.public.show', $w['id']) }}" onclick="event.stopPropagation();event.preventDefault();window.location.href=this.dataset.href"><i class="fa-regular fa-user" aria-hidden="true"></i> View Profile</span>
            <span class="btn btn-solid" data-id="{{ $w['id'] }}" data-name="{{ $w['name'] }}" data-category="{{ $w['category'] }}" onclick="event.stopPropagation();event.preventDefault();showBookModal(this.dataset.id, this.dataset.name, this.dataset.category)"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book Now</span>
          </div>
        </a>
      @endforeach
    </div>
    <div class="pagination fade-up">{{ $workers->links() }}</div>
  @else
    <div class="empty-workers fade-up">
      <i class="fa-solid fa-users-slash"></i>
      <h3>No workers found</h3>
      <p>No workers are available in this category yet. Check back soon or browse all categories.</p>
      <a href="/#services" class="btn btn-solid btn-lg" onclick="document.querySelector('.cat-pill.active')?.click()"><i class="fa-solid fa-arrow-left"></i> View All Workers</a>
    </div>
  @endif