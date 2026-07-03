@extends('layouts.client')

@section('title', 'Reviews')
@section('page_title', 'Reviews')

@section('content')

@if(!empty($reviews['pending']))
    <div class="section-header">
        <h2 class="section-title">Pending Reviews</h2>
    </div>

    @foreach($reviews['pending'] as $pi => $pending)
        <div class="review-card pending" data-pending-index="{{ $pi }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div>
                    <div class="eyebrow">{{ $pending['service'] }}</div>
                    <h3 style="font-size:1rem;font-weight:700;color:var(--b9);">{{ $pending['worker'] }}</h3>
                    <p style="font-size:.82rem;color:var(--g4);margin-top:4px;">Completed {{ $pending['date'] }}</p>
                </div>
            </div>
            <div class="star-picker" data-pending="{{ $pi }}">
                @for($s = 1; $s <= 5; $s++)
                    <i class="fa-regular fa-star" data-star="{{ $s }}" aria-hidden="true"></i>
                @endfor
            </div>
            <textarea class="review-textarea" placeholder="Share your experience — it helps the community find trusted workers."></textarea>
            <button type="button" class="btn btn-solid" onclick="submitReview({{ $pi }})">Submit Review</button>
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
                @for($s = 1; $s <= 5; $s++)
                    <i class="fa-solid fa-star" style="{{ $s <= $review['rating'] ? '' : 'opacity:.25;' }}" aria-hidden="true"></i>
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

@push('scripts')
<script>
const pendingReviews = @json($reviews['pending']);

// Star picker interaction
document.querySelectorAll('.star-picker').forEach(function (picker) {
    const stars = picker.querySelectorAll('.fa-star');
    stars.forEach(function (star) {
        star.addEventListener('click', function () {
            const val = parseInt(this.dataset.star);
            stars.forEach(function (s, i) {
                s.className = i < val ? 'fa-solid fa-star' : 'fa-regular fa-star';
            });
            picker.dataset.rating = val;
        });
    });
});

function submitReview(index) {
    const pending = pendingReviews[index];
    if (!pending) return;

    const card = document.querySelector(`.review-card[data-pending-index="${index}"]`);
    if (!card) return;

    const picker = card.querySelector('.star-picker');
    const rating = parseInt(picker?.dataset?.rating || '0');
    if (!rating) {
        alert('Please select a rating.');
        return;
    }

    const comment = card.querySelector('.review-textarea')?.value?.trim() || '';

    const btn = card.querySelector('.btn.btn-solid');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    fetch('{{ route('client.bookings.review', '__BOOKING__') }}'.replace('__BOOKING__', pending.booking_id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ rating: rating, comment: comment }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to submit review.');
        }
    })
    .catch(() => alert('Something went wrong.'))
    .finally(function () {
        btn.disabled = false;
        btn.textContent = 'Submit Review';
    });
}
</script>
@endpush
