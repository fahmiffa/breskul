@extends('base.layout')
@section('title', $title ?? $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto md:mx-0">
        <div class="font-semibold mb-4 text-xl text-gray-800 border-b pb-2">{{ $action }}</div>
        
        <form method="POST"
            action="{{ isset($items) ? route('dashboard.master.bank.update', ['bank' => $items->id]) : route('dashboard.master.bank.store') }}"
            class="space-y-4">
            @isset($items)
                @method('PUT')
            @endisset
            @csrf

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Bank <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                    placeholder="Contoh: Bank BCA, Bank Mandiri, Bank BRI, Bank BSI"
                    class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245] focus:border-green-600 transition"
                    required>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor Rekening <span class="text-red-500">*</span></label>
                <input type="text" name="number" value="{{ old('number', $items->number ?? '') }}"
                    placeholder="Contoh: 1234567890"
                    class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245] focus:border-green-600 transition"
                    required>
                @error('number')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if(auth()->user()->role == 0)
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Aplikasi / Lembaga</label>
                <select name="app_id"
                    class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245] focus:border-green-600 transition">
                    <option value="">-- Pilih Aplikasi (Opsional) --</option>
                    @foreach ($apps as $app)
                        <option value="{{ $app->id }}"
                            {{ old('app_id', $items->app_id ?? '') == $app->id ? 'selected' : '' }}>
                            {{ $app->name }}
                        </option>
                    @endforeach
                </select>
                @error('app_id')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <div class="flex items-center gap-3 pt-4 border-t">
                <button type="submit"
                    class="cursor-pointer bg-green-600 text-sm hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-xl focus:outline-none transition-colors shadow">
                    Simpan
                </button>
                <a href="{{ route('dashboard.master.bank.index') }}"
                    class="cursor-pointer bg-gray-500 text-sm hover:bg-gray-600 text-white font-semibold py-2.5 px-5 rounded-xl focus:outline-none transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
