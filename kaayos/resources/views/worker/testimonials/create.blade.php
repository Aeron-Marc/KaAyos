@extends('layouts.worker')

@section('title', 'Share Your Testimonial')
@section('page_title', 'Share Your Testimonial')

@section('skeleton')
    <div class="skeleton skeleton-title" style="width:260px;margin-bottom:20px;"></div>
    <div class="sp-panel">
        <div class="skeleton skeleton-text" style="width:60%;margin-bottom:12px;"></div>
        <div class="skeleton skeleton-text-sm" style="width:40%;margin-bottom:20px;"></div>
        <div style="display:flex;gap:6px;margin-bottom:16px;">
            <div class="skeleton" style="width:28px;height:28px;border-radius:6px;"></div>
            <div class="skeleton" style="width:28px;height:28px;border-radius:6px;"></div>
            <div class="skeleton" style="width:28px;height:28px;border-radius:6px;"></div>
            <div class="skeleton" style="width:28px;height:28px;border-radius:6px;"></div>
            <div class="skeleton" style="width:28px;height:28px;border-radius:6px;"></div>
        </div>
        <div class="skeleton skeleton-text"></div>
        <div class="skeleton skeleton-text"></div>
        <div class="skeleton skeleton-text-sm" style="width:50%;"></div>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="background:rgba(16,185,129,.1);color:#047857;padding:14px 20px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:.9rem;font-weight:500;">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="background:rgba(239,68,68,.1);color:#B91C1C;padding:14px 20px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:.9rem;font-weight:500;">
        <i class="fa-solid fa-circle-exclamation"></i>
        {{ $errors->first() }}
    </div>
@endif

@if($hasExisting)
    <div style="background:rgba(26,111,196,.06);border:1px solid rgba(26,111,196,.15);border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-size:.88rem;color:#185FA5;">
        <i class="fa-solid fa-circle-info" style="font-size:1.1rem;flex-shrink:0;"></i>
        <span>You have already submitted a testimonial. You can submit another one below.</span>
    </div>
@endif

<div class="testimonial-form-card">
    <div class="form-header">
        <h2 class="form-title"><i class="fa-solid fa-quote-left" style="color:var(--b6);margin-right:8px;"></i> Share Your Experience</h2>
        <p class="form-subtitle">Tell others about your experience with KaAyos. Your testimonial will appear on the landing page after admin approval.</p>
    </div>

    <form method="POST" action="{{ route('worker.testimonials.store') }}" id="testimonialForm">
        @csrf

        <div class="form-group">
            <label class="form-label">Your Rating</label>
            <div class="star-picker" id="starPicker">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fa-regular fa-star" data-star="{{ $i }}" role="button" tabindex="0" aria-label="Rate {{ $i }} stars"></i>
                @endfor
                <span class="rating-text" id="ratingText"></span>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', '') }}">
            @error('rating')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="content">Your Testimonial</label>
            <textarea
                name="content"
                id="content"
                class="form-textarea"
                rows="5"
                maxlength="1000"
                placeholder="What did you like about KaAyos? How has it helped you as a worker?"
                required
            >{{ old('content') }}</textarea>
            <div class="char-count"><span id="charCount">0</span>/1000</div>
            @error('content')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-preview">
            <div class="preview-label"><i class="fa-solid fa-eye" style="margin-right:6px;"></i> Preview</div>
            <div class="testimonial-preview-card">
                <div class="stars" id="previewStars"></div>
                <p class="quote" id="previewQuote">Your testimonial will appear here...</p>
                <div class="author">
                    <div class="author-avatar">{{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) ?: strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
                    <div class="author-info">
                        <div class="name">{{ auth()->user()->name }}</div>
                        <div class="role">Trabahador, {{ auth()->user()->barangay ?? 'Tuy' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('worker.testimonials.index') }}" class="btn-cancel">My Testimonials</a>
            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="fa-solid fa-paper-plane"></i> Submit Testimonial
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .testimonial-form-card {
        background: #fff;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 3px 10px rgba(0,0,0,.06);
        border: 1px solid rgba(0,0,0,.05);
        max-width: 640px;
    }
    .form-header { margin-bottom: 28px; }
    .form-title { font-size: 1.2rem; font-weight: 700; color: var(--b9); margin: 0 0 6px 0; }
    .form-subtitle { font-size: .88rem; color: var(--g4); margin: 0; }
    .form-group { margin-bottom: 24px; }
    .form-label { display: block; font-size: .82rem; font-weight: 700; color: var(--g7); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }
    .star-picker { display: flex; align-items: center; gap: 4px; }
    .star-picker i { font-size: 1.6rem; color: #D1D5DB; cursor: pointer; transition: color .15s, transform .15s; padding: 2px; }
    .star-picker i:hover { transform: scale(1.15); }
    .star-picker i.active, .star-picker i.hover { color: #F59E0B; }
    .rating-text { margin-left: 10px; font-size: .85rem; color: var(--g4); font-weight: 500; }
    .form-textarea { width: 100%; padding: 14px 16px; border: 1.5px solid var(--g1); border-radius: 10px; font-size: .95rem; font-family: 'Inter', sans-serif; color: var(--g9); resize: vertical; min-height: 120px; outline: none; transition: border-color .18s; }
    .form-textarea:focus { border-color: var(--b4); box-shadow: 0 0 0 3px rgba(26,111,196,.08); }
    .char-count { text-align: right; font-size: .78rem; color: var(--g4); margin-top: 6px; }
    .error-text { color: #EF4444; font-size: .82rem; margin-top: 6px; }
    .form-preview { margin-bottom: 28px; }
    .preview-label { font-size: .78rem; font-weight: 600; color: var(--g4); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 12px; }
    .testimonial-preview-card { background: var(--off); border-radius: 12px; padding: 20px; border: 1px solid var(--g1); }
    .testimonial-preview-card .stars { margin-bottom: 10px; }
    .testimonial-preview-card .stars i { color: #F59E0B; font-size: .95rem; margin-right: 1px; }
    .testimonial-preview-card .quote { font-size: .92rem; color: var(--g7); font-style: italic; line-height: 1.5; margin: 0 0 14px 0; }
    .testimonial-preview-card .author { display: flex; align-items: center; gap: 10px; }
    .testimonial-preview-card .author-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--b6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: .78rem; font-weight: 700; }
    .testimonial-preview-card .author-info .name { font-size: .85rem; font-weight: 600; color: var(--b9); }
    .testimonial-preview-card .author-info .role { font-size: .75rem; color: var(--g4); }
    .form-actions { display: flex; gap: 12px; justify-content: flex-end; }
    .btn-cancel { padding: 10px 18px; border-radius: 8px; font-size: .9rem; font-weight: 600; text-decoration: none; color: var(--g7); border: 1.5px solid var(--g1); background: #fff; transition: all .18s; }
    .btn-cancel:hover { border-color: var(--b4); color: var(--b6); }
    .btn-submit { padding: 10px 22px; border-radius: 8px; font-size: .9rem; font-weight: 600; border: none; background: var(--b6); color: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all .18s; }
    .btn-submit:hover { background: var(--b7); transform: translateY(-1px); }
</style>
@endpush

@push('scripts')
<script>
(function() {
    var ratingInput = document.getElementById('ratingInput');
    var ratingText = document.getElementById('ratingText');
    var previewStars = document.getElementById('previewStars');
    var previewQuote = document.getElementById('previewQuote');
    var content = document.getElementById('content');
    var charCount = document.getElementById('charCount');
    var currentRating = parseInt(ratingInput.value) || 0;

    var ratingLabels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

    function updateStars(rating) {
        var stars = document.querySelectorAll('#starPicker i');
        stars.forEach(function(star, idx) {
            if (idx < rating) {
                star.classList.add('active');
                star.classList.remove('fa-regular');
                star.classList.add('fa-solid');
            } else {
                star.classList.remove('active');
                star.classList.remove('fa-solid');
                star.classList.add('fa-regular');
            }
        });
        ratingText.textContent = rating > 0 ? ratingLabels[rating] : '';
        previewStars.innerHTML = '';
        for (var i = 0; i < rating; i++) {
            previewStars.innerHTML += '<i class="fa-solid fa-star"></i>';
        }
        for (var j = rating; j < 5; j++) {
            previewStars.innerHTML += '<i class="fa-regular fa-star" style="color:#D1D5DB;"></i>';
        }
    }

    updateStars(currentRating);

    document.querySelectorAll('#starPicker i').forEach(function(star) {
        star.addEventListener('click', function() {
            currentRating = parseInt(this.dataset.star);
            ratingInput.value = currentRating;
            updateStars(currentRating);
        });
        star.addEventListener('mouseenter', function() {
            var val = parseInt(this.dataset.star);
            document.querySelectorAll('#starPicker i').forEach(function(s, idx) {
                if (idx < val) s.classList.add('hover');
                else s.classList.remove('hover');
            });
        });
        star.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    document.getElementById('starPicker').addEventListener('mouseleave', function() {
        document.querySelectorAll('#starPicker i').forEach(function(s) { s.classList.remove('hover'); });
    });

    content.addEventListener('input', function() {
        var len = this.value.length;
        charCount.textContent = len;
        if (len > 0) {
            previewQuote.textContent = '"' + this.value + '"';
        } else {
            previewQuote.textContent = 'Your testimonial will appear here...';
        }
    });
})();
</script>
@endpush
@endsection
