@extends('base.layout')
@section('title', $title)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="dataTable({{ json_encode($items) }})">

        <div class="mb-4 flex justify-between items-center gap-2">
            <input type="text" x-model="search" placeholder="Pencarian Bank / No Rekening..."
                class="w-full md:w-1/2 border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

            <a href="{{ route('dashboard.master.bank.create') }}"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Bank
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-green-500 text-left text-white">
                        <th class="px-4 py-2 w-16">No</th>
                        <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama Bank</th>
                        <th @click="sortBy('number')" class="cursor-pointer px-4 py-2">Nomor Rekening</th>
                        @if(auth()->user()->role == 0)
                        <th @click="sortBy('app.name')" class="cursor-pointer px-4 py-2">Aplikasi</th>
                        @endif
                        <th class="px-4 py-2 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in paginatedData()" :key="row.id">
                        <tr class="border-t border-gray-300 hover:bg-gray-50">
                            <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                            <td class="px-4 py-2 font-medium text-gray-800" x-text="row.name"></td>
                            <td class="px-4 py-2 font-mono text-gray-700" x-text="row.number"></td>
                            @if(auth()->user()->role == 0)
                            <td class="px-4 py-2 text-gray-500" x-text="row.app ? row.app.name : '-'"></td>
                            @endif
                            <td class="px-4 py-2">
                                <div class="flex items-center justify-center gap-2">
                                    <a :href="'/dashboard/master/bank/' + row.id + '/edit'"
                                        class="text-green-600 hover:text-green-700" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-pencil">
                                            <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                            <path d="m15 5 4 4" />
                                        </svg>
                                    </a>

                                    <form :action="'/dashboard/master/bank/' + row.id" method="POST"
                                        @submit.prevent="deleteRow($event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 cursor-pointer" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-trash-2">
                                                <path d="M3 6h18" />
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                <line x1="14" y1="11" x2="14" y2="17" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData().length === 0">
                        <td colspan="{{ auth()->user()->role == 0 ? 5 : 4 }}" class="text-center py-4 text-gray-500">Tidak ada data ditemukan.</td>
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
