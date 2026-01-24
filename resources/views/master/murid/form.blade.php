@extends('base.layout')
@section('title', $action)
@section('content')

<div class="flex flex-col bg-white rounded-lg shadow-md p-6">
    <div class="font-semibold mb-3 text-xl">{{ $action }}</div>

    <form method="POST"
        action="{{ isset($items) ? route('dashboard.master.murid.update', ['murid' => $items->id]) : route('dashboard.master.murid.store') }}"
        class="grid grid-cols-1" enctype="multipart/form-data">
        @isset($items)
        @method('PUT')
        @endisset
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">{{ config('app.school_mode') ? 'NIS' : 'NIM' }}</label>
                <div class="relative">
                    <input type="text" name="nis" value="{{ old('nis', $items->nis ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
                @error('nis')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                <div class="relative">
                    <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
                @error('name')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            @if(config('app.school_mode'))
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Kelas</label>
                <select name="kelas"
                    class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                    required>
                    <option value="">Pilih kelas</option>
                    @foreach ($kelas as $row)
                    <option value="{{ $row->id }}" @selected(old('kelas', isset($items) && $items->head->first() ? $items->head->first()->class_id : '') == $row->id)>{{ $row->name }}</option>
                    @endforeach
                </select>

                @error('kelas')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            @else
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Prodi</label>
                <select name="prodi"
                    class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                    required>
                    <option value="">Pilih Prodi</option>
                    @foreach ($prodis as $row)
                    <option value="{{ $row->id }}" @selected(old('prodi', isset($items) && $items->head->first() ? $items->head->first()->prodi_id : '') == $row->id)>{{ $row->name }}</option>
                    @endforeach
                </select>

                @error('prodi')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            @endif
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Semester</label>
                <select name="akademik"
                    class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                    required>
                    <option value="">Pilih Semester</option>
                    @foreach ($akademik as $row)
                    <option value="{{ $row->id }}" @selected(old('akademik', isset($items) && $items->head->first() ? $items->head->first()->academic_id : '') == $row->id)>{{ $row->name }}</option>
                    @endforeach
                </select>

                @error('akademik')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4" x-data="{ imagePreview: '{{ isset($items) && $items->img ? asset('storage/' . $items->img) : null }}' }">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Photo</label>
                <input type="file" name="image" accept="image/*"
                    @change="let file = $event.target.files[0]; imagePreview = URL.createObjectURL(file)"
                    class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0
                   file:text-sm file:font-semibold file:bg-blue-50 file:text-green-700 
                   hover:file:bg-blue-100 cursor-pointer" />
                <template x-if="imagePreview">
                    <img :src="imagePreview" class="w-24 h-24 object-cover rounded border border-gray-300 my-3" />
                </template>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat</label>
                <textarea name="alamat" class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">{{ old('alamat', $items->alamat ?? '') }}</textarea>
                @error('alamat')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Jenis Kelamin</label>
                <select name="gender"
                    class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                    required>
                    <option value="">Pilih Jenis</option>
                    <option value="1" @selected(old('gender', isset($items) && $items->gender) == '1')>Laki-laki</option>
                    <option value="2" @selected(old('gender', isset($items) && $items->gender) == '2')>Perempuan</option>
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
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    <input type="date" name="birth" value="{{ old('birth', $items->birth ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor HP</label>
                <div class="relative">
                    <input type="text" name="hp_siswa" value="{{ old('hp_siswa', $items->hp_siswa ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
                @error('hp_siswa')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <div class="relative">
                    <input type="email" name="email" value="{{ old('email', $items->email ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
                @error('email')
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
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    <input type="text" placeholder="Pekerjaan" name="dadJob"
                        value="{{ old('dadJob', $items->dadJob ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Ibu</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="mom" placeholder="Nama"
                        value="{{ old('mom', $items->mom ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    <input type="text" placeholder="Pekerjaan" name="momJob"
                        value="{{ old('momJob', $items->momJob ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor HP Orang Tua</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="hp_parent" placeholder="Nomor HP"
                        value="{{ old('hp_parent', $items->hp_parent ?? '') }}"
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                </div>
            </div>
        </div>
        <div class="flex items-center">
            <button type="submit"
                class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection