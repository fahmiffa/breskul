@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="halaqahForm({
        teaches: {{ json_encode($teaches) }},
        defaultTeachName: '{{ old('teach_id') ? '' : (isset($items) ? optional($items->teach)->name : '') }}',
        defaultTeachId: '{{ old('teach_id', $items->teach_id ?? '') }}',
        allStudents: {{ json_encode($students) }},
        selectedStudents: {{ json_encode(isset($selectedStudents) ? $students->whereIn('id', $selectedStudents)->values() : []) }}
    })">
        <div class="font-semibold mb-3 text-xl">{{ $action }} Halaqah</div>
        @isset($items)
            <form method="POST" action="{{ route('dashboard.master.halaqah.update', $items->id) }}"
                class="grid grid-cols-1">
                @method('PUT')
            @else
                <form method="POST" action="{{ route('dashboard.master.halaqah.store') }}" class="grid grid-cols-1">
                @endisset
                @csrf

                {{-- Nama Halaqah --}}
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Halaqah</label>
                    <div class="relative">
                        <input type="text" name="nama" value="{{ old('nama', $items->nama ?? '') }}"
                            placeholder="Contoh: Halaqah Abu Bakar"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    @error('nama')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Guru (Select Search) --}}
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Guru Pengajar</label>
                    <div class="relative w-full md:w-1/2">
                        <input type="text" x-model="teachSearch" @focus="showTeachDropdown = true"
                            @click.away="showTeachDropdown = false" placeholder="Cari guru..."
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                        <input type="hidden" name="teach_id" :value="selectedTeachId">

                        <div x-show="showTeachDropdown && filteredTeaches.length > 0"
                            class="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-1 max-h-48 overflow-y-auto shadow-lg">
                            <template x-for="teach in filteredTeaches" :key="teach.id">
                                <div @click="selectTeach(teach)"
                                    class="px-3 py-2 hover:bg-green-100 cursor-pointer text-sm"
                                    x-text="teach.name">
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('teach_id')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Hari --}}
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Hari</label>
                    <div class="relative">
                        <select name="hari"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                            <option value="">Pilih Hari</option>
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                <option value="{{ $hari }}"
                                    {{ old('hari', $items->hari ?? '') == $hari ? 'selected' : '' }}>
                                    {{ $hari }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('hari')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Waktu --}}
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Waktu</label>
                    <div class="relative">
                        <input type="time" name="waktu" value="{{ old('waktu', $items->waktu ?? '') }}"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    @error('waktu')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Status Halaqah</label>
                    <div class="flex items-center gap-6 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                            <input type="radio" name="status" value="1"
                                {{ old('status', isset($items) ? ($items->status ? '1' : '0') : '1') === '1' ? 'checked' : '' }}
                                class="accent-green-600 w-4 h-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                Aktif
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                            <input type="radio" name="status" value="0"
                                {{ old('status', isset($items) ? ($items->status ? '1' : '0') : '1') === '0' ? 'checked' : '' }}
                                class="accent-red-600 w-4 h-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                Tidak Aktif
                            </span>
                        </label>
                    </div>
                    @error('status')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Keterangan (Nullable) --}}
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        Keterangan <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                    </label>
                    <div class="relative">
                        <textarea name="keterangan" rows="3" placeholder="Tambahkan catatan atau deskripsi halaqah (opsional)..."
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">{{ old('keterangan', $items->keterangan ?? '') }}</textarea>
                    </div>
                    @error('keterangan')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Siswa Section --}}
                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Tambah Siswa (Khusus Boarding)</label>

                    {{-- Mode Selection --}}
                    <div class="flex gap-4 mb-3">
                        <label class="flex items-center gap-1.5 text-sm cursor-pointer font-medium text-gray-700">
                            <input type="radio" x-model="selectMode" value="siswa" class="accent-green-600">
                            Per Siswa
                        </label>
                        <label class="flex items-center gap-1.5 text-sm cursor-pointer font-medium text-gray-700">
                            <input type="radio" x-model="selectMode" value="kelas" class="accent-green-600">
                            Per Kelas
                        </label>
                    </div>

                    {{-- Opsi Per Kelas --}}
                    <div x-show="selectMode === 'kelas'" class="mb-4 w-full md:w-2/3">
                        <div class="mb-3">
                            <select @change="loadStudentsByClass($event.target.value)"
                                class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-3/4 focus:outline-[#177245]">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($classes as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Loading Indicator --}}
                        <div x-show="isLoadingClassStudents" class="flex items-center gap-2 text-sm text-gray-500 py-2">
                            <svg class="animate-spin h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memuat daftar siswa boarding...</span>
                        </div>

                        {{-- Preview Siswa Per Kelas --}}
                        <div x-show="!isLoadingClassStudents && classLoaded" class="border border-green-200 bg-green-50/40 rounded-xl p-4 mt-2">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-green-200">
                                <div class="text-sm font-semibold text-gray-800">
                                    Daftar Siswa Boarding di Kelas (<span x-text="classStudents.length"></span>)
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-1.5 text-xs cursor-pointer font-medium text-gray-600" x-show="classStudents.length > 0">
                                        <input type="checkbox" @change="toggleAllClassStudents($event)" :checked="isAllClassStudentsChecked()" class="accent-green-600 rounded">
                                        <span>Pilih Semua</span>
                                    </label>
                                    <button type="button" @click="addAllClassStudents()"
                                        :disabled="selectedClassStudentIds.length === 0"
                                        class="bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold py-1.5 px-3 rounded-lg flex items-center gap-1 cursor-pointer transition">
                                        <span>+ Masukkan ke Halaqah</span>
                                        <span x-show="selectedClassStudentIds.length > 0" x-text="'(' + selectedClassStudentIds.length + ')'"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- List Siswa Kelas --}}
                            <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1">
                                <template x-for="st in classStudents" :key="st.id">
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition text-sm">
                                        <label class="flex items-center gap-2 cursor-pointer flex-1">
                                            <input type="checkbox" :value="st.id" x-model="selectedClassStudentIds"
                                                :disabled="isClassStudentAlreadyAdded(st.id)"
                                                class="accent-green-600 rounded">
                                            <span :class="isClassStudentAlreadyAdded(st.id) ? 'text-gray-400 line-through' : 'text-gray-800 font-medium'" x-text="st.name"></span>
                                        </label>
                                        <span x-show="isClassStudentAlreadyAdded(st.id)" class="text-[11px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">
                                            Sudah ada di daftar
                                        </span>
                                    </div>
                                </template>
                                <div x-show="classStudents.length === 0" class="text-sm text-gray-500 py-3 text-center">
                                    Tidak ada siswa dengan status boarding di kelas ini.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Opsi Per Siswa (Search) --}}
                    <div x-show="selectMode === 'siswa'" class="mb-3">
                        <div class="relative w-full md:w-1/2">
                            <input type="text" x-model="studentSearch" @focus="showStudentDropdown = true"
                                @click.away="showStudentDropdown = false" placeholder="Ketik nama atau cari siswa..."
                                class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                            <div x-show="showStudentDropdown && filteredStudents.length > 0"
                                class="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-1 max-h-48 overflow-y-auto shadow-lg">
                                <template x-for="student in filteredStudents" :key="student.id">
                                    <div @click="addStudent(student)"
                                        class="px-3 py-2 hover:bg-green-100 cursor-pointer text-sm border-b border-gray-50 last:border-0"
                                        x-text="student.name">
                                    </div>
                                </template>
                            </div>
                            <div x-show="showStudentDropdown && studentSearch && filteredStudents.length === 0"
                                class="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-1 p-3 text-xs text-gray-500 shadow-lg">
                                Siswa boarding tidak ditemukan atau sudah dipilih.
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Siswa Terpilih yang Akan Disimpan --}}
                    <div class="border border-gray-300 rounded-xl p-4 w-full md:w-1/2 bg-gray-50/50 mt-4">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-200">
                            <div class="text-sm font-semibold text-gray-700">
                                Siswa Terpilih untuk Halaqah (<span x-text="selectedStudents.length" class="text-green-700 font-bold"></span>)
                            </div>
                            <button type="button" @click="selectedStudents = []" x-show="selectedStudents.length > 0"
                                class="text-xs text-red-500 hover:text-red-700 cursor-pointer">
                                Hapus Semua
                            </button>
                        </div>

                        <div class="max-h-64 overflow-y-auto space-y-1 pr-1">
                            <template x-for="(s, i) in selectedStudents" :key="s.id">
                                <div class="flex justify-between items-center py-1.5 px-2 bg-white rounded-lg border border-gray-200 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400" x-text="(i + 1) + '.'"></span>
                                        <span class="font-medium text-gray-800" x-text="s.name"></span>
                                    </div>
                                    <input type="hidden" :name="'students_id[' + i + ']'" :value="s.id">
                                    <button type="button" @click="removeStudent(i)"
                                        title="Hapus"
                                        class="text-red-400 hover:text-red-600 cursor-pointer p-1 rounded hover:bg-red-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <div x-show="selectedStudents.length === 0" class="text-xs text-gray-400 py-3 text-center italic">
                                Belum ada siswa yang dipilih. Gunakan pilihan "Per Siswa" atau "Per Kelas" di atas.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-4">
                    <button type="submit"
                        class="cursor-pointer bg-green-600 text-sm hover:bg-green-700 text-white font-bold py-2.5 px-5 rounded-2xl focus:outline-none focus:shadow-outline transition">
                        Simpan
                    </button>
                    <a href="{{ route('dashboard.master.halaqah.index') }}"
                        class="ml-2 text-sm text-gray-600 hover:text-gray-800 py-2.5 px-4 rounded-xl border border-gray-300 hover:bg-gray-100 transition">
                        Batal
                    </a>
                </div>
            </form>
    </div>
@endsection
