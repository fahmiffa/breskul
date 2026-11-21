@extends('base.layout')
@section('title', 'Dashboard Absensi')
@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush
@section('content')
<div class="flex flex-col bg-white rounded-2xl shadow-md p-3.5 sm:p-6 border border-gray-100" 
     x-data="{
         ...absensiTable({{ json_encode($items) }}, '{{ $type ?? '' }}'),
         exportModalOpen: false,
         exportType: '{{ $type === 'murid' ? 'murid' : 'karyawan' }}',
         exportMonth: '{{ !empty($start) ? substr($start, 0, 7) : date('Y-m') }}'
     }">

    <form action="{{ route('dashboard.absensi') }}" method="GET" class="mb-5 flex flex-wrap items-end gap-3 sm:gap-4 p-3.5 sm:p-4 bg-gray-50 rounded-xl border border-gray-100">
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-[11px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $start }}"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-xs bg-white w-full" />
        </div>
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-[11px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ $end }}"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-xs bg-white w-full" />
        </div>
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-[11px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider">Tipe</label>
            <select name="type"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-xs bg-white cursor-pointer w-full sm:min-w-[150px]">
                <option value="" {{ empty($type) ? 'selected' : '' }}>Semua</option>
                <option value="murid" {{ ($type ?? '') == 'murid' ? 'selected' : '' }}>Murid</option>
                <option value="karyawan" {{ ($type ?? '') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
            </select>
        </div>
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <a href="{{ route('dashboard.absensi') }}" class="bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 font-bold py-2 px-4 rounded-lg transition duration-200 text-sm flex items-center justify-center shadow-xs">
                Reset
            </a>
            <button type="button" @click="exportModalOpen = true" class="col-span-2 sm:col-span-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </button>
        </div>
    </form>

    {{-- Filter Cepat (Tabs) & Pencarian --}}
    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="filterType = ''"
                class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer border"
                :class="filterType === '' ? 'bg-green-600 text-white border-green-600 shadow-sm' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200'">
                Semua
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                    :class="filterType === '' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-600'"
                    x-text="rows.length"></span>
            </button>
            <button type="button" @click="filterType = 'murid'"
                class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer border"
                :class="filterType === 'murid' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200'">
                {{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                    :class="filterType === 'murid' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-600'"
                    x-text="countMurid()"></span>
            </button>
            <button type="button" @click="filterType = 'karyawan'"
                class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer border"
                :class="filterType === 'karyawan' ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200'">
                Karyawan
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                    :class="filterType === 'karyawan' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-600'"
                    x-text="countKaryawan()"></span>
            </button>
        </div>

        <div class="w-full md:w-80">
            <input type="text" x-model="search" placeholder="Cari nama atau status..."
                class="w-full border border-gray-300 ring-0 rounded-xl px-4 py-2 text-sm focus:outline-green-500 focus:ring-1 focus:ring-green-500 shadow-sm" />
        </div>
    </div>

    {{-- Tampilan Tabel Desktop (Layar md ke atas >= 768px) --}}
    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full bg-white text-sm">
            <thead>
                <tr class="bg-green-600 text-left text-white">
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold w-12 text-center">No</th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-3 text-xs uppercase tracking-wider font-semibold hover:bg-green-700 transition-colors">
                        <div class="flex items-center gap-1">
                            <span>Nama</span>
                            <span class="text-xs" x-show="sortColumn === 'name'" x-text="sortAsc ? '↑' : '↓'"></span>
                        </div>
                    </th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center w-28">Tipe</th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Absensi Masuk</th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Absensi Pulang</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="(row, index) in paginatedData()" :key="row.id">
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3 text-gray-500 text-xs text-center font-medium" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                        <td class="px-4 py-3">
                            <template x-if="row.employee_id">
                                <a :href="'/dashboard/absensi/karyawan/' + row.employee_id" 
                                   class="font-semibold text-green-700 hover:text-green-800 hover:underline inline-flex items-center gap-1.5 transition-colors group cursor-pointer"
                                   title="Lihat detail grafik absensi karyawan">
                                    <span x-text="row.name || (row.employee ? row.employee.name : '-')"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-green-600 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </template>
                            <template x-if="!row.employee_id">
                                <div class="font-semibold text-gray-800" x-text="row.name || (row.murid ? row.murid.name : '-')"></div>
                            </template>
                            <div class="text-[11px] text-gray-400 font-normal mt-0.5" x-show="row.date" x-text="row.date"></div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold inline-block"
                                :class="row.employee_id ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                                x-text="row.employee_id ? 'Karyawan' : 'Murid'">
                            </span>
                        </td>
                        {{-- Kolom Absensi Masuk (Foto & Waktu) --}}
                        <td class="px-4 py-3">
                            <template x-if="row.masuk">
                                <div class="flex items-center gap-3">
                                    <template x-if="row.masuk.img">
                                        <div @click="openPhotoModal({ name: row.name, tipe: row.tipe, employee_id: row.employee_id, img: row.masuk.img, time: row.masuk.full_time, status: 'Masuk' })" 
                                             class="group relative inline-block cursor-pointer rounded-lg overflow-hidden border border-gray-200 shadow-xs hover:shadow-md transition-all shrink-0">
                                            <img :src="row.masuk.img.startsWith('http') || row.masuk.img.startsWith('/') ? row.masuk.img : '/storage/' + row.masuk.img" 
                                                 class="h-11 w-11 object-cover transition-transform duration-200 group-hover:scale-110" 
                                                 alt="Foto Masuk">
                                            <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!row.masuk.img">
                                        <div class="h-11 w-11 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0" title="Tidak ada foto">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </template>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-xs sm:text-sm" x-text="row.masuk.time"></span>
                                        <template x-if="row.masuk.late_time">
                                            <span class="mt-0.5 inline-block w-fit px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600 border border-red-200"
                                                x-text="row.masuk.late_time.replace(/(\d+(?:\.\d+)?)/, m => Math.ceil(Number(m)))">
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!row.masuk">
                                <span class="text-xs text-gray-400 italic font-medium">-</span>
                            </template>
                        </td>
                        {{-- Kolom Absensi Pulang (Foto & Waktu) --}}
                        <td class="px-4 py-3">
                            <template x-if="row.pulang">
                                <div class="flex items-center gap-3">
                                    <template x-if="row.pulang.img">
                                        <div @click="openPhotoModal({ name: row.name, tipe: row.tipe, employee_id: row.employee_id, img: row.pulang.img, time: row.pulang.full_time, status: 'Pulang' })" 
                                             class="group relative inline-block cursor-pointer rounded-lg overflow-hidden border border-gray-200 shadow-xs hover:shadow-md transition-all shrink-0">
                                            <img :src="row.pulang.img.startsWith('http') || row.pulang.img.startsWith('/') ? row.pulang.img : '/storage/' + row.pulang.img" 
                                                 class="h-11 w-11 object-cover transition-transform duration-200 group-hover:scale-110" 
                                                 alt="Foto Pulang">
                                            <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!row.pulang.img">
                                        <div class="h-11 w-11 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0" title="Tidak ada foto">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </template>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-xs sm:text-sm" x-text="row.pulang.time"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!row.pulang">
                                <span class="text-xs text-gray-400 italic font-medium">-</span>
                            </template>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="5" class="text-center py-10 text-gray-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm">Tidak ada data absensi yang sesuai filter.</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Tampilan Mobile Cards (Hanya Tampil di Layar < 768px) --}}
    <div class="md:hidden flex flex-col gap-3">
        <template x-for="(row, index) in paginatedData()" :key="row.id">
            <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs p-3.5 flex flex-col gap-3 hover:border-green-300 transition-colors">
                <!-- Header Kartu: Avatar Nama, Tipe & Tanggal -->
                <div class="flex items-start justify-between gap-2 border-b border-gray-100 pb-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 shadow-xs"
                             :class="row.employee_id ? 'bg-gradient-to-br from-purple-500 to-indigo-600 text-white' : 'bg-gradient-to-br from-blue-500 to-cyan-600 text-white'">
                            <span x-text="((row.name || (row.murid ? row.murid.name : (row.employee ? row.employee.name : 'U'))).substring(0, 2)).toUpperCase()"></span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <template x-if="row.employee_id">
                                <a :href="'/dashboard/absensi/karyawan/' + row.employee_id" 
                                   class="font-bold text-sm text-green-700 hover:text-green-800 hover:underline flex items-center gap-1 group truncate"
                                   title="Lihat detail grafik absensi karyawan">
                                    <span class="truncate" x-text="row.name || (row.employee ? row.employee.name : '-')"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-green-600 shrink-0 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </template>
                            <template x-if="!row.employee_id">
                                <div class="font-bold text-sm text-gray-800 truncate" x-text="row.name || (row.murid ? row.murid.name : '-')"></div>
                            </template>
                            <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5" x-show="row.date">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span x-text="row.date"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
                            :class="row.employee_id ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                            x-text="row.employee_id ? 'Karyawan' : 'Murid'">
                        </span>
                        <span class="text-[11px] text-gray-400 font-mono font-medium" x-text="'#' + (((currentPage - 1) * perPage) + index + 1)"></span>
                    </div>
                </div>

                <!-- Bagian Foto & Jam: 2 Kolom Sejajar (Masuk & Pulang) -->
                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Kotak Absensi Masuk -->
                    <div class="bg-gray-50/90 rounded-xl p-2.5 border border-gray-100 flex flex-col justify-between gap-2">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="flex items-center gap-1 text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Masuk
                            </span>
                            <template x-if="row.masuk">
                                <span class="font-bold text-gray-800 text-xs" x-text="row.masuk.time"></span>
                            </template>
                        </div>

                        <!-- Foto Masuk -->
                        <template x-if="row.masuk && row.masuk.img">
                            <div @click="openPhotoModal({ name: row.name, tipe: row.tipe, employee_id: row.employee_id, img: row.masuk.img, time: row.masuk.full_time, status: 'Masuk' })"
                                 class="relative w-full aspect-4/3 rounded-xl overflow-hidden border border-gray-200 shadow-xs cursor-pointer group active:scale-95 transition-transform bg-gray-100">
                                <img :src="row.masuk.img.startsWith('http') || row.masuk.img.startsWith('/') ? row.masuk.img : '/storage/' + row.masuk.img" 
                                     class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105" 
                                     alt="Foto Masuk">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end justify-between p-1.5">
                                    <span class="text-[10px] text-white font-medium flex items-center gap-1 drop-shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Foto
                                    </span>
                                    <span class="bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded-full backdrop-blur-xs flex items-center gap-0.5 font-medium">
                                        🔍 Perbesar
                                    </span>
                                </div>
                            </div>
                        </template>
                        <template x-if="row.masuk && !row.masuk.img">
                            <div class="w-full aspect-4/3 rounded-xl bg-gray-100 border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] text-gray-400 font-medium">Tanpa Foto</span>
                            </div>
                        </template>
                        <template x-if="!row.masuk">
                            <div class="w-full aspect-4/3 rounded-xl bg-gray-100/60 border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 gap-1">
                                <span class="text-xs text-gray-400 italic">Belum Masuk</span>
                            </div>
                        </template>

                        <!-- Badge Terlambat -->
                        <template x-if="row.masuk && row.masuk.late_time">
                            <div class="mt-0.5">
                                <span class="inline-block w-full text-center px-1.5 py-0.5 rounded-lg text-[10px] font-bold bg-red-100 text-red-600 border border-red-200"
                                    x-text="row.masuk.late_time.replace(/(\d+(?:\.\d+)?)/, m => Math.ceil(Number(m)))">
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Kotak Absensi Pulang -->
                    <div class="bg-gray-50/90 rounded-xl p-2.5 border border-gray-100 flex flex-col justify-between gap-2">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="flex items-center gap-1 text-blue-700">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Pulang
                            </span>
                            <template x-if="row.pulang">
                                <span class="font-bold text-gray-800 text-xs" x-text="row.pulang.time"></span>
                            </template>
                        </div>

                        <!-- Foto Pulang -->
                        <template x-if="row.pulang && row.pulang.img">
                            <div @click="openPhotoModal({ name: row.name, tipe: row.tipe, employee_id: row.employee_id, img: row.pulang.img, time: row.pulang.full_time, status: 'Pulang' })"
                                 class="relative w-full aspect-4/3 rounded-xl overflow-hidden border border-gray-200 shadow-xs cursor-pointer group active:scale-95 transition-transform bg-gray-100">
                                <img :src="row.pulang.img.startsWith('http') || row.pulang.img.startsWith('/') ? row.pulang.img : '/storage/' + row.pulang.img" 
                                     class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105" 
                                     alt="Foto Pulang">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end justify-between p-1.5">
                                    <span class="text-[10px] text-white font-medium flex items-center gap-1 drop-shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Foto
                                    </span>
                                    <span class="bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded-full backdrop-blur-xs flex items-center gap-0.5 font-medium">
                                        🔍 Perbesar
                                    </span>
                                </div>
                            </div>
                        </template>
                        <template x-if="row.pulang && !row.pulang.img">
                            <div class="w-full aspect-4/3 rounded-xl bg-gray-100 border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] text-gray-400 font-medium">Tanpa Foto</span>
                            </div>
                        </template>
                        <template x-if="!row.pulang">
                            <div class="w-full aspect-4/3 rounded-xl bg-gray-100/60 border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 gap-1">
                                <span class="text-xs text-gray-400 italic">Belum Pulang</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Pesan Data Kosong Mobile -->
        <div x-show="filteredData().length === 0" class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200 text-gray-400">
            <div class="flex flex-col items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium">Tidak ada data absensi yang sesuai filter.</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 mt-4 text-sm text-gray-600">
        <div>
            Menampilkan <span class="font-semibold" x-text="filteredData().length"></span> data
        </div>
        <div class="flex items-center gap-2">
            <button @click="prevPage()" :disabled="currentPage === 1"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors cursor-pointer">
                Prev
            </button>
            <span class="text-xs">Halaman <span class="font-bold text-gray-800" x-text="currentPage"></span> dari <span class="font-bold text-gray-800" x-text="totalPages()"></span></span>
            <button @click="nextPage()" :disabled="currentPage === totalPages() || totalPages() === 0"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors cursor-pointer">
                Next
            </button>
        </div>
    </div>

    {{-- Photo Modal with Zoom In / Out --}}
    <div x-show="photoModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="closePhotoModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-sm"
         style="display: none;"
         x-cloak>

        <div @click.outside="closePhotoModal()" 
             class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] border border-gray-100">
            
            {{-- Modal Header --}}
            <div class="px-4 sm:px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-white z-10 gap-2">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <div class="font-bold text-gray-800 text-sm sm:text-base truncate" x-text="activePhoto ? (activePhoto.name || (activePhoto.murid ? activePhoto.murid.name : (activePhoto.employee ? activePhoto.employee.name : 'Foto Absen'))) : 'Foto Absen'"></div>
                    <template x-if="activePhoto && (activePhoto.tipe || activePhoto.employee_id)">
                        <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold shrink-0"
                            :class="activePhoto.employee_id ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                            x-text="activePhoto.employee_id ? 'Karyawan' : 'Murid'">
                        </span>
                    </template>
                </div>
                
                <button @click="closePhotoModal()" type="button" 
                    class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Image Display Area --}}
            <div class="relative flex-1 overflow-auto bg-gray-950 flex items-center justify-center min-h-[220px] max-h-[55vh] sm:max-h-[65vh] p-3 sm:p-4 select-none"
                 @wheel.prevent="handleWheel($event)">
                <template x-if="activePhoto && activePhoto.img">
                    <img :src="activePhoto.img.startsWith('http') || activePhoto.img.startsWith('/') ? activePhoto.img : '/storage/' + activePhoto.img" 
                         @click="zoomLevel === 1 ? zoomLevel = 2 : zoomLevel = 1"
                         class="max-w-full max-h-[52vh] sm:max-h-[58vh] object-contain rounded-lg transition-transform duration-150 origin-center cursor-zoom-in"
                         :style="`transform: scale(${zoomLevel});`"
                         title="Klik untuk zoom 2x / reset"
                         alt="Foto Absensi" />
                </template>
            </div>

            {{-- Modal Footer with Info & Zoom Controls --}}
            <div class="px-4 sm:px-5 py-2.5 sm:py-3 border-t border-gray-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs">
                <div class="text-gray-600 font-medium text-center sm:text-left w-full sm:w-auto" x-text="activePhoto ? (activePhoto.time + (activePhoto.status ? ' • ' + activePhoto.status.toUpperCase() : '')) : ''"></div>
                
                {{-- Zoom Controls Toolbar --}}
                <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl border border-gray-200 shrink-0">
                    <button type="button" @click="zoomOut()" :disabled="zoomLevel <= 0.5" 
                        title="Perkecil (Zoom Out)"
                        class="p-1.5 text-gray-700 hover:bg-white hover:text-green-600 rounded-lg transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                        </svg>
                    </button>

                    <button type="button" @click="resetZoom()" 
                        title="Reset Zoom (100%)"
                        class="px-2.5 py-1 text-xs font-bold text-gray-700 hover:bg-white hover:text-green-600 rounded-lg transition cursor-pointer shadow-xs min-w-[50px] text-center"
                        x-text="Math.round(zoomLevel * 100) + '%'">
                    </button>

                    <button type="button" @click="zoomIn()" :disabled="zoomLevel >= 3.5" 
                        title="Perbesar (Zoom In)"
                        class="p-1.5 text-gray-700 hover:bg-white hover:text-green-600 rounded-lg transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Export Excel --}}
    <div x-show="exportModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="exportModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         style="display: none;"
         x-cloak>

        <div @click.outside="exportModalOpen = false"
             class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-green-100 text-green-700 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-base">Export Absensi Excel</h3>
                        <p class="text-xs text-gray-500">Pilih tipe absensi dan bulan laporan</p>
                    </div>
                </div>

                <button @click="exportModalOpen = false" type="button"
                    class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Modal Body / Form --}}
            <form action="{{ route('dashboard.absensi.export') }}" method="GET" class="p-6 flex flex-col gap-5">
                <input type="hidden" name="export" value="excel">

                {{-- Pilihan Tipe --}}
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Tipe Absensi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-all"
                               :class="exportType === 'karyawan' ? 'border-green-600 bg-green-50/50 ring-2 ring-green-500/20' : 'border-gray-200 hover:bg-gray-50'">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full border flex items-center justify-center"
                                      :class="exportType === 'karyawan' ? 'border-green-600 bg-green-600' : 'border-gray-400'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="exportType === 'karyawan'"></span>
                                </span>
                                <span class="text-sm font-semibold text-gray-800">Karyawan</span>
                            </div>
                            <input type="radio" name="type" value="karyawan" x-model="exportType" class="sr-only">
                        </label>

                        <label class="relative flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-all"
                               :class="exportType === 'murid' ? 'border-green-600 bg-green-50/50 ring-2 ring-green-500/20' : 'border-gray-200 hover:bg-gray-50'">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full border flex items-center justify-center"
                                      :class="exportType === 'murid' ? 'border-green-600 bg-green-600' : 'border-gray-400'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="exportType === 'murid'"></span>
                                </span>
                                <span class="text-sm font-semibold text-gray-800">{{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}</span>
                            </div>
                            <input type="radio" name="type" value="murid" x-model="exportType" class="sr-only">
                        </label>
                    </div>
                </div>

                {{-- Pilihan Bulan --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Pilih Bulan</label>
                    <input type="month" name="month" x-model="exportMonth" required
                           class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-green-500 focus:ring-1 focus:ring-green-500 shadow-sm bg-white cursor-pointer" />
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Setiap tanggal absensi akan dibuat menjadi sheet terpisah (contoh: <span class="font-medium text-gray-700">1-10-2026</span>, <span class="font-medium text-gray-700">2-10-2026</span>, dst).
                    </p>
                </div>

                {{-- Info Box --}}
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/70 text-xs text-amber-800 flex gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>
                        Setiap sheet menampilkan kolom <strong>Nama</strong>, <strong>Masuk</strong>, dan <strong>Pulang</strong>. Waktu masuk yang terlambat akan otomatis diblock dengan warna merah.
                    </span>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-gray-100">
                    <button type="button" @click="exportModalOpen = false"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" @click="setTimeout(() => exportModalOpen = false, 500)"
                            class="px-5 py-2 text-sm font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Excel
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection