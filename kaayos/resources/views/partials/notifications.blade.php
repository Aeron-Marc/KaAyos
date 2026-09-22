{{-- In-app Toasts and Confirmation Modal (Replaces browser alert() and confirm() to prevent "localhost says") --}}
<div id="toastContainer" class="toast-container" aria-live="polite"></div>

<div id="kaayosConfirmModal" class="kc-modal-overlay" style="display:none;" onclick="kaayosConfirmCancel(event)">
    <div class="kc-modal-box" onclick="event.stopPropagation()">
        <div class="kc-modal-icon" id="kcModalIcon">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <div class="kc-modal-content">
            <h3 class="kc-modal-title" id="kcModalTitle">Confirm Action</h3>
            <p class="kc-modal-text" id="kcModalMessage">Are you sure you want to proceed?</p>
        </div>
        <div class="kc-modal-actions">
            <button type="button" class="kc-btn kc-btn-cancel" id="kcCancelBtn" onclick="kaayosConfirmCancel()">Cancel</button>
            <button type="button" class="kc-btn kc-btn-confirm" id="kcConfirmBtn">Confirm</button>
        </div>
    </div>
</div>

<style>
/* Toast System Styles */
.toast-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 420px;
    pointer-events: none;
}
.toast {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 18px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    font-size: .88rem;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    pointer-events: auto;
    animation: toastSlideIn .25s ease-out;
    transition: opacity .25s, transform .25s;
}
@keyframes toastSlideIn {
    from { opacity: 0; transform: translateY(12px) scale(.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.toast.toast-fadeout {
    opacity: 0;
    transform: translateY(-8px) scale(.96);
}
.toast-success { border-left: 4px solid #10b981; }
.toast-success i.toast-icon { color: #10b981; font-size: 1.15rem; margin-top: 1px; }
.toast-error { border-left: 4px solid #ef4444; }
.toast-error i.toast-icon { color: #ef4444; font-size: 1.15rem; margin-top: 1px; }
.toast-warning { border-left: 4px solid #f59e0b; }
.toast-warning i.toast-icon { color: #f59e0b; font-size: 1.15rem; margin-top: 1px; }
.toast-info { border-left: 4px solid #1a6fc4; }
.toast-info i.toast-icon { color: #1a6fc4; font-size: 1.15rem; margin-top: 1px; }

.toast-msg {
    flex: 1;
    line-height: 1.45;
    word-break: break-word;
}
.toast-close {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: .95rem;
    padding: 0 0 0 8px;
    line-height: 1;
    margin-top: 2px;
}
.toast-close:hover { color: #475569; }

/* Custom Confirm Modal Styles */
.kc-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(2px);
    z-index: 99998;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: kcFadeIn .18s ease-out;
}
@keyframes kcFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.kc-modal-box {
    background: #ffffff;
    border-radius: 14px;
    max-width: 440px;
    width: 100%;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    padding: 24px;
    text-align: center;
    animation: kcBoxIn .2s ease-out;
}
@keyframes kcBoxIn {
    from { opacity: 0; transform: scale(.94) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.kc-modal-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    margin: 0 auto 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    background: #eff6ff;
    color: #1a6fc4;
}
.kc-modal-icon.danger {
    background: #fef2f2;
    color: #dc2626;
}
.kc-modal-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px;
}
.kc-modal-text {
    font-size: .92rem;
    color: #64748b;
    margin: 0 0 22px;
    line-height: 1.5;
}
.kc-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}
.kc-btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.kc-btn-cancel {
    background: #f1f5f9;
    color: #475569;
}
.kc-btn-cancel:hover { background: #e2e8f0; color: #1e293b; }
.kc-btn-confirm {
    background: #1a6fc4;
    color: #ffffff;
}
.kc-btn-confirm:hover { background: #14599e; }
.kc-btn-confirm.danger {
    background: #dc2626;
}
.kc-btn-confirm.danger:hover { background: #b91c1c; }
</style>

<script>
(function() {
    var confirmCallback = null;
    var cancelCallback = null;

    // Global showToast
    window.showToast = function(msg, type, duration) {
        // Auto-detect argument order if called as showToast('success', 'Done!') vs showToast('Done!', 'success')
        var validTypes = ['success', 'error', 'warning', 'info'];
        var message = msg;
        var toastType = type || 'info';

        if (validTypes.indexOf(msg) !== -1 && validTypes.indexOf(type) === -1) {
            toastType = msg;
            message = type;
        }

        if (!message) return;
        duration = duration || 4000;

        var container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        var icons = {
            success: 'fa-solid fa-circle-check',
            error: 'fa-solid fa-circle-exclamation',
            warning: 'fa-solid fa-triangle-exclamation',
            info: 'fa-solid fa-circle-info'
        };

        var div = document.createElement('div');
        div.className = 'toast toast-' + (icons[toastType] ? toastType : 'info');
        div.innerHTML = '<i class="toast-icon ' + (icons[toastType] || icons.info) + '"></i>' +
                        '<div class="toast-msg">' + message + '</div>' +
                        '<button type="button" class="toast-close" aria-label="Close">&times;</button>';

        div.querySelector('.toast-close').addEventListener('click', function() {
            div.classList.add('toast-fadeout');
            setTimeout(function() { div.remove(); }, 250);
        });

        container.appendChild(div);

        setTimeout(function() {
            if (div.parentNode) {
                div.classList.add('toast-fadeout');
                setTimeout(function() { div.remove(); }, 250);
            }
        }, duration);
    };

    // Override native browser alert to prevent "localhost says" popups
    window.alert = function(message) {
        var lower = String(message || '').toLowerCase();
        var type = 'info';
        if (lower.indexOf('error') !== -1 || lower.indexOf('fail') !== -1 || lower.indexOf('went wrong') !== -1) {
            type = 'error';
        } else if (lower.indexOf('success') !== -1 || lower.indexOf('submitted') !== -1 || lower.indexOf('updated') !== -1) {
            type = 'success';
        } else if (lower.indexOf('agree') !== -1 || lower.indexOf('select') !== -1 || lower.indexOf('please') !== -1 || lower.indexOf('required') !== -1) {
            type = 'warning';
        }
        window.showToast(message, type);
    };

    // Global showConfirm
    window.showConfirm = function(options, onConfirm, onCancel) {
        var opts = typeof options === 'string' ? { message: options } : (options || {});
        var modal = document.getElementById('kaayosConfirmModal');
        var titleEl = document.getElementById('kcModalTitle');
        var msgEl = document.getElementById('kcModalMessage');
        var iconEl = document.getElementById('kcModalIcon');
        var confirmBtn = document.getElementById('kcConfirmBtn');
        var cancelBtn = document.getElementById('kcCancelBtn');

        if (!modal) return false;

        titleEl.textContent = opts.title || 'Confirm Action';
        msgEl.textContent = opts.message || 'Are you sure you want to proceed?';
        cancelBtn.textContent = opts.cancelText || 'Cancel';
        confirmBtn.textContent = opts.confirmText || 'Confirm';

        var isDanger = opts.isDanger !== undefined ? opts.isDanger : (
            /delete|cancel|remove|reject|decline|suspend/i.test(opts.title || '') ||
            /delete|cancel|remove|reject|decline|suspend/i.test(opts.message || '')
        );

        if (isDanger) {
            iconEl.className = 'kc-modal-icon danger';
            iconEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
            confirmBtn.className = 'kc-btn kc-btn-confirm danger';
        } else {
            iconEl.className = 'kc-modal-icon';
            iconEl.innerHTML = '<i class="fa-solid fa-circle-question"></i>';
            confirmBtn.className = 'kc-btn kc-btn-confirm';
        }

        confirmCallback = onConfirm || opts.onConfirm || null;
        cancelCallback = onCancel || opts.onCancel || null;

        modal.style.display = 'flex';
        confirmBtn.focus();

        return new Promise(function(resolve) {
            confirmBtn.onclick = function() {
                modal.style.display = 'none';
                if (typeof confirmCallback === 'function') confirmCallback();
                resolve(true);
            };
        });
    };

    window.kaayosConfirmCancel = function(e) {
        var modal = document.getElementById('kaayosConfirmModal');
        if (modal) modal.style.display = 'none';
        if (typeof cancelCallback === 'function') cancelCallback();
    };

    // Intercept forms and buttons with data-confirm
    document.addEventListener('submit', function(e) {
        var form = e.target;
        var confirmText = form.getAttribute('data-confirm');
        if (confirmText && !form._confirmed) {
            e.preventDefault();
            window.showConfirm({
                title: form.getAttribute('data-confirm-title') || 'Please Confirm',
                message: confirmText,
                confirmText: form.getAttribute('data-confirm-btn') || 'Proceed',
                onConfirm: function() {
                    form._confirmed = true;
                    form.submit();
                }
            });
        }
    }, true);

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-confirm]');
        if (btn && btn.tagName !== 'FORM' && !btn.closest('form[data-confirm]')) {
            if (btn._confirmed) return;
            var confirmText = btn.getAttribute('data-confirm');
            e.preventDefault();
            e.stopPropagation();
            window.showConfirm({
                title: btn.getAttribute('data-confirm-title') || 'Please Confirm',
                message: confirmText,
                onConfirm: function() {
                    btn._confirmed = true;
                    btn.click();
                    setTimeout(function() { btn._confirmed = false; }, 1000);
                }
            });
        }
    }, true);
})();
</script>

{{-- Session Flash Messages --}}
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.showToast(@json(session('success')), 'success');
});
</script>
@endif
@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.showToast(@json(session('error')), 'error');
});
</script>
@endif
@if(session('warning'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.showToast(@json(session('warning')), 'warning');
});
</script>
@endif
@if(session('info'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.showToast(@json(session('info')), 'info');
});
</script>
@endif

