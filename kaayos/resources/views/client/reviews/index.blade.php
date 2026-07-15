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
            <div class="review-photo-upload">
                <label class="photo-upload-label">
                    <i class="fa-regular fa-camera" aria-hidden="true"></i>
                    <span>Add photo</span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="review-photo-input" data-pending="{{ $pi }}">
                </label>
                <div class="review-photo-preview" id="reviewPhotoPreview{{ $pi }}" style="display:none;">
                    <img src="" alt="Preview">
                    <button type="button" class="photo-remove" data-pending="{{ $pi }}">&times;</button>
                </div>
            </div>
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
            @if($review['photo_url'])
                <div class="review-photo-wrap" onclick="openLightbox('{{ $review['photo_url'] }}')">
                    <img src="{{ $review['photo_url'] }}" alt="Review photo">
                    <div class="review-photo-overlay"><i class="fa-solid fa-expand" aria-hidden="true"></i> View photo</div>
                </div>
            @endif
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

{{-- Lightbox --}}
<div id="lightbox" class="lightbox-overlay" style="display:none;" onclick="closeLightbox()">
    <button type="button" class="lightbox-close" onclick="closeLightbox()">&times;</button>
    <img id="lightboxImg" src="" alt="Photo">
</div>

@endsection

@push('styles')
<style>
.review-photo-upload {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
}

.photo-upload-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border: 1px dashed var(--g3);
    border-radius: 8px;
    font-size: .82rem;
    color: var(--g5);
    cursor: pointer;
    transition: border-color .12s, color .12s;
}
.photo-upload-label:hover {
    border-color: var(--b5);
    color: var(--b7);
}
.photo-upload-label input[type="file"] {
    display: none;
}

.review-photo-preview {
    position: relative;
    display: inline-flex;
}
.review-photo-preview img {
    height: 60px;
    width: auto;
    border-radius: 6px;
    object-fit: cover;
    border: 1px solid var(--g1);
}
.photo-remove {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #dc2626;
    color: #fff;
    border: none;
    font-size: .8rem;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.review-photo-wrap {
    position: relative;
    display: inline-block;
    margin-top: 8px;
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
    max-width: 220px;
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
    font-size: .8rem;
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
</style>
@endpush

@push('scripts')
<script>
const pendingReviews = @json($reviews['pending']);

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

// Photo preview
document.querySelectorAll('.review-photo-input').forEach(function (input) {
    input.addEventListener('change', function () {
        const pi = this.dataset.pending;
        const preview = document.getElementById('reviewPhotoPreview' + pi);
        const file = this.files[0];
        if (!file) { preview.style.display = 'none'; return; }
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'inline-flex';
        };
        reader.readAsDataURL(file);
    });
});

document.querySelectorAll('.photo-remove').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const pi = this.dataset.pending;
        const input = document.querySelector('.review-photo-input[data-pending="' + pi + '"]');
        const preview = document.getElementById('reviewPhotoPreview' + pi);
        if (input) input.value = '';
        preview.style.display = 'none';
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
    const photoInput = card.querySelector('.review-photo-input');
    const photoFile = photoInput?.files?.[0] || null;

    const btn = card.querySelector('.btn.btn-solid');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    const formData = new FormData();
    formData.append('rating', rating);
    formData.append('comment', comment);
    if (photoFile) formData.append('photo', photoFile);

    fetch('{{ route('client.bookings.review', '__BOOKING__') }}'.replace('__BOOKING__', pending.booking_id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
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
