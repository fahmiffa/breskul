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
    filterKelas: '{{ request('kelas', '') }}',
    filterPesantren: '',
    showImportModal: false,
    showJob: false,
    showRfidModal: false,
    selectedStudentId: null,
    rfid: null,

    filteredData() {
        let temp = this.rows.filter((row) => {
            const searchLower = this.search ? this.search.toLowerCase() : '';
            const rowName = (row.name || '').toLowerCase();
            const rowNis = (row.nis || '').toLowerCase();
            const rowAlamat = (row.alamat || '').toLowerCase();
            const rowKelas = row.reg ? ({{ config('app.school_mode') ? 'row.reg.kelas?.name' : 'row.reg.prodi?.name' }} || '').toLowerCase() : '';
            const rowPesantren = (row.boarding ? 'pesantren' : 'bukan pesantren non pesantren');

            const matchesSearch = searchLower === '' ||
                rowName.includes(searchLower) ||
                rowNis.includes(searchLower) ||
                rowAlamat.includes(searchLower) ||
                rowKelas.includes(searchLower) ||
                rowPesantren.includes(searchLower);

            let matchesKelas = true;
            if (this.filterKelas !== '') {
                if (row.reg) {
                    const classId = {{ config('app.school_mode') ? 'row.reg.class_id' : 'row.reg.prodi_id' }};
                    matchesKelas = classId == this.filterKelas;
                } else {
                    matchesKelas = false;
                }
            }

            let matchesPesantren = true;
            if (this.filterPesantren !== '') {
                matchesPesantren = (this.filterPesantren === '1' ? !!row.boarding : !row.boarding);
            }

            return matchesSearch && matchesKelas && matchesPesantren;
        });

        temp.sort((a, b) => {
            let valA = a[this.sortColumn];
            let valB = b[this.sortColumn];

            if (this.sortColumn === 'kelas') {
                valA = a.reg ? ({{ config('app.school_mode') ? 'a.reg.kelas?.name' : 'a.reg.prodi?.name' }} || '') : '';
                valB = b.reg ? ({{ config('app.school_mode') ? 'b.reg.kelas?.name' : 'b.reg.prodi?.name' }} || '') : '';
            }

            if (this.sortColumn === 'boarding') {
                valA = a.boarding ? 1 : 0;
                valB = b.boarding ? 1 : 0;
            }

            if (typeof valA === 'string') valA = valA.toLowerCase();
            if (typeof valB === 'string') valB = valB.toLowerCase();

            if (valA < valB) return this.sortAsc ? -1 : 1;
            if (valA > valB) return this.sortAsc ? 1 : -1;
            return 0;
        });

        return temp;
    }
}">

    <div class="mb-4 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3">
        <div class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" x-model="search" @input="currentPage = 1" placeholder="Pencarian nama, {{ config('app.school_mode') ? 'NIS' : 'NIM' }}, alamat..."
                class="flex-1 min-w-[200px] border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245]" />

            <select x-model="filterKelas" @change="currentPage = 1"
                class="border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245] bg-white text-sm">
                <option value="">Semua {{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</option>
                @foreach ($kelas as $k)
                <option value="{{ $k->id }}">{{ $k->name }}</option>
                @endforeach
            </select>

            <select x-model="filterPesantren" @change="currentPage = 1"
                class="border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245] bg-white text-sm">
                <option value="">Semua Status</option>
                <option value="1">Pesantren</option>
                <option value="0">Bukan Pesantren</option>
            </select>

            <select x-model="perPage" @change="currentPage = 1"
                class="border border-gray-300 ring-0 rounded-xl px-3 py-2 focus:outline-[#177245] bg-white text-sm">
                <option value="10">Tampilkan 10</option>
                <option value="100">Tampilkan 100</option>
                <option value="1000">Tampilkan 1000</option>
                <option value="all">Tampilkan Semua</option>
            </select>
        </div>

        <div class="flex gap-2 items-center justify-end shrink-0">
            <a href="{{ route('dashboard.master.murid.create') }}"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah
            </a>

            <!-- Ubah tombol jadi buka modal -->
            <button @click="showImportModal = true"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Import
            </button>
        </div>
    </div>

    <!-- Tabel (tetap sama) -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead>
                <tr class="bg-green-500 text-left text-white">
                    <th class="px-4 py-2">No</th>
                    <th @click="sortBy('nis')" class="cursor-pointer px-4 py-2">{{ config('app.school_mode') ? 'NIS' : 'NIM' }}</th>
                    <th @click="sortBy('name')" class="cursor-pointer px-4 py-2">Nama</th>
                    <th @click="sortBy('kelas')" class="cursor-pointer px-4 py-2">{{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</th>
                    <th @click="sortBy('jenis')" class="cursor-pointer px-4 py-2">Gender</th>
                    <th @click="sortBy('boarding')" class="cursor-pointer px-4 py-2 text-center">Pesantren</th>
                    <th class="cursor-pointer px-4 py-2">Alamat</th>
                    @if(config('app.qrcode'))
                    <th class="px-4 py-2">QR Code</th>
                    @endif
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in paginatedData()" :key="index">
                    <tr class="border-t border-gray-300">
                        <td class="px-4 py-2" x-text="perPage === 'all' ? index + 1 : ((currentPage - 1) * parseInt(perPage)) + index + 1"></td>
                        <td class="px-4 py-2" x-text="row.nis"></td>
                        <td class="px-4 py-2" x-text="row.name"></td>
                        <td class="px-4 py-2">
                            <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 text-nowrap"
                                x-text="row.reg ? ({{ config('app.school_mode') ? 'row.reg.kelas?.name' : 'row.reg.prodi?.name' }} || '-') : '-'">
                            </span>
                        </td>
                        <td class="px-4 py-2" x-text="row.jenis"></td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold text-nowrap"
                                :class="row.boarding ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500'"
                                x-text="row.boarding ? 'Pesantren' : 'Bukan Pesantren'">
                            </span>
                        </td>
                        <td class="px-4 py-2" x-text="row.alamat"></td>
                        @if(config('app.qrcode'))
                        <td class="px-4 py-2">
                            <div class="flex flex-col items-center gap-1">
                                <img :src="'/dashboard/master/murid/' + row.id + '/qrcode'" alt="QR Code" class="w-16 h-16 border p-1 bg-white">
                                <a :href="'/dashboard/master/murid/' + row.id + '/qrcode/download'"
                                    class="text-[10px] bg-blue-500 hover:bg-blue-600 text-white px-2 py-0.5 rounded transition-colors">
                                    Download
                                </a>
                            </div>
                        </td>
                        @endif
                        <td class="px-4 py-2 flex items-center gap-1">
                            <a :href="'/dashboard/master/murid/' + row.id + '/edit'"
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

                            <form :action="'/dashboard/master/murid/' + row.id" method="POST"
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

                            @if(config('app.uuid'))
                            <button @click="showRfidModal = true; selectedStudentId = row.id; rfid = row.uuid"
                                :class="{
                                        'bg-green-500 hover:bg-green-700': row.uuid,
                                        'bg-red-500 hover:bg-red-700': !row.uuid
                                    }"
                                class="cursor-pointer text-xs text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                                <div class="flex gap-2 items-center">
                                    RFID
                                </div>
                            </button>
                            @endif

                        </td>
                    </tr>
                </template>
                <tr x-show="filteredData().length === 0">
                    <td colspan="{{ config('app.qrcode') ? 9 : 8 }}" class="text-center px-4 py-2 text-gray-500">No results found.</td>
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

    <!-- Modal Import (Background Job + Progress) -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="showImportModal = false">
            <h2 class="text-lg font-semibold mb-4">Import Data {{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}</h2>

            <div x-data="generateStudentsImport()">
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 flex items-start gap-3 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500 shrink-0 mt-0.5">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <div class="text-xs text-blue-700 leading-relaxed">
                        Nama dan NIS wajib diisi. Kolom lainnya opsional.
                        Pastikan format file sesuai dengan template.
                        <br>
                        <a href="{{ route('dashboard.master.murid.template') }}" class="font-bold underline hover:text-blue-800">Download Template di sini</a>
                    </div>
                </div>

                <div class="mb-4">
                    <input type="file" accept=".xlsx,.xls,.csv" @change="file = $event.target.files[0]"
                        class="border border-gray-300 rounded px-3 py-2 w-full" />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">{{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}</label>
                    <select x-model="kelas"
                        class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                        required>
                        <option value="">Pilih {{ config('app.school_mode') ? 'kelas' : 'prodi' }}</option>
                        @foreach ($kelas as $row)
                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showImportModal = false"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" :disabled="isLoading">Batal</button>

                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                            :disabled="isLoading || !file || !kelas">
                            <span x-show="!isLoading">Import</span>
                            <span x-show="isLoading">Memproses...</span>
                        </button>
                    </div>
                </form>

                <div class="w-full bg-gray-200 h-4 mt-4 rounded">
                    <div class="bg-green-500 h-4 rounded transition-all duration-300" :style="{ width: progress + '%' }"></div>
                </div>
                <p class="mt-2 text-sm text-gray-700" x-text="progress + '%' "></p>

                <template x-if="message">
                    <p class="text-green-600 text-sm mt-2" x-text="message"></p>
                </template>

                <template x-if="error">
                    <p class="text-red-600 text-sm mt-2" x-text="error"></p>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal RFID -->
    <div x-show="showRfidModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="showRfidModal = false">
            <h2 class="text-lg font-semibold mb-4">RFID</h2>

            <!-- Status RFID saat ini -->
            <div class="mb-4 p-3 rounded-lg" :class="rfid ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-3 h-3 rounded-full" :class="rfid ? 'bg-green-500' : 'bg-red-500'"></span>
                    <span class="text-sm font-medium" :class="rfid ? 'text-green-700' : 'text-red-700'" x-text="rfid ? 'RFID Terdaftar' : 'RFID Belum Terdaftar'"></span>
                </div>
                <template x-if="rfid">
                    <p class="mt-1 text-sm text-green-600 font-mono ml-5" x-text="rfid"></p>
                </template>
            </div>

            <form :action="'/dashboard/master/rfid/' + selectedStudentId" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode RFID</label>
                    <input type="text" name="rfid" required x-model="rfid"
                        placeholder="Tap kartu RFID atau ketik manual..."
                        class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-[#177245]" />
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRfidModal = false"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showJob" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md" @click.away="showJob = false">
            <h2 class="text-lg font-semibold mb-4">Bill Job</h2>

            <div x-data="generateBill()">
                <form @submit.prevent="submitForm">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showJob = false"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                            :disabled="isLoading">Batal</button>

                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                            :disabled="isLoading">
                            <span x-show="!isLoading">Import</span>
                            <span x-show="isLoading">Memproses...</span>
                        </button>
                    </div>
                </form>

                <!-- Progress bar -->
                <div class="w-full bg-gray-200 h-4 mt-4 rounded">
                    <div class="bg-green-500 h-4 rounded transition-all duration-300"
                        :style="{ width: progress + '%' }"></div>
                </div>
                <p class="mt-2 text-sm text-gray-700" x-text="progress + '%'"></p>

                <!-- Notification -->
                <template x-if="message">
                    <p class="text-green-600 text-sm mt-2" x-text="message"></p>
                </template>

                <template x-if="error">
                    <p class="text-red-600 text-sm mt-2" x-text="error"></p>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection