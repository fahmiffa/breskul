<header class="bg-green-700 text-white">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-5 md:px-6 py-3">
        <div class="text-2xl font-bold">
            <a href="{{ route('dashboard.home') }}" class="hover:opacity-90 transition-opacity">
                {{ auth()->user()->role == 0 ? (auth()->user()->app->name ?? env('APP_NAME')) : (auth()->user()->data->apps->name ?? env('APP_NAME')) }}
            </a>
        </div>
        {{-- <nav class="space-x-6 flex items-center">
            <a href="{{ route('dashboard.home') }}" class="font-semibold inline-flex items-center gap-1.5 hover:text-green-200 transition-colors {{ Route::is('dashboard.home') ? 'underline underline-offset-4 decoration-2' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard">
                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
                Dashboard
            </a>
        </nav> --}}
        <div class="flex space-x-4 items-center">
            <div class="font-semibold hidden md:flex">{{auth()->user()->name}}</div>
            <a href="{{ route('dashboard.setting') }}" title="Pengaturan" class="hover:text-green-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-settings-icon lucide-settings">
                    <path
                        d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </a>
            <a class="text-sm hover:text-red-200 transition-colors" href="{{ route('dashboard.logout') }}" title="Logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-log-out-icon lucide-log-out">
                    <path d="m16 17 5-5-5-5" />
                    <path d="M21 12H9" />
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                </svg>
            </a>
        </div>
    </div>
</header>

<div class="bg-green-600 text-white shadow-sm">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-5 md:px-6 py-3 gap-4">
        <div>
            <h2 class="text-xl font-semibold">
                {{ request()->segment(2) ? str_replace("-"," ",ucfirst(request()->segment(2))) : 'Dashboard' }}
            </h2>
            @if (request()->segment(2))
            <nav class="text-xs sm:text-sm opacity-90 flex items-center gap-1.5 mt-0.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.home') }}" class="hover:underline flex items-center gap-1 text-white/95 hover:text-white font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Dashboard
                </a>
                <span class="opacity-60">&gt;</span>
                @if (request()->segment(3))
                    <span class="opacity-80">{{ str_replace("-"," ",ucfirst(request()->segment(2))) }}</span>
                    <span class="opacity-60">&gt;</span>
                    <span class="font-semibold text-white">{{ str_replace("-"," ",ucfirst(request()->segment(3))) }}</span>
                @else
                    <span class="font-semibold text-white">{{ str_replace("-"," ",ucfirst(request()->segment(2))) }}</span>
                @endif
            </nav>
            @endif
        </div>

        @if (!Route::is('dashboard.home'))
        <div class="flex items-center shrink-0">
            <a href="javascript:window.history.back()"
                class="inline-flex items-center gap-2 bg-white text-green-700 hover:bg-green-50 active:scale-95 font-semibold text-xs sm:text-sm px-3.5 py-2 rounded-xl shadow transition-all duration-150 border border-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>
        @endif
    </div>
</div>