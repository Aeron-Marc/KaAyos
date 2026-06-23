import { useEffect } from 'react';

export default function ToastContainer({ toasts, onDismiss }) {
    return (
        <div className="toast-container" aria-live="polite">
            {toasts.map((toast) => (
                <Toast key={toast.id} toast={toast} onDismiss={onDismiss} />
            ))}
        </div>
    );
}

function Toast({ toast, onDismiss }) {
    useEffect(() => {
        const timer = setTimeout(() => onDismiss(toast.id), 3500);
        return () => clearTimeout(timer);
    }, [toast.id, onDismiss]);

    const isSuccess = toast.type === 'success';

    return (
        <div className={`toast toast-${toast.type}`} role="status">
            <i
                className={`fa-solid ${isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation'}`}
                aria-hidden="true"
            />
            <span>{toast.message}</span>
        </div>
    );
}
