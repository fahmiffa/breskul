@extends('base.layout')
@section('title', $title)
@section('content')
<div class="flex flex-col gap-6">

    <!-- Top Action & Info Bar -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Jurnal Transaksi Keuangan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar entri jurnal umum double-entry akuntansi GnuCash (Total: {{ number_format($transactions->total(), 0, ',', '.') }} transaksi).</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard.gnucash.accounts.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2.5 px-4 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Bagan Akun (COA)
                </a>

                <a href="{{ route('dashboard.gnucash.reports.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2.5 px-4 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    Laporan
                </a>

                <a href="{{ route('dashboard.gnucash.transactions.import.form') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2.5 px-4 rounded-xl flex items-center gap-1.5 transition shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Import .gnucash
                </a>

                <a href="{{ route('dashboard.gnucash.transactions.create') }}"
                   class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-2.5 px-4 rounded-xl flex items-center gap-1.5 transition shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tambah Transaksi
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('dashboard.gnucash.transactions.index') }}" class="mt-6 pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Pencarian</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari keterangan, nomor ref, memo..."
                           class="w-full border border-gray-300 rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500" />
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-2.5 text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500" />
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500" />
            </div>

            <!-- Account Filter & Submit -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Akun Terkait</label>
                <div class="flex gap-2">
                    <select name="account_id" class="w-full border border-gray-300 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Akun</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $selectedAccountId === $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition">
                        Cari
                    </button>
                    @if($search || $fromDate || $toDate || $selectedAccountId)
                        <a href="{{ route('dashboard.gnucash.transactions.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-2.5 py-2 rounded-xl transition flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions List Card -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                <thead>
                    <tr class="bg-green-600 text-white font-semibold text-left">
                        <th class="px-3.5 py-3 w-28">Tanggal</th>
                        <th class="px-3.5 py-3 w-28">No. Ref</th>
                        <th class="px-3.5 py-3">Deskripsi Transaksi</th>
                        <th class="px-3.5 py-3">Rincian Akun (Splits)</th>
                        <th class="px-3.5 py-3 text-right w-36">Total Nominal</th>
                        <th class="px-3.5 py-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3.5 py-3 font-mono text-gray-600 whitespace-nowrap">
                                {{ $tx->post_date ? $tx->post_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-3.5 py-3 font-mono text-gray-500 whitespace-nowrap">
                                {{ $tx->num ?: '-' }}
                            </td>
                            <td class="px-3.5 py-3">
                                <a href="{{ route('dashboard.gnucash.transactions.show', $tx->id) }}" class="text-green-800 hover:underline font-medium block">
                                    {{ $tx->description ?: '(Tanpa Deskripsi)' }}
                                </a>
                                @if($tx->notes)
                                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-1 italic">{{ $tx->notes }}</p>
                                @endif
                            </td>
                            <td class="px-3.5 py-3">
                                <div class="flex flex-col gap-1 max-w-md">
                                    @foreach($tx->splits->take(4) as $sp)
                                        <div class="flex items-center justify-between text-[11px] gap-2">
                                            <span class="text-gray-700 truncate font-medium">
                                                @if($sp->account)
                                                    <a href="{{ route('dashboard.gnucash.accounts.ledger', $sp->account->id) }}" class="text-green-700 hover:text-green-900 hover:underline" title="Buka buku besar {{ $sp->account->name }}">
                                                        {{ $sp->account->name }}
                                                    </a>
                                                @else
                                                    Unknown Account
                                                @endif
                                                @if($sp->memo)
                                                    <span class="text-gray-400 font-normal">({{ $sp->memo }})</span>
                                                @endif
                                            </span>
                                            <span class="font-mono whitespace-nowrap {{ $sp->value > 0 ? 'text-blue-600' : 'text-amber-600' }}">
                                                {{ $sp->value > 0 ? 'D: Rp ' . number_format($sp->value, 0, ',', '.') : 'K: Rp ' . number_format(abs($sp->value), 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                    @if($tx->splits->count() > 4)
                                        <span class="text-[10px] text-gray-400 font-medium">+{{ $tx->splits->count() - 4 }} baris lainnya...</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3.5 py-3 text-right font-mono font-bold text-gray-900 whitespace-nowrap">
                                {{ $tx->formatted_total }}
                            </td>
                            <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Show Detail -->
                                    <a href="{{ route('dashboard.gnucash.transactions.show', $tx->id) }}"
                                       title="Lihat Detail"
                                       class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('dashboard.gnucash.transactions.edit', $tx->id) }}"
                                       title="Edit Transaksi"
                                       class="text-emerald-600 hover:text-emerald-800 p-1 rounded hover:bg-emerald-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>
                                        </svg>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('dashboard.gnucash.transactions.destroy', $tx->id) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Transaksi" class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                Tidak ada transaksi yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection
