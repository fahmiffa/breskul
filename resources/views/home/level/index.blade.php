@extends('base.layout')
@section('title', 'Dashboard Level')
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="dataTableReg({{ json_encode($items) }})">

        <div class="mb-4 flex justify-between items-center gap-2">
            <input type="text" x-model="search" placeholder="Cari Nama"
                class="w-full md:w-1/2 border border-gray-300  ring-0 rounded-xl px-3 py-2 focus:outline-[#FF9966]" />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-orange-500 text-left text-white">
                        <th class="px-4 py-2">No</th>
                        <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Program</th>
                        <th class="px-4 py-2">Level</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in paginatedData()" :key="row.id">
                        <tr class="border-t border-gray-300">
                            <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                            <td class="px-4 py-2" x-text="row.murid.name"></td>
                            <td class="px-4 py-2" x-text="row.programs.name"></td>
                            <td class="px-4 py-2">
                                <template x-for="(item, index) in row.level" :key="index">
                                    <div class="flex gap-3 justify-between items-center">
                                        <div x-text="item.level" class="font-semibold capitalize"></div>
                                        <div x-show="item.status === 0">
                                            <button @click="openModal(item)"
                                                class="bg-orange-500 text-xs cursor-pointer font-semibold text-white px-3 py-2 rounded-2xl hover:bg-orange-600"
                                                type="button">Verifikasi</button>
                                        </div>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData().length === 0">
                        <td colspan="3" class="text-center px-4 py-2 text-gray-500">No results found.</td>
                    </tr>
                </tbody>
            </table>

            <div x-show="modalOpen" style="background-color: rgba(0,0,0,0.5);"
                class="fixed inset-0 flex items-center justify-center z-50"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative" @click.away="closeModal()">
                    <h2 class="text-sm font-bold mb-4">Upgrade Level</h2>
                    <form method="POST" :action="'/dashboard/layanan/' + md5Component(selectedItem?.student_id) + ''">
                        @csrf
                        <p class="text-sm mb-4">Note: <span x-text="selectedItem?.note"></span></p>
                        <input type="hidden" name="level" :value="selectedItem?.id">
                        <div class="mb-4">
                            <select name="book" required
                                class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                                <option value="">Pilih Buku</option>
                                @foreach ($lay as $val)
                                    <option value="{{ $val->id }}">{{ $val->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button
                            class="cursor-pointer bg-orange-500 text-xs hover:bg-orange-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                            Upgrade
                        </button>
                    </form>
                    <button
                        class="mt-6 bg-gray-800 text-white text-xs cursor-pointer px-3 py-2 rounded-2xl hover:bg-gray-900 float-end"
                        @click="closeModal()" type="button">Close</button>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mt-4">
            <button @click="prevPage()" :disabled="currentPage === 1"
                class="px-3 py-1 text-white rounded bg-orange-500 hover:bg-orange-600 disabled:opacity-50">Prev</button>

            <span>Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages()"></span></span>

            <button @click="nextPage()" :disabled="currentPage === totalPages()"
                class="px-3 py-1 text-white rounded bg-orange-500 hover:bg-orange-600 disabled:opacity-50">Next</button>
        </div>
    </div>
@endsection
