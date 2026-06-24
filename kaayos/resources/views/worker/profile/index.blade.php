@extends('layouts.worker')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')

<div class="profile-grid">

    <aside>
        <div class="profile-sidebar-card">
            <div class="profile-big-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
            <h3>{{ auth()->user()->name ?? 'User' }}</h3>
            <p>Tuy, Batangas</p>
            <span class="profile-role-tag">Trabahador</span>

            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--g1);">
                <div style="display:flex;justify-content:center;gap:20px;">
                    <div>
                        <div style="font-size:1.3rem;font-weight:700;color:var(--b9);">4.8</div>
                        <div style="font-size:.72rem;color:var(--g4);">★ Rating</div>
                    </div>
                    <div>
                        <div style="font-size:1.3rem;font-weight:700;color:var(--b9);">47</div>
                        <div style="font-size:.72rem;color:var(--g4);">Jobs Done</div>
                    </div>
                    <div>
                        <div style="font-size:1.3rem;font-weight:700;color:var(--b9);">2</div>
                        <div style="font-size:.72rem;color:var(--g4);">Active</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div>

        <div class="form-section">
            <h3>About Me</h3>
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" class="review-textarea" placeholder="Tell clients about yourself, your experience, and the services you offer…">Experienced trabahador with over 5 years of hands-on work in plumbing, electrical repairs, and general home maintenance. I take pride in doing honest, quality work for every client.</textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Services Offered</h3>
            <div class="skill-tags" style="margin-bottom:12px;">
                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;">Plumbing</span>
                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;">Electrical</span>
                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;">Carpentry</span>
                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;">Painting</span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Primary Category</label>
                    <select id="category">
                        <option>Plumbing</option>
                        <option>Electrical</option>
                        <option>Cleaning</option>
                        <option>Carpentry</option>
                        <option selected>Painting</option>
                        <option>Aircon</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rate">Hourly Rate (₱)</label>
                    <input type="number" id="rate" value="400" min="0">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Availability</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="days">Available Days</label>
                    <select id="days">
                        <option>Monday — Friday</option>
                        <option selected>Monday — Saturday</option>
                        <option>All Week</option>
                        <option>Weekends Only</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hours">Preferred Hours</label>
                    <select id="hours">
                        <option>Morning (8 AM — 12 PM)</option>
                        <option selected>Full Day (8 AM — 5 PM)</option>
                        <option>Afternoon (1 PM — 5 PM)</option>
                        <option>Flexible</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Service Areas</h3>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;background:var(--b0);border-color:var(--b1);color:var(--b6);">
                    <i class="fa-solid fa-check" aria-hidden="true" style="margin-right:4px;"></i> Tuy, Batangas
                </span>
                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;background:var(--b0);border-color:var(--b1);color:var(--b6);">
                    <i class="fa-solid fa-check" aria-hidden="true" style="margin-right:4px;"></i> Nasugbu, Batangas
                </span>
                <span class="skill-tag" style="font-size:.82rem;padding:6px 14px;background:var(--b0);border-color:var(--b1);color:var(--b6);">
                    <i class="fa-solid fa-check" aria-hidden="true" style="margin-right:4px;"></i> Balayan, Batangas
                </span>
            </div>
            <div class="form-group">
                <input type="text" placeholder="Add a municipality…" style="width:100%;border:1.5px solid var(--g1);border-radius:var(--radius-sm);padding:10px 14px;font-family:inherit;font-size:.875rem;color:var(--g9);background:var(--off);outline:none;">
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="button" class="btn btn-solid">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
            </button>
            <button type="button" class="btn btn-ghost">Cancel</button>
        </div>

    </div>

</div>

@endsection
