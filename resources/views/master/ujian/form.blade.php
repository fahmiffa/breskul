@extends('base.layout')
@section('title', $title)
@section('content')

<div class="flex flex-col bg-white rounded-lg shadow-md p-6">
    <form method="POST"
        action="{{ isset($items) ? route('dashboard.master.ujian.update', ['ujian' => $items->id]) : route('dashboard.master.ujian.store') }}"
        class="space-y-6"
        x-data="{
            selectedSoals: {{ json_encode(old('soal_id', isset($items) ? ($items->soal_id ?? []) : [])) }},
            allSoals: {{ json_encode($soals) }},

            mapels: {{ json_encode($mapels->values()) }},
            mapelSearch: '',
            selectedMapelId: '{{ old('mapel_id', isset($items) ? ($items->mapel_id ?? '') : '') }}',
            mapelDropdownOpen: false,

            isPaid: {{ old('is_paid', isset($items) ? ($items->is_paid ? 'true' : 'false') : 'false') }},
            harga: '{{ old('harga', isset($items) ? ($items->harga ?? 0) : 0) }}',

            get selectedMapelName() {
                const found = this.mapels.find(m => String(m.id) === String(this.selectedMapelId));
                return found ? found.name : '';
            },
            get filteredMapels() {
                if (!this.mapelSearch) return this.mapels;
                return this.mapels.filter(m => m.name.toLowerCase().includes(this.mapelSearch.toLowerCase()));
            },
            selectMapel(mapel) {
                this.selectedMapelId = String(mapel.id);
                this.mapelSearch = '';
                this.mapelDropdownOpen = false;
                this.selectedSoals = [];
            }
        }"
        x-init="$watch('selectedMapelId', () => selectedSoals = [])">
        @isset($items)
        @method('PUT')
        @endisset
        @csrf

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.master.ujian.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
                <div class="font-semibold text-xl text-gray-800">{{ $action }} Ujian</div>
            </div>

            <button type="submit"
                class="cursor-pointer bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl focus:outline-none transition-all duration-200 shadow-lg shadow-green-100 flex items-center gap-2 w-full md:w-auto justify-center">
                <span>Simpan Ujian</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Left Column --}}
            <div class="space-y-4">

                {{-- Nama Ujian --}}
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 ml-1">Nama Ujian</label>
                    <input type="text" name="nama" value="{{ old('nama', isset($items) ? $items->nama : '') }}" required
                        placeholder="Contoh: Ujian Tengah Semester Ganjil"
                        class="border border-gray-300 ring-0 rounded-xl px-4 py-3 w-full focus:outline-[#177245] bg-white transition-all shadow-sm">
                    @error('nama')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mata Pelajaran - Searchable Select --}}
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 ml-1">Mata Pelajaran</label>

                    {{-- Hidden input for form submission --}}
                    <input type="hidden" name="mapel_id" :value="selectedMapelId">

                    <div class="relative" @click.outside="mapelDropdownOpen = false">
                        {{-- Trigger button --}}
                        <button type="button"
                            @click="mapelDropdownOpen = !mapelDropdownOpen"
                            class="w-full flex items-center justify-between border border-gray-300 rounded-xl px-4 py-3 bg-white text-left shadow-sm focus:outline-[#177245] transition-all"
                            :class="mapelDropdownOpen ? 'border-[#177245] ring-1 ring-[#177245]' : ''">
                            <span :class="selectedMapelId ? 'text-gray-800 font-medium' : 'text-gray-400'"
                                x-text="selectedMapelId ? selectedMapelName : 'Pilih Mata Pelajaran'">
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                class="text-gray-400 transition-transform duration-200"
                                :class="mapelDropdownOpen ? 'rotate-180' : ''">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="mapelDropdownOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">

                            {{-- Search input --}}
                            <div class="p-2 border-b border-gray-100">
                                <div class="relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.3-4.3" />
                                    </svg>
                                    <input type="text" x-model="mapelSearch" placeholder="Cari mata pelajaran..."
                                        class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#177245]"
                                        @click.stop>
                                </div>
                            </div>

                            {{-- Options list --}}
                            <ul class="max-h-48 overflow-y-auto py-1">
                                <template x-for="mapel in filteredMapels" :key="mapel.id">
                                    <li @click="selectMapel(mapel)"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm cursor-pointer hover:bg-green-50 transition-colors"
                                        :class="String(selectedMapelId) === String(mapel.id) ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700'">
                                        <svg x-show="String(selectedMapelId) === String(mapel.id)"
                                            xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                            class="text-green-600 flex-shrink-0">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                        <span x-text="mapel.name"></span>
                                    </li>
                                </template>
                                <li x-show="filteredMapels.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                                    Tidak ada hasil
                                </li>
                            </ul>
                        </div>
                    </div>

                    @error('mapel_id')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pengaturan Pembayaran --}}
                <div class="rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                            <rect width="20" height="14" x="2" y="5" rx="2" />
                            <line x1="2" x2="22" y1="10" y2="10" />
                        </svg>
                        <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Pengaturan Pembayaran</span>
                    </div>
                    <div class="p-4 space-y-4">
                        {{-- Toggle Berbayar --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-sm text-gray-800">Ujian Berbayar</div>
                                <div class="text-xs text-gray-500 mt-0.5">Murid wajib membayar sebelum mengerjakan</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_paid" value="1" class="sr-only peer" x-model="isPaid">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>

                        {{-- Input Harga --}}
                        <div x-show="isPaid" x-transition>
                            <label class="block text-gray-700 text-xs font-bold mb-1.5 ml-1">Harga Ujian (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rp</span>
                                <input type="number" name="harga" x-model="harga" min="0" placeholder="0"
                                    class="border border-gray-300 rounded-xl pl-12 pr-4 py-2.5 w-full focus:outline-[#177245] bg-white transition-all shadow-sm text-sm">
                            </div>
                            @error('harga')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 space-y-2">
                    <h4 class="font-bold text-blue-800 text-xs uppercase tracking-wider flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4" />
                            <path d="M12 8h.01" />
                        </svg>
                        Ringkasan
                    </h4>
                    <div class="text-sm text-blue-700">
                        Mata Pelajaran: <span class="font-semibold" x-text="selectedMapelId ? selectedMapelName : '-'"></span>
                    </div>
                    <div class="text-sm text-blue-700">
                        Total Soal Terpilih: <span class="font-black text-lg" x-text="selectedSoals.length"></span>
                    </div>
                    <div class="text-sm text-blue-700" x-show="isPaid">
                        Harga: <span class="font-semibold" x-text="'Rp ' + Number(harga || 0).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="text-sm text-blue-700" x-show="!isPaid">
                        Biaya: <span class="font-semibold text-green-700">Gratis</span>
                    </div>
                </div>
            </div>


            {{-- Right Column - Soal List --}}
            <div class="space-y-4">
                <label class="block text-gray-700 text-sm font-bold mb-2 ml-1">Pilih Soal (Pilih Minimal 1)</label>
                <div class="border border-gray-300 rounded-2xl overflow-hidden bg-gray-50 flex flex-col h-[400px]">
                    <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Daftar Bank Soal</span>
                        <div class="text-[10px] text-gray-400 font-medium">Klik untuk memilih</div>
                    </div>

                    <div class="overflow-y-auto flex-1 divide-y divide-gray-200">
                        @forelse($soals as $soal)
                        <label class="flex items-start gap-3 p-4 hover:bg-white cursor-pointer transition-colors group">
                            <input type="checkbox" name="soal_id[]" value="{{ $soal->id }}"
                                x-model="selectedSoals"
                                class="mt-1 w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500 transition-all">
                            <div class="space-y-1">
                                <div class="text-sm font-medium text-gray-800 group-hover:text-green-700 transition-colors line-clamp-2">
                                    {!! $soal->nama !!}
                                </div>
                                <div class="flex gap-2">
                                    <span class="text-[9px] font-bold uppercase py-0.5 px-2 rounded-full {{ $soal->tipe == 'Pilihan ganda' ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600' }}">
                                        {{ $soal->tipe }}
                                    </span>
                                </div>
                            </div>
                        </label>
                        @empty
                        <div class="flex flex-col items-center justify-center h-full text-gray-400 p-8 text-center space-y-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                <path d="M10 13h4" />
                                <path d="M12 11v4" />
                            </svg>
                            <p class="text-xs font-medium">Belum ada soal tersedia. <br> <a href="{{ route('dashboard.master.soal.create') }}" class="text-green-600 hover:underline">Buat soal dulu?</a></p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @error('soal_id')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </form>
</div>
@endsection