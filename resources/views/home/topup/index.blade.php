@extends('base.layout')
@section('title', $title)
@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush
@section('content')
<div class="flex flex-col gap-6" x-data="topupTable()" x-init="fetchData()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $title }}</h1>
            <p class="text-xs text-gray-500 mt-1">Kelola dan verifikasi data topup saldo siswa secara manual</p>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-2xl shadow-md p-4 sm:p-6 border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            {{-- Status Tabs --}}
            <div class="flex flex-wrap items-center gap-2">
                <template x-for="tab in statusTabs" :key="tab.value">
                    <button type="button" @click="statusFilter = tab.value; currentPage = 1; fetchData()"
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer border"
                        :class="statusFilter === tab.value
                            ? tab.activeClass
                            : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200'">
                        <span x-text="tab.label"></span>
                    </button>
                </template>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-80">
                <input type="text" x-model="search" @input.debounce.400ms="currentPage = 1; fetchData()"
                    placeholder="Cari NIS atau Nama..."
                    class="w-full border border-gray-300 ring-0 rounded-xl px-4 py-2 text-sm focus:outline-green-500 focus:ring-1 focus:ring-green-500 shadow-sm" />
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full bg-white text-sm">
                <thead>
                    <tr class="bg-green-600 text-left text-white">
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center w-12">No</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Nama</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">{{ config('app.school_mode') ? 'NIS' : 'NIM' }}</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">{{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-right">Nominal</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Kode Unik</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-right">Total Transfer</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Status</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Expired</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Dibuat</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    {{-- Loading --}}
                    <template x-if="loading">
                        <tr>
                            <td colspan="11" class="text-center py-12 text-gray-400">
                                <svg class="animate-spin h-6 w-6 text-green-500 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Memuat data...
                            </td>
                        </tr>
                    </template>

                    {{-- Data Rows --}}
                    <template x-if="!loading && rows.length > 0">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-4 py-3 text-center text-xs text-gray-500" x-text="meta.from + index"></td>
                                <td class="px-4 py-3 font-medium text-gray-800" x-text="row.student_name"></td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600" x-text="row.nis"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="row.kelas"></td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700" x-text="formatRp(row.nominal)"></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700" x-text="row.kode_unik"></span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-green-700" x-text="formatRp(row.total_nominal)"></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700': row.status === 'pending',
                                            'bg-green-100 text-green-700': row.status === 'success',
                                            'bg-red-100 text-red-700': row.status === 'expired',
                                            'bg-gray-100 text-gray-600': !['pending','success','expired'].includes(row.status)
                                        }"
                                        x-text="statusLabel(row.status)">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500" x-text="row.expired_at"></td>
                                <td class="px-4 py-3 text-xs text-gray-500" x-text="row.created_at"></td>
                                <td class="px-4 py-3 text-center">
                                    <template x-if="row.status === 'pending'">
                                        <button @click="verifyTopup(row)"
                                            class="bg-green-500 hover:bg-green-600 text-white text-[11px] font-semibold py-1.5 px-3 rounded-lg shadow-sm transition flex items-center gap-1 mx-auto cursor-pointer"
                                            :disabled="row._verifying">
                                            <template x-if="row._verifying">
                                                <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="!row._verifying">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 6 9 17l-5-5"/>
                                                </svg>
                                            </template>
                                            <span x-text="row._verifying ? 'Proses...' : 'Verifikasi'"></span>
                                        </button>
                                    </template>
                                    <template x-if="row.status === 'success'">
                                        <span class="text-xs text-green-600 font-semibold">✓ Terverifikasi</span>
                                    </template>
                                    <template x-if="row.status === 'expired'">
                                        <span class="text-xs text-red-500 font-semibold">Kadaluarsa</span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </template>

                    {{-- Empty --}}
                    <template x-if="!loading && rows.length === 0">
                        <tr>
                            <td colspan="11" class="text-center py-12 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                Tidak ada data topup ditemukan.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3" x-show="meta.last_page > 1">
            <p class="text-xs text-gray-500">
                Menampilkan <span x-text="meta.from" class="font-semibold"></span> - <span x-text="meta.to" class="font-semibold"></span> dari <span x-text="meta.total" class="font-semibold"></span> data
            </p>
            <div class="flex items-center gap-1">
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition cursor-pointer"
                    :class="currentPage <= 1 ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                    &laquo; Prev
                </button>
                <template x-for="p in paginationPages()" :key="p">
                    <button @click="goToPage(p)" x-text="p"
                        class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition cursor-pointer"
                        :class="p === currentPage ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                    </button>
                </template>
                <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= meta.last_page"
                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition cursor-pointer"
                    :class="currentPage >= meta.last_page ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                    Next &raquo;
                </button>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div x-show="toast.show" x-cloak x-transition
        class="fixed top-6 right-6 z-50 max-w-sm shadow-lg rounded-xl p-4 flex items-center gap-3"
        :class="toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
        <template x-if="toast.type === 'success'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </template>
        <template x-if="toast.type === 'error'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
            </svg>
        </template>
        <span x-text="toast.message" class="text-sm font-semibold"></span>
    </div>

</div>
@endsection

@push('script')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('topupTable', () => ({
        rows: [],
        loading: false,
        search: '',
        statusFilter: '',
        currentPage: 1,
        meta: { from: 0, to: 0, total: 0, last_page: 1, current_page: 1 },
        toast: { show: false, message: '', type: 'success' },

        statusTabs: [
            { value: '', label: 'Semua', activeClass: 'bg-green-600 text-white border-green-600 shadow-sm' },
            { value: 'pending', label: 'Pending', activeClass: 'bg-yellow-500 text-white border-yellow-500 shadow-sm' },
            { value: 'success', label: 'Success', activeClass: 'bg-emerald-600 text-white border-emerald-600 shadow-sm' },
            { value: 'expired', label: 'Expired', activeClass: 'bg-red-500 text-white border-red-500 shadow-sm' },
        ],

        async fetchData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: 15,
                });
                if (this.search) params.append('search', this.search);
                if (this.statusFilter) params.append('status', this.statusFilter);

                const res = await fetch(`{{ route('dashboard.topup.index') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                const data = await res.json();
                this.rows = data.data.map(r => ({ ...r, _verifying: false }));
                this.meta = {
                    from: data.from,
                    to: data.to,
                    total: data.total,
                    last_page: data.last_page,
                    current_page: data.current_page,
                };
            } catch (e) {
                console.error('Fetch error', e);
            } finally {
                this.loading = false;
            }
        },

        goToPage(page) {
            if (page < 1 || page > this.meta.last_page) return;
            this.currentPage = page;
            this.fetchData();
        },

        paginationPages() {
            const pages = [];
            const total = this.meta.last_page;
            const current = this.currentPage;
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        formatRp(val) {
            if (!val && val !== 0) return '-';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val));
        },

        statusLabel(status) {
            const map = { pending: 'Pending', success: 'Success', expired: 'Expired' };
            return map[status] || status;
        },

        async verifyTopup(row) {
            if (!confirm(`Verifikasi topup untuk ${row.student_name} sebesar ${this.formatRp(row.total_nominal)}?\n\nSaldo siswa akan bertambah otomatis.`)) return;

            row._verifying = true;
            try {
                const res = await fetch(`{{ route('dashboard.topup.verify') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ id: row.id }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.showToast(data.message || 'Berhasil diverifikasi!', 'success');
                    this.fetchData();
                } else {
                    this.showToast(data.message || 'Gagal verifikasi.', 'error');
                }
            } catch (e) {
                this.showToast('Terjadi kesalahan jaringan.', 'error');
            } finally {
                row._verifying = false;
            }
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3500);
        },
    }));
});
</script>
@endpush
