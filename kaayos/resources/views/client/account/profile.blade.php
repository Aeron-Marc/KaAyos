@extends('layouts.client')

@section('title', 'Account')
@section('page_title', 'Account')

@section('content')

@php
    $user = auth()->user();
    $barangays = ['Acle','Bayudbud','Bolbok','Burgos','Dalima','Dao','Guinhawa','Lumbangan','Luna','Luntal','Magahis','Malibu','Mataywanac','Palincaro','Putol','Rillo','Rizal','Sabang','San Jose','Talon','Toong','Tuyon-Tuyon'];
    $notificationOptions = ['All updates','Bookings only','Messages only','None'];
    $languageOptions = ['Filipino','English'];
@endphp

{{-- Toast container --}}
<div id="toastContainer" class="toast-container" aria-live="polite"></div>

{{-- Profile grid: sidebar card + form sections --}}
<div class="profile-grid">

    {{-- Left: Profile sidebar card --}}
    <div class="profile-sidebar-card">
        <div class="profile-avatar-wrap">
            @if($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" alt="" class="profile-big-avatar" id="sidebarAvatar">
            @else
                <div class="profile-big-avatar" id="sidebarAvatar">{{ strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)) }}</div>
            @endif
        </div>
        <h3 id="sidebarName">{{ $user->name }}</h3>
        <p id="sidebarEmail">{{ $user->email }}</p>
        <span class="profile-role-tag">Client</span>
        <button type="button" class="btn btn-outline change-photo-btn" id="changePhotoBtn">
            Change photo
        </button>
        <form id="avatarForm" style="display:none;" enctype="multipart/form-data">
            @csrf
            <input type="file" name="avatar" id="avatarInput" accept="image/*">
        </form>
    </div>

    {{-- Right: Sections --}}
    <div>

        {{-- Personal Information --}}
        <div class="form-section" id="personalInfoSection">
            <h3 class="form-section-title">
                Personal Information
                <span class="section-unsaved-dot" id="personalUnsavedDot" style="display:none" title="Unsaved changes">●</span>
            </h3>

            <form id="personalInfoForm">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" value="{{ $user->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ $user->phone ?? '' }}" placeholder="09XXXXXXXXX">
                        <p class="field-error" id="phoneError" style="display:none"></p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="barangaySelect">Barangay</label>
                        <select id="barangaySelect" name="barangay">
                            <option value="">Select barangay...</option>
                            @foreach($barangays as $b)
                                <option value="{{ $b }}" {{ ($user->barangay == $b) ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-solid" id="personalSaveBtn">Save changes</button>
                    <button type="button" class="btn btn-ghost" id="personalDiscardBtn" disabled>Discard</button>
                </div>
            </form>
        </div>

        {{-- Email Address --}}
        <div class="form-section" id="emailSection">
            <h3 class="form-section-title">Email Address</h3>
            <div id="emailSuccessMsg" class="info-banner" style="display:none;margin-bottom:12px">
                <i class="fa-solid fa-circle-check"></i>
                <span id="emailSuccessText"></span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Current Email</label>
                    <p class="form-value" id="currentEmailDisplay">{{ $user->email }}</p>
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px">
                    <button type="button" class="btn btn-outline" id="changeEmailBtn">
                        <i class="fa-solid fa-pen"></i> Change Email
                    </button>
                </div>
            </div>
            <p style="font-size:.8rem;color:var(--g4);margin-top:8px">
                You can change your email once every 30 days. A verification code will be sent to confirm.
            </p>
        </div>

        {{-- Preferences --}}
        <div class="form-section" id="preferencesSection">
            <h3 class="form-section-title">
                Preferences
                <span class="section-unsaved-dot" id="prefsUnsavedDot" style="display:none" title="Unsaved changes">●</span>
            </h3>
            <form id="preferencesForm">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="emailNotificationsSelect">Email Notifications</label>
                        <select id="emailNotificationsSelect" name="emailNotifications">
                            @foreach($notificationOptions as $opt)
                                <option value="{{ $opt }}" {{ ($user->email_notifications == $opt) ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="languageSelect">Language</label>
                        <select id="languageSelect" name="language">
                            @foreach($languageOptions as $opt)
                                <option value="{{ $opt }}" {{ ($user->language == $opt) ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-solid" id="prefsSaveBtn">Save preferences</button>
                </div>
            </form>
        </div>

        {{-- Security --}}
        <div class="form-section">
            <h3 class="form-section-title">Security</h3>
            <p style="font-size:.85rem;color:var(--g5);margin-bottom:12px">
                An OTP will be sent to your email before your password is changed.
            </p>
            <button type="button" class="btn btn-outline" id="changePasswordBtn">
                <i class="fa-solid fa-key"></i> Change Password
            </button>
        </div>

        {{-- Danger Zone --}}
        <div class="form-section danger-zone">
            <h3 class="form-section-title danger-zone-title">Danger Zone</h3>
            <p class="danger-zone-text">
                Deleting your account is permanent. All bookings, reviews, and data will be removed
                and cannot be recovered.
            </p>
            <button type="button" class="btn btn-danger-outline" id="deleteAccountBtn">
                Delete account
            </button>
        </div>

    </div>
</div>

{{-- ── CHANGE EMAIL MODAL ── --}}
<div class="modal-overlay" id="emailModal" style="display:none" role="dialog" aria-modal="true" aria-labelledby="emailModalTitle">
    <div class="otp-modal" onclick="event.stopPropagation()">
        <div class="otp-modal-header">
            <div class="otp-modal-icon">
                <i class="fa-solid fa-envelope" id="emailModalIcon"></i>
            </div>
            <div id="emailModalTitleBlock">
                <h2 id="emailModalTitle">Change Email Address</h2>
                <p id="emailModalSubtitle">Current: <strong id="emailModalCurrentEmail">{{ $user->email }}</strong></p>
            </div>
        </div>

        {{-- Step 1: Form --}}
        <div id="emailFormStep">
            <div class="form-row">
                <div class="form-group">
                    <label for="emailNew">New Email</label>
                    <input type="email" id="emailNew" autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="emailConfirm">Confirm New Email</label>
                    <input type="email" id="emailConfirm" autocomplete="email">
                    <p class="field-error" id="emailMismatchError" style="display:none">Emails do not match.</p>
                </div>
            </div>
            <div class="form-group">
                <label for="emailCurrentPassword">Current Password</label>
                <input type="password" id="emailCurrentPassword" placeholder="Enter your current password">
            </div>
            <p class="field-error otp-error" id="emailSendError" role="alert" style="display:none"></p>
            <div class="otp-actions">
                <button type="button" class="btn btn-solid" id="emailSendBtn">Send verification code</button>
                <button type="button" class="btn btn-ghost" id="emailModalCloseBtn">Cancel</button>
            </div>
        </div>

        {{-- Step 2: OTP --}}
        <div id="emailOtpStep" style="display:none">
            <p style="margin-bottom:12px">
                Enter the 6-digit code sent to <strong id="emailOtpTarget"></strong>. It expires in 10 minutes.
            </p>
            <div class="otp-inputs" id="emailOtpInputs">
                @for($i=0;$i<6;$i++)
                    <input type="text" inputmode="numeric" maxlength="1" class="otp-input" data-index="{{ $i }}">
                @endfor
            </div>
            <p class="field-error otp-error" id="emailVerifyError" role="alert" style="display:none"></p>
            <div class="otp-actions">
                <button type="button" class="btn btn-solid" id="emailVerifyBtn">Verify &amp; change email</button>
                <button type="button" class="btn btn-ghost" id="emailBackToFormBtn">Back</button>
            </div>
        </div>
    </div>
</div>

{{-- ── CHANGE PASSWORD MODAL ── --}}
<div class="modal-overlay" id="passwordModal" style="display:none" role="dialog" aria-modal="true" aria-labelledby="passwordModalTitle">
    <div class="otp-modal" onclick="event.stopPropagation()">
        <div class="otp-modal-header">
            <div class="otp-modal-icon">
                <i class="fa-solid fa-key" id="passwordModalIcon"></i>
            </div>
            <div>
                <h2 id="passwordModalTitle">Change Password</h2>
                <p id="passwordModalSubtitle">An OTP will be sent to <strong>{{ $user->email }}</strong> before your password is changed.</p>
            </div>
        </div>

        {{-- Step 1: Send OTP --}}
        <div id="passwordFormStep">
            <div class="form-group">
                <label for="pwCurrent">Current Password</label>
                <input type="password" id="pwCurrent" placeholder="Enter your current password">
            </div>
            <p class="field-error otp-error" id="pwSendError" role="alert" style="display:none"></p>
            <div class="otp-actions">
                <button type="button" class="btn btn-solid" id="pwSendBtn">Send verification code</button>
                <button type="button" class="btn btn-ghost" id="passwordModalCloseBtn">Cancel</button>
            </div>
        </div>

        {{-- Step 2: OTP + new password --}}
        <div id="passwordOtpStep" style="display:none">
            <p style="margin-bottom:12px">
                Enter the 6-digit code sent to <strong>{{ $user->email }}</strong>. It expires in 10 minutes.
            </p>
            <div class="otp-inputs" id="pwOtpInputs">
                @for($i=0;$i<6;$i++)
                    <input type="text" inputmode="numeric" maxlength="1" class="otp-input" data-index="{{ $i }}">
                @endfor
            </div>

            <div class="otp-timer-row" style="margin-top:12px;text-align:center">
                <span class="otp-countdown" id="pwCountdown" style="font-size:.8rem;color:var(--g5)"></span>
                <button type="button" class="btn-link otp-resend" id="pwResendBtn" style="display:none;background:none;border:none;color:var(--b6);cursor:pointer;font-size:.85rem">Resend code</button>
            </div>

            <div class="form-group" style="margin-top:14px">
                <label for="pwNew">New Password</label>
                <input type="password" id="pwNew" placeholder="At least 8 characters">
            </div>
            <div class="form-group" style="margin-top:10px">
                <label for="pwConfirm">Confirm New Password</label>
                <input type="password" id="pwConfirm" placeholder="Re-enter new password">
            </div>

            <p class="field-error otp-error" id="pwVerifyError" role="alert" style="display:none"></p>
            <div class="otp-actions">
                <button type="button" class="btn btn-solid" id="pwVerifyBtn">Verify &amp; change password</button>
                <button type="button" class="btn btn-ghost" id="pwBackToFormBtn">Back</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var token = '{{ auth()->user()->createToken('account-page')->plainTextToken }}';

    function getHeaders(json) {
        var h = { Accept: 'application/json', Authorization: 'Bearer ' + token };
        if (json) h['Content-Type'] = 'application/json';
        return h;
    }

    // ── Toast ──────────────────────────────────────────────────────
    function showToast(type, message) {
        var icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation' };
        var div = document.createElement('div');
        div.className = 'toast toast-' + type;
        div.innerHTML = '<i class="fa-solid ' + icons[type] + '"></i><span>' + message + '</span>';
        document.getElementById('toastContainer').appendChild(div);
        setTimeout(function () { div.remove(); }, 3500);
    }

    // ── Personal info: track dirty + save ─────────────────────────
    var origFullName = document.getElementById('fullName').value;
    var origPhone = document.getElementById('phone').value;
    var origBarangay = document.getElementById('barangaySelect').value;

    function checkPersonalDirty() {
        var dirty = document.getElementById('fullName').value !== origFullName ||
                    document.getElementById('phone').value !== origPhone ||
                    document.getElementById('barangaySelect').value !== origBarangay;
        document.getElementById('personalUnsavedDot').style.display = dirty ? 'inline' : 'none';
        document.getElementById('personalDiscardBtn').disabled = !dirty;
    }

    document.getElementById('fullName').addEventListener('input', checkPersonalDirty);
    document.getElementById('phone').addEventListener('input', checkPersonalDirty);
    document.getElementById('barangaySelect').addEventListener('change', checkPersonalDirty);

    document.getElementById('personalDiscardBtn').addEventListener('click', function () {
        document.getElementById('fullName').value = origFullName;
        document.getElementById('phone').value = origPhone;
        document.getElementById('barangaySelect').value = origBarangay;
        document.getElementById('phoneError').style.display = 'none';
        checkPersonalDirty();
    });

    document.getElementById('personalInfoForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var phone = document.getElementById('phone').value;
        var phoneRegex = /^09\d{9}$/;
        if (phone && !phoneRegex.test(phone)) {
            var err = document.getElementById('phoneError');
            err.textContent = 'Enter a valid PH mobile number (09XXXXXXXXX).';
            err.style.display = 'block';
            return;
        }
        document.getElementById('phoneError').style.display = 'none';

        var btn = document.getElementById('personalSaveBtn');
        btn.disabled = true;
        btn.textContent = 'Saving…';

        var formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('fullName', document.getElementById('fullName').value);
        formData.append('phone', phone);
        formData.append('barangay', document.getElementById('barangaySelect').value);

        fetch('/api/profile', { method: 'POST', headers: getHeaders(), body: formData })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok) {
                    var msg = result.data.message || 'Failed to save.';
                    showToast('error', msg);
                    return;
                }
                var data = result.data;
                if (!data.message) throw new Error(data.message || 'Failed to save.');
                origFullName = data.fullName || origFullName;
                origPhone = data.phone || '';
                origBarangay = data.barangay || '';
                if (data.fullName) document.getElementById('sidebarName').textContent = data.fullName;
                if (data.email) document.getElementById('sidebarEmail').textContent = data.email;
                if (data.email) document.getElementById('currentEmailDisplay').textContent = data.email;
                checkPersonalDirty();
                showToast('success', data.message);
            })
            .catch(function (err) { showToast('error', err.message); })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = 'Save changes';
            });
    });

    // ── Avatar upload ─────────────────────────────────────────────
    document.getElementById('changePhotoBtn').addEventListener('click', function () {
        document.getElementById('avatarInput').click();
    });

    document.getElementById('avatarInput').addEventListener('change', function () {
        var file = this.files && this.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        var formData = new FormData();
        formData.append('avatar', file);

        fetch('/api/profile/avatar', { method: 'POST', headers: getHeaders(), body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.avatar_url) throw new Error(data.message || 'Upload failed.');
                var avatar = document.getElementById('sidebarAvatar');
                if (avatar.tagName === 'IMG') {
                    avatar.src = data.avatar_url;
                } else {
                    var img = document.createElement('img');
                    img.src = data.avatar_url;
                    img.className = 'profile-big-avatar';
                    img.id = 'sidebarAvatar';
                    avatar.replaceWith(img);
                }
                showToast('success', data.message);
            })
            .catch(function (err) { showToast('error', err.message); });
    });

    // ── Preferences: track dirty + save ──────────────────────────────
    var origNotif = document.getElementById('emailNotificationsSelect').value;
    var origLang = document.getElementById('languageSelect').value;

    function checkPrefsDirty() {
        var dirty = document.getElementById('emailNotificationsSelect').value !== origNotif ||
                    document.getElementById('languageSelect').value !== origLang;
        document.getElementById('prefsUnsavedDot').style.display = dirty ? 'inline' : 'none';
    }

    document.getElementById('emailNotificationsSelect').addEventListener('change', checkPrefsDirty);
    document.getElementById('languageSelect').addEventListener('change', checkPrefsDirty);

    document.getElementById('preferencesForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('prefsSaveBtn');
        btn.disabled = true;
        btn.textContent = 'Saving…';

        var formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('emailNotifications', document.getElementById('emailNotificationsSelect').value);
        formData.append('language', document.getElementById('languageSelect').value);

        fetch('/api/preferences', { method: 'POST', headers: getHeaders(), body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.message) throw new Error(data.message || 'Failed to save.');
                origNotif = document.getElementById('emailNotificationsSelect').value;
                origLang = document.getElementById('languageSelect').value;
                checkPrefsDirty();
                showToast('success', data.message);
            })
            .catch(function (err) { showToast('error', err.message); })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = 'Save preferences';
            });
    });

    // ── Delete account ─────────────────────────────────────────────
    document.getElementById('deleteAccountBtn').addEventListener('click', function () {
        showToast('success', 'Please contact support at support@kaayos.ph to delete your account.');
    });

    // ── OTP helpers ────────────────────────────────────────────────
    function setupOtpInputs(containerId, digits) {
        var inputs = document.querySelectorAll('#' + containerId + ' .otp-input');
        inputs.forEach(function (input, idx) {
            input.addEventListener('input', function () {
                var v = this.value.replace(/\D/g, '').slice(-1);
                this.value = v;
                digits[idx] = v;
                if (v && idx < 5) inputs[idx + 1].focus();
            });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !digits[idx] && idx > 0) {
                    inputs[idx - 1].focus();
                }
            });
            input.addEventListener('paste', function (e) {
                e.preventDefault();
                var pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                pasted.split('').forEach(function (ch, i) {
                    if (inputs[i]) { inputs[i].value = ch; digits[i] = ch; }
                });
                var focusIdx = Math.min(pasted.length, 5);
                if (inputs[focusIdx]) inputs[focusIdx].focus();
            });
        });
    }

    function getOtpDigits(containerId) {
        var digits = ['', '', '', '', '', ''];
        document.querySelectorAll('#' + containerId + ' .otp-input').forEach(function (input, i) {
            digits[i] = input.value.replace(/\D/g, '');
        });
        return digits.join('');
    }

    function clearOtpInputs(containerId) {
        document.querySelectorAll('#' + containerId + ' .otp-input').forEach(function (input) {
            input.value = '';
        });
    }

    // ── Email modal ────────────────────────────────────────────────
    var emailModal = document.getElementById('emailModal');
    var emailNewVal = '', emailConfirmVal = '', emailPwVal = '';
    var emailDigits = ['', '', '', '', '', ''];

    document.getElementById('changeEmailBtn').addEventListener('click', function () {
        emailModal.style.display = 'flex';
        document.getElementById('emailFormStep').style.display = 'block';
        document.getElementById('emailOtpStep').style.display = 'none';
        document.getElementById('emailNew').value = '';
        document.getElementById('emailConfirm').value = '';
        document.getElementById('emailCurrentPassword').value = '';
        document.getElementById('emailMismatchError').style.display = 'none';
        document.getElementById('emailSendError').style.display = 'none';
        clearOtpInputs('emailOtpInputs');
    });

    document.getElementById('emailModalCloseBtn').addEventListener('click', function () {
        emailModal.style.display = 'none';
    });

    emailModal.addEventListener('click', function (e) {
        if (e.target === emailModal) emailModal.style.display = 'none';
    });

    document.getElementById('emailBackToFormBtn').addEventListener('click', function () {
        document.getElementById('emailFormStep').style.display = 'block';
        document.getElementById('emailOtpStep').style.display = 'none';
        clearOtpInputs('emailOtpInputs');
    });

    document.getElementById('emailSendBtn').addEventListener('click', function () {
        var newEmail = document.getElementById('emailNew').value;
        var confirmEmail = document.getElementById('emailConfirm').value;
        var pw = document.getElementById('emailCurrentPassword').value;
        var mismatchErr = document.getElementById('emailMismatchError');
        var sendErr = document.getElementById('emailSendError');

        mismatchErr.style.display = 'none';
        sendErr.style.display = 'none';

        if (newEmail !== confirmEmail) {
            mismatchErr.style.display = 'block';
            return;
        }
        if (!newEmail || !pw) return;

        this.disabled = true;
        this.textContent = 'Sending…';

        fetch('/email-otp/send', {
            method: 'POST',
            headers: getHeaders(true),
            body: JSON.stringify({ new_email: newEmail, new_email_confirmation: confirmEmail, current_password: pw })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!r.ok) throw new Error(data.message || 'Failed to send code.');
            emailNewVal = newEmail;
            document.getElementById('emailOtpTarget').textContent = newEmail;
            document.getElementById('emailFormStep').style.display = 'none';
            document.getElementById('emailOtpStep').style.display = 'block';
            clearOtpInputs('emailOtpInputs');
            setupOtpInputs('emailOtpInputs', emailDigits);
            document.querySelector('#emailOtpInputs .otp-input')?.focus();
        })
        .catch(function (err) {
            sendErr.textContent = err.message;
            sendErr.style.display = 'block';
        })
        .finally(function () {
            document.getElementById('emailSendBtn').disabled = false;
            document.getElementById('emailSendBtn').textContent = 'Send verification code';
        });
    });

    document.getElementById('emailVerifyBtn').addEventListener('click', function () {
        var otp = getOtpDigits('emailOtpInputs');
        if (otp.length !== 6) return;

        this.disabled = true;
        this.textContent = 'Verifying…';
        document.getElementById('emailVerifyError').style.display = 'none';

        fetch('/email-otp/verify', {
            method: 'POST',
            headers: getHeaders(true),
            body: JSON.stringify({ otp: otp })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!r.ok) throw new Error(data.message || 'Verification failed.');
            emailModal.style.display = 'none';
            document.getElementById('currentEmailDisplay').textContent = emailNewVal;
            document.getElementById('sidebarEmail').textContent = emailNewVal;
            var successBanner = document.getElementById('emailSuccessMsg');
            document.getElementById('emailSuccessText').textContent = data.message;
            successBanner.style.display = 'flex';
            showToast('success', data.message);
        })
        .catch(function (err) {
            document.getElementById('emailVerifyError').textContent = err.message;
            document.getElementById('emailVerifyError').style.display = 'block';
            clearOtpInputs('emailOtpInputs');
            document.querySelector('#emailOtpInputs .otp-input')?.focus();
        })
        .finally(function () {
            document.getElementById('emailVerifyBtn').disabled = false;
            document.getElementById('emailVerifyBtn').textContent = 'Verify & change email';
        });
    });

    // ── Password modal ─────────────────────────────────────────────
    var passwordModal = document.getElementById('passwordModal');
    var pwCurrentVal = '';
    var pwNewVal = '', pwConfirmVal = '';
    var pwDigits = ['', '', '', '', '', ''];
    var pwCountdownInterval = null;

    document.getElementById('changePasswordBtn').addEventListener('click', function () {
        passwordModal.style.display = 'flex';
        document.getElementById('passwordFormStep').style.display = 'block';
        document.getElementById('passwordOtpStep').style.display = 'none';
        document.getElementById('pwCurrent').value = '';
        document.getElementById('pwNew').value = '';
        document.getElementById('pwConfirm').value = '';
        document.getElementById('pwSendError').style.display = 'none';
        document.getElementById('pwVerifyError').style.display = 'none';
        clearOtpInputs('pwOtpInputs');
        if (pwCountdownInterval) { clearInterval(pwCountdownInterval); pwCountdownInterval = null; }
        document.getElementById('pwCountdown').style.display = 'inline';
        document.getElementById('pwResendBtn').style.display = 'none';
    });

    document.getElementById('passwordModalCloseBtn').addEventListener('click', function () {
        passwordModal.style.display = 'none';
    });

    passwordModal.addEventListener('click', function (e) {
        if (e.target === passwordModal) passwordModal.style.display = 'none';
    });

    document.getElementById('pwBackToFormBtn').addEventListener('click', function () {
        document.getElementById('passwordFormStep').style.display = 'block';
        document.getElementById('passwordOtpStep').style.display = 'none';
        clearOtpInputs('pwOtpInputs');
        if (pwCountdownInterval) { clearInterval(pwCountdownInterval); pwCountdownInterval = null; }
    });

    function startPwCountdown() {
        var seconds = 60;
        var countdownEl = document.getElementById('pwCountdown');
        var resendBtn = document.getElementById('pwResendBtn');
        countdownEl.style.display = 'inline';
        resendBtn.style.display = 'none';
        pwCountdownInterval = setInterval(function () {
            seconds--;
            if (seconds <= 0) {
                clearInterval(pwCountdownInterval);
                countdownEl.style.display = 'none';
                resendBtn.style.display = 'inline';
            } else {
                countdownEl.textContent = 'Resend available in ' + seconds + 's';
            }
        }, 1000);
    }

    document.getElementById('pwSendBtn').addEventListener('click', function () {
        var pw = document.getElementById('pwCurrent').value;
        if (!pw) {
            var err = document.getElementById('pwSendError');
            err.textContent = 'Please enter your current password.';
            err.style.display = 'block';
            return;
        }
        this.disabled = true;
        this.textContent = 'Sending…';
        document.getElementById('pwSendError').style.display = 'none';

        fetch('/password-otp/send', {
            method: 'POST',
            headers: getHeaders(true),
            body: JSON.stringify({ current_password: pw })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!r.ok) throw new Error(data.message || 'Failed to send OTP.');
            pwCurrentVal = pw;
            document.getElementById('passwordFormStep').style.display = 'none';
            document.getElementById('passwordOtpStep').style.display = 'block';
            clearOtpInputs('pwOtpInputs');
            setupOtpInputs('pwOtpInputs', pwDigits);
            document.querySelector('#pwOtpInputs .otp-input')?.focus();
            startPwCountdown();
        })
        .catch(function (err) {
            var errEl = document.getElementById('pwSendError');
            errEl.textContent = err.message;
            errEl.style.display = 'block';
        })
        .finally(function () {
            document.getElementById('pwSendBtn').disabled = false;
            document.getElementById('pwSendBtn').textContent = 'Send verification code';
        });
    });

    document.getElementById('pwResendBtn').addEventListener('click', function () {
        if (!pwCurrentVal) return;
        this.disabled = true;
        fetch('/password-otp/send', {
            method: 'POST',
            headers: getHeaders(true),
            body: JSON.stringify({ current_password: pwCurrentVal })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!r.ok) throw new Error(data.message || 'Failed to resend.');
            clearOtpInputs('pwOtpInputs');
            document.getElementById('pwVerifyError').style.display = 'none';
            startPwCountdown();
        })
        .catch(function (err) {
            var errEl = document.getElementById('pwVerifyError');
            errEl.textContent = err.message;
            errEl.style.display = 'block';
        })
        .finally(function () { document.getElementById('pwResendBtn').disabled = false; });
    });

    document.getElementById('pwVerifyBtn').addEventListener('click', function () {
        var otp = getOtpDigits('pwOtpInputs');
        var newPw = document.getElementById('pwNew').value;
        var confirmPw = document.getElementById('pwConfirm').value;
        var errEl = document.getElementById('pwVerifyError');

        errEl.style.display = 'none';

        if (otp.length !== 6) { errEl.textContent = 'Enter the 6-digit code.'; errEl.style.display = 'block'; return; }
        if (!newPw || newPw.length < 8) { errEl.textContent = 'New password must be at least 8 characters.'; errEl.style.display = 'block'; return; }
        if (newPw !== confirmPw) { errEl.textContent = 'Passwords do not match.'; errEl.style.display = 'block'; return; }

        this.disabled = true;
        this.textContent = 'Verifying…';

        fetch('/password-otp/verify', {
            method: 'POST',
            headers: getHeaders(true),
            body: JSON.stringify({
                otp: otp,
                current_password: pwCurrentVal,
                new_password: newPw,
                new_password_confirmation: confirmPw
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!r.ok) throw new Error(data.message || 'Verification failed.');
            passwordModal.style.display = 'none';
            if (pwCountdownInterval) { clearInterval(pwCountdownInterval); pwCountdownInterval = null; }
            showToast('success', 'Password changed successfully.');
            document.getElementById('pwCurrent').value = '';
            document.getElementById('pwNew').value = '';
            document.getElementById('pwConfirm').value = '';
        })
        .catch(function (err) {
            errEl.textContent = err.message;
            errEl.style.display = 'block';
            clearOtpInputs('pwOtpInputs');
            document.querySelector('#pwOtpInputs .otp-input')?.focus();
        })
        .finally(function () {
            document.getElementById('pwVerifyBtn').disabled = false;
            document.getElementById('pwVerifyBtn').textContent = 'Verify & change password';
        });
    });

})();
</script>
@endpush
