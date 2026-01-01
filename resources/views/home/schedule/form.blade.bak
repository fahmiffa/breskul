@extends('base.layout')
@section('title', 'Form Jadwal')
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        <form method="POST"
            action="{{ isset($items) ? route('dashboard.jadwal.update', $items->id) : route('dashboard.jadwal.store') }}">
            @csrf
            @isset($items)
                @method('PUT')
            @endisset
            @php
                $selected = $selected ?? [
                    'unit' => null,
                    'kelas' => null,
                    'program' => null,
                    'murid' => [],
                ];
            @endphp

            <div class="flex flex-col">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="schedule({{ json_encode($murid) }}, {{ json_encode($selected) }})" x-init="init()" x-cloak>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Unit</label>
                        <select name="unit" x-model="selectedUnit"
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]">
                            <option>Pilih Unit</option>
                            @foreach ($unit as $item)
                                <option value="{{ $item['id'] }}" @selected(isset($items) && $items->units->id == $item['id'])>{{ $item['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('murid')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Kelas</label>
                        <select name="kelas" x-model="selectedKelas" class="block w-full border px-3 py-2 rounded-xl"
                            required>
                            <template x-for="(option,index) in kelas" :key="index">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                            @isset($items)
                                <option x-show="kelas.length === 0" value="{{ $items->class->id }}">
                                    {{ $items->class->name }}</option>
                            @endisset
                        </select>
                        @error('murid')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Program Belajar</label>
                        <select name="program" x-model="selectedProgram" class="block w-full border px-3 py-2 rounded-xl"
                            required>
                            <template x-for="(option,index) in programs" :key="index">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                            @isset($items)
                                <option x-show="programs.length === 0" value="{{ $items->programs->id }}">
                                    {{ $items->programs->name }}</option>
                            @endisset
                        </select>
                        @error('murid')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Murid</label>
                        <select name="murid[]" x-ref="selectMurid" multiple>
                            @isset($items)
                                @foreach ($items->murid as $val)
                                    <option value="{{ $val->id }}" selected>
                                        {{ $val->name }}</option>
                                @endforeach
                            @endisset
                        </select>
                        @error('murid')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-4" x-data="jadwal({{ isset($items) ? json_encode($da) : null }})">
                    <template x-for="(pertemuan, index) in pertemuanList" :key="index">
                        <div class="border border-gray-300 p-4 mb-4 rounded-xl shadow">
                            <div class="mb-2">
                                <label class="block text-sm font-medium">Nama</label>
                                <div class="flex gap-2">
                                    <input type="text" :name="`pertemuan[${index}][nama]`" x-model="pertemuan.nama"
                                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]"
                                        :placeholder="`Pertemuan ${index+1}`">
                                    <button type="button" @click="pertemuanList.splice(index, 1)"
                                        class="text-red-600 text-sm hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-trash2-icon lucide-trash-2">
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <template x-for="(item, i) in pertemuan.tanggalList" :key="i">
                                <div class="flex flex-col gap-1 mb-4">
                                    <div class="flex items-center gap-2">
                                        <input type="datetime-local"
                                            :name="`pertemuan[${index}][tanggal][]`"x-model="item.tanggal"
                                            class="border border-gray-300 ring-0 rounded-xl p-2 w-50 focus:outline-[#FF9966]" />
                                        <button type="button" @click="pertemuan.tanggalList.splice(i, 1)"
                                            class="text-red-500 hover:text-red-700 text-xs md:text-sm">
                                            <!-- icon trash -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-trash2-icon lucide-trash-2">
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-xs text-gray-500 italic" x-text="formatWIB(item.tanggal)"></div>

                                    {{-- <input type="text" x-model="item.materi" placeholder="Materi pertemuan"
                                            class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]" /> --}}
                                </div>
                            </template>


                            <button type="button" @click="addTanggal(index)"
                                class="text-orange-600 text-sm mt-2 hover:underline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-calendar-plus-icon lucide-calendar-plus">
                                    <path d="M16 19h6" />
                                    <path d="M16 2v4" />
                                    <path d="M19 16v6" />
                                    <path d="M21 12.598V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8.5" />
                                    <path d="M3 10h18" />
                                    <path d="M8 2v4" />
                                </svg>
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="addPertemuan()" class="cursor-pointer text-orange-600 font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-file-plus2-icon lucide-file-plus-2">
                            <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            <path d="M3 15h6" />
                            <path d="M6 12v6" />
                        </svg>
                    </button>
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
