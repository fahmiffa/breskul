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
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="verificationPayment('{{ $defaultMonth }}')">
    <!-- Filter Bar -->
    <div class="mb-4 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3">
        <div class="flex flex-wrap items-center gap-2 w-full md:w-3/4">
            <!-- Search Input -->
            <div class="w-full sm:w-56">
                <input type="text" x-model="search" @input="handleSearch()" placeholder="Cari {{ config('app.school_mode') ? 'NIS' : 'NIM' }} atau Nama..."
                    class="w-full border border-gray-300 ring-0 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]" />
            </div>

            <!-- Filter Kelas / Prodi -->
            <div class="w-full sm:w-44">
                <select x-model="selectedKelas" @change="handleFilterChange()"
                    class="w-full border border-gray-300 ring-0 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                    <option value="">Semua {{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</option>
                    @foreach($classes as $kelas)
                    <option value="{{ $kelas->name }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Bulan (Default bulan berjalan) -->
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <span class="text-xs font-medium text-gray-500">Bulan:</span>
                <input type="month" x-model="selectedMonth" @change="handleFilterChange()"
                    class="border border-gray-300 ring-0 rounded-xl px-3 py-1.5 text-sm focus:outline-[#177245]" />
            </div>

            <!-- Per Page -->
            <div class="w-auto">
                <select x-model="perPage" @change="handleFilterChange()"
                    class="border border-gray-300 ring-0 rounded-xl px-2.5 py-2 text-sm focus:outline-[#177245]">
                    <option value="10">10 data</option>
                    <option value="25">25 data</option>
                    <option value="50">50 data</option>
                    <option value="100">100 data</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button @click="showTambahKelas = true" :disabled="selectedItems.length === 0"
                class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-2xl focus:outline-none focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap shadow-sm">
                + Tambah Pembayaran
            </button>
        </div>
    </div>

    <!-- Action & Selection Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <div class="flex items-center gap-3">
            <button @click="selectAll()" :disabled="rows.length === 0"
                class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-text="isAllSelected() ? 'Batalkan Semua' : 'Pilih Semua'"></span>
            </button>
            <div class="text-sm font-medium text-red-500">
                {{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }} Terpilih : <span x-text="selectedItems.length" class="font-bold"></span>
            </div>
        </div>

        <div class="flex items-center gap-2" x-show="isLoading" x-cloak>
            <svg class="animate-spin h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs text-gray-500">Memuat data...</span>
        </div>
    </div>

    <!-- Server-Side DataTable -->
    <div class="overflow-x-auto relative">
        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead>
                <tr class="bg-green-500 text-left text-white select-none">
                    <th class="px-4 py-2.5 w-12 text-center">Opsi</th>
                    <th @click="sortBy('nis')" class="cursor-pointer px-4 py-2.5 hover:bg-green-600 transition">
                        <div class="flex items-center gap-1">
                            <span>{{ config('app.school_mode') ? 'NIS' : 'NIM' }}</span>
                            <span class="text-[10px]" x-show="sortColumn === 'nis'" x-text="sortAsc ? '▲' : '▼'"></span>
                        </div>
                    </th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-2.5 hover:bg-green-600 transition">
                        <div class="flex items-center gap-1">
                            <span>Nama</span>
                            <span class="text-[10px]" x-show="sortColumn === 'name'" x-text="sortAsc ? '▲' : '▼'"></span>
                        </div>
                    </th>
                    <th class="px-4 py-2.5">{{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</th>
                    <th class="px-4 py-2.5">Data Tagihan & Pembayaran</th>
                </tr>
            </thead>
            <tbody class="relative">
                <template x-for="(row, index) in rows" :key="row.id">
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition">
                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" :checked="selectedItems.includes(row.head)"
                                @change="toggleItem(row.head, $event)" :value="row.head" class="rounded cursor-pointer">
                        </td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-700" x-text="row.nis"></td>
                        <td class="px-4 py-2 font-medium text-gray-800" x-text="row.name"></td>
                        <td class="px-4 py-2 text-gray-600" x-text="row.kelas || '-'"></td>
                        <td class="px-4 py-2">
                            <div class="flex flex-col gap-1.5">
                                <template x-for="(val, bIndex) in (row.bill || [])" :key="bIndex">
                                    <div class="flex flex-wrap items-center gap-2 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-lg">
                                        <span class="text-xs font-semibold text-gray-800" x-text="val.name"></span>
                                        <span class="text-xs text-gray-600" x-text="'Rp ' + (val.nominal || '0')"></span>
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                            :class="val.status === 'Lunas' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                            x-text="val.status"></span>
                                        <template x-if="val.status === 'Tagihan'">
                                            <button @click="verifyBill(val.bill)"
                                                class="bg-green-600 hover:bg-green-700 text-white text-[10px] font-medium px-2 py-0.5 rounded shadow-sm cursor-pointer transition">
                                                Verifikasi
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!row.bill || row.bill.length === 0">
                                    <span class="text-xs text-gray-400 italic">Belum ada tagihan di bulan ini</span>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="rows.length === 0 && !isLoading">
                    <td colspan="5" class="text-center py-6 text-gray-400">Tidak ada data ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Server-Side Pagination Controls -->
    <div class="flex flex-col sm:flex-row justify-between items-center mt-4 gap-2 text-sm text-gray-600">
        <div>
            Menampilkan <span class="font-semibold text-gray-800" x-text="from"></span> sampai <span class="font-semibold text-gray-800" x-text="to"></span> dari <span class="font-semibold text-gray-800" x-text="totalRows"></span> data
        </div>

        <div class="flex items-center gap-2">
            <button @click="prevPage()" :disabled="currentPage === 1 || isLoading"
                class="px-3 py-1.5 text-xs text-white font-medium rounded-xl bg-green-500 hover:bg-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition">
                Prev
            </button>

            <span class="text-xs">Halaman <span class="font-bold" x-text="currentPage"></span> dari <span class="font-bold" x-text="totalPages"></span></span>

            <button @click="nextPage()" :disabled="currentPage >= totalPages || isLoading"
                class="px-3 py-1.5 text-xs text-white font-medium rounded-xl bg-green-500 hover:bg-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition">
                Next
            </button>
        </div>
    </div>

    <!-- Modal Tambah Pembayaran -->
    <div x-show="showTambahKelas" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition>
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4"
            @click.away="showTambahKelas = false">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                Tambah Bayar untuk <span class="text-green-600" x-text="selectedItems.length"></span> {{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}
            </h2>

            <form @submit.prevent="assignPay">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Pembayaran</label>
                    <select x-model="selectedClass" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">-- Pilih Pembayaran --</option>
                        @foreach (\App\Models\Payment::latest()->get() as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} (Rp {{ number_format($item->nominal, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showTambahKelas = false"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" :disabled="isLoading">
                        Batal
                    </button>

                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                        :disabled="isLoading || !selectedClass">
                        <span x-show="!isLoading">Simpan</span>
                        <span x-show="isLoading">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection