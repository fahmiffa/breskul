@extends('base.layout')
@section('title', $title)

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-wrapper.ledger-ts-select {
            font-size: 11px;
            width: 100%;
        }
        .ts-wrapper.ledger-ts-select .ts-control {
            border-radius: 0.25rem;
            border: 1px solid #fcd34d; /* amber-300 */
            padding: 2px 6px;
            font-size: 11px;
            min-height: 28px;
            background-color: #ffffff;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }
        .ts-wrapper.ledger-ts-select.focus .ts-control {
            border-color: #16a34a;
            box-shadow: 0 0 0 1px #16a34a;
        }
        .ts-wrapper.ledger-ts-select .ts-control input {
            font-size: 11px;
        }
        .ts-dropdown.ledger-ts-dropdown {
            font-size: 11px;
            z-index: 99999 !important;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .ts-dropdown.ledger-ts-dropdown .option {
            padding: 5px 8px;
            font-size: 11px;
        }
        .ts-dropdown.ledger-ts-dropdown .option.active {
            background-color: #f0fdf4 !important;
            color: #166534 !important;
        }
        .ts-dropdown.ledger-ts-dropdown .option.selected {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
<div class="flex flex-col gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-xs px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('err') || session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="font-medium">{{ session('err') ?: session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Tab Header (matching GnuCash tab styling in sub.png) -->
    <div class="bg-gray-200/80 rounded-t-xl px-2 pt-2 border-b border-gray-300 flex items-center justify-between">
        <div class="flex items-center gap-1 overflow-x-auto text-xs">
            <!-- Tab Akun (Bagan Akun) -->
            <a href="{{ route('dashboard.gnucash.accounts.index') }}"
               class="px-3.5 py-2 rounded-t-lg bg-gray-100 hover:bg-white text-gray-700 font-medium flex items-center gap-1.5 transition border border-b-0 border-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="14" x="2" y="5" rx="2"/>
                    <line x1="2" x2="22" y1="10" y2="10"/>
                </svg>
                <span>Akun</span>
            </a>

            <!-- Tab Active (Current Account Register) -->
            <div class="px-4 py-2 rounded-t-lg bg-white text-green-900 font-bold flex items-center gap-2 border-t-2 border-t-green-600 border-x border-gray-300 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/>
                    <path d="M8 11h8"/><path d="M8 7h6"/>
                </svg>
                <span class="max-w-[220px] sm:max-w-md truncate">{{ $account->name }}</span>
                <a href="{{ route('dashboard.gnucash.accounts.index') }}"
                   class="text-gray-400 hover:text-red-600 p-0.5 rounded-full hover:bg-gray-100 transition"
                   title="Tutup Register">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </a>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2 pb-1.5 text-xs text-gray-500 font-mono">
            <span class="px-2 py-0.5 rounded bg-gray-100 border border-gray-300">{{ $account->account_type }}</span>
            <span class="px-2 py-0.5 rounded bg-gray-100 border border-gray-300">IDR</span>
        </div>
    </div>

    <!-- Register Toolbar & Info Card -->
    <div class="bg-white rounded-b-xl rounded-t-sm shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <!-- Full Path Breadcrumb -->
                <div class="flex items-center gap-1.5 text-xs text-gray-500 mb-1 flex-wrap">
                    <span class="text-gray-400">Jalur:</span>
                    <span class="font-mono font-medium text-gray-700 bg-gray-50 px-2 py-0.5 rounded border border-gray-200">
                        {{ $accountFullPath ?: $account->name }}
                    </span>
                    @if($account->description)
                        <span class="text-gray-400">&bull; {{ $account->description }}</span>
                    @endif
                </div>
            </div>

            <!-- Actions & Filter Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard.gnucash.transactions.create', ['account_id' => $account->id]) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5"
                   title="Catat transaksi kompleks yang melibatkan lebih dari 2 akun">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>+ Multi-Split</span>
                </a>

                <form method="GET" action="{{ route('dashboard.gnucash.accounts.ledger', $account->id) }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari deskripsi / ref..."
                           class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-green-600 focus:outline-none" />

                    <input type="date" name="from_date" value="{{ $fromDate }}" title="Dari Tanggal"
                           class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-green-600 focus:outline-none" />

                    <span class="text-xs text-gray-400">-</span>

                    <input type="date" name="to_date" value="{{ $toDate }}" title="Sampai Tanggal"
                           class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-green-600 focus:outline-none" />

                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition shadow-sm">
                        Filter
                    </button>

                    @if($search || $fromDate || $toDate)
                        <a href="{{ route('dashboard.gnucash.accounts.ledger', $account->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs px-2.5 py-1.5 rounded-lg transition">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden form for HTML5 input association -->
    <form id="quickTxForm" action="{{ route('dashboard.gnucash.accounts.quick-transaction', $account->id) }}" method="POST">
        @csrf
    </form>

    <!-- GnuCash Register Table (styled faithfully to sub.png) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-300 overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-xs select-none font-sans" id="ledgerTable">
                <thead>
                    <!-- Green Register Header matching sub.png (#5c874b) -->
                    <tr class="bg-[#5c874b] text-white font-semibold text-left divide-x divide-[#4e743e] tracking-tight">
                        <th class="py-2 px-3 w-24 text-center font-semibold">Tanggal</th>
                        <th class="py-2 px-2.5 w-16 text-center font-semibold">Num</th>
                        <th class="py-2 px-3 font-semibold">Deskripsi</th>
                        <th class="py-2 px-3 w-64 lg:w-96 font-semibold">Transfer</th>
                        <th class="py-2 px-2 w-8 text-center font-semibold">R</th>
                        <th class="py-2 px-3 w-28 text-right font-semibold">Tambah</th>
                        <th class="py-2 px-3 w-28 text-right font-semibold">Kurang</th>
                        <th class="py-2 px-3 w-32 text-right font-semibold">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#c2d7bb] text-gray-900 font-normal">
                    @forelse($rows as $index => $r)
                        @php
                            $sp = $r['split'];
                            $tx = $r['tx'];
                            // Alternating soft green stripes like GnuCash register
                            $rowBg = ($index % 2 === 0) ? 'bg-[#d8ecd1]/80' : 'bg-[#eaf4e6]/60';
                        @endphp
                        <tr class="{{ $rowBg }} hover:bg-yellow-50/80 transition-colors divide-x divide-[#c2d7bb]"
                            data-split-id="{{ $sp->id }}"
                            data-tx-id="{{ $tx->id }}">

                            <!-- Tanggal -->
                            <td class="py-1.5 px-3 text-center whitespace-nowrap font-mono text-gray-700">
                                {{ $tx->post_date ? $tx->post_date->format('d/m/Y') : '' }}
                            </td>

                            <!-- Num -->
                            <td class="py-1.5 px-2.5 text-center font-mono text-gray-600 whitespace-nowrap">
                                {{ $tx->num ?: '' }}
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-1.5 px-3 text-gray-900">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-medium">{{ $tx->description ?: '' }}</span>
                                    @if($sp->memo && $sp->memo !== $tx->description)
                                        <span class="text-[11px] text-gray-500 italic">({{ $sp->memo }})</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Transfer -->
                            <td class="py-1.5 px-3 text-gray-800 font-mono text-[11px]">
                                @if($r['isMultiSplit'])
                                    <span class="text-gray-700 italic font-semibold flex items-center justify-between cursor-pointer"
                                          onclick="toggleSplitDetail('{{ $sp->id }}')">
                                        <span>{{ $r['transfer'] }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </span>
                                @else
                                    <span class="truncate block max-w-xs lg:max-w-md" title="{{ $r['transfer'] }}">
                                        {{ $r['transfer'] }}
                                    </span>
                                @endif
                            </td>

                            <!-- R (Reconciled state) -->
                            <td class="py-1.5 px-2 text-center font-mono text-gray-600">
                                {{ $sp->reconcile_state ?: 'n' }}
                            </td>

                            <!-- Tambah -->
                            <td class="py-1.5 px-3 text-right font-mono whitespace-nowrap text-gray-900">
                                {{ $r['tambah'] > 0.001 ? number_format($r['tambah'], 2, ',', '.') : '' }}
                            </td>

                            <!-- Kurang -->
                            <td class="py-1.5 px-3 text-right font-mono whitespace-nowrap text-gray-900">
                                {{ $r['kurang'] > 0.001 ? number_format($r['kurang'], 2, ',', '.') : '' }}
                            </td>

                            <!-- Saldo -->
                            <td class="py-1.5 px-3 text-right font-mono font-medium whitespace-nowrap {{ $r['balance'] < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ ($r['balance'] < 0 ? '-' : '') . number_format(abs($r['balance']), 2, ',', '.') }}
                            </td>
                        </tr>

                        <!-- Multi-split expandable detail (if user clicks) -->
                        @if($r['isMultiSplit'] && $r['allSplits'])
                            <tr id="split-detail-{{ $sp->id }}" class="hidden bg-[#edf5e8] divide-x divide-[#c2d7bb] text-[11px]">
                                <td colspan="3" class="py-1.5 px-4 text-gray-500 italic pl-10">
                                    Rincian Seluruh Split Transaksi (Total: {{ $r['allSplits']->count() }} splits):
                                </td>
                                <td colspan="5" class="py-1.5 px-3">
                                    <div class="space-y-1">
                                        @foreach($r['allSplits'] as $s)
                                            @php
                                                $sAcc = $allAccounts[$s->account_id] ?? null;
                                                $sVal = (float) $s->value;
                                            @endphp
                                            <div class="flex items-center justify-between font-mono {{ $s->id === $sp->id ? 'font-bold text-green-900' : 'text-gray-700' }}">
                                                <span class="truncate pr-2">
                                                    {{ $sAcc ? $sAcc->name : 'Unknown' }}
                                                    @if($s->memo) <span class="text-gray-400 font-sans">({{ $s->memo }})</span> @endif
                                                </span>
                                                <span class="{{ $sVal > 0 ? 'text-blue-700' : 'text-amber-700' }}">
                                                    {{ $sVal > 0 ? 'D: Rp ' . number_format($sVal, 2, ',', '.') : 'K: Rp ' . number_format(abs($sVal), 2, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-400 bg-[#eaf4e6]/40">
                                Belum ada baris transaksi untuk akun ini.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Interactive Inline Double-Entry Input Row (matching sub.png active yellow row) -->
                    <tr class="bg-[#fffde7] text-gray-800 divide-x divide-[#c2d7bb] text-xs">
                        <!-- Tanggal -->
                        <td class="py-1 px-1.5 text-center">
                            <input form="quickTxForm" type="date" name="post_date" value="{{ now()->format('Y-m-d') }}" required
                                   class="w-full text-[11px] font-mono border border-amber-300 rounded px-1 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-green-600 shadow-sm" />
                        </td>

                        <!-- Num -->
                        <td class="py-1 px-1 text-center">
                            <input form="quickTxForm" type="text" name="num" placeholder="Num"
                                   class="w-full text-[11px] font-mono text-center border border-amber-300 rounded px-1 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-green-600 shadow-sm" />
                        </td>

                        <!-- Deskripsi -->
                        <td class="py-1 px-1.5">
                            <input form="quickTxForm" type="text" name="description" placeholder="Keterangan transaksi..." required
                                   class="w-full text-xs border border-amber-300 rounded px-2 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-green-600 shadow-sm" />
                        </td>

                        <!-- Transfer (Akun Lawan) -->
                        <td class="py-1 px-1.5 min-w-[220px]">
                            <select form="quickTxForm" name="transfer_account_id" id="transfer_account_id"
                                    class="w-full text-[11px] border border-amber-300 rounded px-1.5 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-green-600 shadow-sm"
                                    data-placeholder="-- Cari / Pilih Akun Lawan --">
                                <option value="">-- Pilih Akun Lawan (Transfer) --</option>
                                @foreach($transferAccounts as $ta)
                                    <option value="{{ $ta->id }}"
                                            data-code="{{ $ta->code }}"
                                            data-type="{{ $ta->account_type }}"
                                            data-path="{{ $ta->resolved_path ?: $ta->name }}">
                                        {{ $ta->code ? '[' . $ta->code . '] ' : '' }}{{ $ta->resolved_path ?: $ta->name }} ({{ $ta->account_type }})
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- R -->
                        <td class="py-1 px-1 text-center font-mono text-gray-400">
                            n
                        </td>

                        <!-- Tambah -->
                        <td class="py-1 px-1.5">
                            <input form="quickTxForm" type="number" step="0.01" min="0" name="tambah" id="inputTambah" placeholder="0,00"
                                   class="w-full text-right text-[11px] font-mono border border-amber-300 rounded px-1.5 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-green-600 shadow-sm"
                                   oninput="if(this.value > 0) document.getElementById('inputKurang').value=''" />
                        </td>

                        <!-- Kurang -->
                        <td class="py-1 px-1.5">
                            <input form="quickTxForm" type="number" step="0.01" min="0" name="kurang" id="inputKurang" placeholder="0,00"
                                   class="w-full text-right text-[11px] font-mono border border-amber-300 rounded px-1.5 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-green-600 shadow-sm"
                                   oninput="if(this.value > 0) document.getElementById('inputTambah').value=''" />
                        </td>

                        <!-- Saldo / Tombol Simpan -->
                        <td class="py-1 px-1.5 text-center">
                            <button form="quickTxForm" type="submit"
                                    class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-1 px-2 rounded text-[11px] transition shadow-sm flex items-center justify-center gap-1"
                                    title="Simpan Transaksi (Enter)">
                                <span>Simpan</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination if splits are large -->
        @if($splits->hasPages())
            <div class="px-4 py-2.5 bg-white border-t border-gray-200">
                {{ $splits->links() }}
            </div>
        @endif

        <!-- Bottom Footer Status Bar (100% faithfully matching sub.png) -->
        <div class="bg-gray-100 border-t border-gray-300 px-4 py-2 flex flex-col sm:flex-row sm:items-center justify-between text-[11px] font-sans text-gray-700 gap-2 select-none">
            <!-- Left 5 status metrics -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 font-mono">
                <div>
                    <span class="font-sans text-gray-600">Saat ini:</span>
                    <span class="font-semibold text-gray-900">
                        Rp{{ number_format($saatIni, 2, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="font-sans text-gray-600">Masa Depan:</span>
                    <span class="font-semibold text-gray-900">
                        Rp{{ number_format($masaDepan, 2, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="font-sans text-gray-600">Klir:</span>
                    <span class="font-semibold text-gray-900">
                        Rp{{ number_format($klir, 2, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="font-sans text-gray-600">Rekonsiliasi:</span>
                    <span class="font-semibold text-gray-900">
                        Rp{{ number_format($rekonsiliasi, 2, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="font-sans text-gray-600">Minimum Diproyeksikan:</span>
                    <span class="font-semibold text-gray-900">
                        Rp{{ number_format($minDiproyeksikan, 2, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Right side sort info -->
            <div class="text-gray-500 font-sans text-[11px]">
                Urutan Berdasar: Urutan Standar &and;
            </div>
        </div>
    </div>

</div>

@push('script')
<script>
function toggleSplitDetail(splitId) {
    const el = document.getElementById('split-detail-' + splitId);
    if (el) {
        el.classList.toggle('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selectEl = document.getElementById('transfer_account_id');
    if (selectEl) {
        const TomSelectConstructor = window.TomSelect || (typeof TomSelect !== 'undefined' ? TomSelect : null);
        if (!TomSelectConstructor) return;

        const ts = new TomSelectConstructor(selectEl, {
            create: false,
            maxOptions: 1000,
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown ledger-ts-dropdown',
            placeholder: '-- Cari / Pilih Akun Lawan --',
            plugins: ['clear_button'],
            controlInput: '<input form="quickTxForm">',
            render: {
                option: function(data, escape) {
                    const codeBadge = data.code ? `<span class="font-mono text-[9px] bg-gray-100 text-gray-700 px-1 py-0.2 rounded mr-1">${escape(data.code)}</span>` : '';
                    const typeBadge = data.type ? `<span class="text-[9px] font-semibold px-1 py-0.2 rounded ml-1 bg-gray-100 text-gray-600">${escape(data.type)}</span>` : '';
                    return `<div class="flex items-center justify-between py-0.5">
                        <div class="truncate text-[11px]">
                            ${codeBadge}
                            <span class="text-gray-900">${escape(data.path || data.text)}</span>
                        </div>
                        ${typeBadge}
                    </div>`;
                },
                item: function(data, escape) {
                    const codeBadge = data.code ? `<span class="font-mono text-[9px] bg-gray-100 text-gray-700 px-1 py-0.2 rounded mr-1">${escape(data.code)}</span>` : '';
                    return `<div class="truncate text-[11px]">${codeBadge}<span>${escape(data.path || data.text)}</span></div>`;
                },
                no_results: function(data, escape) {
                    return '<div class="no-results text-[11px] text-gray-500 p-2">Tidak ditemukan akun "' + escape(data.input) + '"</div>';
                }
            }
        });

        ts.on('change', function(val) {
            selectEl.value = val || '';
        });

        selectEl.addEventListener('change', function() {
            selectEl.value = selectEl.value || '';
        });

        selectEl.closest('.ts-wrapper')?.classList.add('ledger-ts-select');

        const quickForm = document.getElementById('quickTxForm');
        if (quickForm) {
            quickForm.addEventListener('submit', function(e) {
                if (!selectEl.value) {
                    e.preventDefault();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Akun Transfer Belum Dipilih',
                            text: 'Silakan pilih Akun Lawan (Transfer) terlebih dahulu.',
                            confirmButtonColor: '#15803d'
                        });
                    } else {
                        alert('Silakan pilih Akun Lawan (Transfer) terlebih dahulu.');
                    }
                    ts.focus();
                    return false;
                }
            });
        }
    }
});
</script>
@endpush
@endsection
