@foreach($workers as $worker)
<a href="{{ route('client.workers.show', $worker['id']) }}" class="worker-card">
    <div class="worker-top">
        @if(!empty($worker['avatar']))
            <img src="{{ $worker['avatar'] }}" alt="{{ $worker['name'] }}" class="worker-avatar">
        @else
            <div class="worker-avatar initials">{{ $worker['initials'] }}</div>
        @endif
        <div class="worker-meta">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                <div>
                    <div class="worker-name">
                        {{ $worker['name'] }}
                        @if($worker['verified'])
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;background:#16a34a;color:#fff;font-size:.55rem;margin-left:3px;vertical-align:middle;"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                        @endif
                    </div>
                    <div class="worker-trade">{{ $worker['category'] }}</div>
                </div>
                <div class="worker-rating">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                    {{ number_format($worker['rating'], 1) }}
                </div>
            </div>
            <div class="worker-details">
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $worker['distance'] }}</span>
                <span class="price">₱{{ number_format($worker['price']) }}/hr</span>
            </div>
        </div>
    </div>
    @if($worker['profile_complete'])
        @if(!empty($worker['skills']) && count($worker['skills']) > 0)
            <div class="skill-tags">
                @foreach(array_slice($worker['skills'], 0, 3) as $skill)
                    <span class="skill-tag">{{ $skill }}</span>
                @endforeach
            </div>
        @endif
    @else
        <div style="font-size:.78rem;color:var(--g4);margin-top:4px;">Profile not yet set up</div>
    @endif
</a>
@endforeach
