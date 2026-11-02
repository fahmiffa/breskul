@extends('base.layout')
@section('title', $title)
@section('content')
<div class="max-w-4xl mx-auto flex flex-col gap-6">

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a href="{{ route('dashboard.gnucash.transactions.index') }}"
                       class="text-green-600 hover:text-green-800 text-xs font-semibold flex items-center gap-1">
                        &larr; Kembali ke Jurnal
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $transaction->description ?: '(Tanpa Deskripsi)' }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-500">
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        {{ $transaction->post_date ? $transaction->post_date->format('d F Y') : '-' }}
                    </span>
                    @if($transaction->num)
                        <span class="bg-gray-100 text-gray-700 font-mono px-2 py-0.5 rounded-md">Ref: {{ $transaction->num }}</span>
                    @endif
                    <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded-md font-semibold">{{ $transaction->currency }}</span>
                    @if($transaction->isBalanced())
                        <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded-md font-semibold">✓ Seimbang</span>
                    @else
                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-md font-semibold">⚠ Tidak Seimbang</span>
                    @endif
                </div>
                @if($transaction->notes)
                    <p class="mt-3 text-sm text-gray-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 italic">
                        {{ $transaction->notes }}
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('dashboard.gnucash.transactions.edit', $transaction->id) }}"
                   class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-2 px-3.5 rounded-xl transition shadow flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>
                    </svg>
                    Edit
                </a>
                <form action="{{ route('dashboard.gnucash.transactions.destroy', $transaction->id) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold py-2 px-3.5 rounded-xl transition flex items-center gap-1.5 border border-red-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-4">
            <span class="text-xs text-blue-600 font-medium uppercase">Total Debit</span>
            <div class="text-xl font-bold font-mono text-blue-700 mt-1">
                Rp {{ number_format($transaction->splits->where('value', '>', 0)->sum('value'), 2, ',', '.') }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-amber-100 p-4">
            <span class="text-xs text-amber-600 font-medium uppercase">Total Kredit</span>
            <div class="text-xl font-bold font-mono text-amber-700 mt-1">
                Rp {{ number_format($transaction->splits->where('value', '<', 0)->sum(fn($s) => abs($s->value)), 2, ',', '.') }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <span class="text-xs text-gray-600 font-medium uppercase">Jumlah Split</span>
            <div class="text-xl font-bold text-gray-800 mt-1">
                {{ $transaction->splits->count() }} baris
            </div>
        </div>
    </div>

    <!-- Splits Detail Table -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Rincian Jurnal (Splits)</h2>
        <div class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="bg-green-600 text-white font-semibold text-left text-xs">
                        <th class="px-4 py-3 w-12 text-center">#</th>
                        <th class="px-4 py-3">Akun</th>
                        <th class="px-4 py-3">Tipe Akun</th>
                        <th class="px-4 py-3">Memo</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                        <th class="px-4 py-3 text-center w-12">R</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($transaction->splits as $index => $sp)
                        @php
                            $debit = $sp->value > 0 ? (float) $sp->value : 0;
                            $credit = $sp->value < 0 ? abs((float) $sp->value) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                @if($sp->account)
                                    <a href="{{ route('dashboard.gnucash.accounts.ledger', $sp->account->id) }}"
                                       class="text-green-700 hover:underline font-medium">
                                        {{ $sp->account->name }}
                                    </a>
                                    @if($sp->account->parent && $sp->account->parent->account_type !== 'ROOT')
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $sp->account->parent->name }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-400">Akun Tidak Ditemukan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($sp->account)
                                    @php
                                        $badge = match($sp->account->account_type) {
                                            'ASSET' => 'bg-blue-100 text-blue-800',
                                            'BANK' => 'bg-indigo-100 text-indigo-800',
                                            'CASH' => 'bg-emerald-100 text-emerald-800',
                                            'LIABILITY' => 'bg-amber-100 text-amber-800',
                                            'EQUITY' => 'bg-purple-100 text-purple-800',
                                            'INCOME' => 'bg-green-100 text-green-800',
                                            'EXPENSE' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badge }}">
                                        {{ $sp->account->account_type }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $sp->memo ?: '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono {{ $debit > 0 ? 'text-blue-700 font-semibold' : 'text-gray-300' }}">
                                {{ $debit > 0 ? 'Rp ' . number_format($debit, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono {{ $credit > 0 ? 'text-amber-700 font-semibold' : 'text-gray-300' }}">
                                {{ $credit > 0 ? 'Rp ' . number_format($credit, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs">
                                @if($sp->reconcile_state === 'y')
                                    <span class="text-green-600" title="Reconciled">✓</span>
                                @elseif($sp->reconcile_state === 'c')
                                    <span class="text-blue-500" title="Cleared">C</span>
                                @else
                                    <span class="text-gray-300" title="Not Reconciled">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold text-xs">
                        <td colspan="4" class="px-4 py-3 text-right uppercase text-gray-600">TOTAL</td>
                        <td class="px-4 py-3 text-right font-mono text-blue-700">
                            Rp {{ number_format($transaction->splits->where('value', '>', 0)->sum('value'), 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-amber-700">
                            Rp {{ number_format($transaction->splits->where('value', '<', 0)->sum(fn($s) => abs($s->value)), 2, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($transaction->enter_date)
            <p class="mt-4 text-[11px] text-gray-400">
                Dicatat: {{ $transaction->enter_date->format('d/m/Y H:i:s') }}
            </p>
        @endif
    </div>
</div>
@endsection
