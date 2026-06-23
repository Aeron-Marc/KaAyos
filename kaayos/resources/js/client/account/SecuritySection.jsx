import { useEffect, useState } from 'react';
import { getPasswordStrength, strengthClass } from './utils';

function PasswordField({ id, label, value, onChange, placeholder, disabled }) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="form-group">
            <label htmlFor={id}>{label}</label>
            <div className="password-field">
                <input
                    id={id}
                    type={visible ? 'text' : 'password'}
                    value={value}
                    placeholder={placeholder}
                    onChange={(e) => onChange(e.target.value)}
                    autoComplete="off"
                    disabled={disabled}
                />
                <button
                    type="button"
                    className="password-toggle"
                    aria-label={visible ? 'Hide password' : 'Show password'}
                    onClick={() => setVisible((v) => !v)}
                    disabled={disabled}
                >
                    <i
                        className={`fa-solid ${visible ? 'fa-eye-slash' : 'fa-eye'}`}
                        aria-hidden="true"
                    />
                </button>
            </div>
        </div>
    );
}

export default function SecuritySection({ email, resetKey, onSubmitOtp, loading }) {
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword]         = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');

    useEffect(() => {
        setCurrentPassword('');
        setNewPassword('');
        setConfirmPassword('');
    }, [resetKey]);

    const strength = getPasswordStrength(newPassword);
    const mismatch = confirmPassword !== '' && newPassword !== confirmPassword;

    const canSubmit =
        currentPassword !== '' &&
        newPassword.length >= 8 &&
        confirmPassword !== '' &&
        newPassword === confirmPassword;

    const handleSubmit = () => {
        if (!canSubmit) return;
        onSubmitOtp({ currentPassword, newPassword });
    };

    return (
        <div className="form-section">
            <h3 className="form-section-title">Security</h3>

            <div className="info-banner">
                <i className="fa-solid fa-circle-info" aria-hidden="true" />
                <span>
                    An OTP will be sent to <strong>{email}</strong> before your password is changed.
                </span>
            </div>

            <div className="form-row">
                <PasswordField
                    id="currentPassword"
                    label="Current Password"
                    value={currentPassword}
                    onChange={setCurrentPassword}
                    placeholder="••••••••"
                    disabled={loading}
                />
                <div className="form-group">
                    <PasswordField
                        id="newPassword"
                        label="New Password"
                        value={newPassword}
                        onChange={setNewPassword}
                        placeholder="••••••••"
                        disabled={loading}
                    />
                    {strength && (
                        <div className={`password-strength ${strengthClass(strength)}`}>
                            <div className="password-strength-bar">
                                <span />
                            </div>
                            <span className="password-strength-label">{strength}</span>
                        </div>
                    )}
                </div>
            </div>

            <div className="form-row">
                <div className="form-group">
                    <PasswordField
                        id="confirmPassword"
                        label="Confirm New Password"
                        value={confirmPassword}
                        onChange={setConfirmPassword}
                        placeholder="••••••••"
                        disabled={loading}
                    />
                    {mismatch && (
                        <p className="field-error">Passwords do not match.</p>
                    )}
                </div>
            </div>

            <div className="form-actions">
                <button
                    type="button"
                    className="btn btn-solid"
                    disabled={!canSubmit || loading}
                    onClick={handleSubmit}
                >
                    {loading ? 'Sending OTP…' : 'Send OTP & update password'}
                </button>
            </div>
        </div>
    );
}