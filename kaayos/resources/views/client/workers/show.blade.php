@extends('layouts.client')

@section('title', $worker->name)
@section('page_title', $worker->name)

@section('topbar_actions')
    <a href="{{ route('client.workers') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Search
    </a>
@endsection

@section('skeleton')
    <div class="sp-sidebar-layout">
        <div>
            <div class="skeleton skeleton-avatar-lg" style="margin:0 auto 16px;"></div>
            <div class="skeleton skeleton-title" style="width:120px;margin:0 auto 8px;"></div>
            <div class="skeleton skeleton-text-sm" style="width:80px;margin:0 auto 20px;"></div>
            <div class="skeleton skeleton-text" style="width:90%;"></div>
            <div class="skeleton skeleton-text-sm" style="width:60%;"></div>
            <div class="skeleton" style="height:38px;width:100%;border-radius:var(--radius-sm);margin-top:16px;"></div>
        </div>
        <div>
            <div class="sp-panel">
                <div class="skeleton skeleton-title" style="width:100px;"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text-sm"></div>
            </div>
            <div class="sp-panel">
                <div class="skeleton skeleton-title" style="width:80px;"></div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <div class="skeleton" style="height:28px;width:70px;border-radius:99px;"></div>
                    <div class="skeleton" style="height:28px;width:90px;border-radius:99px;"></div>
                    <div class="skeleton" style="height:28px;width:60px;border-radius:99px;"></div>
                </div>
            </div>
            <div class="sp-panel">
                <div style="display:flex;gap:12px;margin-bottom:12px;">
                    <div class="skeleton" style="height:28px;width:70px;border-radius:99px;"></div>
                    <div class="skeleton" style="height:28px;width:60px;border-radius:99px;"></div>
                    <div class="skeleton" style="height:28px;width:80px;border-radius:99px;"></div>
                </div>
                <div class="skeleton skeleton-card"></div>
            </div>
        </div>
    </div>
@endsection

@section('content')

<div class="worker-profile-layout">
    {{-- Left: Profile Card --}}
    <div class="card-panel" style="flex:0 0 340px;align-self:start;">
        <div style="text-align:center;padding:8px 0;">
            @if($worker->avatar)
                <img src="{{ Storage::url($worker->avatar) }}" alt="{{ $worker->name }}"
                     style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--b2);">
            @else
                <div style="width:96px;height:96px;border-radius:50%;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;margin:0 auto;">
                    {{ strtoupper(substr($worker->first_name, 0, 1) . substr($worker->last_name, 0, 1)) }}
                </div>
            @endif
            <h2 style="margin-top:14px;font-size:1.15rem;">{{ $worker->name }}</h2>
            <p style="color:var(--b6);font-weight:500;font-size:.9rem;">{{ $worker->service_category ?? 'General' }}</p>

            @if($workerProfile && $workerProfile->average_rating)
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:6px;">
                    <i class="fa-solid fa-star" style="color:#f59e0b;" aria-hidden="true"></i>
                    <span style="font-weight:600;">{{ number_format($workerProfile->average_rating, 1) }}</span>
                    <span style="color:var(--g4);font-size:.82rem;">({{ $reviews->count() }} reviews)</span>
                </div>
            @endif

            <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-top:10px;">
                @if($workerProfile && $workerProfile->government_id_verified)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#166534;padding:4px 10px;border-radius:20px;font-size:.76rem;font-weight:600;" title="Government Valid ID Verified">
                        <i class="fa-solid fa-id-card" aria-hidden="true"></i> ID Verified
                    </span>
                @endif
                @if($workerProfile && $workerProfile->tesda_certified)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#e0f2fe;color:#0369a1;padding:4px 10px;border-radius:20px;font-size:.76rem;font-weight:600;" title="Technical Education and Skills Development Authority Certified">
                        <i class="fa-solid fa-certificate" aria-hidden="true"></i> TESDA Certified
                    </span>
                @endif
                @if($workerProfile && $workerProfile->barangay_clearance_verified)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:20px;font-size:.76rem;font-weight:600;" title="Barangay Clearance on Record in Tuy">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Barangay Verified
                    </span>
                @endif
                @if($workerProfile && (float)$workerProfile->average_rating >= 4.5 && $reviews->count() >= 1)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#fdf2f8;color:#9d174d;padding:4px 10px;border-radius:20px;font-size:.76rem;font-weight:600;">
                        <i class="fa-solid fa-award" aria-hidden="true"></i> Top Rated
                    </span>
                @endif
            </div>

            <div style="margin-top:12px;">
                <button type="button" class="btn btn-sm btn-outline" onclick="openShareModal()" style="width:100%;justify-content:center;font-size:.8rem;gap:6px;">
                    <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Share Profile & QR
                </button>
            </div>

            <div style="margin-top:18px;display:flex;flex-direction:column;gap:8px;text-align:left;">
                @if($workerProfile && $workerProfile->hourly_rate)
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Rate</span>
                        <span style="font-weight:600;">₱{{ number_format($workerProfile->hourly_rate) }}/hr</span>
                    </div>
                @endif
                @if($workerProfile && $workerProfile->years_of_experience)
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Experience</span>
                        <span>{{ $workerProfile->years_of_experience }} years</span>
                    </div>
                @endif
                @if($worker->residence)
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Location</span>
                        <span style="font-weight:500;">{{ $worker->residence }}</span>
                    </div>
                @endif
                @php
                    $availDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                    $dayShort = ['Monday'=>'Mon','Tuesday'=>'Tue','Wednesday'=>'Wed','Thursday'=>'Thu','Friday'=>'Fri','Saturday'=>'Sat','Sunday'=>'Sun'];
                    $availMap = [];
                    $hasAnyActive = false;
                    if ($workerProfile && $workerProfile->availability) {
                        foreach ($workerProfile->availability as $a) {
                            $availMap[$a['day']] = $a;
                            if ($a['active'] ?? false) $hasAnyActive = true;
                        }
                    }
                    $hasAvailabilitySet = $workerProfile && $workerProfile->availability && count($workerProfile->availability) > 0;
                @endphp
                    <div style="font-size:.82rem;">
                        <div style="color:var(--g5);font-weight:500;margin-bottom:6px;">Availability</div>
                        @if($hasAvailabilitySet)
                            @foreach($availDays as $day)
                                @php
                                    $a = $availMap[$day] ?? null;
                                    $active = $a && ($a['active'] ?? false);
                                @endphp
                                <div style="display:flex;justify-content:space-between;padding:3px 0;{{ !$active ? 'opacity:.5;' : '' }}">
                                    <span>{{ $dayShort[$day] ?? $day }}</span>
                                    <span>
                                        @if($active)
                                            {{ \Carbon\Carbon::createFromFormat('H:i', $a['start'])->format('g:i A') }} – {{ \Carbon\Carbon::createFromFormat('H:i', $a['end'])->format('g:i A') }}
                                        @else
                                            <span style="color:var(--g4);">Unavailable</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div style="color:var(--g4);font-style:italic;padding:4px 0;">Not yet set</div>
                        @endif
                    </div>
                @if($worker->phone)
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Contact</span>
                        <span>{{ $worker->phone }}</span>
                    </div>
                @endif
            </div>

            <hr style="border:none;border-top:1px solid var(--g1);margin:16px 0 12px;">
            @if($canMessage)
                <a href="{{ route('client.messages.start', ['worker_id' => $worker->id]) }}" class="btn btn-outline" style="width:100%;justify-content:center;">
                    <i class="fa-regular fa-comment" aria-hidden="true"></i> Send Message
                </a>
            @endif
            @if($hasAnyActive)
            <button type="button" class="btn btn-solid" onclick="openBookModal()" style="width:100%;justify-content:center;{{ $canMessage ? 'margin-top:8px;' : '' }}">
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book Now
            </button>
            @else
            <button type="button" class="btn btn-solid" disabled title="Worker hasn't set their availability yet" style="width:100%;justify-content:center;opacity:.55;cursor:not-allowed;{{ $canMessage ? 'margin-top:8px;' : '' }}">
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Not Available
            </button>
            @endif
        </div>
    </div>

    {{-- Right: Details --}}
    @php
        $profileHasContent = $workerProfile && (
            $workerProfile->bio
            || !empty($workerProfile->skills)
            || !empty($workerProfile->spoken_languages)
            || ($workerProfile->portfolios && $workerProfile->portfolios->count() > 0)
        );
        $profileHasContent = $profileHasContent || ($documents && count($documents) > 0);
    @endphp
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:20px;">
        @if($profileHasContent)
            {{-- Bio --}}
            @if($workerProfile && $workerProfile->bio)
                <div class="card-panel">
                    <div class="card-panel-header">
                        <h3 class="section-title">About</h3>
                    </div>
                    <p style="font-size:.9rem;color:var(--g7);line-height:1.7;">{{ $workerProfile->bio }}</p>
                </div>
            @endif

            {{-- Skills --}}
            @if($workerProfile && !empty($workerProfile->skills))
                <div class="card-panel">
                    <div class="card-panel-header">
                        <h3 class="section-title">Skills</h3>
                    </div>
                    <div class="skill-tags" style="margin-top:4px;">
                        @foreach($workerProfile->skills as $skill)
                            <span class="skill-tag">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Equipped Tools & Gear --}}
            @if($workerProfile && !empty($workerProfile->tools_equipped))
                <div class="card-panel">
                    <div class="card-panel-header">
                        <h3 class="section-title"><i class="fa-solid fa-toolbox" aria-hidden="true" style="color:#2563eb;"></i> {{ __('badges.tools_equipped') }}</h3>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                        @foreach($workerProfile->tools_equipped as $tool)
                            <span style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:5px 12px;border-radius:20px;font-size:.82rem;font-weight:500;">
                                <i class="fa-solid fa-wrench" style="font-size:.75rem;"></i> {{ $tool }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Services Offered --}}
            @if($workerServices && $workerServices->count() > 0)
                <div class="card-panel">
                    <div class="card-panel-header">
                        <h3 class="section-title"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Services Offered</h3>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;margin-top:8px;">
                        @foreach($workerServices as $ps)
                            <div style="background:var(--off);border:1px solid var(--g1);border-radius:10px;padding:10px 12px;display:flex;flex-direction:column;gap:2px;">
                                <span style="font-size:.85rem;font-weight:600;color:var(--b9);">{{ $ps->service->name }}</span>
                                <span style="font-size:.8rem;color:var(--b6);font-weight:500;">
                                    @if($ps->custom_price)
                                        ₱{{ number_format($ps->custom_price, 2) }}
                                    @else
                                        ₱{{ number_format($ps->service->base_price, 2) }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Peers Recommended by Worker --}}
            @php
                $recommendedPeers = $workerProfile?->recommended_peers_details ?? [];
            @endphp
            @if(!empty($recommendedPeers))
                <div class="card-panel">
                    <div class="card-panel-header">
                        <div>
                            <div class="eyebrow" style="color:var(--p6,#2563eb);">Peer Network</div>
                            <h3 class="section-title"><i class="fa-solid fa-users" aria-hidden="true"></i> Trusted Peers Recommended by {{ $worker->name }}</h3>
                        </div>
                    </div>
                    <p style="font-size:.82rem;color:var(--g5);margin:2px 0 14px;">
                        Workers and specialists personally endorsed by {{ $worker->first_name }} for teamwork and collaborative jobs:
                    </p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
                        @foreach($recommendedPeers as $p)
                            <div style="background:var(--off,#f8fafc);border:1px solid var(--g1,#e2e8f0);border-radius:12px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;gap:10px;">
                                <div>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        @if($p['avatar'])
                                            <img src="{{ $p['avatar'] }}" alt="{{ $p['name'] }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:1px solid var(--g2,#cbd5e1);">
                                        @else
                                            <div style="width:40px;height:40px;border-radius:50%;background:#e0e7ff;color:#4338ca;font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:center;">
                                                {{ $p['initials'] }}
                                            </div>
                                        @endif
                                        <div style="flex:1;min-width:0;">
                                            <a href="{{ route('client.workers.show', $p['id']) }}" style="font-weight:700;color:var(--b9,#0f172a);font-size:.9rem;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                {{ $p['name'] }}
                                            </a>
                                            <div style="font-size:.78rem;color:var(--g5,#64748b);">{{ $p['service_category'] ?? 'Skilled Worker' }}</div>
                                        </div>
                                        @if($p['rating'] > 0)
                                            <span style="font-size:.78rem;font-weight:700;color:#d97706;display:inline-flex;align-items:center;gap:3px;">
                                                <i class="fa-solid fa-star" style="font-size:.7rem;"></i> {{ number_format($p['rating'], 1) }}
                                            </span>
                                        @endif
                                    </div>
                                    @if(!empty($p['note']))
                                        <div style="margin-top:10px;font-size:.8rem;color:var(--g6,#475569);font-style:italic;background:#fff;padding:8px 10px;border-radius:8px;border-left:3px solid #3b82f6;">
                                            &ldquo;{{ $p['note'] }}&rdquo;
                                        </div>
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px;margin-top:4px;">
                                    <a href="{{ route('client.workers.show', $p['id']) }}" class="btn btn-sm btn-outline" style="flex:1;justify-content:center;padding:5px 8px;font-size:.78rem;">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- Languages --}}
            @if($workerProfile && !empty($workerProfile->spoken_languages))
                <div class="card-panel">
                    <div class="card-panel-header">
                        <h3 class="section-title">Languages</h3>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">
                        @foreach($workerProfile->spoken_languages as $lang)
                            <span style="background:var(--g0);padding:4px 12px;border-radius:20px;font-size:.82rem;color:var(--g7);">{{ $lang }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Documents --}}
            @if($documents && count($documents) > 0)
                <div class="card-panel">
                    <div class="card-panel-header">
                        <h3 class="section-title">Worker Documents</h3>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px;">
                        @foreach($documents as $doc)
                            <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--g0);border-radius:8px;font-size:.85rem;">
                                <i class="fa-solid {{ $doc['icon'] }}" style="color:var(--b5);" aria-hidden="true"></i>
                                <span style="flex:1;">{{ $doc['name'] }}</span>
                                @if($doc['status'] === 'Verified')
                                    <span style="color:#166534;font-size:.78rem;"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                                @else
                                    <span style="color:var(--g4);font-size:.78rem;">{{ $doc['status'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                $hasPortfolio = $workerProfile && $workerProfile->portfolios && $workerProfile->portfolios->count() > 0;
                $hasReviews = $reviews->count() > 0;
            @endphp

            <div class="card-panel tabs-card">
                <input type="radio" name="p-tabs" id="pt-posts"{{ $hasPortfolio ? ' checked' : '' }}>
                <input type="radio" name="p-tabs" id="pt-reviews"{{ !$hasPortfolio ? ' checked' : '' }}>

                <div class="tabs-bar">
                    <label for="pt-posts"><i class="fa-solid fa-images" aria-hidden="true"></i> Posts</label>
                    <label for="pt-reviews"><i class="fa-solid fa-star" aria-hidden="true"></i> Reviews</label>
                    <div class="tabs-slider"></div>
                </div>

                <div class="tab-content" id="tc-posts">
                    @if($hasPortfolio)
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;">
                            @foreach($workerProfile->portfolios as $item)
                                @php $portfolioPhoto = \App\Support\PortfolioPhoto::url($item->photo_path, $item->id); @endphp
                                <div class="works-item">
                                    <div class="thumb" style="background-image:url('{{ $portfolioPhoto }}')">
                                    </div>
                                    @if($item->caption)
                                        <div class="works-caption">{{ $item->caption }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="tab-empty">
                            <i class="fa-regular fa-image" aria-hidden="true"></i>
                            <p>No posts yet</p>
                        </div>
                    @endif
                </div>

                <div class="tab-content" id="tc-reviews">
                    @if($hasReviews)
                        @foreach($reviews as $review)
                            <div style="padding:14px 0;border-bottom:1px solid var(--g1);">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-weight:500;font-size:.88rem;">{{ $review->client?->name ?? 'Anonymous' }}</span>
                                    <div style="display:flex;gap:2px;">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="fa-{{ $s <= $review->rating ? 'solid' : 'regular' }} fa-star" style="color:#f59e0b;font-size:.75rem;" aria-hidden="true"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if($review->photo_url)
                                    <div class="review-photo-wrap" onclick="openLightbox('{{ $review->photo_url }}')">
                                        <img src="{{ $review->photo_url }}" alt="Review photo">
                                        <div class="review-photo-overlay"><i class="fa-solid fa-expand" aria-hidden="true"></i> View photo</div>
                                    </div>
                                @endif
                                @if($review->comment)
                                    <p style="font-size:.85rem;color:var(--g7);margin-top:6px;line-height:1.5;">{{ $review->comment }}</p>
                                @endif
                                <p style="font-size:.75rem;color:var(--g4);margin-top:4px;">{{ $review->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="tab-empty">
                            <i class="fa-regular fa-comment" aria-hidden="true"></i>
                            <p>No reviews yet</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card-panel" style="text-align:center;padding:40px 22px;">
                <div style="font-size:2.5rem;color:var(--g2);margin-bottom:14px;">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                </div>
                <h3 style="font-size:1.05rem;color:var(--g6);margin-bottom:6px;">Profile not yet set up</h3>
                <p style="font-size:.85rem;color:var(--g4);max-width:360px;margin:0 auto;">
                    {{ $worker->name }} hasn't added their profile details yet. Check back later for updates.
                </p>
            </div>
        @endif


    </div>
</div>

{{-- Book Now Modal --}}
<div id="book-modal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeBookModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Book {{ $worker->name }}</h3>
            <button type="button" class="modal-close" onclick="closeBookModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="book-form" onsubmit="submitBooking(event)">
                @csrf
                <input type="hidden" name="worker_id" value="{{ $worker->id }}">

                <div class="form-group">
                    <label>Service</label>
                    <input type="text" class="form-control" value="{{ $worker->service_category ?? 'General' }}" readonly style="background:#f5f5f5;cursor:default;">
                    <input type="hidden" name="service_category" value="{{ $worker->service_category ?? 'General' }}">
                </div>

                <div class="form-group">
                    <label for="scheduled_at">Schedule</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                           class="form-control" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required>
                    <div id="schedule-warning" style="display:none;margin-top:8px;padding:8px 12px;background:#fff3cd;border:1px solid #ffc107;border-radius:6px;font-size:.8rem;color:#856404;align-items:flex-start;gap:6px;">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-top:1px;" aria-hidden="true"></i>
                        <span id="schedule-warning-text"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="property_type">Property / Premises Type</label>
                    <select name="property_type" id="property_type" class="form-control">
                        <option value="residential" selected>Residential House</option>
                        <option value="apartment">Apartment / Rental Unit</option>
                        <option value="commercial">Commercial Shop / Store / Office</option>
                        <option value="industrial">Warehouse / Industrial Facility</option>
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label for="pricing_type">Billing Mode</label>
                        <select name="pricing_type" id="pricing_type" class="form-control" onchange="recalculatePriceEstimate()">
                            <option value="fixed" selected>Fixed Task Rate</option>
                            <option value="hourly">Hourly Rate (₱{{ number_format($workerProfile->hourly_rate ?? 350) }}/hr)</option>
                        </select>
                    </div>
                    <div class="form-group" id="duration_group">
                        <label for="estimated_duration_hours">Estimated Hours</label>
                        <input type="number" step="0.5" min="1" max="24" id="estimated_duration_hours" name="estimated_duration_hours" value="2" class="form-control" onchange="recalculatePriceEstimate()">
                    </div>
                </div>

                <div class="form-group">
                    <label for="complexity_level">Job Complexity / Difficulty</label>
                    <select name="complexity_level" id="complexity_level" class="form-control" onchange="recalculatePriceEstimate()">
                        <option value="standard" selected>Standard (1.0x - Routine maintenance / basic repair)</option>
                        <option value="moderate">Moderate (1.2x - Multiple stages / ceiling / attic / tight access)</option>
                        <option value="high_hazard">High Risk / Hazardous (1.5x - 2-story roof / 220V live panel / structural)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="house_no">House No. / Street</label>
                    <input type="text" id="house_no" name="house_no" class="form-control"
                           placeholder="e.g. 123 Mabini St" required>
                </div>

                @php
                    $allBarangays = ['Acle','Bayudbud','Bolbok','Burgos','Dalima','Dao','Guinhawa','Lumbangan','Luna','Luntal','Magahis','Malibu','Mataywanac','Palincaro','Putol','Rillo','Rizal','Sabang','San Jose','Talon','Toong','Tuyon-Tuyon'];
                    $coveredBarangays = $workerProfile ? ($workerProfile->service_areas ?? $workerProfile->service_zone ?? []) : [];
                @endphp
                <input type="hidden" name="latitude" id="client_lat" value="">
                <input type="hidden" name="longitude" id="client_lng" value="">

                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select id="barangay" name="barangay" class="form-control" required onchange="onBarangayChange(this.value)">
                        <option value="">Select barangay…</option>
                        @forelse($coveredBarangays as $barangay)
                            <option value="{{ $barangay }}">{{ $barangay }}</option>
                        @empty
                            @foreach($allBarangays as $barangay)
                                <option value="{{ $barangay }}">{{ $barangay }}</option>
                            @endforeach
                        @endforelse
                    </select>
                    <div id="loc-indicator" style="display:none;font-size:.78rem;color:var(--g5);margin-top:4px;align-items:center;gap:6px;">
                        <i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> <span id="loc-indicator-text"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes <small>(optional)</small></label>
                    <div class="notes-textarea-wrap">
                        <textarea id="notes" name="notes" class="form-control"
                                  placeholder="Describe what you need done…" maxlength="2000"></textarea>
                        <span class="notes-counter">0 / 2000</span>
                    </div>
                </div>

                @if($worker->workerProfile && $worker->workerProfile->hourly_rate)
                    <div style="font-size:.85rem;color:var(--g5);margin-bottom:12px;">
                        Rate: <strong>₱{{ number_format($worker->workerProfile->hourly_rate) }}/hr</strong>
                    </div>
                @endif

                <div class="agreement-box">
                    <p class="agreement-title">Service Agreement</p>
                    <div class="agreement-summary">
                        <span><strong>Service:</strong> <span id="agree-service">{{ $worker->service_category ?? '—' }}</span></span>
                        <span><strong>Date:</strong> <span id="agree-date">—</span></span>
                        <span><strong>Location:</strong> <span id="agree-location">—</span></span>
                        <span><strong>Price:</strong> <span id="agree-price">—</span></span>
                    </div>
                    <label class="agreement-check">
                        <input type="checkbox" id="agree-terms" required>
                        <span>I agree to the <a href="{{ url('/terms') }}" target="_blank">Terms of Service</a> and confirm that the details above are accurate. I understand that this creates a binding service agreement with the worker.</span>
                    </label>
                </div>

                <div id="book-msg" style="display:none;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeBookModal()">Cancel</button>
            <button type="submit" class="btn btn-solid" id="book-submit-btn" form="book-form">Send Request</button>
        </div>
    </div>
</div>

{{-- Share Profile Modal --}}
<div id="share-modal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeShareModal()">
    <div class="modal-box" style="max-width:420px;text-align:center;">
        <div class="modal-header">
            <h3>Share {{ $worker->name }}'s Profile</h3>
            <button type="button" class="modal-close" onclick="closeShareModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:20px;">
            <p style="font-size:.85rem;color:var(--g5);margin-bottom:14px;">Scan QR Code or share this link to book directly:</p>
            <div style="background:#f8fafc;padding:16px;border-radius:12px;display:inline-block;border:1px solid #e2e8f0;margin-bottom:16px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(url('/workers/' . $worker->id)) }}" alt="Worker QR Code" style="width:160px;height:160px;border-radius:6px;display:block;">
            </div>
            <div style="display:flex;gap:6px;margin-bottom:16px;">
                <input type="text" id="shareProfileUrl" value="{{ url('/workers/' . $worker->id) }}" readonly class="form-control" style="font-size:.82rem;">
                <button type="button" class="btn btn-solid" onclick="copyShareUrl()" id="copyShareBtn">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
            <div style="display:flex;gap:10px;justify-content:center;">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/workers/' . $worker->id)) }}" target="_blank" class="btn btn-outline" style="font-size:.82rem;gap:6px;">
                    <i class="fa-brands fa-facebook" style="color:#1877F2;"></i> Facebook
                </a>
                <a href="https://api.whatsapp.com/send?text={{ urlencode('Book ' . $worker->name . ' on KaAyos: ' . url('/workers/' . $worker->id)) }}" target="_blank" class="btn btn-outline" style="font-size:.82rem;gap:6px;">
                    <i class="fa-brands fa-whatsapp" style="color:#22c55e;"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const WORKER_AVAILABILITY = @json($workerProfile && $workerProfile->availability ? $workerProfile->availability : []);
const DAY_MAP = {0:'Sunday',1:'Monday',2:'Tuesday',3:'Wednesday',4:'Thursday',5:'Friday',6:'Saturday'};

const TUY_COORDS = {
    'Acle': [14.0051, 120.7477], 'Bayudbud': [14.0555, 120.7363], 'Bolbok': [14.0215, 120.7582],
    'Burgos': [14.0162, 120.7301], 'Dalima': [14.0339, 120.6950], 'Dao': [13.9996, 120.7547],
    'Guinhawa': [13.9823, 120.7268], 'Lumbangan': [14.0245, 120.7150], 'Luna': [14.0192, 120.7353],
    'Luntal': [14.0314, 120.7129], 'Magahis': [14.0429, 120.7532], 'Malibu': [13.9956, 120.7058],
    'Mataywanac': [14.0394, 120.7393], 'Palincaro': [14.0096, 120.7045], 'Putol': [13.9930, 120.7281],
    'Rillo': [14.0163, 120.7258], 'Rizal': [14.0187, 120.7289], 'Sabang': [14.0576, 120.7080],
    'San Jose': [14.0236, 120.7820], 'Talon': [14.0179, 120.6986], 'Toong': [14.0492, 120.7909],
    'Tuyon-Tuyon': [14.0044, 120.7297]
};

function onBarangayChange(val) {
    if (TUY_COORDS[val]) {
        var pt = TUY_COORDS[val];
        document.getElementById('client_lat').value = pt[0];
        document.getElementById('client_lng').value = pt[1];
        var ind = document.getElementById('loc-indicator');
        var indText = document.getElementById('loc-indicator-text');
        if (ind && indText) {
            indText.textContent = 'Coordinates mapped to Barangay ' + val + ' (' + pt[0] + ', ' + pt[1] + ')';
            ind.style.display = 'flex';
        }
    }
    checkTravelBuffer();
    updateAgreementSummary();
}

function openBookModal() {
    document.getElementById('book-modal').style.display = 'flex';
    document.getElementById('book-msg').style.display = 'none';
    document.getElementById('schedule-warning').style.display = 'none';
    document.getElementById('book-submit-btn').disabled = false;
    // Set default coordinates if barangay selected
    var bVal = document.getElementById('barangay')?.value;
    if (bVal && TUY_COORDS[bVal]) {
        document.getElementById('client_lat').value = TUY_COORDS[bVal][0];
        document.getElementById('client_lng').value = TUY_COORDS[bVal][1];
    }
}

function closeBookModal() {
    document.getElementById('book-modal').style.display = 'none';
}

var scheduleCheckTimer = null;
function checkTravelBuffer() {
    clearTimeout(scheduleCheckTimer);
    var dt = document.getElementById('scheduled_at').value;
    var brgy = document.getElementById('barangay').value;
    var lat = document.getElementById('client_lat')?.value || '';
    var lng = document.getElementById('client_lng')?.value || '';
    var warning = document.getElementById('schedule-warning');
    var warningText = document.getElementById('schedule-warning-text');
    var btn = document.getElementById('book-submit-btn');

    if (!dt || !brgy) return;

    scheduleCheckTimer = setTimeout(function() {
        fetch('/client/workers/{{ $worker->id }}/check-schedule?scheduled_at=' + encodeURIComponent(dt) + '&barangay=' + encodeURIComponent(brgy) + '&latitude=' + lat + '&longitude=' + lng)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.status === 'conflict') {
                    warningText.innerHTML = '<strong>Schedule/Travel Conflict:</strong> ' + res.message +
                        (res.recommended_time ? ' <button type="button" class="btn btn-sm btn-solid" style="margin-left:6px;padding:3px 8px;font-size:.75rem;" onclick="applyRecommendedTime(\'' + res.recommended_time + '\')">Use ' + res.recommended_time + '</button>' : '');
                    warning.style.display = 'flex';
                    warning.style.background = '#fef2f2';
                    warning.style.borderColor = '#fca5a5';
                    warning.style.color = '#991b1b';
                    btn.disabled = true;
                } else if (res.status === 'out_of_radius') {
                    warningText.innerHTML = '<strong>Outside Worker Radius:</strong> ' + res.message;
                    warning.style.display = 'flex';
                    warning.style.background = '#fef2f2';
                    warning.style.borderColor = '#fca5a5';
                    warning.style.color = '#991b1b';
                    btn.disabled = true;
                } else if (res.status === 'tight') {
                    warningText.innerHTML = '<strong>Tight Buffer:</strong> ' + res.message + ' (Worker has an adjacent job; travel buffer may be snug)';
                    warning.style.display = 'flex';
                    warning.style.background = '#fffbeb';
                    warning.style.borderColor = '#fde68a';
                    warning.style.color = '#92400e';
                    btn.disabled = false;
                }
            })
            .catch(function() {});
    }, 350);
}

function applyRecommendedTime(recTime) {
    // recTime is e.g. "11:30 AM" or "3:00 PM"
    var input = document.getElementById('scheduled_at');
    if (!input.value) return;
    var parts = recTime.match(/(\d+):(\d+)\s*(AM|PM)/i);
    if (!parts) return;
    var hours = parseInt(parts[1], 10);
    var mins = parts[2];
    var ampm = parts[3].toUpperCase();
    if (ampm === 'PM' && hours < 12) hours += 12;
    if (ampm === 'AM' && hours === 12) hours = 0;

    var cur = new Date(input.value);
    var y = cur.getFullYear();
    var m = String(cur.getMonth() + 1).padStart(2, '0');
    var d = String(cur.getDate()).padStart(2, '0');
    var h = String(hours).padStart(2, '0');
    input.value = y + '-' + m + '-' + d + 'T' + h + ':' + mins;

    validateSchedule();
    updateAgreementSummary();
}

function validateSchedule() {
    const input = document.getElementById('scheduled_at');
    const warning = document.getElementById('schedule-warning');
    const btn = document.getElementById('book-submit-btn');
    const warningText = document.getElementById('schedule-warning-text');

    // Reset default styling
    warning.style.background = '#fff3cd';
    warning.style.borderColor = '#ffc107';
    warning.style.color = '#856404';

    if (!input.value || WORKER_AVAILABILITY.length === 0) {
        warningText.textContent = 'This worker hasn\u2019t set their availability yet. Booking is currently unavailable.';
        warning.style.display = 'flex';
        btn.disabled = true;
        return;
    }

    const date = new Date(input.value);
    const dayName = DAY_MAP[date.getDay()];
    const timeStr = String(date.getHours()).padStart(2,'0') + ':' + String(date.getMinutes()).padStart(2,'0');

    const slot = WORKER_AVAILABILITY.find(function(a) {
        return a.day === dayName && a.active;
    });

    if (!slot) {
        warningText.textContent = dayName + ' is not in this worker\u2019s available days. The worker may not accept this schedule.';
        warning.style.display = 'flex';
        btn.disabled = true;
        return;
    }

    if (timeStr < slot.start || timeStr >= slot.end) {
        warningText.textContent = 'The selected time (' + timeStr.slice(0,5) + ') is outside the worker\u2019s available hours (' + slot.start.slice(0,5) + '\u2013' + slot.end.slice(0,5) + '). The worker may not accept this schedule.';
        warning.style.display = 'flex';
        btn.disabled = true;
        return;
    }

    warning.style.display = 'none';
    btn.disabled = false;

    // Also check transit buffer with backend
    checkTravelBuffer();
}

function recalculatePriceEstimate() {
    updateAgreementSummary();
}

function updateAgreementSummary() {
    const select = document.getElementById('service-select');
    const svcHidden = document.getElementById('service_category_hidden');
    let svcName = '—';
    let basePrice = 0;

    if (select && select.value) {
        const opt = select.options[select.selectedIndex];
        svcName = opt.textContent.split(' - ')[0].trim();
        const svcs = JSON.parse(document.getElementById('worker-services-json')?.value || '[]');
        const found = svcs.find(s => String(s.id) === String(select.value));
        if (found) {
            basePrice = found.price;
            if (svcHidden) svcHidden.value = found.category;
        }
    } else {
        const fallback = document.querySelector('[name="service_category"]');
        if (fallback) svcName = fallback.value || '—';
        basePrice = {{ (float) ($workerProfile->hourly_rate ?? 350) }};
    }

    const pricingType = document.getElementById('pricing_type')?.value || 'fixed';
    const hours = parseFloat(document.getElementById('estimated_duration_hours')?.value || '2');
    const complexity = document.getElementById('complexity_level')?.value || 'standard';
    const multiplier = complexity === 'high_hazard' ? 1.5 : (complexity === 'moderate' ? 1.2 : 1.0);

    let laborPrice = basePrice;
    if (pricingType === 'hourly') {
        const hourlyRate = {{ (float) ($workerProfile->hourly_rate ?? 350) }};
        laborPrice = hourlyRate * hours;
    }

    const finalEstimate = Math.round(laborPrice * multiplier);

    const dt   = document.querySelector('[name="scheduled_at"]')?.value || '—';
    const addr = [document.querySelector('[name="house_no"]')?.value, document.querySelector('[name="street"]')?.value, document.querySelector('[name="barangay"]')?.value].filter(Boolean).join(', ') || '—';
    const pr   = document.querySelector('[name="price"]')?.value;
    document.getElementById('agree-service').textContent  = svcName;
    document.getElementById('agree-date').textContent     = dt ? new Date(dt).toLocaleString('en-PH',{dateStyle:'long',timeStyle:'short'}) : '—';
    document.getElementById('agree-location').textContent = addr;
    document.getElementById('agree-price').innerHTML      = '\u20B1' + Number(finalEstimate).toLocaleString() +
        ' <small style="font-weight:normal;color:#64748b;">(' + (pricingType === 'hourly' ? hours + ' hrs @ ₱' + {{ (float) ($workerProfile->hourly_rate ?? 350) }} + '/hr' : 'Fixed Task') + (multiplier > 1.0 ? ' &times; ' + multiplier + 'x diff.' : '') + ')</small>';
}

function openShareModal() {
    document.getElementById('share-modal').style.display = 'flex';
}

function closeShareModal() {
    document.getElementById('share-modal').style.display = 'none';
}

function copyShareUrl() {
    const input = document.getElementById('shareProfileUrl');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
    const btn = document.getElementById('copyShareBtn');
    btn.innerHTML = '<i class="fa-solid fa-check" style="color:#22c55e;"></i>';
    setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 2000);
}

function onServiceChange() {
    updateAgreementSummary();
}

function updateNotesCounter() {
    const textarea = document.getElementById('notes');
    const counter = document.querySelector('.notes-counter');
    if (textarea && counter) {
        counter.textContent = textarea.value.length + ' / 2000';
    }
}

function submitBooking(e) {
    e.preventDefault();
    if (!document.getElementById('agree-terms').checked) {
        alert('Please agree to the Service Agreement before submitting.');
        return;
    }
    const form = e.target;
    const btn = document.getElementById('book-submit-btn');
    const msg = document.getElementById('book-msg');
    btn.disabled = true;
    btn.textContent = 'Sending…';
    msg.style.display = 'none';

    const formData = new FormData(form);
    const data = {};
    formData.forEach((v, k) => data[k] = v);

    fetch('{{ route('client.bookings.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            msg.style.display = 'block';
            msg.className = 'alert alert-success';
            msg.innerHTML = 'Booking request sent! <a href="' + res.redirect + '" style="text-decoration:underline;">View my bookings</a>';
            btn.textContent = 'Sent!';
            setTimeout(function() {
                closeBookModal();
                if (res.redirect) window.location.href = res.redirect;
            }, 1500);
        } else {
            if (res.conflict && res.recommended_time) {
                msg.style.display = 'block';
                msg.className = 'alert alert-error';
                msg.innerHTML = '<div>' + res.message + '</div>' +
                    '<div style="margin-top:8px;"><button type="button" class="btn btn-sm btn-solid" onclick="applyRecommendedTime(\'' + res.recommended_time + '\')"><i class="fa-regular fa-clock"></i> Switch to ' + res.recommended_time + '</button></div>';
                btn.disabled = false;
                btn.textContent = 'Send Request';
                return;
            }
            throw new Error(res.message || 'Something went wrong');
        }
    })
    .catch(function(err) {
        msg.style.display = 'block';
        msg.className = 'alert alert-error';
        msg.textContent = err.message;
        btn.disabled = false;
        btn.textContent = 'Send Request';
    });
}

function openLightbox(url) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('lightbox').style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeLightbox();
});
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('scheduled_at').addEventListener('change', validateSchedule);
    document.getElementById('scheduled_at').addEventListener('input', validateSchedule);

    document.querySelector('#book-form').addEventListener('input', updateAgreementSummary);
    document.querySelector('#book-form').addEventListener('change', updateAgreementSummary);

    const notesInput = document.getElementById('notes');
    if (notesInput) {
        notesInput.addEventListener('input', updateNotesCounter);
        updateNotesCounter();
    }
});
</script>
@endpush

@push('styles')
<style>
.worker-profile-layout {
    display: flex; gap: 24px; align-items: flex-start;
}
.worker-profile-layout .card-panel {
    padding: 18px 22px;
}
.worker-profile-layout .card-panel-header {
    margin: -18px -22px 16px;
    padding: 18px 22px;
}
@media (max-width: 768px) {
    .worker-profile-layout { flex-direction: column; }
    .worker-profile-layout > .card-panel { flex: none !important; width: 100%; }
}

/* TABS */
.tabs-card{position:relative}
.tabs-bar{display:flex;position:relative;border-bottom:2px solid var(--g1);margin-bottom:16px}
.tabs-bar label{flex:1;padding:10px 0;text-align:center;cursor:pointer;font-weight:600;font-size:.9rem;color:var(--g4);transition:color .2s;position:relative;z-index:1}
.tabs-card input[type="radio"]{display:none}
#pt-posts:checked~.tabs-bar label[for="pt-posts"],
#pt-reviews:checked~.tabs-bar label[for="pt-reviews"]{color:var(--b6)}
.tabs-slider{position:absolute;bottom:-2px;left:0;width:50%;height:3px;background:var(--b6);transition:left .3s ease;border-radius:2px}
#pt-reviews:checked~.tabs-bar .tabs-slider{left:50%}
.tab-content{display:none}
#pt-posts:checked~.tab-content#tc-posts,
#pt-reviews:checked~.tab-content#tc-reviews{display:block}
.tab-empty{text-align:center;padding:40px 20px;color:var(--g4)}
.tab-empty i{font-size:2.5rem;display:block;margin-bottom:12px}
.tab-empty p{font-size:.9rem}
.works-item{position:relative;border-radius:10px;overflow:hidden;background:var(--g0);border:1px solid var(--g1);transition:all .2s}
.works-item:hover{border-color:var(--b4);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.works-item .thumb{width:100%;aspect-ratio:1;background-size:cover;background-position:center;display:flex;align-items:center;justify-content:center;color:var(--g4);font-size:1.3rem}
.works-caption{padding:6px 10px 10px;font-size:.8rem;color:var(--g6);line-height:1.4}
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.45); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.modal-box {
    background: #fff; border-radius: 14px; width: 90%; max-width: 520px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: modalIn .2s ease;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px 0; font-weight: 600; font-size: 1.05rem;
}
.modal-close {
    background: none; border: none; font-size: 1.5rem; cursor: pointer;
    color: var(--g4); line-height: 1;
}
.modal-close:hover { color: var(--g8); }
.modal-body { padding: 16px 22px; max-height: 60vh; overflow-y: auto; }
#book-form .form-control { width: 100%; box-sizing: border-box; }
#book-form .notes-textarea-wrap { position: relative; background: #f8fafc; border: 1px solid var(--g1); border-radius: 8px; padding: 10px 14px; }
#book-form .notes-textarea-wrap textarea { border: none; background: transparent; padding-bottom: 24px; resize: vertical; min-height: 80px; }
.notes-counter { position: absolute; bottom: 8px; right: 10px; font-size: .8rem; color: var(--g4); pointer-events: none; }
.modal-footer {
    display: flex; gap: 10px; justify-content: flex-end;
    padding: 0 22px 18px;
}

/* ── Review photo ── */
.review-photo-wrap {
    position: relative;
    display: inline-block;
    margin-top: 6px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    transition: box-shadow .2s, transform .2s;
    cursor: pointer;
}
.review-photo-wrap:hover {
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
    transform: scale(1.02);
}
.review-photo-wrap img {
    display: block;
    max-width: 200px;
    width: 100%;
    height: auto;
    border-radius: 10px;
}
.review-photo-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.35);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: .78rem;
    font-weight: 500;
    opacity: 0;
    transition: opacity .2s;
    border-radius: 10px;
    letter-spacing: .02em;
}
.review-photo-wrap:hover .review-photo-overlay {
    opacity: 1;
}

/* ── Lightbox ── */
.lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.8);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    animation: lbFade .2s ease;
}
@keyframes lbFade {
    from { opacity: 0; }
    to { opacity: 1; }
}
.lightbox-overlay img {
    max-width: 90vw;
    max-height: 85vh;
    border-radius: 8px;
    box-shadow: 0 8px 40px rgba(0,0,0,.5);
    animation: lbZoom .25s ease;
}
@keyframes lbZoom {
    from { transform: scale(.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.lightbox-close {
    position: fixed;
    top: 20px;
    right: 24px;
    background: rgba(255,255,255,.12);
    border: none;
    color: #fff;
    font-size: 1.5rem;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
}
.lightbox-close:hover {
    background: rgba(255,255,255,.25);
}

/* ── Agreement ── */
.agreement-box {
    margin-top: 14px;
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid var(--g1);
    border-radius: 8px;
}
.agreement-title {
    font-size: .82rem;
    font-weight: 600;
    color: var(--b8);
    margin: 0 0 6px;
}
.agreement-summary {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: .8rem;
    color: var(--g6);
    margin-bottom: 10px;
}
.agreement-check {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: .78rem;
    color: var(--g6);
    line-height: 1.45;
    cursor: pointer;
}
.agreement-check input[type="checkbox"] {
    margin-top: 2px;
    flex-shrink: 0;
}
.agreement-check a {
    color: var(--b6);
    text-decoration: underline;
}
</style>
@endpush
