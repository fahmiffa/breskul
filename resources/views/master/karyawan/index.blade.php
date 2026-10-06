@extends('base.layout')
@section('title', $title)
@section('content')
    @php
        $jabatans = \App\Models\Jabatan::orderBy('name', 'asc')->get();
    @endphp

    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="{ ...dataTable({{ json_encode($items) }}), importModal: false }">

        {{-- Success / Error Alert --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-4 flex flex-wrap justify-between items-center gap-2">
            <input type="text" x-model="search" placeholder="Pencarian"
                class="w-full md:w-1/2 border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

            <div class="flex gap-2 flex-wrap">
                {{-- Import Button --}}
                <button @click="importModal = true"
                    class="cursor-pointer bg-blue-500 text-xs hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-2xl flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import Excel
                </button>

                <a href="{{ route('dashboard.master.karyawan.create') }}"
                    class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-green-500 text-left text-white">
                        <th class="px-4 py-2">No</th>
                        <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                        <th @click="sortBy('jenis')" class="cursor-pointer px-4 py-2">Jenis Kelamin</th>
                        <th class="cursor-pointer px-4 py-2">No HP</th>
                        <th class="cursor-pointer px-4 py-2">Alamat</th>
                        <th class="px-4 py-2">Jabatan</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in paginatedData()" :key="row.id">
                        <tr class="border-t border-gray-300">
                            <td class="px-4 py-2" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                            <td class="px-4 py-2" x-text="row.name"></td>
                            <td class="px-4 py-2" x-text="row.jenis"></td>
                            <td class="px-4 py-2" x-text="row.user ? (row.user.nomor || '-') : '-'"></td>
                            <td class="px-4 py-2" x-text="row.alamat"></td>
                            <td class="px-4 py-2">
                                <span x-show="row.jabatan" class="bg-purple-200 text-purple-600 py-1 px-3 rounded-full text-xs" x-text="row.jabatan ? row.jabatan.name : '-'"></span>
                                <span x-show="!row.jabatan" class="text-gray-400 text-xs">-</span>
                            </td>
                            <td class="px-4 py-2 flex items-center gap-1">
                                <a :href="'/dashboard/master/karyawan/' + row.id + '/edit'"
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

                                <form :action="'/dashboard/master/karyawan/' + row.id" method="POST"
                                    @submit.prevent="deleteRow($event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 cursor-pointer">
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
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData().length === 0">
                        <td colspan="7" class="text-center px-4 py-2 text-gray-500">No results found.</td>
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

        {{-- Import Modal --}}
        <div x-show="importModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="importModal = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display: none;"
             x-cloak>

            <div @click.outside="importModal = false"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-gray-100">

                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Import Karyawan dari Excel</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Pilih jabatan lalu upload file Excel</p>
                    </div>
                    <button @click="importModal = false" type="button"
                        class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form action="{{ route('dashboard.master.karyawan.import') }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                    @csrf

                    {{-- Jabatan --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Jabatan <span class="text-red-500">*</span>
                        </label>
                        <select name="jabatan_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-green-500 focus:ring-1 focus:ring-green-500 bg-white">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}">{{ $jabatan->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Upload File --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            File Excel <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="file" accept=".xlsx,.xls" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-green-500 bg-white file:mr-3 file:py-1 file:px-3 file:border-0 file:rounded-md file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer" />
                        <p class="text-xs text-gray-400 mt-1">Maksimal 5MB. Format: .xlsx atau .xls</p>
                    </div>

                    {{-- Info Kolom --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-xs text-blue-700">
                        <p class="font-semibold mb-1">📋 Kolom yang diperlukan:</p>
                        <ul class="space-y-0.5 pl-2">
                            <li><span class="font-mono bg-blue-100 px-1 rounded">A</span> — <strong>nama</strong> (wajib)</li>
                            <li><span class="font-mono bg-blue-100 px-1 rounded">B</span> — <strong>nomor_hp</strong></li>
                            <li><span class="font-mono bg-blue-100 px-1 rounded">C</span> — <strong>jenis_kelamin</strong> (Laki-laki / Perempuan)</li>
                        </ul>
                        <p class="mt-2">Password default: <span class="font-mono font-bold">breskul</span></p>
                    </div>

                    {{-- Download Template --}}
                    <div class="flex items-center justify-between pt-1">
                        <a href="{{ route('dashboard.master.karyawan.template') }}"
                            class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Template Excel
                        </a>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="importModal = false"
                            class="flex-1 py-2 px-4 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition cursor-pointer flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
