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
<div class="flex flex-col gap-6" x-data="kasTable()" x-init="fetchData()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $title }}</h1>
            <p class="text-xs text-gray-500 mt-1">Pencatatan pemasukan dan pengeluaran kas</p>
        </div>
        <button @click="openForm()" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2.5 px-5 rounded-xl transition flex items-center gap-2 shadow-sm cursor-pointer self-start sm:self-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Kas
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-md p-5 text-white">
            <p class="text-green-100 text-xs font-semibold uppercase tracking-wider">Total Pemasukan</p>
            <h2 class="text-xl sm:text-2xl font-extrabold mt-1" x-text="formatRp(summary.pemasukan)"></h2>
        </div>
        <div class="bg-gradient-to-r from-red-500 to-rose-600 rounded-2xl shadow-md p-5 text-white">
            <p class="text-red-100 text-xs font-semibold uppercase tracking-wider">Total Pengeluaran</p>
            <h2 class="text-xl sm:text-2xl font-extrabold mt-1" x-text="formatRp(summary.pengeluaran)"></h2>
        </div>
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-md p-5 text-white">
            <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider">Saldo Kas</p>
            <h2 class="text-xl sm:text-2xl font-extrabold mt-1" x-text="formatRp(summary.saldo)"></h2>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white rounded-2xl shadow-md p-4 sm:p-6 border border-gray-100">
        {{-- Filters --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                {{-- Tipe Tabs --}}
                <template x-for="tab in tipeTabs" :key="tab.value">
                    <button type="button" @click="tipeFilter = tab.value; currentPage = 1; fetchData()"
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer border"
                        :class="tipeFilter === tab.value ? tab.activeClass : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200'">
                        <span x-text="tab.label"></span>
                    </button>
                </template>

                {{-- Filter Bulan --}}
                <input type="month" x-model="bulanFilter" @change="currentPage = 1; fetchData()"
                    class="border border-gray-300 rounded-xl px-3 py-1.5 text-xs focus:outline-green-500 focus:ring-1 focus:ring-green-500 shadow-sm cursor-pointer" />
                <template x-if="bulanFilter">
                    <button @click="bulanFilter = ''; currentPage = 1; fetchData()" class="text-xs text-gray-500 hover:text-gray-700 underline cursor-pointer">
                        Reset
                    </button>
                </template>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-72">
                <input type="text" x-model="search" @input.debounce.400ms="currentPage = 1; fetchData()"
                    placeholder="Cari keterangan..."
                    class="w-full border border-gray-300 ring-0 rounded-xl px-4 py-2 text-sm focus:outline-green-500 focus:ring-1 focus:ring-green-500 shadow-sm" />
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full bg-white text-sm">
                <thead>
                    <tr class="bg-green-600 text-left text-white">
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center w-12">No</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Tanggal</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Tipe</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Keterangan</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Kategori</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-right">Nominal</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Referensi</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    {{-- Loading --}}
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <svg class="animate-spin h-6 w-6 text-green-500 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Memuat data...
                            </td>
                        </tr>
                    </template>

                    {{-- Data --}}
                    <template x-if="!loading && rows.length > 0">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-4 py-3 text-center text-xs text-gray-500" x-text="meta.from + index"></td>
                                <td class="px-4 py-3 text-gray-700 text-xs" x-text="formatDate(row.tanggal)"></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold"
                                        :class="row.tipe === 'pemasukan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                        x-text="row.tipe === 'pemasukan' ? '↑ Pemasukan' : '↓ Pengeluaran'">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-800 max-w-xs truncate" x-text="row.keterangan"></td>
                                <td class="px-4 py-3 text-gray-500 text-xs" x-text="row.kategori"></td>
                                <td class="px-4 py-3 text-right font-semibold"
                                    :class="row.tipe === 'pemasukan' ? 'text-green-600' : 'text-red-600'"
                                    x-text="(row.tipe === 'pengeluaran' ? '- ' : '+ ') + formatRp(row.nominal)">
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400" x-text="row.referensi"></td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="editRow(row)"
                                            class="bg-blue-500 hover:bg-blue-600 text-white text-[11px] font-semibold py-1 px-2.5 rounded-lg shadow-sm transition flex items-center gap-1 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                            </svg>
                                            Edit
                                        </button>
                                        <button @click="deleteRow(row)"
                                            class="bg-red-500 hover:bg-red-600 text-white text-[11px] font-semibold py-1 px-2.5 rounded-lg shadow-sm transition flex items-center gap-1 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    {{-- Empty --}}
                    <template x-if="!loading && rows.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                Tidak ada data kas ditemukan.
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

    {{-- Modal Form (Create / Edit) --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.away="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800" x-text="editingId ? 'Edit Kas' : 'Tambah Kas'"></h3>
                <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form @submit.prevent="submitForm()" class="mt-4 flex flex-col gap-4">
                {{-- Tipe --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tipe Transaksi</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-2 p-2.5 border rounded-xl cursor-pointer transition text-sm font-semibold"
                            :class="form.tipe === 'pemasukan' ? 'border-green-600 bg-green-50 text-green-700 ring-2 ring-green-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" value="pemasukan" x-model="form.tipe" class="hidden">
                            <span class="text-green-600 font-bold">↑</span> Pemasukan
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 border rounded-xl cursor-pointer transition text-sm font-semibold"
                            :class="form.tipe === 'pengeluaran' ? 'border-red-600 bg-red-50 text-red-700 ring-2 ring-red-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" value="pengeluaran" x-model="form.tipe" class="hidden">
                            <span class="text-red-600 font-bold">↓</span> Pengeluaran
                        </label>
                    </div>
                </div>

                {{-- Nominal --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nominal (Rp)</label>
                    <input type="number" x-model="form.nominal" min="1" required placeholder="Contoh: 500000"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Keterangan</label>
                    <input type="text" x-model="form.keterangan" required placeholder="Contoh: Beli ATK, SPP Januari, dll"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Kategori (Opsional)</label>
                    <input type="text" x-model="form.kategori" placeholder="Contoh: Operasional, Gaji, SPP"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal</label>
                    <input type="date" x-model="form.tanggal" required
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                </div>

                {{-- Action --}}
                <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
                    <button type="button" @click="showModal = false"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition cursor-pointer flex items-center gap-2">
                        <template x-if="submitting">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast --}}
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
    Alpine.data('kasTable', () => ({
        rows: [],
        loading: false,
        search: '',
        tipeFilter: '',
        bulanFilter: '',
        currentPage: 1,
        meta: { from: 0, to: 0, total: 0, last_page: 1 },
        summary: { pemasukan: 0, pengeluaran: 0, saldo: 0 },
        toast: { show: false, message: '', type: 'success' },

        // Modal
        showModal: false,
        editingId: null,
        submitting: false,
        form: { tipe: 'pemasukan', nominal: '', keterangan: '', kategori: '', tanggal: new Date().toISOString().split('T')[0] },

        tipeTabs: [
            { value: '', label: 'Semua', activeClass: 'bg-green-600 text-white border-green-600 shadow-sm' },
            { value: 'pemasukan', label: '↑ Pemasukan', activeClass: 'bg-emerald-600 text-white border-emerald-600 shadow-sm' },
            { value: 'pengeluaran', label: '↓ Pengeluaran', activeClass: 'bg-red-500 text-white border-red-500 shadow-sm' },
        ],

        async fetchData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.currentPage, per_page: 15 });
                if (this.search) params.append('search', this.search);
                if (this.tipeFilter) params.append('tipe', this.tipeFilter);
                if (this.bulanFilter) params.append('bulan', this.bulanFilter);

                const res = await fetch(`{{ route('dashboard.kas.index') }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.rows = data.data;
                this.meta = { from: data.from, to: data.to, total: data.total, last_page: data.last_page };
                this.summary = {
                    pemasukan: data.total_pemasukan,
                    pengeluaran: data.total_pengeluaran,
                    saldo: data.saldo_kas,
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
            if (!val && val !== 0) return 'Rp 0';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val));
        },

        formatDate(d) {
            if (!d) return '-';
            const dt = new Date(d + 'T00:00:00');
            return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        openForm() {
            this.editingId = null;
            this.form = { tipe: 'pemasukan', nominal: '', keterangan: '', kategori: '', tanggal: new Date().toISOString().split('T')[0] };
            this.showModal = true;
        },

        editRow(row) {
            this.editingId = row.id;
            this.form = {
                tipe: row.tipe,
                nominal: Math.round(row.nominal),
                keterangan: row.keterangan,
                kategori: row.kategori === '-' ? '' : row.kategori,
                tanggal: row.tanggal,
            };
            this.showModal = true;
        },

        async submitForm() {
            this.submitting = true;
            try {
                const url = this.editingId
                    ? `{{ url('dashboard/kas') }}/${this.editingId}`
                    : `{{ route('dashboard.kas.store') }}`;
                const method = this.editingId ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (res.ok) {
                    this.showToast(data.message || 'Berhasil!', 'success');
                    this.showModal = false;
                    this.fetchData();
                } else {
                    this.showToast(data.message || 'Gagal menyimpan.', 'error');
                }
            } catch (e) {
                this.showToast('Terjadi kesalahan jaringan.', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async deleteRow(row) {
            if (!confirm(`Hapus data kas "${row.keterangan}"?`)) return;
            try {
                const res = await fetch(`{{ url('dashboard/kas') }}/${row.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await res.json();
                if (res.ok) {
                    this.showToast(data.message || 'Berhasil dihapus!', 'success');
                    this.fetchData();
                } else {
                    this.showToast(data.message || 'Gagal menghapus.', 'error');
                }
            } catch (e) {
                this.showToast('Terjadi kesalahan jaringan.', 'error');
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
