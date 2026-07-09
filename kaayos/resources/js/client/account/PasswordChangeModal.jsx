import { useEffect, useRef, useState } from 'react';
import { sendOtpRequest, verifyOtpRequest } from './utils';

export default function PasswordChangeModal({ email, open, onClose, onSuccess }) {
    const [step, setStep] = useState('form');
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [digits, setDigits] = useState(['', '', '', '', '', '']);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [countdown, setCountdown] = useState(60);
    const inputsRef = useRef([]);

    useEffect(() => {
        if (!open) return;
        setStep('form');
        setCurrentPassword('');
        setNewPassword('');
        setConfirmPassword('');
        setDigits(['', '', '', '', '', '']);
        setError('');
        setLoading(false);
        setCountdown(60);
    }, [open]);

    useEffect(() => {
        if (step !== 'otp' || countdown <= 0) return;
        const timer = setInterval(() => {
            setCountdown((prev) => (prev > 0 ? prev - 1 : 0));
        }, 1000);
        return () => clearInterval(timer);
    }, [step, countdown]);

    const resetOtp = () => {
        setDigits(['', '', '', '', '', '']);
        setError('');
        setTimeout(() => inputsRef.current[0]?.focus(), 50);
    };

    const handleSendOtp = async () => {
        if (!currentPassword) {
            setError('Please enter your current password.');
            return;
        }
        setLoading(true);
        setError('');
        try {
            await sendOtpRequest(currentPassword);
            setStep('otp');
            setCountdown(60);
            resetOtp();
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleResend = async () => {
        setLoading(true);
        setError('');
        try {
            await sendOtpRequest(currentPassword);
            setCountdown(60);
            resetOtp();
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleVerify = async () => {
        const otp = digits.join('');
        if (otp.length !== 6) {
            setError('Enter the 6-digit code.');
            return;
        }
        if (!newPassword || newPassword.length < 8) {
            setError('New password must be at least 8 characters.');
            return;
        }
        if (newPassword !== confirmPassword) {
            setError('Passwords do not match.');
            return;
        }
        setLoading(true);
        setError('');
        try {
            await verifyOtpRequest({ otp, currentPassword, newPassword });
            onSuccess();
        } catch (err) {
            setError(err.message);
            resetOtp();
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
        if (value && index < 5) {
            inputsRef.current[index + 1]?.focus();
        }
    };

    const handleKeyDown = (index, event) => {
        if (event.key === 'Backspace' && !digits[index] && index > 0) {
            inputsRef.current[index - 1]?.focus();
        }
    };

    const handlePaste = (event) => {
        event.preventDefault();
        const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        if (!pasted) return;
        const next = pasted.split('').concat(Array(6).fill('')).slice(0, 6);
        setDigits(next);
        setError('');
        const focusIndex = Math.min(pasted.length, 5);
        inputsRef.current[focusIndex]?.focus();
    };

    if (!open) return null;

    return (
        <div className="modal-overlay" onClick={onClose} role="presentation">
            <div className="otp-modal" role="dialog" aria-modal="true"
                aria-labelledby="pw-modal-title" onClick={(e) => e.stopPropagation()}
            >
                <div className="otp-modal-header">
                    <div className="otp-modal-icon">
                        <i className={`fa-solid ${step === 'form' ? 'fa-key' : 'fa-shield-check'}`}
                            aria-hidden="true" />
                    </div>
                    <h2 id="pw-modal-title">
                        {step === 'form' ? 'Change Password' : 'Verify the code'}
                    </h2>
                    {step === 'form' ? (
                        <p>An OTP will be sent to <strong>{email}</strong> before your password is changed.</p>
                    ) : (
                        <p>Enter the 6-digit code sent to <strong>{email}</strong>. It expires in 10 minutes.</p>
                    )}
                </div>

                {step === 'form' && (
                    <>
                        <div className="form-group">
                            <label htmlFor="pw-current">Current Password</label>
                            <input type="password" id="pw-current"
                                value={currentPassword}
                                onChange={(e) => setCurrentPassword(e.target.value)}
                                placeholder="Enter your current password"
                                disabled={loading}
                            />
                        </div>

                        {error && <p className="field-error otp-error" role="alert">{error}</p>}

                        <div className="otp-actions">
                            <button type="button" className="btn btn-solid"
                                disabled={loading || !currentPassword} onClick={handleSendOtp}>
                                {loading ? 'Sending…' : 'Send verification code'}
                            </button>
                            <button type="button" className="btn btn-ghost"
                                onClick={onClose} disabled={loading}>Cancel</button>
                        </div>
                    </>
                )}

                {step === 'otp' && (
                    <>
                        <div className="otp-inputs" onPaste={handlePaste}>
                            {digits.map((digit, index) => (
                                <input key={index}
                                    ref={(el) => { inputsRef.current[index] = el; }}
                                    type="text" inputMode="numeric" maxLength={1}
                                    className={`otp-input${digit ? ' otp-input-filled' : ''}`}
                                    value={digit}
                                    aria-label={`Digit ${index + 1}`}
                                    onChange={(e) => handleDigitChange(index, e.target.value.slice(-1))}
                                    onKeyDown={(e) => handleKeyDown(index, e)}
                                    disabled={loading}
                                />
                            ))}
                        </div>

                        <div className="otp-timer-row" style={{ marginTop: '12px', textAlign: 'center' }}>
                            {countdown > 0 ? (
                                <span className="otp-countdown" style={{ fontSize: '.8rem', color: 'var(--g5)' }}>
                                    Resend available in {countdown}s
                                </span>
                            ) : (
                                <button type="button" className="btn-link otp-resend"
                                    onClick={handleResend} disabled={loading}
                                    style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: 'pointer', fontSize: '.85rem' }}>
                                    Resend code
                                </button>
                            )}
                        </div>

                        <div className="form-group" style={{ marginTop: '14px' }}>
                            <label htmlFor="pw-new">New Password</label>
                            <input type="password" id="pw-new"
                                value={newPassword}
                                onChange={(e) => setNewPassword(e.target.value)}
                                placeholder="At least 8 characters"
                                disabled={loading}
                            />
                        </div>
                        <div className="form-group" style={{ marginTop: '10px' }}>
                            <label htmlFor="pw-confirm">Confirm New Password</label>
                            <input type="password" id="pw-confirm"
                                value={confirmPassword}
                                onChange={(e) => setConfirmPassword(e.target.value)}
                                placeholder="Re-enter new password"
                                disabled={loading}
                            />
                        </div>

                        {error && <p className="field-error otp-error" role="alert">{error}</p>}

                        <div className="otp-actions">
                            <button type="button" className="btn btn-solid"
                                disabled={loading || !digits.every(d => d)} onClick={handleVerify}>
                                {loading ? 'Verifying…' : 'Verify & change password'}
                            </button>
                            <button type="button" className="btn btn-ghost"
                                onClick={() => { setStep('form'); setError(''); }}
                                disabled={loading}>Back</button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}
