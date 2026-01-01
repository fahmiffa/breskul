@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        <form method="POST"
            action="{{ isset($items) ? route('dashboard.master.guru.update', ['guru' => $items->id]) : route('dashboard.master.guru.store') }}"
            class="grid grid-cols-1">
            @isset($items)
                @method('PUT')
            @endisset
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full  focus:outline-[#177245]">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Gender</label>
                    <select name="gender"
                        class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                        required>
                        <option value="">Pilih Gender</option>
                        <option value="1" @selected(old('gender', isset($items) && $items->gender) == '1')>Laki-laki</option>
                        <option value="2" @selected(old('gender', isset($items) && $items->gender) == '2')>Perempuan</option>
                    </select>
    
                    @error('gender')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat</label>
                    <textarea name="alamat" class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">{{ old('alamat', $items->alamat ?? '') }}</textarea>
                    @error('alamat')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
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
