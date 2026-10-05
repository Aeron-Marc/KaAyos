@extends('layouts.client')

@section('title', 'Reviews')
@section('page_title', 'Reviews')

@section('skeleton')
    <div class="skeleton skeleton-title" style="width:200px;margin-bottom:20px;"></div>
    <div class="sp-panel">
        <div style="display:flex;gap:16px;margin-bottom:16px;">
            <div class="skeleton skeleton-avatar"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:40%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:30%;"></div>
                <div style="display:flex;gap:4px;margin-top:8px;">
                    <div class="skeleton" style="width:18px;height:18px;border-radius:4px;"></div>
                    <div class="skeleton" style="width:18px;height:18px;border-radius:4px;"></div>
                    <div class="skeleton" style="width:18px;height:18px;border-radius:4px;"></div>
                    <div class="skeleton" style="width:18px;height:18px;border-radius:4px;"></div>
                    <div class="skeleton" style="width:18px;height:18px;border-radius:4px;"></div>
                </div>
            </div>
        </div>
        <div class="skeleton skeleton-text"></div>
        <div class="skeleton skeleton-text-sm"></div>
    </div>
    <div class="sp-panel">
        <div class="skeleton skeleton-title" style="width:140px;margin-bottom:16px;"></div>
        <div style="display:flex;gap:16px;margin-bottom:16px;">
            <div class="skeleton skeleton-avatar"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:50%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:35%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:60%;"></div>
            </div>
        </div>
        <div style="display:flex;gap:16px;">
            <div class="skeleton skeleton-avatar"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:45%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:40%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:55%;"></div>
            </div>
        </div>
    </div>
@endsection

@section('content')

<div id="reviewPageData"
    data-submit-url-template="{{ route('client.bookings.review', '__BOOKING__') }}"
    data-csrf-token="{{ csrf_token() }}"></div>
<script id="pendingReviewsData" type="application/json">@json($reviews['pending'])</script>

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
                    <i class="fa-solid fa-camera" aria-hidden="true"></i>
                    <span>Add photo</span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="review-photo-input" data-pending="{{ $pi }}">
                </label>
                <div class="review-photo-preview" id="reviewPhotoPreview{{ $pi }}" style="display:none;">
                    <img src="" alt="Preview">
                    <button type="button" class="photo-remove" data-pending="{{ $pi }}">&times;</button>
                </div>
            </div>
            <label class="anon-toggle" for="anonToggle{{ $pi }}">
                <input type="checkbox" id="anonToggle{{ $pi }}" class="anon-checkbox" data-pending="{{ $pi }}">
                <span class="anon-switch" aria-hidden="true"></span>
                <span class="anon-text">
                    <strong>Post anonymously</strong>
                    <small>Your name will be hidden and shown as "Anonymous" on your worker's public profile.</small>
                </span>
            </label>
            <button type="button" class="btn btn-solid submit-review-btn" data-pending-index="{{ $pi }}">Submit Review</button>
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
                @if(!empty($review['is_anonymous']))
                    <span class="anon-badge"><i class="fa-solid fa-user-secret" aria-hidden="true"></i> Anonymous</span>
                @endif
            </div>
            @if($review['photo_url'])
                <div class="review-photo-wrap js-lightbox-trigger" data-photo-url="{{ $review['photo_url'] }}">
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

{{-- Pre-negative feedback: nudges the client to message the worker before submitting a 1-2 star review --}}
<div id="negativeFeedbackModal" class="modal-overlay" style="display:none;" onclick="closeNegativeFeedbackModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3><i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i> We're sorry it didn't go well</h3>
            <button type="button" class="modal-close" onclick="closeNegativeFeedbackModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="font-size:.9rem;color:var(--g7);line-height:1.6;margin-bottom:10px;">
                Before sharing your feedback, we encourage you to message <strong id="negativeFeedbackWorker">your worker</strong>
                directly — most issues can be resolved quickly when both parties talk it out.
            </p>
            <p style="font-size:.82rem;color:var(--g4);line-height:1.6;">
                Your rating and comment are saved and will still be there if you choose to continue.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeNegativeFeedbackModal()">Cancel</button>
            <a id="negativeFeedbackMessageBtn" href="#" class="btn btn-outline"><i class="fa-regular fa-comment"></i> Message Worker</a>
            <button type="button" class="btn btn-solid" id="negativeFeedbackContinueBtn">Continue &amp; Submit</button>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" class="lightbox-overlay" style="display:none;">
    <button type="button" class="lightbox-close" id="lightboxCloseBtn">&times;</button>
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
    margin-bottom: 14px;
}

.photo-upload-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: .875rem;
    font-weight: 600;
    color: #fff;
    background: var(--b6);
    cursor: pointer;
    white-space: nowrap;
    transition: all .18s;
}
.photo-upload-label:hover {
    background: var(--b7);
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

/* ── Anonymous review toggle ── */
.anon-toggle {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    margin-bottom: 14px;
    background: var(--off, #F7F8FA);
    border: 1px solid var(--g1, #E8ECF0);
    border-radius: 10px;
    cursor: pointer;
}
.anon-toggle input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.anon-switch {
    position: relative;
    width: 34px;
    height: 19px;
    flex-shrink: 0;
    margin-top: 2px;
    background: var(--g2, #D5DBE2);
    border-radius: 999px;
    transition: background .18s;
}
.anon-switch::after {
    content: "";
    position: absolute;
    top: 2px;
    left: 2px;
    width: 15px;
    height: 15px;
    background: #fff;
    border-radius: 50%;
    transition: transform .18s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.anon-toggle input:checked + .anon-switch {
    background: var(--b6, #1A6FC4);
}
.anon-toggle input:checked + .anon-switch::after {
    transform: translateX(15px);
}
.anon-text strong {
    display: block;
    font-size: .85rem;
    color: var(--g9, #1B2430);
}
.anon-text small {
    display: block;
    font-size: .75rem;
    color: var(--g4, #8C97A4);
    line-height: 1.4;
}
.anon-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
    padding: 2px 8px;
    font-size: .7rem;
    font-weight: 600;
    color: var(--b7, #185FA5);
    background: var(--b0, #E6F1FB);
    border-radius: 999px;
    vertical-align: middle;
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
@media(max-width:768px){
    .review-photo-upload{flex-wrap:wrap}
    .review-photo-wrap img{max-width:160px}
    .review-textarea{min-height:80px}
}
@media(max-width:480px){
    .review-photo-wrap img{max-width:120px}
    .lightbox-overlay img{max-width:95vw;max-height:80vh}
}
</style>
@endpush

@push('scripts')
<script>
const pageDataEl = document.getElementById('reviewPageData');
const pendingReviewsRaw = document.getElementById('pendingReviewsData');
const pendingReviews = pendingReviewsRaw ? JSON.parse(pendingReviewsRaw.textContent || '[]') : [];
const submitUrlTemplate = pageDataEl ? pageDataEl.dataset.submitUrlTemplate : '';
const csrfToken = pageDataEl ? pageDataEl.dataset.csrfToken : '';

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

const lightbox = document.getElementById('lightbox');
const lightboxImage = document.getElementById('lightboxImg');
const lightboxCloseBtn = document.getElementById('lightboxCloseBtn');

if (lightbox) {
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) closeLightbox();
    });
}
if (lightboxImage) {
    lightboxImage.addEventListener('click', function (e) {
        e.stopPropagation();
    });
}
if (lightboxCloseBtn) {
    lightboxCloseBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        closeLightbox();
    });
}

document.querySelectorAll('.js-lightbox-trigger').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
        const url = this.dataset.photoUrl || '';
        if (url) openLightbox(url);
    });
});

// Star picker interaction
document.querySelectorAll('.star-picker').forEach(function (picker) {
    const stars = picker.querySelectorAll('.fa-star');
    stars.forEach(function (star) {
        star.addEventListener('click', function () {
            const val = parseInt(this.dataset.star, 10);
            stars.forEach(function (s, i) {
                const isSelected = i < val;
                s.classList.toggle('fa-solid', isSelected);
                s.classList.toggle('fa-regular', !isSelected);
                s.classList.toggle('active', isSelected);
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

document.querySelectorAll('.submit-review-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const index = parseInt(this.dataset.pendingIndex, 10);
        submitReview(index);
    });
});

function submitReview(index) {
    const pending = pendingReviews[index];
    if (!pending) return;

    const card = document.querySelector('.review-card[data-pending-index="' + index + '"]');
    if (!card) return;

    const picker = card.querySelector('.star-picker');
    const rating = parseInt((picker && picker.dataset && picker.dataset.rating) ? picker.dataset.rating : '0', 10);
    if (!rating) {
        alert('Please select a rating.');
        return;
    }

    if (rating <= 2 && pending.worker_id) {
        openNegativeFeedbackModal(index, pending);
        return;
    }

    doSubmitReview(index);
}

function doSubmitReview(index) {
    const pending = pendingReviews[index];
    if (!pending) return;

    const card = document.querySelector('.review-card[data-pending-index="' + index + '"]');
    if (!card) return;

    const picker = card.querySelector('.star-picker');
    const rating = parseInt((picker && picker.dataset && picker.dataset.rating) ? picker.dataset.rating : '0', 10);
    if (!rating) {
        alert('Please select a rating.');
        return;
    }

    const reviewTextarea = card.querySelector('.review-textarea');
    const comment = reviewTextarea ? reviewTextarea.value.trim() : '';
    const photoInput = card.querySelector('.review-photo-input');
    const photoFile = photoInput && photoInput.files ? (photoInput.files[0] || null) : null;
    const anonInput = card.querySelector('.anon-checkbox');
    const isAnonymous = anonInput && anonInput.checked ? '1' : '0';

    const btn = card.querySelector('.btn.btn-solid');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    const formData = new FormData();
    formData.append('rating', rating);
    formData.append('comment', comment);
    formData.append('is_anonymous', isAnonymous);
    if (photoFile) formData.append('photo', photoFile);

    fetch(submitUrlTemplate.replace('__BOOKING__', pending.booking_id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
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

// ── Pre-negative feedback modal (1-2 stars) ──
let negativeFeedbackIndex = null;

function openNegativeFeedbackModal(index, pending) {
    negativeFeedbackIndex = index;
    const modal = document.getElementById('negativeFeedbackModal');
    const workerName = document.getElementById('negativeFeedbackWorker');
    const messageBtn = document.getElementById('negativeFeedbackMessageBtn');

    if (workerName) workerName.textContent = pending.worker || 'your worker';
    if (messageBtn) {
        messageBtn.href = '{{ route('client.messages.start') }}?worker_id=' + pending.worker_id;
    }

    if (modal) modal.style.display = 'flex';
}

function closeNegativeFeedbackModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('negativeFeedbackModal');
    if (modal) modal.style.display = 'none';
    negativeFeedbackIndex = null;
}

document.addEventListener('DOMContentLoaded', function () {
    const continueBtn = document.getElementById('negativeFeedbackContinueBtn');
    if (continueBtn) {
        continueBtn.addEventListener('click', function () {
            const index = negativeFeedbackIndex;
            closeNegativeFeedbackModal();
            if (index !== null) doSubmitReview(index);
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNegativeFeedbackModal();
    });
});
</script>
@endpush
