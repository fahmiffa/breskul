@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        <form method="POST"
            action="{{ isset($items) ? route('dashboard.master.fitur.update', ['fitur' => $items->id]) : route('dashboard.master.fitur.store') }}"
            class="grid grid-cols-1">
            @isset($items)
                @method('PUT')
            @endisset
            @csrf
            <div class="grid grid-cols-1 gap-2">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Fitur</label>
                    <div class="relative">
                        <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                            placeholder="Contoh: Manajemen Pengguna"
                            required
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <button type="submit"
                    class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-4 rounded-2xl focus:outline-none focus:shadow-outline transition-colors">
                    Simpan
                </button>
                <a href="{{ route('dashboard.master.fitur.index') }}"
                    class="cursor-pointer bg-gray-500 text-sm hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-2xl focus:outline-none focus:shadow-outline transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
