@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        @isset($items)
            <form method="POST" action="{{ route('dashboard.master.ekstrakurikuler.update', $items->id) }}" class="grid grid-cols-1">
                @method('PUT')
        @else
            <form method="POST" action="{{ route('dashboard.master.ekstrakurikuler.store') }}" class="grid grid-cols-1">
        @endisset
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" name="nama" value="{{ old('nama', $items->nama ?? '') }}"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    @error('nama')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Guru</label>
                    <div class="relative">
                        <select name="guru_id" class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                            <option value="">Pilih Guru</option>
                            @foreach ($teaches as $teach)
                                <option value="{{ $teach->id }}" {{ old('guru_id', $items->guru_id ?? '') == $teach->id ? 'selected' : '' }}>
                                    {{ $teach->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('guru_id')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4" x-data="{ 
                    waktu: '{{ old('waktu', isset($items) ? \Illuminate\Support\Carbon::parse($items->waktu)->format('Y-m-d\TH:i') : '') }}',
                    get formattedWaktuIndo() {
                        if (!this.waktu) return '';
                        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        
                        const date = new Date(this.waktu);
                        if (isNaN(date.getTime())) return '';
                        const dayName = days[date.getDay()];
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');
                        
                        return `${dayName}, ${day} ${monthName} ${year} Jam ${hours}:${minutes}`;
                    }
                }">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Waktu</label>
                    <div class="relative">
                        <input type="datetime-local" name="waktu" x-model="waktu"
                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    <p class="text-green-600 text-xs font-semibold mt-1" x-show="waktu" x-text="formattedWaktuIndo"></p>
                    @error('waktu')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <button type="submit"
                        class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                        Simpan
                    </button>
                    <a href="{{ route('dashboard.master.ekstrakurikuler.index') }}" class="ml-2 text-gray-600 hover:text-gray-800">Batal</a>
                </div>
            </form>
    </div>
@endsection
