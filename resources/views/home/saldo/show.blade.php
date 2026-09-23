@extends('base.layout')
@section('title', $title)
@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Back & Student Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.saldo.index') }}"
                class="p-2 bg-white rounded-xl shadow-sm border border-gray-200 hover:bg-gray-50 transition text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Riwayat Mutasi Saldo</h1>
                <p class="text-xs text-gray-500">Histori penambahan dan pemotongan saldo murid</p>
            </div>
        </div>

        <a href="{{ route('dashboard.saldo.index') }}"
            class="text-xs text-green-700 hover:text-green-800 font-semibold flex items-center gap-1 self-start sm:self-auto">
            &larr; Kembali ke Daftar Saldo
        </a>
    </div>

    <!-- Student Info Card & Quick Transaction -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Student Details & Saldo -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Data {{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}</span>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Pesantren</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Aktif</span>
                    </div>
                </div>
                <h3 class="text-lg font-extrabold text-gray-800">{{ $student->name }}</h3>
                <p class="text-xs font-mono text-gray-500 mt-0.5">{{ config('app.school_mode') ? 'NIS' : 'NIM' }}: {{ $student->nis }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ config('app.school_mode') ? 'Kelas' : 'Prodi' }}: 
                    <span class="font-semibold text-gray-700">
                        {{ config('app.school_mode') ? ($student->reg->kelas->name ?? '-') : ($student->reg->prodi->name ?? '-') }}
                    </span>
                </p>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 font-medium">Saldo Saat Ini</p>
                <p class="text-2xl font-black text-green-600 mt-1">
                    Rp {{ number_format($student->saldo->nominal ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Quick Transaction Form -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 col-span-1 md:col-span-2">
            <h4 class="text-sm font-bold text-gray-800 pb-3 border-b border-gray-100 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Tambah Transaksi Cepat
            </h4>

            <form action="{{ route('dashboard.saldo.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <input type="hidden" name="students_id" value="{{ $student->id }}">

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tipe Transaksi</label>
                    <select name="tipe" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                        <option value="kredit">+ Kredit (Top Up / Masuk)</option>
                        <option value="debit">- Debit (Tarik / Keluar)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal (Rp)</label>
                    <input type="number" name="nominal" min="1" required placeholder="Contoh: 20000"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <div class="flex gap-2">
                        <input type="text" name="keterangan" placeholder="Contoh: Tabungan mingguan, jajan kantin, dll"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-[#177245]">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold text-xs px-5 py-2 rounded-xl transition whitespace-nowrap cursor-pointer">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Log Table Section -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex flex-col">
        <h3 class="text-base font-bold text-gray-800 mb-4">Riwayat Log Mutasi</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600 border-b border-gray-200 text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($logs as $index => $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center text-xs text-gray-400">
                                {{ $logs->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                {{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($log->tipe === 'kredit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        + Kredit (Masuk)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        - Debit (Keluar)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold {{ $log->tipe === 'kredit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $log->tipe === 'kredit' ? '+' : '-' }} Rp {{ number_format($log->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                {{ $log->keterangan ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">Belum ada riwayat mutasi saldo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
