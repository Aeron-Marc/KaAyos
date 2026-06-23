import { useCallback, useEffect, useState } from 'react';
import DangerZone from './DangerZone';
import OtpModal from './OtpModal';
import PersonalInfoSection from './PersonalInfoSection';
import PreferencesSection from './PreferencesSection';
import ProfileCard from './ProfileCard';
import SecuritySection from './SecuritySection';
import ToastContainer from './Toast';
import { sendOtpRequest, updateProfileRequest, updatePreferencesRequest, uploadAvatarRequest } from './utils';

const DEFAULT_PREFS = {
    emailNotifications: 'All updates',
    language: 'English',
};

export default function AccountPage({ initial }) {
    const [savedProfile, setSavedProfile] = useState({
        fullName: initial.fullName || '',
        email: initial.email || '',
        phone: initial.phone || '',
        barangay: initial.barangay || '',
        avatarUrl: initial.avatarUrl || null,
    });
    const [draftProfile, setDraftProfile] = useState(savedProfile);

    const [savedPrefs, setSavedPrefs] = useState({
        emailNotifications: initial.emailNotifications || DEFAULT_PREFS.emailNotifications,
        language: initial.language || DEFAULT_PREFS.language,
    });
    const [draftPrefs, setDraftPrefs] = useState({ ...savedPrefs });

    const [displayProfile, setDisplayProfile] = useState(savedProfile);

    const [otpOpen, setOtpOpen] = useState(false);
    const [pwPayload, setPwPayload] = useState(null);
    const [otpLoading, setOtpLoading] = useState(false);
    const [securityResetKey, setSecurityResetKey] = useState(0);

    const [profileSaving, setProfileSaving] = useState(false);
    const [prefsSaving, setPrefsSaving] = useState(false);

    const [toasts, setToasts] = useState([]);

    const addToast = useCallback((type, message) => {
        setToasts((prev) => [...prev, { id: crypto.randomUUID(), type, message }]);
    }, []);

    const dismissToast = useCallback((id) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    }, []);

    const profileDirty =
        draftProfile.fullName !== savedProfile.fullName ||
        draftProfile.email !== savedProfile.email ||
        draftProfile.phone !== savedProfile.phone ||
        draftProfile.barangay !== savedProfile.barangay ||
        draftProfile.avatarUrl !== savedProfile.avatarUrl;

    const prefsDirty =
        draftPrefs.emailNotifications !== savedPrefs.emailNotifications ||
        draftPrefs.language !== savedPrefs.language;

    const hasUnsaved = profileDirty || prefsDirty;

    useEffect(() => {
        const handler = (event) => {
            if (hasUnsaved) {
                event.preventDefault();
                event.returnValue = '';
            }
        };

        window.addEventListener('beforeunload', handler);
        return () => window.removeEventListener('beforeunload', handler);
    }, [hasUnsaved]);

    const handleProfileChange = (field, value) => {
        setDraftProfile((prev) => ({ ...prev, [field]: value }));
    };

    const handlePhotoSelect = async (file) => {
        try {
            const result = await uploadAvatarRequest(file);
            setDraftProfile((prev) => ({ ...prev, avatarUrl: result.avatar_url }));
        } catch (err) {
            addToast('error', err.message);
        }
    };

    const handleProfileSave = async () => {
        setProfileSaving(true);
        try {
            const result = await updateProfileRequest({
                fullName: draftProfile.fullName,
                email: draftProfile.email,
                phone: draftProfile.phone,
                barangay: draftProfile.barangay,
            });
            const next = {
                fullName: result.fullName,
                email: result.email,
                phone: result.phone,
                barangay: result.barangay,
                avatarUrl: draftProfile.avatarUrl,
            };
            setSavedProfile(next);
            setDraftProfile(next);
            setDisplayProfile(next);
            addToast('success', result.message);
        } catch (err) {
            addToast('error', err.message);
        } finally {
            setProfileSaving(false);
        }
    };

    const handleProfileDiscard = () => {
        setDraftProfile({ ...savedProfile });
    };

    const handlePrefsChange = (field, value) => {
        setDraftPrefs((prev) => ({ ...prev, [field]: value }));
    };

    const handlePrefsSave = async () => {
        setPrefsSaving(true);
        try {
            const result = await updatePreferencesRequest({
                emailNotifications: draftPrefs.emailNotifications,
                language: draftPrefs.language,
            });
            const next = { ...draftPrefs };
            setSavedPrefs(next);
            setDraftPrefs(next);
            addToast('success', result.message);
        } catch (err) {
            addToast('error', err.message);
        } finally {
            setPrefsSaving(false);
        }
    };

    const handleSubmitOtp = async ({ currentPassword, newPassword }) => {
        setOtpLoading(true);
        try {
            await sendOtpRequest(currentPassword);
            setPwPayload({ currentPassword, newPassword });
            setOtpOpen(true);
        } catch (err) {
            addToast('error', err.message);
        } finally {
            setOtpLoading(false);
        }
    };

    const handleOtpSuccess = () => {
        setOtpOpen(false);
        setPwPayload(null);
        setSecurityResetKey((k) => k + 1);
        addToast('success', 'Password changed successfully.');
    };

    const handleDeleteAccount = () => {
        addToast('success', 'Please contact support at support@kaayos.ph to delete your account.');
    };

    return (
        <>
            <div className="profile-grid">
                <ProfileCard
                    fullName={displayProfile.fullName}
                    email={displayProfile.email}
                    avatarUrl={draftProfile.avatarUrl ?? displayProfile.avatarUrl}
                    onPhotoSelect={handlePhotoSelect}
                />

                <div>
                    <PersonalInfoSection
                        draft={draftProfile}
                        saved={savedProfile}
                        saving={profileSaving}
                        onChange={handleProfileChange}
                        onSave={handleProfileSave}
                        onDiscard={handleProfileDiscard}
                    />

                    <PreferencesSection
                        draft={draftPrefs}
                        saved={savedPrefs}
                        saving={prefsSaving}
                        onChange={handlePrefsChange}
                        onSave={handlePrefsSave}
                    />

                    <SecuritySection
                        email={displayProfile.email}
                        resetKey={securityResetKey}
                        loading={otpLoading}
                        onSubmitOtp={handleSubmitOtp}
                    />

                    <DangerZone onDeleteClick={handleDeleteAccount} />
                </div>
            </div>

            <OtpModal
                email={displayProfile.email}
                open={otpOpen}
                currentPassword={pwPayload?.currentPassword}
                newPassword={pwPayload?.newPassword}
                onClose={() => setOtpOpen(false)}
                onSuccess={handleOtpSuccess}
                onResend={() => addToast('success', 'Verification code resent.')}
            />

            <ToastContainer toasts={toasts} onDismiss={dismissToast} />
        </>
    );
}
