@extends('base.layout')
@section('title', $action)
@section('content')

<div class="flex flex-col bg-white rounded-lg shadow-md p-6">


    <form method="POST"
        action="{{ isset($items) ? route('dashboard.master.soal.update', ['soal' => $items->id]) : route('dashboard.master.soal.store') }}"
        class="space-y-6" x-data="{ tipe: '{{ old('tipe', $items->tipe ?? 'Pilihan ganda') }}' }">
        @isset($items)
        @method('PUT')
        @endisset
        @csrf

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
            <div class="font-semibold text-xl text-gray-800">{{ $action }} Soal</div>
            <div class="w-full md:w-64">
                <label class="block text-gray-600 text-[11px] font-bold uppercase mb-1 ml-1">Tipe Pertanyaan</label>
                <select name="tipe" x-model="tipe" required
                    class="block border border-gray-300 ring-0 rounded-xl px-4 py-2.5 w-full focus:outline-[#177245] bg-gray-50 font-semibold text-gray-700">
                    <option value="Pilihan ganda">Pilihan ganda</option>
                    <option value="Isian">Isian</option>
                </select>
                @error('tipe')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 ml-1">Pertanyaan / Nama Soal</label>
                <div x-data="trixEditor()" x-init="init()" class="bg-white rounded-xl overflow-hidden border border-gray-300 focus-within:border-[#177245] transition-colors">
                    <input id="nama_soal" type="hidden" name="nama" value="{{ old('nama', $items->nama ?? '') }}" x-ref="input">
                    <trix-editor input="nama_soal" x-ref="trix" class="min-h-[200px] p-4 text-gray-800" placeholder="Tuliskan pertanyaan di sini..."></trix-editor>
                </div>
                @error('nama')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="tipe == 'Pilihan ganda'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-5 bg-gray-50 rounded-2xl border border-gray-200 space-y-4 shadow-inner">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1 h-5 bg-green-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">Opsi Jawaban</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                    <div class="group">
                        <label class="block text-gray-600 text-[10px] font-bold uppercase mb-1 ml-2 group-focus-within:text-green-600 transition-colors tracking-widest">Opsi {{ strtoupper($opt) }}</label>
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-300 font-bold text-gray-500 text-xs uppercase">{{ $opt }}</span>
                            <input type="text" name="opsi_{{ $opt }}" :required="tipe == 'Pilihan ganda'" value="{{ old('opsi_'.$opt, $items->{'opsi_'.$opt} ?? '') }}"
                                class="border border-gray-300 ring-0 rounded-xl px-4 py-2 w-full focus:outline-[#177245] bg-white group-hover:border-gray-400 focus:bg-white transition-all shadow-sm">
                        </div>
                        @error('opsi_'.$opt)
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="p-5 bg-green-50/30 rounded-2xl border border-green-100 space-y-3">
                <label class="block text-green-800 text-sm font-bold mb-1 ml-1">Kunci Jawaban</label>
                <div class="relative">
                    <input type="text" name="jawaban" required value="{{ old('jawaban', $items->jawaban ?? '') }}"
                        placeholder="{{ $action == 'Tambah' ? 'Masukkan jawaban yang benar' : '' }}"
                        class="border border-green-200 ring-0 rounded-xl px-4 py-3 w-full focus:outline-green-500 bg-white font-medium text-green-900 shadow-sm">
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400">
                            <path d="m9 12 2 2 4-4" />
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-start gap-2 text-gray-500 text-[11px] italic ml-1 bg-white p-2 rounded-lg border border-dashed border-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <span><strong>Tips:</strong> Untuk pilihan ganda, masukkan teks opsi yang benar agar sistem dapat melakukan koreksi otomatis.</span>
                </div>
                @error('jawaban')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-between pt-6 border-t">
            <a href="{{ route('dashboard.master.soal.index') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors font-semibold group">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-1 transition-transform">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Kembali
            </a>
            <button type="submit"
                class="cursor-pointer bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-10 rounded-xl focus:outline-none focus:shadow-outline transition-all duration-200 shadow-lg shadow-green-200 flex items-center gap-2">
                <span>Simpan Soal</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
            </button>
        </div>
    </form>
</div>
@endsection