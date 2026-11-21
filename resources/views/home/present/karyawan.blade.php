@extends('base.layout')
@section('title', 'Detail Absensi - ' . $employee->name)

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="flex flex-col gap-6"
     x-data="{
         photoModalOpen: false,
         activePhoto: null,
         zoomLevel: 1,

         openPhotoModal(photo) {
             if (!photo || !photo.img) return;
             this.activePhoto = photo;
             this.zoomLevel = 1;
             this.photoModalOpen = true;
         },

         closePhotoModal() {
             this.photoModalOpen = false;
             this.activePhoto = null;
             this.zoomLevel = 1;
         },

         zoomIn() {
             if (this.zoomLevel < 3.5) {
                 this.zoomLevel = +(this.zoomLevel + 0.25).toFixed(2);
             }
         },

         zoomOut() {
             if (this.zoomLevel > 0.5) {
                 this.zoomLevel = +(this.zoomLevel - 0.25).toFixed(2);
             }
         },

         resetZoom() {
             this.zoomLevel = 1;
         },

         handleWheel(event) {
             if (event.deltaY < 0) {
                 this.zoomIn();
             } else {
                 this.zoomOut();
             }
         }
     }">

    {{-- Breadcrumb & Tombol Kembali --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard.absensi') }}" class="hover:text-green-600 transition-colors flex items-center gap-1 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Daftar Absensi
            </a>
            <span>/</span>
            <span class="text-gray-800 font-semibold">Detail Absensi Karyawan</span>
        </div>

        <a href="{{ route('dashboard.absensi') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-xs transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    {{-- Kartu Profil & Filter Bulan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center gap-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-700 text-white flex items-center justify-center font-bold text-xl sm:text-2xl shadow-md shrink-0">
                {{ strtoupper(substr($employee->name, 0, 2)) }}
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900">{{ $employee->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                        Karyawan
                    </span>
                    @if($employee->jabatan)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $employee->jabatan->name }}
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-gray-500">
                    @if($employee->jenis)
                        <span>Jenis Kelamin: <strong class="text-gray-700">{{ $employee->jenis }}</strong></span>
                    @endif
                    @if($employee->alamat)
                        <span>Alamat: <strong class="text-gray-700">{{ $employee->alamat }}</strong></span>
                    @endif
                    @if($config && $config->clock_in_end)
                        <span>Batas Jam Masuk: <strong class="text-green-700">{{ substr($config->clock_in_end, 0, 5) }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Form Filter Bulan --}}
        <form action="{{ route('dashboard.absensi.karyawan', $employee->id) }}" method="GET" class="flex flex-wrap items-center gap-2">
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Periode Bulan</label>
                <div class="flex items-center gap-2">
                    <input type="month" name="month" value="{{ $month }}"
                           class="border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-green-500 focus:ring-1 focus:ring-green-500 bg-white shadow-xs cursor-pointer font-medium text-gray-700" />
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl transition duration-200 text-sm flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Kartu Ringkasan KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        {{-- Total Hadir --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Hadir</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-gray-800 mt-1">{{ $totalHadir }}</span>
                <span class="text-[11px] text-gray-400 mt-0.5">Hari Kehadiran</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        {{-- Tepat Waktu --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Tepat Waktu</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1">{{ $totalTepatWaktu }}</span>
                <span class="text-[11px] text-gray-400 mt-0.5">Hari Masuk Tepat</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Terlambat</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-rose-600 mt-1">{{ $totalTerlambat }}</span>
                <span class="text-[11px] text-gray-400 mt-0.5">Hari Terlambat</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        {{-- Persentase Ketepatan --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Disiplin Waktu</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-green-600 mt-1">{{ $persentaseTepatWaktu }}%</span>
                <span class="text-[11px] text-gray-400 mt-0.5">Rasio Tepat Waktu</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Bagian Grafik Absensi --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Grafik Garis / Tren Jam Masuk & Pulang Harian --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-800">Grafik Jam Kehadiran</h2>
                    <p class="text-xs text-gray-500">Tren jam masuk dan jam pulang per tanggal di bulan {{ $carbonMonth->locale('id')->translatedFormat('F Y') }}</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 font-medium">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Jam Masuk
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-blue-700 font-medium">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span> Jam Pulang
                    </span>
                    @if($targetInHour)
                    <span class="inline-flex items-center gap-1.5 text-rose-600 font-medium">
                        <span class="w-3 h-0.5 bg-rose-500"></span> Batas Masuk
                    </span>
                    @endif
                </div>
            </div>

            <div class="relative w-full h-80">
                @if($totalHadir == 0)
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 z-10 text-gray-400 gap-2 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="text-xs font-medium">Belum ada data absensi untuk periode {{ $carbonMonth->locale('id')->translatedFormat('F Y') }}</span>
                    </div>
                @endif
                <canvas id="employeeAttendanceChart"></canvas>
            </div>
        </div>

        {{-- Grafik Donat Komposisi Kehadiran --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-gray-800">Komposisi Kehadiran</h2>
                <p class="text-xs text-gray-500">Perbandingan tepat waktu vs terlambat</p>
            </div>

            <div class="relative w-full h-56 my-auto flex items-center justify-center">
                @if($totalHadir > 0)
                    <canvas id="employeePieChart"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center text-gray-400 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        </svg>
                        <span class="text-xs">Belum ada data absensi di bulan ini</span>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-emerald-500 shrink-0"></span>
                    <div class="flex flex-col">
                        <span class="text-gray-500">Tepat Waktu</span>
                        <span class="font-bold text-gray-800">{{ $totalTepatWaktu }} hari</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-rose-500 shrink-0"></span>
                    <div class="flex flex-col">
                        <span class="text-gray-500">Terlambat</span>
                        <span class="font-bold text-gray-800">{{ $totalTerlambat }} hari</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Absensi Karyawan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-gray-800">Riwayat Absensi Harian</h2>
                <p class="text-xs text-gray-500">Daftar kehadiran harian {{ $employee->name }} periode {{ $carbonMonth->locale('id')->translatedFormat('F Y') }}</p>
            </div>
            <div class="text-xs text-gray-500 font-medium">
                Total: <span class="font-bold text-gray-800">{{ count($dailyHistory) }}</span> hari hadir
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full bg-white text-sm">
                <thead>
                    <tr class="bg-green-600 text-left text-white">
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold w-12 text-center">No</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Hari & Tanggal</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Absensi Masuk</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold">Absensi Pulang</th>
                        <th class="px-4 py-3 text-xs uppercase tracking-wider font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dailyHistory as $idx => $row)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-4 py-3 text-gray-500 text-xs text-center font-medium">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800">{{ $row['day_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $row['date'] }}</div>
                            </td>
                            {{-- Absensi Masuk --}}
                            <td class="px-4 py-3">
                                @if($row['masuk'])
                                    <div class="flex items-center gap-3">
                                        @if($row['masuk']['img'])
                                            <div @click="openPhotoModal({ name: '{{ addslashes($employee->name) }}', tipe: 'Karyawan', img: '{{ $row['masuk']['img'] }}', time: '{{ $row['masuk']['full_time'] }}', status: 'Masuk' })"
                                                 class="group relative inline-block cursor-pointer rounded-lg overflow-hidden border border-gray-200 shadow-xs hover:shadow-md transition-all shrink-0">
                                                <img src="{{ str_starts_with($row['masuk']['img'], 'http') || str_starts_with($row['masuk']['img'], '/') ? $row['masuk']['img'] : '/storage/' . $row['masuk']['img'] }}" 
                                                     class="h-11 w-11 object-cover transition-transform duration-200 group-hover:scale-110" 
                                                     alt="Foto Masuk">
                                                <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="h-11 w-11 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0" title="Tidak ada foto">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800 text-xs sm:text-sm">{{ $row['masuk']['time'] }}</span>
                                            @if($row['masuk']['late_time'])
                                                <span class="mt-0.5 inline-block w-fit px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600 border border-red-200">
                                                    {{ preg_replace_callback('/(\d+(?:\.\d+)?)/', fn($m) => ceil((float)$m[1]), $row['masuk']['late_time']) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic font-medium">-</span>
                                @endif
                            </td>
                            {{-- Absensi Pulang --}}
                            <td class="px-4 py-3">
                                @if($row['pulang'])
                                    <div class="flex items-center gap-3">
                                        @if($row['pulang']['img'])
                                            <div @click="openPhotoModal({ name: '{{ addslashes($employee->name) }}', tipe: 'Karyawan', img: '{{ $row['pulang']['img'] }}', time: '{{ $row['pulang']['full_time'] }}', status: 'Pulang' })"
                                                 class="group relative inline-block cursor-pointer rounded-lg overflow-hidden border border-gray-200 shadow-xs hover:shadow-md transition-all shrink-0">
                                                <img src="{{ str_starts_with($row['pulang']['img'], 'http') || str_starts_with($row['pulang']['img'], '/') ? $row['pulang']['img'] : '/storage/' . $row['pulang']['img'] }}" 
                                                     class="h-11 w-11 object-cover transition-transform duration-200 group-hover:scale-110" 
                                                     alt="Foto Pulang">
                                                <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="h-11 w-11 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0" title="Tidak ada foto">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800 text-xs sm:text-sm">{{ $row['pulang']['time'] }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic font-medium">-</span>
                                @endif
                            </td>
                            {{-- Status Badge --}}
                            <td class="px-4 py-3 text-center">
                                @if($row['is_late'])
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Terlambat
                                    </span>
                                @elseif($row['masuk'])
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Tepat Waktu
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                        Hadir
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm">Tidak ada catatan absensi untuk periode {{ $carbonMonth->locale('id')->translatedFormat('F Y') }}.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                    <div class="font-bold text-gray-800 text-sm sm:text-base" x-text="activePhoto ? (activePhoto.name || 'Foto Absen') : 'Foto Absen'"></div>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                        Karyawan
                    </span>
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
                    <img :src="activePhoto.img.startsWith('http') || activePhoto.img.startsWith('/') ? activePhoto.img : '/storage/' + activePhoto.img" 
                         class="max-w-full max-h-[58vh] object-contain rounded-lg transition-transform duration-150 origin-center cursor-zoom-in"
                         :style="`transform: scale(${zoomLevel});`"
                         alt="Foto Absensi" />
                </template>
            </div>

            {{-- Modal Footer with Info & Zoom Controls --}}
            <div class="px-5 py-3 border-t border-gray-100 bg-white flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="text-gray-500 font-medium" x-text="activePhoto ? ((activePhoto.time || '') + (activePhoto.status ? ' • ' + activePhoto.status.toUpperCase() : '')) : ''"></div>
                
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

@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    function initEmployeeCharts() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js belum siap dimuat.');
            return;
        }

        const labels = @json($chartLabels);
        const masukHours = @json($chartMasukHours);
        const pulangHours = @json($chartPulangHours);
        const masukTimes = @json($chartMasukTimes);
        const pulangTimes = @json($chartPulangTimes);
        const targetIn = @json($targetInHour);

        const datasets = [
            {
                label: 'Jam Masuk',
                data: masukHours,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: false,
                tension: 0.25,
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                spanGaps: true
            },
            {
                label: 'Jam Pulang',
                data: pulangHours,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: false,
                tension: 0.25,
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                spanGaps: true
            }
        ];

        if (targetIn !== null) {
            datasets.push({
                label: 'Batas Masuk',
                data: labels.map(() => targetIn),
                borderColor: '#ef4444',
                borderDash: [5, 5],
                borderWidth: 1.5,
                pointRadius: 0,
                fill: false
            });
        }

        const canvasLine = document.getElementById('employeeAttendanceChart');
        if (canvasLine) {
            new Chart(canvasLine.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels.map(d => 'Tgl ' + d),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            suggestedMin: 6,
                            suggestedMax: 18,
                            ticks: {
                                stepSize: 2,
                                callback: function (val) {
                                    const h = String(Math.floor(val)).padStart(2, '0');
                                    const m = String(Math.round((val % 1) * 60)).padStart(2, '0');
                                    return `${h}:${m}`;
                                }
                            },
                            title: {
                                display: true,
                                text: 'Waktu (Jam)',
                                font: { size: 11 }
                            },
                            grid: {
                                color: '#f3f4f6'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 10 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const idx = context.dataIndex;
                                    if (context.datasetIndex === 0) {
                                        return 'Masuk: ' + (masukTimes[idx] || 'Tidak Absen');
                                    } else if (context.datasetIndex === 1) {
                                        return 'Pulang: ' + (pulangTimes[idx] || 'Tidak Absen');
                                    } else if (context.datasetIndex === 2) {
                                        const h = String(Math.floor(targetIn)).padStart(2, '0');
                                        const m = String(Math.round((targetIn % 1) * 60)).padStart(2, '0');
                                        return `Batas Masuk: ${h}:${m}`;
                                    }
                                    return context.dataset.label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Pie / Donut Chart
        const canvasPie = document.getElementById('employeePieChart');
        const totalTepat = {{ $totalTepatWaktu }};
        const totalTelat = {{ $totalTerlambat }};

        if (canvasPie && (totalTepat > 0 || totalTelat > 0)) {
            new Chart(canvasPie.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Tepat Waktu', 'Terlambat'],
                    datasets: [{
                        data: [totalTepat, totalTelat],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = totalTepat + totalTelat;
                                    const val = context.parsed;
                                    const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                    return ` ${context.label}: ${val} hari (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEmployeeCharts);
    } else {
        initEmployeeCharts();
    }
})();
</script>
@endpush
