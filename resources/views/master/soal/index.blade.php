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
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="{ ...dataTable({{ json_encode($items) }}), showImportModal: false }">

    <div class="mb-4 flex justify-between items-center gap-2">
        <input type="text" x-model="search" placeholder="Pencarian"
            class="w-full md:w-1/2 border border-gray-300  ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

        <div class="flex gap-2 items-center">
            <a href="{{ route('dashboard.master.soal.create') }}"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah
            </a>
            <button @click="showImportModal = true"
                class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Import
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead>
                <tr class="bg-green-500 text-left text-white">
                    <th class="px-4 py-2">No</th>
                    <th @click="sortBy('nama')" class="cursor-pointer px-4 py-2">Soal</th>
                    <th @click="sortBy('tipe')" class="cursor-pointer px-4 py-2">Tipe</th>
                    <th class="px-4 py-2">Jawaban</th>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in paginatedData()" :key="index">
                    <tr class="border-t border-gray-300 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                        <td class="px-4 py-3 max-w-xs md:max-w-md">
                            <div class="line-clamp-2 text-gray-700" x-html="row.nama"></div>
                            <template x-if="row.used_in_exams && row.used_in_exams.length > 0">
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <template x-for="exam in row.used_in_exams" :key="exam">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-red-50 text-red-700 border border-red-100" x-text="exam"></span>
                                    </template>
                                </div>
                            </template>
                        </td>
                        <td class="px-4 py-3 text-nowrap">
                            <span :class="row.tipe === 'Pilihan ganda' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-orange-100 text-orange-700 border border-orange-200'"
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider" x-text="row.tipe"></span>
                        </td>
                        <td class="px-4 py-3 font-medium text-green-700" x-text="row.jawaban"></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a :href="'/dashboard/master/soal/' + row.id + '/edit'"
                                    class="p-1.5 bg-green-50 text-green-600 hover:bg-green-600 hover:text-white rounded-lg transition-all duration-200 shadow-sm border border-green-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                        <path d="m15 5 4 4" />
                                    </svg>
                                </a>

                                <form :action="'/dashboard/master/soal/' + row.id" method="POST"
                                    @submit.prevent="deleteRow($event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition-all duration-200 shadow-sm border border-red-100 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="5" class="text-center px-4 py-2 text-gray-500">No results found.</td>
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

    <div x-show="showImportModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md transform transition-all" @click.away="showImportModal = false">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Import Soal Excel</h2>
                <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('dashboard.master.soal.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 ml-1">Pilih File Excel/CSV</label>
                    <div class="relative group">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-all border border-gray-200 rounded-xl p-1 group-hover:border-green-300">
                    </div>
                </div>

                <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500 shrink-0 mt-0.5">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <div class="text-xs text-blue-700 leading-relaxed">
                        Gunakan template yang tersedia untuk memastikan format data sudah benar.
                        <a href="{{ route('dashboard.master.soal.template') }}" class="font-bold underline hover:text-blue-800">Download Template di sini</a>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showImportModal = false"
                        class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 shadow-lg shadow-green-100 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" x2="12" y1="3" y2="15" />
                        </svg>
                        <span>Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection