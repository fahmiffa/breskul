{{-- ============================================== --}}
{{-- MASTER SIDEBAR - Dynamic Menu dari Database   --}}
{{-- Menu di-filter berdasarkan RBAC permission     --}}
{{-- ============================================== --}}

@php
    if (!isset($groupedMenus)) {
        $menuService = app(\App\Services\MenuService::class);
        $groupedMenus = $menuService->getMenusForUser(auth()->user(), 'master');
    }
    if (!isset($dynamicTitles)) {
        $dynamicTitles = [
            'dynamic:murid' => config('app.school_mode') ? 'Murid' : 'Mahasiswa',
            'dynamic:guru'  => config('app.school_mode') ? 'Guru' : 'Dosen',
            'dynamic:kelas' => 'Kelas',
            'dynamic:mapel' => config('app.school_mode') ? 'Mapel' : 'Makul',
        ];
    }
@endphp

@forelse($groupedMenus as $groupLabel => $menus)
    <li class="col-span-full border-b border-gray-200 mt-2 pb-2">
        <h3 class="text-base sm:text-lg font-semibold text-gray-700">{{ $groupLabel }}</h3>
    </li>

    @foreach($menus as $menu)
        @php
            $menuTitle = $dynamicTitles[$menu->title] ?? $menu->title;
            $isActive = false;
            try {
                $isActive = request()->routeIs($menu->route_name) || request()->routeIs(str_replace('.index', '.*', $menu->route_name));
            } catch (\Throwable $e) {
                $isActive = false;
            }
        @endphp
        <a href="{{ route($menu->route_name) }}">
            <li class="flex flex-col justify-center items-center p-2.5 sm:p-4 border border-gray-200 rounded-xl shadow-sm hover:bg-green-100 bg-white text-center h-full text-xs sm:text-sm font-medium transition-colors {{ $isActive ? 'bg-green-100' : '' }}">
                <span class="text-green-500 mb-1.5 sm:mb-2">
                    {!! $menu->icon !!}
                </span>
                {{ $menuTitle }}
            </li>
        </a>
    @endforeach
@empty
    {{-- Tidak ada menu yang tersedia untuk role ini --}}
@endforelse
