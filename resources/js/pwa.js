// PWA Service Worker Registration & Install Prompt Management

let deferredPrompt = null;

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                // Check for updates on subsequent visits
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('[PWA] Versi baru tersedia.');
                            }
                        });
                    }
                });
            })
            .catch((error) => {
                console.error('[PWA] Gagal mendaftarkan service worker:', error);
            });
    });
}

// Handle Before Install Prompt
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent browser mini-infobar from appearing immediately
    e.preventDefault();
    deferredPrompt = e;
    window.dispatchEvent(new CustomEvent('pwa:can-install'));
});

// Handle App Installed
window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    window.dispatchEvent(new CustomEvent('pwa:installed'));
    console.log('[PWA] Aplikasi berhasil diinstall.');
});

// Global helper to trigger install prompt
window.installPwa = async () => {
    if (!deferredPrompt) {
        return false;
    }
    try {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null;
        window.dispatchEvent(new CustomEvent('pwa:installed'));
        return outcome === 'accepted';
    } catch (err) {
        console.error('[PWA] Install prompt error:', err);
        return false;
    }
};

// Check if currently running in standalone PWA mode
window.isPwaStandalone = () => {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true
    );
};
