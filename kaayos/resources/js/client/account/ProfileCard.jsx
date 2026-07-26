import { useRef, useState } from 'react';
import { getInitials } from './utils';
import Skeleton from './Skeleton';

export default function ProfileCard({ fullName, email, avatarUrl, onPhotoSelect }) {
    const fileRef = useRef(null);
    const initials = getInitials(fullName);
    const [imageLoaded, setImageLoaded] = useState(false);

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
                    <>
                        {!imageLoaded && <Skeleton variant="avatar-lg" />}
                        <img
                            src={avatarUrl}
                            alt=""
                            className={`profile-big-avatar profile-avatar-img${imageLoaded ? '' : ' skeleton-loading'}`}
                            style={imageLoaded ? {} : { display: 'none' }}
                            onLoad={() => setImageLoaded(true)}
                        />
                    </>
                ) : (
                    <div className="profile-big-avatar">{initials}</div>
                )}
            </div>
            <h3>{fullName || 'User'}</h3>
            <p>{email}</p>
            <span className="profile-role-tag">Client</span>
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
