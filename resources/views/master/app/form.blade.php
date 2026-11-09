@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        <form method="POST"
            action="{{ isset($items) ? route('dashboard.master.app.update', ['app' => $items->id]) : route('dashboard.master.app.store') }}"
            class="grid grid-cols-1">
            @isset($items)
                @method('PUT')
            @endisset
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                            placeholder="Nama Aplikasi / Sekolah / Kampus"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email', $items->user->email ?? '') }}"
                            placeholder="admin@example.com"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor HP</label>
                    <div class="relative">
                        <input type="text" name="hp" value="{{ old('hp', $items->user->nomor ?? '') }}"
                            placeholder="081234567890"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                    </div>
                    @error('hp')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Relasi Fitur (feature_apps) --}}
            <div class="mt-4 mb-6 border-t border-gray-200 pt-4">
                <label class="block text-gray-700 text-sm font-semibold mb-1">Fitur Aplikasi</label>
                <p class="text-xs text-gray-500 mb-3">Pilih fitur yang aktif dan dapat diakses oleh aplikasi ini:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @forelse ($features as $feature)
                        @php
                            $checked = in_array($feature->id, old('feature_ids', $selectedFeatures ?? []));
                        @endphp
                        <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-green-50 border-gray-200 bg-white has-[:checked]:bg-green-50 has-[:checked]:border-green-500">
                            <input type="checkbox" name="feature_ids[]" value="{{ $feature->id }}"
                                {{ $checked ? 'checked' : '' }}
                                class="w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500">
                            <span class="text-sm font-medium text-gray-800">{{ $feature->name }}</span>
                        </label>
                    @empty
                        <div class="col-span-full p-4 bg-gray-50 rounded-xl text-xs text-gray-500 border border-dashed border-gray-300">
                            Belum ada master fitur yang tersedia. Silakan tambahkan fitur terlebih dahulu di menu <a href="{{ route('dashboard.master.fitur.index') }}" class="text-green-600 underline font-semibold">Master Fitur</a>.
                        </div>
                    @endforelse
                </div>
                @error('feature_ids')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                    class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-4 rounded-2xl focus:outline-none focus:shadow-outline transition-colors">
                    Simpan
                </button>
                <a href="{{ route('dashboard.master.app.index') }}"
                    class="cursor-pointer bg-gray-500 text-sm hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-2xl focus:outline-none focus:shadow-outline transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
