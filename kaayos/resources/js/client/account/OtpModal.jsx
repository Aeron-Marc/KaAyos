import { useCallback, useEffect, useRef, useState } from 'react';
import { verifyOtpRequest, sendOtpRequest } from './utils';

export default function OtpModal({
    email,
    open,
    onClose,
    onSuccess,
    onResend,
    currentPassword,
    newPassword,
}) {
    const [digits, setDigits]     = useState(['', '', '', '', '', '']);
    const [error, setError]       = useState('');
    const [loading, setLoading]   = useState(false);
    const [countdown, setCountdown] = useState(60);
    const inputsRef = useRef([]);

    const resetDigits = useCallback(() => {
        setDigits(['', '', '', '', '', '']);
        setError('');
        setTimeout(() => inputsRef.current[0]?.focus(), 0);
    }, []);

    useEffect(() => {
        if (!open) return;
        resetDigits();
        setCountdown(60);
        setLoading(false);
        setTimeout(() => inputsRef.current[0]?.focus(), 50);
    }, [open, resetDigits]);

    useEffect(() => {
        if (!open || countdown <= 0) return;
        const timer = setInterval(() => {
            setCountdown((prev) => (prev > 0 ? prev - 1 : 0));
        }, 1000);
        return () => clearInterval(timer);
    }, [open, countdown]);

    const handleChange = (index, value) => {
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

    const handleResend = async () => {
        setCountdown(60);
        resetDigits();
        try {
            await sendOtpRequest();
            onResend();
        } catch (err) {
            setError(err.message);
        }
    };

    const handleVerify = async () => {
        setLoading(true);
        setError('');
        try {
            await verifyOtpRequest({
                otp: digits.join(''),
                currentPassword,
                newPassword,
            });
            onSuccess();
        } catch (err) {
            setError(err.message);
            resetDigits();
        } finally {
            setLoading(false);
        }
    };

    const allFilled = digits.every((d) => d !== '');

    if (!open) return null;

    return (
        <div
            className="modal-overlay"
            onClick={onClose}
            role="presentation"
        >
            <div
                className="otp-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="otp-modal-title"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="otp-modal-header">
                    <div className="otp-modal-icon">
                        <i className="fa-solid fa-shield-check" aria-hidden="true" />
                    </div>
                    <h2 id="otp-modal-title">Verify it&apos;s you</h2>
                    <p>
                        Enter the 6-digit code sent to <strong>{email}</strong>.{' '}
                        It expires in 10 minutes.
                    </p>
                </div>

                <div className="otp-inputs" onPaste={handlePaste}>
                    {digits.map((digit, index) => (
                        <input
                            key={index}
                            ref={(el) => { inputsRef.current[index] = el; }}
                            type="text"
                            inputMode="numeric"
                            maxLength={1}
                            className={`otp-input${digit ? ' otp-input-filled' : ''}`}
                            value={digit}
                            aria-label={`Digit ${index + 1}`}
                            onChange={(e) => handleChange(index, e.target.value.slice(-1))}
                            onKeyDown={(e) => handleKeyDown(index, e)}
                            disabled={loading}
                        />
                    ))}
                </div>

                {error && (
                    <p className="field-error otp-error" role="alert">
                        {error}
                    </p>
                )}

                <div className="otp-timer-row">
                    {countdown > 0 ? (
                        <span className="otp-countdown">
                            Resend available in {countdown}s
                        </span>
                    ) : (
                        <button
                            type="button"
                            className="btn-link otp-resend"
                            onClick={handleResend}
                            disabled={loading}
                        >
                            Resend code
                        </button>
                    )}
                </div>

                <div className="otp-actions">
                    <button
                        type="button"
                        className="btn btn-solid"
                        disabled={!allFilled || loading}
                        onClick={handleVerify}
                    >
                        {loading ? 'Verifying…' : 'Verify & change password'}
                    </button>
                    <button
                        type="button"
                        className="btn btn-ghost"
                        onClick={onClose}
                        disabled={loading}
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    );
}