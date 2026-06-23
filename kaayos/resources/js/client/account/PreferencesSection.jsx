import { EMAIL_NOTIFICATION_OPTIONS, LANGUAGE_OPTIONS } from './utils';

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

export default function PreferencesSection({ draft, saved, saving, onChange, onSave }) {
    const hasUnsaved =
        draft.emailNotifications !== saved.emailNotifications ||
        draft.language !== saved.language;

    return (
        <div className="form-section">
            <SectionTitle title="Preferences" hasUnsaved={hasUnsaved} />

            <div className="form-row">
                <div className="form-group">
                    <label htmlFor="emailNotifications">Email Notifications</label>
                    <select
                        id="emailNotifications"
                        value={draft.emailNotifications}
                        onChange={(e) => onChange('emailNotifications', e.target.value)}
                        disabled={saving}
                    >
                        {EMAIL_NOTIFICATION_OPTIONS.map((option) => (
                            <option key={option} value={option}>
                                {option}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="form-group">
                    <label htmlFor="language">Language</label>
                    <select
                        id="language"
                        value={draft.language}
                        onChange={(e) => onChange('language', e.target.value)}
                        disabled={saving}
                    >
                        {LANGUAGE_OPTIONS.map((option) => (
                            <option key={option} value={option}>
                                {option}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="form-actions">
                <button
                    type="button"
                    className="btn btn-solid"
                    disabled={!hasUnsaved || saving}
                    onClick={onSave}
                >
                    {saving ? 'Saving…' : 'Save preferences'}
                </button>
            </div>
        </div>
    );
}
