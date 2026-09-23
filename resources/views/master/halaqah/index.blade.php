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
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="{
        ...dataTable({{ json_encode($items) }}),
        openDetail: false,
        selectedHalaqah: null,
        showDetail(row) {
            this.selectedHalaqah = row;
            this.openDetail = true;
        }
    }">

        <div class="mb-4 flex justify-between items-center gap-2">
            <input type="text" x-model="search" placeholder="Pencarian nama, guru, hari, keterangan..."
                class="w-full md:w-1/2 border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

            <a href="{{ route('dashboard.master.halaqah.create') }}"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-green-500 text-left text-white">
                        <th class="px-4 py-2">No</th>
                        <th @click="sortBy('nama')" class="cursor-pointer px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Guru</th>
                        <th @click="sortBy('hari')" class="cursor-pointer px-4 py-2">Hari</th>
                        <th class="px-4 py-2">Waktu</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2">Jumlah Siswa</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in paginatedData()" :key="row.id">
                        <tr class="border-t border-gray-300 hover:bg-gray-50/75 transition-colors">
                            <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                            <td class="px-4 py-2 font-medium text-gray-800" x-text="row.nama"></td>
                            <td class="px-4 py-2" x-text="row.teach ? row.teach.name : '-'"></td>
                            <td class="px-4 py-2" x-text="row.hari"></td>
                            <td class="px-4 py-2" x-text="row.waktu"></td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :class="row.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    x-text="row.status ? 'Aktif' : 'Tidak Aktif'">
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700"
                                    x-text="(row.students ? row.students.length : 0) + ' Siswa'">
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-1.5">
                                    {{-- Tombol Detail (Modal) --}}
                                    <button type="button" @click="showDetail(row)"
                                        title="Lihat Detail & List Siswa"
                                        class="text-blue-500 hover:text-blue-700 cursor-pointer p-1 rounded hover:bg-blue-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>

                                    {{-- Tombol Edit --}}
                                    <a :href="'/dashboard/master/halaqah/' + row.id + '/edit'"
                                        title="Edit"
                                        class="text-green-600 hover:text-green-700 p-1 rounded hover:bg-green-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-pencil-icon lucide-pencil">
                                            <path
                                                d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                            <path d="m15 5 4 4" />
                                        </svg>
                                    </a>

                                    {{-- Tombol Delete --}}
                                    <form :action="'/dashboard/master/halaqah/' + row.id" method="POST"
                                        @submit.prevent="deleteRow($event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="text-red-500 hover:text-red-700 cursor-pointer p-1 rounded hover:bg-red-50 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
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
                        <td colspan="8" class="text-center px-4 py-6 text-gray-500">Tidak ada data halaqah ditemukan.</td>
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

        {{-- MODAL DETAIL HALAQAH --}}
        <div x-show="openDetail" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="openDetail = false"
                class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in">
                
                {{-- Header Modal --}}
                <div class="bg-green-600 text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold" x-text="selectedHalaqah?.nama || 'Detail Halaqah'"></h3>
                        <p class="text-xs text-green-100 mt-0.5">Informasi lengkap dan daftar siswa halaqah</p>
                    </div>
                    <button @click="openDetail = false" class="text-white hover:text-green-200 text-2xl font-bold leading-none cursor-pointer">
                        &times;
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="p-6 overflow-y-auto space-y-5">
                    {{-- Grid Informasi Halaqah --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100 text-sm">
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold">Guru Pengajar</span>
                            <span class="font-bold text-gray-800" x-text="selectedHalaqah?.teach ? selectedHalaqah.teach.name : '-'"></span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold">Hari</span>
                            <span class="font-bold text-gray-800" x-text="selectedHalaqah?.hari || '-'"></span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold">Waktu</span>
                            <span class="font-bold text-gray-800" x-text="selectedHalaqah?.waktu || '-'"></span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold">Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-0.5"
                                :class="selectedHalaqah?.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                x-text="selectedHalaqah?.status ? 'Aktif' : 'Tidak Aktif'">
                            </span>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-sm">
                        <span class="text-xs text-gray-400 block font-semibold mb-1">Keterangan / Catatan</span>
                        <p class="text-gray-700 whitespace-pre-line" x-text="selectedHalaqah?.keterangan ? selectedHalaqah.keterangan : 'Tidak ada keterangan.'"></p>
                    </div>

                    {{-- List Siswa --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-bold text-gray-800">
                                Daftar Siswa (<span x-text="selectedHalaqah?.students ? selectedHalaqah.students.length : 0" class="text-green-600"></span>)
                            </span>
                            <span class="text-xs text-gray-500 italic">Khusus siswa status boarding</span>
                        </div>

                        <div class="border border-gray-200 rounded-xl overflow-hidden max-h-60 overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 text-gray-700 text-xs">
                                    <tr>
                                        <th class="px-4 py-2 text-left w-12">No</th>
                                        <th class="px-4 py-2 text-left">Nama Siswa</th>
                                        <th class="px-4 py-2 text-left">NIS</th>
                                        <th class="px-4 py-2 text-left">Kelas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(hs, sIndex) in (selectedHalaqah?.students || [])" :key="hs.id">
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-gray-500" x-text="sIndex + 1"></td>
                                            <td class="px-4 py-2 font-medium text-gray-800" x-text="hs.student ? hs.student.name : '-'"></td>
                                            <td class="px-4 py-2 text-gray-600" x-text="hs.student ? (hs.student.nis || '-') : '-'"></td>
                                            <td class="px-4 py-2 text-gray-600">
                                                <span class="px-2 py-0.5 bg-gray-100 rounded text-xs"
                                                    x-text="hs.student?.kelas?.[0]?.name || '-'">
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!selectedHalaqah?.students || selectedHalaqah.students.length === 0">
                                        <td colspan="4" class="text-center py-4 text-gray-400 text-xs italic">
                                            Belum ada siswa yang tergabung di halaqah ini.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-between items-center">
                    <a :href="'/dashboard/master/halaqah/' + selectedHalaqah?.id + '/edit'"
                        class="text-xs bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-xl transition flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                            <path d="m15 5 4 4" />
                        </svg>
                        Edit Halaqah Ini
                    </a>
                    <button type="button" @click="openDetail = false"
                        class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    </div>
@endsection
