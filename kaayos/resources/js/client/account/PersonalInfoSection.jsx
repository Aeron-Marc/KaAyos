import { PHONE_REGEX } from './utils';

function SectionTitle({ title, hasUnsaved }) {
    return (
        <h3 className="form-section-title">
            {title}
            {hasUnsaved && (
                <span className="section-unsaved-dot" title="Unsaved changes" aria-hidden="true">
                    ●
                </span>
            )}
        </h3>
    );
}

export default function PersonalInfoSection({
    draft,
    saved,
    saving,
    onChange,
    onSave,
    onDiscard,
}) {
    const hasUnsaved =
        draft.fullName !== saved.fullName ||
        draft.phone !== saved.phone ||
        draft.barangay !== saved.barangay ||
        draft.avatarUrl !== saved.avatarUrl;

    const phoneInvalid = draft.phone !== '' && !PHONE_REGEX.test(draft.phone);

    const handleSave = () => {
        if (phoneInvalid) return;
        onSave();
    };

    return (
        <div className="form-section">
            <SectionTitle title="Personal Information" hasUnsaved={hasUnsaved} />

            <div className="form-row">
                <div className="form-group">
                    <label htmlFor="fullName">Full Name</label>
                    <input
                        id="fullName"
                        type="text"
                        value={draft.fullName}
                        onChange={(e) => onChange('fullName', e.target.value)}
                        disabled={saving}
                    />
                </div>
                <div className="form-group">
                    <label htmlFor="phone">Phone Number</label>
                    <input
                        id="phone"
                        type="tel"
                        placeholder="09XXXXXXXXX"
                        value={draft.phone}
                        onChange={(e) => onChange('phone', e.target.value)}
                        aria-invalid={phoneInvalid}
                        disabled={saving}
                    />
                    {phoneInvalid && (
                        <p className="field-error">Enter a valid PH mobile number (09XXXXXXXXX).</p>
                    )}
                </div>
            </div>

            <div className="form-row">
                <div className="form-group">
                    <label htmlFor="barangay">Barangay</label>
                    <select
                        id="barangay"
                        value={draft.barangay}
                        onChange={(e) => onChange('barangay', e.target.value)}
                        disabled={saving}
                    >
                        <option value="">Select barangay...</option>
                        {[
                            'Acle','Bayudbud','Bolbok','Burgos','Dalima','Dao',
                            'Guinhawa','Lumbangan','Luna','Luntal','Magahis','Malibu',
                            'Mataywanac','Palincaro','Putol','Rillo','Rizal','Sabang',
                            'San Jose','Talon','Toong','Tuyon-Tuyon'
                        ].map((b) => (
                            <option key={b} value={b}>{b}</option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="form-actions">
                <button
                    type="button"
                    className="btn btn-solid"
                    disabled={!hasUnsaved || phoneInvalid || saving}
                    onClick={handleSave}
                >
                    {saving ? 'Saving…' : 'Save changes'}
                </button>
                <button
                    type="button"
                    className="btn btn-ghost"
                    disabled={!hasUnsaved || saving}
                    onClick={onDiscard}
                >
                    Discard
                </button>
            </div>
        </div>
    );
}
