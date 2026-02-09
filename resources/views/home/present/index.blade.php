@extends('base.layout')
@section('title', 'Dashboard Absensi')
@section('content')
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="dataTable({{ json_encode($items) }})">

    <form action="{{ route('dashboard.absensi') }}" method="GET" class="mb-6 flex flex-wrap items-end gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $start }}"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-sm" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ $end }}"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 text-sm shadow-sm" />
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 text-sm flex items-center gap-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <a href="{{ route('dashboard.absensi') }}" class="bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 font-bold py-2 px-6 rounded-lg transition duration-200 text-sm flex items-center justify-center shadow-sm">
                Reset
            </a>
        </div>
    </form>

    <div class="mb-4 flex justify-between items-center gap-2">
        <input type="text" x-model="search" placeholder="Cari Nama Siswa..."
            class="w-full md:w-1/3 border border-gray-300 ring-0 rounded-xl px-4 py-2 focus:outline-green-500 focus:ring-1 focus:ring-green-500 shadow-sm" />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead>
                <tr class="bg-green-500 text-left text-white">
                    <th class="px-4 py-2">No</th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in paginatedData()" :key="row.id">
                    <tr class="border-t border-gray-300 hover:bg-gray-50">
                        <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                        <td class="px-4 py-2 font-medium" x-text="row.murid.name"></td>
                        <td class="px-4 py-2" x-text="row.time"></td>
                        <td class="px-4 py-2 text-center">
                            <template x-if="row.status">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                    :class="{
                                            'bg-blue-100 text-blue-700': row.status === 'masuk',
                                            'bg-orange-100 text-orange-700': row.status === 'pulang',
                                            'bg-gray-100 text-gray-700': row.status !== 'masuk' && row.status !== 'pulang'
                                        }"
                                    x-text="row.status">
                                </span>
                            </template>
                            <template x-if="!row.status">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                    -
                                </span>
                            </template>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="4" class="text-center px-4 py-2 text-gray-500">No results found.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-4">
        <button @click="prevPage()" :disabled="currentPage === 1"
            class="px-3 py-1 text-white rounded bg-green-500 hover:bg-green-600 disabled:opacity-50">Prev</button>
        <span>Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages()"></span></span>
        <button @click="nextPage()" :disabled="currentPage === totalPages()"
            class="px-3 py-1 text-white rounded bg-green-500 hover:bg-green-600 disabled:opacity-50">Next</button>
    </div>
</div>
@endsection