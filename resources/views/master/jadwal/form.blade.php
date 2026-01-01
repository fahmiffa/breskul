@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        @if ($errors->any())
            <div class="text-red-500">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST"
            action="{{ isset($items) ? route('dashboard.master.jadwal.update', $items->id) : route('dashboard.master.jadwal.store') }}">
            @isset($items)
                @method('PUT')
            @endisset
            @csrf
            <div class="flex-row mb-4" x-data="jadwalForm({{ old('jadwal') ? json_encode(array_values(old('jadwal'))) : (isset($jadwals) ? $jadwals->toJson() : 'null') }})">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Kelas</label>
                    <select name="kelas" required
                        class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                        @isset($items)
                            <option value="{{ $items->id }}">{{ $items->name }}
                            </option>
                        @else
                            <option value="">Pilih kelas</option>
                            @foreach ($kelas as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}
                                </option>
                            @endforeach
                        @endisset

                    </select>
                    @error('kelas')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <template x-for="(jadwal, index) in jadwals" :key="index">
                    <div class="flex flex-col border border-gray-300 p-5 rounded-2xl mb-5 bg-white shadow-sm">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Hari</label>
                            <select :name="`jadwal[${index}][hari]`" x-model="jadwal.hari"
                                class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]"
                                required>
                                <option value="">Pilih hari</option>
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                                <option value="7">Minggu</option>
                            </select>
                            @error('hari')
                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, mapelIndex) in jadwal.mapels" :key="mapelIndex">
                                <div class="border border-gray-200 p-4 rounded-xl bg-gray-50 relative">
                                    {{-- <input type="hidden" :value="jadwal.hari" :name="`jadwal[${index}][mapels][${mapelIndex}][hari]`"> --}}
                                    @isset($items)
                                        <input type="hidden" :value="item.id" x-model="item.id"
                                            :name="`jadwal[${index}][mapels][${mapelIndex}][id]`">
                                    @endisset

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4">
                                        <div class="mb-2">
                                            <label class="block text-gray-700 text-sm font-semibold mb-2">Mapel</label>
                                            <select :name="`jadwal[${index}][mapels][${mapelIndex}][mapel]`" x-model="item.mapel_id"
                                                required
                                                class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                                                <option value="">Pilih Mapel</option>
                                                @foreach ($mapel as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('mapel')
                                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-2">
                                            <label class="block text-gray-700 text-sm font-semibold mb-2">Guru</label>
                                            <select :name="`jadwal[${index}][mapels][${mapelIndex}][guru]`" x-model="item.guru"
                                                required
                                                class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#177245]">
                                                <option value="">Pilih Guru</option>
                                                @foreach ($teach as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('mapel')
                                                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-2">
                                            <label class="block text-gray-700 text-sm font-semibold mb-2">Waktu</label>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-full">
                                                    <label
                                                        class="block mb-1 text-xs font-semibold text-gray-700">Mulai</label>
                                                    <input type="time" x-model="item.start_time"
                                                        :name="`jadwal[${index}][mapels][${mapelIndex}][start_time]`"
                                                        class="border border-gray-300  ring-0 rounded-xl px-2 py-2 w-full focus:outline-[#177245]">
                                                    <p class="mt-1">
                                                        <span class="text-xs text-red-600"
                                                            x-text="formatWIB(item.start_time) || '-'"></span>
                                                    </p>
                                                </div>
                                                <div class="w-full">
                                                    <label
                                                        class="block mb-1 text-xs font-semibold text-gray-700">Selesai</label>
                                                    <input type="time" x-model="item.end_time"
                                                        :name="`jadwal[${index}][mapels][${mapelIndex}][end_time]`"
                                                        class="border border-gray-300  ring-0 rounded-xl px-2 py-2 w-full focus:outline-[#177245]">
                                                    <p class="mt-1">
                                                        <span class="text-xs text-red-600"
                                                            x-text="formatWIB(item.end_time) || '-'"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="cursor-pointer text-xs text-red-500 hover:text-white bg-red-100 hover:bg-red-500 absolute top-2 right-2 py-2 px-3 rounded-2xl"
                                        @click="removeMapel(index, mapelIndex)" title="Hapus Mapel">
                                        Hapus Mapel
                                    </button>
                                </div>
                            </template>

                            <button type="button"
                                class="w-full py-2 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 hover:border-green-500 hover:text-green-500 transition font-semibold text-sm flex justify-center items-center gap-2"
                                @click="addMapel(index)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Mapel
                            </button>
                        </div>

                        <div class="mt-4 flex justify-end pt-2 border-t border-gray-100">
                            <button type="button"
                                class="cursor-pointer bg-red-100 text-red-500 hover:bg-red-500 hover:text-white text-xs font-bold px-3 py-2 rounded-xl focus:outline-none transition"
                                @click="removeJadwal(index)">Hapus Hari</button>
                        </div>
                    </div>
                </template>

                @error('jadwal')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror

                <div class="flex justify-end">
                    <button type="button"
                        class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline"
                        @click="addJadwal()">Tambah Hari</button>
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
