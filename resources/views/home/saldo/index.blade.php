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
<div class="flex flex-col gap-6" x-data="{
    modalTransaksi: false,
    selectedStudent: null,
    tipe: 'kredit',
    nominal: '',
    keterangan: '',
    openTransaksi(student) {
        this.selectedStudent = student;
        this.tipe = 'kredit';
        this.nominal = '';
        this.keterangan = '';
        this.modalTransaksi = true;
    }
}">
    <!-- Header & Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-md p-6 text-white flex items-center justify-between col-span-1 md:col-span-2">
            <div>
                <p class="text-green-100 text-xs font-semibold uppercase tracking-wider">Total Saldo Terhimpun</p>
                <h2 class="text-2xl md:text-3xl font-extrabold mt-1">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h2>
                <p class="text-xs text-green-100 mt-2">Akumulasi saldo seluruh santri / murid pesantren aktif</p>
            </div>
            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm hidden sm:block">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet">
                    <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" />
                    <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex flex-col justify-between">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Santri / Murid Pesantren</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ $items->total() }} Data</h3>
            <p class="text-xs text-gray-400 mt-2">Daftar saldo dan mutasi kas murid pesantren</p>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex flex-col">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('dashboard.saldo.index') }}" class="mb-4 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full md:w-3/4">
                <div class="w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari {{ config('app.school_mode') ? 'NIS' : 'NIM' }} atau Nama..."
                        class="w-full border border-gray-300 ring-0 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]" />
                </div>

                <div class="w-full sm:w-48">
                    <select name="kelas" onchange="this.form.submit()"
                        class="w-full border border-gray-300 ring-0 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                        <option value="">Semua {{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</option>
                        @foreach($classes as $kelas)
                        <option value="{{ $kelas->name }}" {{ request('kelas') == $kelas->name ? 'selected' : '' }}>{{ $kelas->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-2.5 px-4 rounded-xl transition">
                    Cari
                </button>

                @if(request('search') || request('kelas'))
                <a href="{{ route('dashboard.saldo.index') }}" class="text-xs text-gray-500 hover:text-gray-700 underline py-2">
                    Reset Filter
                </a>
                @endif
            </div>
        </form>

        <!-- Table Data -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-green-600 text-left text-white select-none">
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3">{{ config('app.school_mode') ? 'NIS' : 'NIM' }}</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">{{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Saldo Saat Ini</th>
                        <th class="px-4 py-3 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($items as $index => $item)
                        @php
                            $currentSaldo = $item->saldo->nominal ?? 0;
                            $kelasName = config('app.school_mode') ? ($item->reg->kelas->name ?? '-') : ($item->reg->prodi->name ?? '-');
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center text-xs text-gray-500">
                                {{ $items->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                {{ $item->nis }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $item->name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $kelasName }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    Pesantren
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ $currentSaldo > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                Rp {{ number_format($currentSaldo, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Transaksi -->
                                    <button type="button"
                                        @click="openTransaksi({ id: {{ $item->id }}, name: '{{ addslashes($item->name) }}', saldo: {{ $currentSaldo }} })"
                                        class="cursor-pointer bg-green-500 hover:bg-green-600 text-white text-[11px] font-semibold py-1 px-2.5 rounded-lg shadow-sm transition flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 5v14M5 12h14"/>
                                        </svg>
                                        Transaksi
                                    </button>

                                    <!-- Tombol Riwayat (Log) -->
                                    <a href="{{ route('dashboard.saldo.show', $item->id) }}"
                                        class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white text-[11px] font-semibold py-1 px-2.5 rounded-lg shadow-sm transition flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20v-6M6 20V10M18 20V4"/>
                                        </svg>
                                        Riwayat
                                    </a>

                                    @if($currentSaldo > 0)
                                    <!-- Reset Saldo -->
                                    <form action="{{ route('dashboard.saldo.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin mereset saldo murid ini ke Rp 0?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1 cursor-pointer transition" title="Reset Saldo">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/>
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-400">Tidak ada data murid pesantren ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Modal Transaksi Saldo (Kredit / Debit) -->
    <div x-show="modalTransaksi" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.away="modalTransaksi = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Transaksi Saldo</h3>
                <button type="button" @click="modalTransaksi = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('dashboard.saldo.store') }}" method="POST" class="mt-4 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="students_id" :value="selectedStudent?.id">

                <!-- Info Murid -->
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 font-medium">{{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}</p>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Pesantren</span>
                    </div>
                    <p class="font-bold text-gray-800" x-text="selectedStudent?.name"></p>
                    <p class="text-xs text-green-600 font-semibold mt-1">
                        Saldo Saat Ini: Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedStudent?.saldo || 0)"></span>
                    </p>
                </div>

                <!-- Tipe Transaksi -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tipe Transaksi</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-2 p-2.5 border rounded-xl cursor-pointer transition text-sm font-semibold"
                            :class="tipe === 'kredit' ? 'border-green-600 bg-green-50 text-green-700 ring-2 ring-green-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" name="tipe" value="kredit" x-model="tipe" class="hidden">
                            <span class="text-green-600 font-bold">+</span> Kredit (Top Up)
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 border rounded-xl cursor-pointer transition text-sm font-semibold"
                            :class="tipe === 'debit' ? 'border-red-600 bg-red-50 text-red-700 ring-2 ring-red-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" name="tipe" value="debit" x-model="tipe" class="hidden">
                            <span class="text-red-600 font-bold">-</span> Debit (Tarik)
                        </label>
                    </div>
                </div>

                <!-- Nominal -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nominal (Rp)</label>
                    <input type="number" name="nominal" x-model="nominal" min="1" required placeholder="Contoh: 50000"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" x-model="keterangan" placeholder="Contoh: Setoran uang saku, bayar kantin, dll"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
                    <button type="button" @click="modalTransaksi = false"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition cursor-pointer">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
