@extends('base.layout')
@section('title', $title)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="mb-4 flex justify-between items-center gap-2">
            <h2 class="text-xl font-bold text-gray-800">{{ $title }}</h2>
            <a href="{{ route('dashboard.master.absensi.create') }}"
                class="cursor-pointer bg-green-500 text-xs hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                Tambah Konfigurasi
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Role</th>
                        <th class="py-3 px-6 text-center">Masuk</th>
                        <th class="py-3 px-6 text-center">Pulang</th>
                        <th class="py-3 px-6 text-center">Koordinat</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse($items as $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">
                                <span class="bg-{{ $item->role == 3 ? 'blue' : 'green' }}-200 text-{{ $item->role == 3 ? 'blue' : 'green' }}-600 py-1 px-3 rounded-full text-xs">
                                    {{ $item->role == 3 ? 'Guru' : 'Murid' }}
                                </span>
                            </td>

                            <td class="py-3 px-6 text-center">
                                {{ substr($item->clock_in_start, 0, 5) }} - {{ substr($item->clock_in_end, 0, 5) }}
                            </td>
                            <td class="py-3 px-6 text-center">
                                {{ substr($item->clock_out_start, 0, 5) }} - {{ substr($item->clock_out_end, 0, 5) }}
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($item->lat && $item->lng)
                                    <div class="text-xs text-blue-500">{{ $item->lat }}, {{ $item->lng }}</div>
                                    <div class="text-[10px] text-gray-400">R: {{ $item->radius }}m</div>
                                @else
                                    <span class="text-xs text-gray-400">Belum diset</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="{{ route('dashboard.master.absensi.edit', $item->id) }}" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('dashboard.master.absensi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110 border-none bg-transparent">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 px-6 text-center">Belum ada data konfigurasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
