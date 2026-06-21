@extends('layouts.client')

@section('title', 'Reviews')
@section('page_title', 'Reviews')

@section('content')

@if(!empty($reviews['pending']))
    <div class="section-header">
        <h2 class="section-title">Pending Reviews</h2>
    </div>

    @foreach($reviews['pending'] as $pending)
        <div class="review-card pending">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div>
                    <div class="eyebrow">{{ $pending['service'] }}</div>
                    <h3 style="font-size:1rem;font-weight:700;color:var(--b9);">{{ $pending['worker'] }}</h3>
                    <p style="font-size:.82rem;color:var(--g4);margin-top:4px;">Completed {{ $pending['date'] }}</p>
                </div>
            </div>
            <div class="star-picker" aria-label="Rate this worker">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fa-regular fa-star" aria-hidden="true"></i>
                @endfor
            </div>
            <textarea class="review-textarea" placeholder="Share your experience — it helps the community find trusted workers."></textarea>
            <button type="button" class="btn btn-solid">Submit Review</button>
        </div>
    @endforeach
@endif

<div class="section-header" style="margin-top:28px;">
    <h2 class="section-title">Your Past Reviews</h2>
</div>

@if(!empty($reviews['past']))
    @foreach($reviews['past'] as $review)
        <div class="review-card">
            <div class="eyebrow">{{ $review['service'] }}</div>
            <h3 style="font-size:1rem;font-weight:700;color:var(--b9);">{{ $review['worker'] }}</h3>
            <div class="review-stars">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star" style="{{ $i <= $review['rating'] ? '' : 'opacity:.25;' }}" aria-hidden="true"></i>
                @endfor
            </div>
            <p style="font-size:.875rem;color:var(--g7);line-height:1.6;">{{ $review['comment'] }}</p>
            <p style="font-size:.78rem;color:var(--g4);margin-top:8px;">{{ $review['date'] }}</p>
        </div>
    @endforeach
@else
    <div class="card-panel">
        <div class="empty-state">
            <i class="fa-regular fa-star" aria-hidden="true"></i>
            <h3>No reviews yet</h3>
            <p>After a job is done, you can rate your worker here.</p>
        </div>
    </div>
@endif

@endsection
