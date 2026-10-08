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
<div class="flex flex-col bg-white rounded-2xl shadow-md p-4 sm:p-6 border border-gray-100" x-data="absensiTable({{ json_encode($items) }}, '{{ $type ?? '' }}')">

    <form action="{{ route('dashboard.absensi') }}" method="GET" class="mb-6 flex flex-wrap items-end gap-3 sm:gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $start }}"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-sm bg-white" />
        </div>
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ $end }}"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-sm bg-white" />
        </div>
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tipe</label>
            <select name="type"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-sm bg-white cursor-pointer min-w-[150px]">
                <option value="" {{ empty($type) ? 'selected' : '' }}>Semua</option>
                <option value="murid" {{ ($type ?? '') == 'murid' ? 'selected' : '' }}>Murid</option>
                <option value="karyawan" {{ ($type ?? '') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
            </select>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button type="submit" class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-5 rounded-lg transition duration-200 text-sm flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <a href="{{ route('dashboard.absensi') }}" class="flex-1 sm:flex-none bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 font-bold py-2 px-5 rounded-lg transition duration-200 text-sm flex items-center justify-center shadow-sm">
                Reset
            </a>
            <a href="{{ route('dashboard.absensi', ['start_date' => $start, 'end_date' => $end, 'type' => $type, 'export' => 'excel']) }}" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg transition duration-200 text-sm flex items-center justify-center shadow-sm">
                Export Excel
            </a>
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

    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full bg-white text-sm">
            <thead>
                <tr class="bg-green-600 text-left text-white">
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">No</th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-3 text-xs uppercase tracking-wider font-semibold hover:bg-green-700 transition-colors">
                        <div class="flex items-center gap-1">
                            <span>Nama</span>
                            <span class="text-xs" x-show="sortColumn === 'name'" x-text="sortAsc ? '↑' : '↓'"></span>
                        </div>
                    </th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Tipe</th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Jabatan</th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Waktu</th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Status</th>
                    <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Foto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="(row, index) in paginatedData()" :key="row.id">
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3 text-gray-500 text-xs" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                        <td class="px-4 py-3 font-semibold text-gray-800" x-text="row.name || (row.murid ? row.murid.name : (row.employee ? row.employee.name : '-'))"></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                :class="row.employee_id ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                                x-text="row.employee_id ? 'Karyawan' : 'Murid'">
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs" x-text="row.employee_id && row.employee && row.employee.jabatan ? row.employee.jabatan.name : '-'"></td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-xs sm:text-sm" x-text="row.time"></td>
                        <td class="px-4 py-3 text-center">
                            <template x-if="row.status">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                        :class="{
                                            'bg-green-100 text-green-700 border border-green-200': row.status.toLowerCase() === 'masuk',
                                            'bg-orange-100 text-orange-700 border border-orange-200': row.status.toLowerCase() === 'pulang',
                                            'bg-gray-100 text-gray-700 border border-gray-200': row.status.toLowerCase() !== 'masuk' && row.status.toLowerCase() !== 'pulang'
                                        }"
                                        x-text="row.status">
                                    </span>
                                    <template x-if="row.status.toLowerCase() === 'masuk' && row.late_time">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600 border border-red-200" x-text="row.late_time"></span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!row.status">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                    -
                                </span>
                            </template>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <template x-if="row.img">
                                <div @click="openPhotoModal(row)" 
                                     class="group relative inline-block cursor-pointer rounded-lg overflow-hidden border border-gray-200 shadow-xs hover:shadow-md transition-all">
                                    <img :src="'/storage/' + row.img" 
                                         class="h-10 w-10 sm:h-12 sm:w-12 object-cover transition-transform duration-200 group-hover:scale-110" 
                                         alt="Foto Absen">
                                    <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                        </svg>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!row.img">
                                <span class="text-xs text-gray-400">-</span>
                            </template>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="6" class="text-center py-10 text-gray-400">
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
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-white z-10">
                <div class="flex items-center gap-2">
                    <div class="font-bold text-gray-800 text-sm sm:text-base" x-text="activePhoto ? (activePhoto.name || (activePhoto.murid ? activePhoto.murid.name : (activePhoto.employee ? activePhoto.employee.name : 'Foto Absen'))) : 'Foto Absen'"></div>
                    <template x-if="activePhoto && (activePhoto.tipe || activePhoto.employee_id)">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                            :class="activePhoto.employee_id ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                            x-text="activePhoto.employee_id ? 'Karyawan' : 'Murid'">
                        </span>
                    </template>
                </div>
                
                <button @click="closePhotoModal()" type="button" 
                    class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Image Display Area --}}
            <div class="relative flex-1 overflow-auto bg-gray-950 flex items-center justify-center min-h-[300px] max-h-[65vh] p-4 select-none"
                 @wheel.prevent="handleWheel($event)">
                <template x-if="activePhoto && activePhoto.img">
                    <img :src="'/storage/' + activePhoto.img" 
                         class="max-w-full max-h-[58vh] object-contain rounded-lg transition-transform duration-150 origin-center cursor-zoom-in"
                         :style="`transform: scale(${zoomLevel});`"
                         alt="Foto Absensi" />
                </template>
            </div>

            {{-- Modal Footer with Info & Zoom Controls --}}
            <div class="px-5 py-3 border-t border-gray-100 bg-white flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="text-gray-500 font-medium" x-text="activePhoto ? (activePhoto.time + (activePhoto.status ? ' • ' + activePhoto.status.toUpperCase() : '')) : ''"></div>
                
                {{-- Zoom Controls Toolbar --}}
                <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl border border-gray-200">
                    <button type="button" @click="zoomOut()" :disabled="zoomLevel <= 0.5" 
                        title="Perkecil (Zoom Out)"
                        class="p-1.5 text-gray-700 hover:bg-white hover:text-green-600 rounded-lg transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                        </svg>
                    </button>

                    <button type="button" @click="resetZoom()" 
                        title="Reset Zoom (100%)"
                        class="px-2.5 py-1 text-xs font-bold text-gray-700 hover:bg-white hover:text-green-600 rounded-lg transition cursor-pointer shadow-sm min-w-[50px] text-center"
                        x-text="Math.round(zoomLevel * 100) + '%'">
                    </button>

                    <button type="button" @click="zoomIn()" :disabled="zoomLevel >= 3.5" 
                        title="Perbesar (Zoom In)"
                        class="p-1.5 text-gray-700 hover:bg-white hover:text-green-600 rounded-lg transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection