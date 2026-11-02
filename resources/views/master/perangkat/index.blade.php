@extends('base.layout')
@section('title', 'Monitoring Perangkat')
@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="flex flex-col gap-6" x-data="deviceMonitoring()" x-init="init()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Monitoring Perangkat</h1>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Pantau status perangkat IoT operasional secara real-time dari endpoint <code>POST /api/state</code>
            </p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Countdown timer & refresh --}}
            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-gray-200 shadow-sm text-xs text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Update: <strong class="text-emerald-600" x-text="countdown + 's'"></strong></span>
            </div>

            <button type="button" @click="fetchData(true)" :disabled="loading"
                class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs sm:text-sm font-semibold py-2 px-4 rounded-xl transition flex items-center gap-2 shadow-sm cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    :class="{ 'animate-spin': loading }">
                    <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/>
                    <path d="M21 3v5h-5"/>
                </svg>
                <span x-text="loading ? 'Memuat...' : 'Perbarui'"></span>
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Total --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Perangkat</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-800 mt-1" x-text="summary.total"></h3>
                <p class="text-xs text-gray-400 mt-1" x-text="'Terdaftar di App ID: ' + (appId ?? 'Semua')"></p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="16" height="16" x="4" y="4" rx="2"/>
                    <rect width="6" height="6" x="9" y="9" rx="1"/>
                    <path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>
                </svg>
            </div>
        </div>

        {{-- Stand By (Status 1) --}}
        <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl shadow-md p-5 text-white flex items-center justify-between">
            <div>
                <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wider">Stand by (Status 1)</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold mt-1" x-text="summary.standby"></h3>
                <p class="text-xs text-emerald-100 mt-1">Siap beroperasi / siaga</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 text-white flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>

        {{-- Startup (Status 0) --}}
        <div class="bg-gradient-to-br from-amber-500 to-yellow-600 rounded-2xl shadow-md p-5 text-white flex items-center justify-between">
            <div>
                <p class="text-amber-100 text-xs font-semibold uppercase tracking-wider">Startup (Status 0)</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold mt-1" x-text="summary.startup"></h3>
                <p class="text-xs text-amber-100 mt-1">Booting / inisialisasi awal</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 text-white flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Main Table Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
        {{-- Table Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5 pb-4 border-b border-gray-100">
            {{-- Tabs --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="filterStatus = 'all'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer"
                    :class="filterStatus === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    Semua (<span x-text="devices.length"></span>)
                </button>
                <button type="button" @click="filterStatus = '1'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer"
                    :class="filterStatus === '1' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'">
                    Stand by (<span x-text="summary.standby"></span>)
                </button>
                <button type="button" @click="filterStatus = '0'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer"
                    :class="filterStatus === '0' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'">
                    Startup (<span x-text="summary.startup"></span>)
                </button>
            </div>

            {{-- Search & Info --}}
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Cari nama perangkat..."
                        class="pl-9 pr-4 py-1.5 text-xs rounded-xl border border-gray-200 focus:ring-emerald-500 focus:border-emerald-500 w-48 sm:w-64">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <span class="text-xs text-gray-400 hidden sm:inline" x-text="'Sinkron: ' + lastUpdated"></span>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/75 text-gray-500 uppercase tracking-wider text-[11px] font-semibold border-b border-gray-100">
                        <th class="px-4 py-3">Nama Perangkat</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Kondisi</th>
                        <th class="px-4 py-3">IP Address</th>
                        <th class="px-4 py-3">Terakhir Update</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="item in filteredDevices" :key="item.id">
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            {{-- Nama Perangkat --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs"
                                        :class="item.status === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="16" height="16" x="4" y="4" rx="2"/>
                                            <rect width="6" height="6" x="9" y="9" rx="1"/>
                                            <path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800 text-sm" x-text="item.device_name"></div>
                                        <div class="text-[11px] text-gray-400" x-text="item.app_name"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <template x-if="item.status === 1">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                        Stand by
                                    </span>
                                </template>
                                <template x-if="item.status !== 1">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                                        Startup
                                    </span>
                                </template>
                            </td>

                            {{-- Kondisi / Aktivitas --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <template x-if="item.is_active">
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif Berkomunikasi
                                    </span>
                                </template>
                                <template x-if="!item.is_active">
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-400 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                        Idle / Terakhir <span x-text="item.last_seen"></span>
                                    </span>
                                </template>
                            </td>

                            {{-- IP Address --}}
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs text-gray-600" x-text="item.ip_address || '-'"></td>

                            {{-- Terakhir Update --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-xs font-medium text-gray-700" x-text="item.updated_at"></div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <button type="button" @click="deleteDevice(item)"
                                    class="text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition cursor-pointer"
                                    title="Hapus Perangkat">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>

                    {{-- Empty State --}}
                    <tr x-show="filteredDevices.length === 0">
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="opacity-30">
                                    <rect width="16" height="16" x="4" y="4" rx="2"/>
                                    <path d="M9 9h6v6H9z"/>
                                </svg>
                                <span class="font-medium text-gray-600">Tidak ada perangkat ditemukan</span>
                                <span class="text-xs text-gray-400 max-w-sm">
                                    Perangkat IoT akan otomatis terdaftar saat mengirimkan state ke endpoint <code>POST /api/state</code>.
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('script')
<script>
window.deviceMonitoring = function() {
    return {
        appId: @json($appId),
        devices: @json($deviceList ?? []),
        summary: {
            total: {{ $totalCount }},
            standby: {{ $standbyCount }},
            startup: {{ $startupCount }},
        },
        search: '',
        filterStatus: 'all',
        loading: false,
        countdown: 5,
        timerInterval: null,
        pollInterval: null,
        lastUpdated: '{{ now()->format("H:i:s") }}',

        init() {
            // Start countdown timer every second
            this.timerInterval = setInterval(() => {
                if (this.countdown > 1) {
                    this.countdown--;
                } else {
                    this.countdown = 5;
                    this.fetchData(false);
                }
            }, 1000);
        },

        get filteredDevices() {
            const seen = new Set();
            return this.devices.filter(d => {
                if (seen.has(d.device_name)) return false;
                seen.add(d.device_name);
                const matchSearch = !this.search || d.device_name.toLowerCase().includes(this.search.toLowerCase());
                const matchStatus = this.filterStatus === 'all' || String(d.status) === this.filterStatus;
                return matchSearch && matchStatus;
            });
        },

        async fetchData(manual = false) {
            if (manual) {
                this.loading = true;
                this.countdown = 5;
            }

            try {
                const url = `{{ route('dashboard.master.perangkat.data') }}`;
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.devices = data.devices;
                        this.summary = {
                            total: data.total,
                            standby: data.standby_count,
                            startup: data.startup_count,
                        };
                        this.lastUpdated = data.server_time || new Date().toLocaleTimeString('id-ID');
                    }
                }
            } catch (err) {
                console.error('Gagal mengambil data perangkat:', err);
            } finally {
                if (manual) {
                    setTimeout(() => { this.loading = false; }, 300);
                }
            }
        },

        async deleteDevice(item) {
            if (!confirm(`Hapus perangkat "${item.device_name}"?`)) return;

            try {
                const res = await fetch(`{{ url('dashboard/master/perangkat') }}/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.fetchData(true);
                } else {
                    alert(data.message || 'Gagal menghapus perangkat.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            }
        }
    };
};

if (window.Alpine) {
    window.Alpine.data('deviceMonitoring', window.deviceMonitoring);
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('deviceMonitoring', window.deviceMonitoring);
    });
}
</script>
@endpush
@endsection
