export default function SecuritySection({ onOpenPasswordChange }) {
    return (
        <div className="form-section">
            <h3 className="form-section-title">Security</h3>
            <p style={{ fontSize: '.85rem', color: 'var(--g5)', marginBottom: '12px' }}>
                An OTP will be sent to your email before your password is changed.
            </p>
            <button type="button" className="btn btn-outline" onClick={onOpenPasswordChange}>
                <i className="fa-solid fa-key" aria-hidden="true" /> Change Password
            </button>
        </div>
    );
}
