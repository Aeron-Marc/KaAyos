const CLASSES = {
  text:      'skeleton skeleton-text',
  'text-sm': 'skeleton skeleton-text-sm',
  'text-lg': 'skeleton skeleton-text-lg',
  title:     'skeleton skeleton-title',
  avatar:    'skeleton skeleton-avatar',
  'avatar-sm': 'skeleton skeleton-avatar-sm',
  'avatar-lg': 'skeleton skeleton-avatar-lg',
  card:      'skeleton skeleton-card',
  button:    'skeleton skeleton-button',
  badge:     'skeleton skeleton-badge',
};

export default function Skeleton({ variant = 'text', width, height, className = '', style: extStyle }) {
  const cls = CLASSES[variant] || CLASSES.text;
  const style = { ...extStyle };
  if (width)  style.width  = typeof width === 'number' ? `${width}px` : width;
  if (height) style.height = typeof height === 'number' ? `${height}px` : height;
  return <div className={`${cls} ${className}`.trim()} style={style} aria-hidden="true" />;
}

export function SkeletonCard({ lines = 3, avatar, className = '' }) {
  return (
    <div className={`skeleton-panel ${className}`}>
      {avatar && (
        <div className="skeleton-header">
          <Skeleton variant="avatar" />
          <div className="skeleton-header-content">
            <Skeleton variant="title" />
            <Skeleton variant="text-sm" />
          </div>
        </div>
      )}
      {Array.from({ length: lines }).map((_, i) => (
        <Skeleton key={i} variant={i === lines - 1 ? 'text-sm' : 'text'} />
      ))}
    </div>
  );
}

export function SkeletonTable({ rows = 4 }) {
  return (
    <div className="skeleton-panel">
      <div className="skeleton-panel-header">
        <Skeleton variant="title" />
      </div>
      {Array.from({ length: rows }).map((_, i) => (
        <Skeleton key={i} variant="table-row" />
      ))}
    </div>
  );
}

export function SkeletonStats({ count = 4 }) {
  return (
    <div className="skeleton-grid">
      {Array.from({ length: count }).map((_, i) => (
        <div key={i} className="skeleton skeleton-stat">
          <div className="skeleton skeleton-stat-circle" />
          <Skeleton variant="title" />
          <Skeleton variant="text-sm" />
        </div>
      ))}
    </div>
  );
}

export function LoadingScreen({ text = 'Loading...' }) {
  return (
    <div className="loading-screen">
      <div className="spinner spinner-lg" />
      <span>{text}</span>
    </div>
  );
}
