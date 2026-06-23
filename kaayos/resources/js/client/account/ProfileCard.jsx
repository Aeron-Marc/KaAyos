import { useRef } from 'react';
import { getInitials } from './utils';

export default function ProfileCard({ fullName, email, avatarUrl, onPhotoSelect }) {
    const fileRef = useRef(null);
    const initials = getInitials(fullName);

    const handleFileChange = (event) => {
        const file = event.target.files?.[0];
        if (!file || !file.type.startsWith('image/')) return;
        onPhotoSelect(file);
        event.target.value = '';
    };

    return (
        <div className="profile-sidebar-card">
            <div className="profile-avatar-wrap">
                {avatarUrl ? (
                    <img src={avatarUrl} alt="" className="profile-big-avatar profile-avatar-img" />
                ) : (
                    <div className="profile-big-avatar">{initials}</div>
                )}
            </div>
            <h3>{fullName || 'User'}</h3>
            <p>{email}</p>
            <span className="profile-role-tag">Homeowner</span>
            <button
                type="button"
                className="btn btn-outline change-photo-btn"
                onClick={() => fileRef.current?.click()}
            >
                Change photo
            </button>
            <input
                ref={fileRef}
                type="file"
                accept="image/*"
                className="sr-only"
                onChange={handleFileChange}
            />
        </div>
    );
}
