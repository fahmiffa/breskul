@extends('base.layout')
@section('title', 'Dashboard Master Unit')
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="dataTable({{ json_encode($items) }})">

        <div class="mb-4 flex justify-end items-center gap-2">

            <a href="{{ route('dashboard.pengumuman.create') }}"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-green-500 text-left text-white">
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Gambar</th>
                        <th class="px-4 py-2">Deskripsi</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in paginatedData()" :key="row.id">
                        <tr class="border-t border-gray-300">
                            <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                            <td class="px-4 py-2 text-nowrap" x-text="row.name"></td>
                            <td class="px-4 py-2 items-center">
                                <div class="w-50 h-24 overflow-hidden">
                                    <img :src="row.gambar" :alt="row.name" class="w-full object-cover" />
                                </div>
                            </td>
                            <td class="px-4 py-2 line-clamp-3" x-html="row.des"></td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-1">
                                    <a :href="'/dashboard/pengumuman/' + row.id + '/edit'"
                                        class="text-green-600 hover:text-green-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-pencil-icon lucide-pencil">
                                            <path
                                                d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                            <path d="m15 5 4 4" />
                                        </svg>
                                    </a>

                                    <form :action="'/dashboard/pengumuman/' + row.id" method="POST"
                                        @submit.prevent="deleteRow($event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-trash2-icon lucide-trash-2">
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
                        <td colspan="3" class="text-center px-4 py-2 text-gray-500">No results found.</td>
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
