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

        <!-- Filter -->
        <form method="GET" action="{{ route('dashboard.master.absensi.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-gray-600 text-xs font-semibold mb-1">Role Target</label>
                <select name="role_target" class="border rounded py-1.5 px-3 text-sm text-gray-700 focus:outline-none focus:shadow-outline">
                    <option value="">Semua</option>
                    <option value="karyawan" {{ request('role_target') === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    <option value="murid" {{ request('role_target') === 'murid' ? 'selected' : '' }}>Murid</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-600 text-xs font-semibold mb-1">Jabatan</label>
                <select name="jabatan_id" class="border rounded py-1.5 px-3 text-sm text-gray-700 focus:outline-none focus:shadow-outline">
                    <option value="">Semua Jabatan</option>
                    @foreach($jabatans as $jabatan)
                        <option value="{{ $jabatan->id }}" {{ request('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                            {{ $jabatan->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Filter
                </button>
                <a href="{{ route('dashboard.master.absensi.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 text-xs font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Reset
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        @if(auth()->user()->role == 0)
                            <th class="py-3 px-6 text-left">Aplikasi</th>
                        @endif
                        <th class="py-3 px-6 text-left">Nama</th>
                        <th class="py-3 px-6 text-left">Target</th>
                        <th class="py-3 px-6 text-center">Masuk</th>
                        <th class="py-3 px-6 text-center">Pulang</th>
                        <th class="py-3 px-6 text-center">Koordinat</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse($items as $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            @if(auth()->user()->role == 0)
                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                    <span class="bg-gray-200 text-gray-700 py-1 px-3 rounded-full text-xs">
                                        {{ $item->appData->name ?? ($item->employee?->app?->name ?? 'App ID: ' . $item->app) }}
                                    </span>
                                </td>
                            @endif
                            <td class="py-3 px-6 text-left whitespace-nowrap font-medium">
                                {{ $item->name ?? '-' }}
                            </td>
                            <td class="py-3 px-6 text-left whitespace-nowrap">
                                @if($item->employee)
                                    <span class="bg-purple-200 text-purple-600 py-1 px-3 rounded-full text-xs">
                                        {{ $item->employee->name }} 
                                        @if($item->employee->jabatan)
                                            <span class="text-[10px] text-purple-500">({{ $item->employee->jabatan->name }})</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Murid</span>
                                @endif
                            </td>

                            <td class="py-3 px-6 text-center">
                                {{ substr($item->clock_in_start, 0, 5) }} - {{ substr($item->clock_in_end, 0, 5) }}
                            </td>
                            <td class="py-3 px-6 text-center">
                                {{ substr($item->clock_out_start, 0, 5) }} - {{ substr($item->clock_out_end, 0, 5) }}
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($item->lat && $item->lng)
                                    <a href="https://www.google.com/maps?q={{ $item->lat }},{{ $item->lng }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-500 hover:text-blue-700 hover:underline inline-flex items-center gap-1">
                                        {{ $item->lat }}, {{ $item->lng }}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
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
                            <td colspan="{{ auth()->user()->role == 0 ? 7 : 6 }}" class="py-3 px-6 text-center">Belum ada data konfigurasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
@endsection
