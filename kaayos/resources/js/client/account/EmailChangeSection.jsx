import { useRef, useState } from 'react';

function getAuthHeaders() {
    return {
        Accept: 'application/json',
        Authorization: `Bearer ${window.authToken ?? ''}`,
    };
}

export default function EmailChangeSection({ email, onEmailChanged }) {
    const [open, setOpen] = useState(false);
    const [step, setStep] = useState('form');
    const [newEmail, setNewEmail] = useState('');
    const [confirmEmail, setConfirmEmail] = useState('');
    const [currentPassword, setCurrentPassword] = useState('');
    const [digits, setDigits] = useState(['', '', '', '', '', '']);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [successMsg, setSuccessMsg] = useState('');
    const inputsRef = useRef([]);

    const mismatch = confirmEmail !== '' && newEmail !== confirmEmail;
    const canSend = newEmail !== '' && confirmEmail !== '' && currentPassword !== '' && !mismatch;
    const allFilled = digits.every((d) => d !== '');

    const reset = () => {
        setStep('form');
        setNewEmail('');
        setConfirmEmail('');
        setCurrentPassword('');
        setDigits(['', '', '', '', '', '']);
        setError('');
    };

    const close = () => {
        setOpen(false);
        reset();
    };

    const handleSendOtp = async () => {
        if (!canSend) return;
        setLoading(true);
        setError('');

        try {
            const res = await fetch('/email-otp/send', {
                method: 'POST',
                headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    new_email: newEmail,
                    new_email_confirmation: confirmEmail,
                    current_password: currentPassword,
                }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to send code.');

            setStep('otp');
            setTimeout(() => inputsRef.current[0]?.focus(), 50);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleDigitChange = (index, value) => {
        if (value && !/^\d$/.test(value)) return;
        const next = [...digits];
        next[index] = value;
        setDigits(next);
        setError('');
        if (value && index < 5) inputsRef.current[index + 1]?.focus();
    };

    const handleKeyDown = (index, e) => {
        if (e.key === 'Backspace' && !digits[index] && index > 0) {
            inputsRef.current[index - 1]?.focus();
        }
    };

    const handlePaste = (e) => {
        e.preventDefault();
        const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        if (!pasted) return;
        const next = pasted.split('').concat(Array(6).fill('')).slice(0, 6);
        setDigits(next);
        const focusIndex = Math.min(pasted.length, 5);
        inputsRef.current[focusIndex]?.focus();
    };

    const handleVerifyOtp = async () => {
        setLoading(true);
        setError('');

        try {
            const res = await fetch('/email-otp/verify', {
                method: 'POST',
                headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
                body: JSON.stringify({ otp: digits.join('') }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Verification failed.');

            setSuccessMsg(data.message);
            if (onEmailChanged) onEmailChanged(newEmail);
            close();
        } catch (err) {
            setError(err.message);
            setDigits(['', '', '', '', '', '']);
            setTimeout(() => inputsRef.current[0]?.focus(), 50);
        } finally {
            setLoading(false);
        }
    };

    if (!open) {
        return (
            <div className="form-section">
                <h3 className="form-section-title">Email Address</h3>
                {successMsg && (
                    <div className="info-banner" style={{ marginBottom: 12 }}>
                        <i className="fa-solid fa-circle-check" aria-hidden="true" />
                        <span>{successMsg}</span>
                    </div>
                )}
                <div className="form-row">
                    <div className="form-group">
                        <label>Current Email</label>
                        <p className="form-value">{email}</p>
                    </div>
                    <div className="form-group" style={{ display: 'flex', alignItems: 'flex-end', paddingBottom: 4 }}>
                        <button type="button" className="btn btn-outline" onClick={() => { reset(); setOpen(true); }}>
                            <i className="fa-solid fa-pen" aria-hidden="true" />
                            Change Email
                        </button>
                    </div>
                </div>
                <p style={{ fontSize: '0.8rem', color: 'var(--g4)', marginTop: 8 }}>
                    You can change your email once every 30 days. A verification code will be sent to confirm.
                </p>
            </div>
        );
    }

    return (
        <div className="modal-overlay" onClick={close} role="presentation">
            <div className="otp-modal" role="dialog" aria-modal="true" onClick={(e) => e.stopPropagation()}>
                <div className="otp-modal-header">
                    <div className="otp-modal-icon">
                        <i className={`fa-solid ${step === 'form' ? 'fa-envelope' : 'fa-shield-check'}`} aria-hidden="true" />
                    </div>
                    {step === 'form' ? (
                        <>
                            <h2>Change Email Address</h2>
                            <p>Current: <strong>{email}</strong></p>
                        </>
                    ) : (
                        <>
                            <h2>Verify the code</h2>
                            <p>
                                Enter the 6-digit code sent to <strong>{newEmail}</strong>. It expires in 10 minutes.
                            </p>
                        </>
                    )}
                </div>

                {step === 'form' ? (
                    <>
                        <div className="form-row">
                            <div className="form-group">
                                <label htmlFor="ceNewEmail">New Email</label>
                                <input id="ceNewEmail" type="email" value={newEmail}
                                    onChange={(e) => setNewEmail(e.target.value)} disabled={loading} />
                            </div>
                            <div className="form-group">
                                <label htmlFor="ceConfirmEmail">Confirm New Email</label>
                                <input id="ceConfirmEmail" type="email" value={confirmEmail}
                                    onChange={(e) => setConfirmEmail(e.target.value)} disabled={loading} />
                                {mismatch && <p className="field-error">Emails do not match.</p>}
                            </div>
                        </div>
                        <div className="form-group">
                            <label htmlFor="ceCurrentPassword">Current Password</label>
                            <input id="ceCurrentPassword" type="password" value={currentPassword}
                                placeholder="Enter your current password"
                                onChange={(e) => setCurrentPassword(e.target.value)} disabled={loading} />
                        </div>
                    </>
                ) : (
                    <div className="otp-inputs" onPaste={handlePaste}>
                        {digits.map((digit, i) => (
                            <input key={i} ref={(el) => { inputsRef.current[i] = el; }}
                                type="text" inputMode="numeric" maxLength={1}
                                className={`otp-input${digit ? ' otp-input-filled' : ''}`} value={digit}
                                onChange={(e) => handleDigitChange(i, e.target.value.slice(-1))}
                                onKeyDown={(e) => handleKeyDown(i, e)} disabled={loading} />
                        ))}
                    </div>
                )}

                {error && <p className="field-error otp-error" role="alert">{error}</p>}

                <div className="otp-actions">
                    {step === 'form' ? (
                        <button type="button" className="btn btn-solid" disabled={!canSend || loading} onClick={handleSendOtp}>
                            {loading ? 'Sending…' : 'Send verification code'}
                        </button>
                    ) : (
                        <button type="button" className="btn btn-solid" disabled={!allFilled || loading} onClick={handleVerifyOtp}>
                            {loading ? 'Verifying…' : 'Verify & change email'}
                        </button>
                    )}
                    <button type="button" className="btn btn-ghost" onClick={close} disabled={loading}>Cancel</button>
                </div>
            </div>
        </div>
    );
}
