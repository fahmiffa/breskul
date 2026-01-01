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
                            class="block border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full focus:outline-[#FF9966]"
                            required>
                            @isset($items)
                                <option value="{{ $items->unit }}" selected>{{ $items->units->name }}</option>
                            @else
                                <option value="">Pilih Unit</option>
                                @foreach ($unit as $item)
                                    <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                @endforeach
                            @endisset
                        </select>
                        @error('unit')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Murid</label>
                        <select name="murid[]" x-ref="selectMurid" multiple>
                        </select>
                        @error('murid')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Jadwal</label>
                        <select name="jadwal[]" x-ref="selectJadwal" multiple>
                            <template x-for="(option,index) in jadwals" :key="index">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                        @error('jadwal')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                    </div>
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
