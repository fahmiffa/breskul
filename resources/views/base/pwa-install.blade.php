<div x-data="{
        showBanner: false,
        dismissed: false,
        init() {
            if (window.isPwaStandalone && window.isPwaStandalone()) {
                return;
            }
            const dismissedAt = localStorage.getItem('pwa_install_dismissed');
            if (dismissedAt) {
                const diffDays = (Date.now() - parseInt(dismissedAt, 10)) / (1000 * 60 * 60 * 24);
                if (diffDays < 7) {
                    this.dismissed = true;
                }
            }
            window.addEventListener('pwa:can-install', () => {
                if (!this.dismissed) {
                    this.showBanner = true;
                }
            });
            window.addEventListener('pwa:installed', () => {
                this.showBanner = false;
            });
        },
        async install() {
            if (window.installPwa) {
                const success = await window.installPwa();
                if (success) {
                    this.showBanner = false;
                }
            }
        },
        dismiss() {
            this.showBanner = false;
            this.dismissed = true;
            localStorage.setItem('pwa_install_dismissed', Date.now().toString());
        }
    }"
    x-cloak
    x-show="showBanner"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-8"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-8"
    class="fixed bottom-4 left-4 right-4 sm:left-6 sm:right-auto sm:bottom-6 sm:max-w-md z-50 bg-white border border-gray-200 rounded-2xl shadow-2xl p-4 flex items-center gap-3">
    
    <img src="{{ asset('icons/icon-192x192.png') }}" alt="{{ config('app.name', 'Breskul') }}" class="w-12 h-12 rounded-xl object-contain shadow-sm border border-gray-100 flex-shrink-0">
    
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold text-gray-900 leading-tight">Install {{ config('app.name', 'Breskul') }}</h4>
        <p class="text-xs text-gray-500 truncate mt-0.5">Pasang aplikasi di layar utama untuk akses lebih cepat.</p>
    </div>

    <div class="flex items-center gap-2 flex-shrink-0">
        <button type="button" @click="install()" class="bg-green-600 hover:bg-green-700 active:scale-95 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" x2="12" y1="15" y2="3"/>
            </svg>
            <span>Install</span>
        </button>
        <button type="button" @click="dismiss()" title="Tutup" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition-colors cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
</div>
