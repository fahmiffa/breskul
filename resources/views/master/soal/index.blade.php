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
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="{ ...dataTable({{ json_encode($items) }}) }">

    <div class="mb-4 flex justify-between items-center gap-2">
        <input type="text" x-model="search" placeholder="Pencarian"
            class="w-full md:w-1/2 border border-gray-300  ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

        <div class="flex gap-2 items-center">
            <a href="{{ route('dashboard.master.soal.create') }}"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah
            </a>
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

</div>
@endsection