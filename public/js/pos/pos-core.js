// pos-core.js

// =========================================================
// INISIALISASI & TOAST
// =========================================================
    const loadingOverlay = document.getElementById('loadingOverlay');
    const mainToast = document.getElementById('mainToast');

    function showLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'flex';
    }
    function hideLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'none';
    }

    function showToast(msg, isError = false) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const t = document.createElement('div');
        t.className = 'toast';
        t.style.background = isError ? '#ef4444' : '#10b981';
        t.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>${msg}`;
        container.appendChild(t);
        setTimeout(() => {
            t.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            t.style.opacity = '0';
            t.style.transform = 'translateX(100%)';
            setTimeout(() => t.remove(), 500);
        }, 3500);
    }

    // Auto-dismiss server-rendered toast
    if (mainToast) {
        setTimeout(() => {
            mainToast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            mainToast.style.opacity = '0';
            mainToast.style.transform = 'translateX(100%)';
            setTimeout(() => mainToast.remove(), 500);
        }, 4000);
    }

Swal = Swal.mixin({ heightAuto: false });

