export default function DangerZone({ onDeleteClick }) {
    return (
        <div className="form-section danger-zone">
            <h3 className="form-section-title danger-zone-title">Danger Zone</h3>
            <p className="danger-zone-text">
                Deleting your account is permanent. All bookings, reviews, and data will be removed
                and cannot be recovered.
            </p>
            <button type="button" className="btn btn-danger-outline" onClick={onDeleteClick}>
                Delete account
            </button>
        </div>
    );
}
