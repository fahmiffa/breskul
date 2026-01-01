@extends('base.layout')
@section('title', 'Dashboard')
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>

        {{-- @if ($errors->any())
            <div class="text-red-500">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}
        <form method="POST" action="{{ route('dashboard.reg.store') }}"
            class="flex flex-col" enctype="multipart/form-data">
            @isset($items)
                @method('PUT')
            @endisset
            @csrf
            <div x-data="{ jenis: '{{ old('option', 1) }}' }">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2"
                    x-data=reg(@json($kelas),@json($paket),@json($unit))>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Jenjang</label>
                        <select name="grade" required
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            <option value="">Pilih Jenjang</option>
                            @foreach ($grade as $val)
                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>

                        @error('grade')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Kelas</label>
                        <select x-model="selectedKelas" name="kelas"
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            <template x-for="(option, index) in optionsKelas" :key="index">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                        @error('kelas')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Pembayaran</label>
                        <select name="kontrak" required
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            <option value="">Pilih Pembayaran</option>
                            @foreach ($kontrak as $row)
                                <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->month }} Bulan)
                                </option>
                            @endforeach
                        </select>
                        @error('kontrak')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Unit</label>
                        <select x-model="selectedUnit" name="unit"
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]"
                            required>
                            <template x-for="option in filteredUnits" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                        @error('unit')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Program Belajar</label>
                        <select x-model="selectedProgram" name="program"
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]"
                            required>
                            <template x-for="option in filteredPrograms" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                        @error('program')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-3">Jenis Siswa</label>
                        <div class="space-y-3">
                            <label class="inline-flex items-center space-x-2">
                                <input type="radio" x-model="jenis" name="option" value="1"
                                    class="form-radio text-orange-600">
                                <span class="text-gray-700">Baru</span>
                            </label>
                            <label class="inline-flex items-center space-x-2">
                                <input type="radio" x-model="jenis" name="option" value="2"
                                    class="form-radio text-orange-600">
                                <span class="text-gray-700">Existing</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-4" x-show="jenis === '2'" id="old">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Pilih Murid</label>
                        <select name="murid"
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            <option value="">Pilih Murid</option>
                            @foreach ($head as $val)
                                <option value="{{ $val->id }}">{{ $val->murid->name }}</option>
                            @endforeach
                        </select>

                        @error('murid')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="new" x-show="jenis === '1'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Siswa</label>
                            <div class="relative">
                                <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('name')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                            <div class="relative">
                                <input type="email" name="email" value="{{ old('email', $items->email ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('email')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-center my-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500 text-sm font-bold">DATA SISWA</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="mb-4" x-data="{ imagePreview: null }">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Photo</label>
                            <input type="file" name="image" accept="image/*"
                                @change="let file = $event.target.files[0]; imagePreview = URL.createObjectURL(file)"
                                class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0
                           file:text-sm file:font-semibold file:bg-blue-50 file:text-orange-700 
                           hover:file:bg-blue-100 cursor-pointer" />
                            <template x-if="imagePreview">
                                <img :src="imagePreview"
                                    class="w-24 h-24 object-cover rounded border border-gray-300 my-3" />
                            </template>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat</label>
                            <textarea name="alamat" class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">{{ old('alamat', $items->alamat ?? '') }}</textarea>
                            @error('alamat')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Gender</label>
                            <select name="gender"
                                class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                                <option value="">Pilih Gender</option>
                                <option value="1">Laki-laki</option>
                                <option value="2">Perempuan</option>
                            </select>

                            @error('gender')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Tempat, Tanggal lahir</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="place" placeholder="Tempat lahir"
                                    value="{{ old('place', $items->place ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                                <input type="date" name="birth" value="{{ old('birth', $items->birth ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Sekolah/Kelas</label>
                            <div class="relative">
                                <input type="text" name="sekolah_kelas"
                                    value="{{ old('sekolah_kelas', $items->sekolah_kelas ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('sekolah_kelas')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat Sekolah</label>
                            <textarea name="alamat_sekolah"
                                class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">{{ old('alamat_sekolah', $items->alamat_sekolah ?? '') }}</textarea>
                            @error('alamat_sekolah')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Cita-cita Siswa</label>
                            <div class="relative">
                                <input type="text" name="dream" value="{{ old('dream', $items->dream ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('dream')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor HP Siswa</label>
                            <div class="relative">
                                <input type="text" name="hp_siswa"
                                    value="{{ old('hp_siswa', $items->hp_siswa ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('hp_siswa')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Agama</label>
                            <div class="relative">
                                <input type="text" name="agama" value="{{ old('agama', $items->agama ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('agama')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4"></div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Sosmed anak</label>
                            <div class="relative">
                                <input type="text" name="sosmedChild"
                                    value="{{ old('sosmedChild', $items->sosmedChild ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('sosmedChild')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Sosmed Ortu/Sdra</label>
                            <div class="relative">
                                <input type="text" name="sosmedOther"
                                    value="{{ old('sosmedOther', $items->sosmedOther ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                            @error('sosmedOther')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-center my-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500 text-sm font-bold">DATA ORANG TUA/WALI</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Ayah</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="dad" placeholder="Nama"
                                    value="{{ old('dad', $items->dad ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                                <input type="text" placeholder="Pekerjaan" name="dadJob"
                                    value="{{ old('dadJob', $items->dadJob ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Ibu</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="mom" placeholder="Nama"
                                    value="{{ old('mom', $items->mom ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                                <input type="text" placeholder="Pekerjaan" name="momJob"
                                    value="{{ old('momJob', $items->momJob ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor HP Orang Tua</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="hp_parent" placeholder="Nomor HP"
                                    value="{{ old('hp_parent', $items->hp_parent ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                        </div>
                        <div class="mb-4"></div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Pendidikan Formal
                                Siswa</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="study" value="{{ old('study', $items->study ?? '') }}"
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Peringkat</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="rank" value="{{ old('rank', $items->rank ?? '') }}" `
                                    class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            </div>
                        </div>

                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Pendidikan Non-Formal
                                Siswa</label>
                            <input id="pendidikan_non_formal" type="hidden" name="pendidikan_non_formal"
                                value="{{ old('pendidikan_non_formal', $data->pendidikan_non_formal ?? '') }}">
                            <trix-editor input="pendidikan_non_formal"
                                class="trix-content bg-transparent border rounded p-2"></trix-editor>

                        </div>

                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Pestasi Akademik/Non Akademik
                                Siswa
                                (3 Tahun Kebelakang)</label>
                            <input id="prestasi" type="hidden" name="prestasi"
                                value="{{ old('prestasi', $data->prestasi ?? '') }}">
                            <trix-editor input="prestasi"
                                class="trix-content bg-transparent border rounded p-2"></trix-editor>
                        </div>

                    </div>
                </div>
            </div>
            <div class="flex items-center">
                <button type="submit"
                    class="cursor-pointer bg-orange-500 text-sm hover:bg-orange-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection
