export const MOCK_OTP = '482931';

export const EMAIL_NOTIFICATION_OPTIONS = [
    'All updates',
    'Bookings only',
    'Messages only',
    'None',
];

export const LANGUAGE_OPTIONS = ['Filipino', 'English'];

export const PHONE_REGEX = /^09\d{9}$/;

export function getInitials(name) {
    const trimmed = (name || 'U').trim();
    return trimmed.substring(0, 2).toUpperCase();
}

export function getPasswordStrength(password) {
    if (!password) return null;

    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score === 1) return 'Weak';
    if (score === 2) return 'Fair';
    if (score === 3) return 'Good';
    if (score === 4) return 'Strong';
    return null;
}

export function strengthClass(strength) {
    const map = {
        Weak: 'strength-weak',
        Fair: 'strength-fair',
        Good: 'strength-good',
        Strong: 'strength-strong',
    };
    return map[strength] ?? '';
}

// Reads the Bearer token injected by Blade into window.authToken
function getAuthHeaders(json = false) {
    const headers = {
        Accept:        'application/json',
        Authorization: `Bearer ${window.authToken ?? ''}`,
    };
    if (json) headers['Content-Type'] = 'application/json';
    return headers;
}

export async function updateProfileRequest(data) {
    const res = await fetch('/api/profile', {
        method: 'PUT',
        headers: getAuthHeaders(true),
        body: JSON.stringify(data),
    });
    const result = await res.json();
    if (!res.ok) throw new Error(result.message || 'Failed to save profile.');
    return result;
}

export async function updatePreferencesRequest(data) {
    const res = await fetch('/api/preferences', {
        method: 'PUT',
        headers: getAuthHeaders(true),
        body: JSON.stringify(data),
    });
    const result = await res.json();
    if (!res.ok) throw new Error(result.message || 'Failed to save preferences.');
    return result;
}

export async function uploadAvatarRequest(file) {
    const form = new FormData();
    form.append('avatar', file);
    const res = await fetch('/api/profile/avatar', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${window.authToken ?? ''}`,
        },
        body: form,
    });
    const result = await res.json();
    if (!res.ok) throw new Error(result.message || 'Failed to upload avatar.');
    return result;
}

// request OTP email (validates current password before sending)
export async function sendOtpRequest(currentPassword) {
    const res = await fetch('/password-otp/send', {
        method:  'POST',
        headers: getAuthHeaders(true),
        body: JSON.stringify({
            current_password: currentPassword,
        }),
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.message || 'Failed to send OTP.');
    }

    return data;
}

// verify OTP and change password
export async function verifyOtpRequest({ otp, currentPassword, newPassword }) {
    const res = await fetch('/password-otp/verify', {
        method:  'POST',
        headers: getAuthHeaders(true),
        body: JSON.stringify({
            otp,
            current_password:          currentPassword,
            new_password:              newPassword,
            new_password_confirmation: newPassword,
        }),
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.message || 'Verification failed.');
    }

    return data;
}