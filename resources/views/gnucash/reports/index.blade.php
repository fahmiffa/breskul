@extends('base.layout')
@section('title', $title)

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
    <style>
        /* TomSelect Report Custom Styles */
        .ts-wrapper.report-ts-select {
            width: 100%;
            font-size: 12px;
        }
        .ts-wrapper.report-ts-select .ts-control {
            border-radius: 0.75rem; /* rounded-xl */
            border: 1px solid #e5e7eb;
            padding: 5px 12px;
            font-size: 12px;
            min-height: 38px;
            background-color: rgba(249, 250, 251, 0.7);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: all 0.15s ease-in-out;
        }
        .ts-wrapper.report-ts-select.focus .ts-control {
            border-color: #10b981; /* emerald-500 */
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
            background-color: #ffffff;
        }
        .ts-wrapper.report-ts-select .ts-control input {
            font-size: 12px;
            color: #374151;
        }
        .ts-wrapper.report-ts-select .clear-button {
            color: #9ca3af;
            font-size: 16px;
            right: 8px;
        }
        .ts-wrapper.report-ts-select .clear-button:hover {
            color: #ef4444;
        }
        .ts-dropdown.report-ts-dropdown {
            font-size: 12px;
            z-index: 99999 !important;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .ts-dropdown.report-ts-dropdown .option {
            padding: 6px 12px;
            font-size: 12px;
            border-bottom: 1px solid #f9fafb;
        }
        .ts-dropdown.report-ts-dropdown .option.active {
            background-color: #ecfdf5 !important;
            color: #065f46 !important;
        }
        .ts-dropdown.report-ts-dropdown .option.selected {
            background-color: #d1fae5 !important;
            color: #047857 !important;
            font-weight: 600;
        }

        @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 11px !important;
        }
        aside, nav, header, .no-print, #sidebar, .filter-container, .action-buttons, .pagination-container {
            display: none !important;
        }
        .main-content, .content-container {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .print-header {
            display: block !important;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .account-card {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
            break-inside: avoid;
            page-break-inside: avoid;
            margin-bottom: 20px !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border: 1px solid #ddd !important;
            padding: 4px 6px !important;
            font-size: 10px !important;
        }
        th {
            background-color: #f3f4f6 !important;
            color: #000 !important;
        }
    }
    .print-header {
        display: none;
    }
</style>
@endpush

@section('content')
<script>
// --- Hoisted Global Functions for Report Page ---
function setDatePreset(preset) {
    var fromInput = document.getElementById('filterFromDate');
    var toInput = document.getElementById('filterToDate');
    if (!fromInput || !toInput) return;
    var today = new Date();

    var formatDate = function(date) {
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    };

    if (preset === 'all') {
        fromInput.value = '';
        toInput.value = '';
    } else if (preset === 'this_month') {
        var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        var lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        fromInput.value = formatDate(firstDay);
        toInput.value = formatDate(lastDay);
    } else if (preset === 'last_month') {
        var firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        var lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
        fromInput.value = formatDate(firstDay);
        toInput.value = formatDate(lastDay);
    } else if (preset === 'this_year') {
        var firstDay = new Date(today.getFullYear(), 0, 1);
        var lastDay = new Date(today.getFullYear(), 11, 31);
        fromInput.value = formatDate(firstDay);
        toInput.value = formatDate(lastDay);
    }
}
window.setDatePreset = setDatePreset;

function switchView(mode) {
    var groupedContainer = document.getElementById('groupedContainer');
    var summaryContainer = document.getElementById('summaryContainer');
    var groupedControls = document.getElementById('groupedControls');
    var tabGroupedBtn = document.getElementById('tabGroupedBtn');
    var tabSummaryBtn = document.getElementById('tabSummaryBtn');

    if (!groupedContainer || !summaryContainer) return;

    if (mode === 'summary') {
        groupedContainer.classList.add('hidden');
        summaryContainer.classList.remove('hidden');
        if (groupedControls) groupedControls.classList.add('hidden');

        if (tabGroupedBtn) tabGroupedBtn.className = 'px-3.5 py-1.5 rounded-lg transition text-gray-600 hover:text-gray-900';
        if (tabSummaryBtn) tabSummaryBtn.className = 'px-3.5 py-1.5 rounded-lg transition bg-white text-emerald-700 shadow-2xs';
    } else {
        groupedContainer.classList.remove('hidden');
        summaryContainer.classList.add('hidden');
        if (groupedControls) groupedControls.classList.remove('hidden');

        if (tabGroupedBtn) tabGroupedBtn.className = 'px-3.5 py-1.5 rounded-lg transition bg-white text-emerald-700 shadow-2xs';
        if (tabSummaryBtn) tabSummaryBtn.className = 'px-3.5 py-1.5 rounded-lg transition text-gray-600 hover:text-gray-900';
    }
}
window.switchView = switchView;

function toggleCard(accId) {
    var body = document.getElementById('body-' + accId);
    var chevron = document.getElementById('chevron-' + accId);
    if (!body) return;

    if (body.classList.contains('hidden')) {
        body.classList.remove('hidden');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    } else {
        body.classList.add('hidden');
        if (chevron) chevron.style.transform = 'rotate(-90deg)';
    }
}
window.toggleCard = toggleCard;

function toggleAllCards(expand) {
    document.querySelectorAll('.card-content').forEach(function(body) {
        if (expand) {
            body.classList.remove('hidden');
        } else {
            body.classList.add('hidden');
        }
    });
    document.querySelectorAll('.chevron-btn').forEach(function(chevron) {
        chevron.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
    });
}
window.toggleAllCards = toggleAllCards;

var allAccountsData = [];
try {
    allAccountsData = {!! $accountOptionsJson !!};
} catch(e) {
    allAccountsData = [];
}

function initAccountSearchSelect() {
    var selectEl = document.getElementById('filterAccountId');
    if (!selectEl) return;

    // Fallback: parse from existing DOM options if allAccountsData empty
    if (!allAccountsData || !allAccountsData.length) {
        allAccountsData = [];
        var rawOptions = selectEl.querySelectorAll('option');
        rawOptions.forEach(function(opt) {
            if (!opt.value) return;
            allAccountsData.push({
                id: opt.value,
                name: opt.textContent.trim(),
                code: opt.getAttribute('data-code') || '',
                account_type: (opt.getAttribute('data-type') || '').toUpperCase(),
                resolved_path: opt.getAttribute('data-path') || opt.textContent.trim()
            });
        });
    }

    var TomSelectConstructor = window.TomSelect || (typeof TomSelect !== 'undefined' ? TomSelect : null);
    if (!TomSelectConstructor) {
        setTimeout(initAccountSearchSelect, 50);
        return;
    }

    if (selectEl.tomselect) {
        selectEl.tomselect.destroy();
    }

    var ts = new TomSelectConstructor(selectEl, {
        create: false,
        maxOptions: 1000,
        placeholder: 'Semua Akun (Ketik untuk mencari...)',
        allowEmptyOption: true,
        dropdownClass: 'ts-dropdown report-ts-dropdown',
        plugins: ['clear_button'],
        searchField: ['text', 'code', 'path'],
        render: {
            option: function(data, escape) {
                if (!data.value) {
                    return '<div class="py-1 text-gray-500 italic font-medium">' + escape(data.text) + '</div>';
                }
                var codeBadge = data.code ? '<span class="font-mono text-[10px] bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded mr-1.5 font-semibold">' + escape(data.code) + '</span>' : '';
                var typeBadge = data.type ? '<span class="text-[9px] font-semibold px-1.5 py-0.5 rounded ml-1 bg-emerald-50 text-emerald-700 border border-emerald-200">' + escape(data.type) + '</span>' : '';
                return '<div class="flex items-center justify-between py-1">' +
                    '<div class="truncate text-xs">' +
                        codeBadge +
                        '<span class="text-gray-900">' + escape(data.path || data.text) + '</span>' +
                    '</div>' +
                    typeBadge +
                '</div>';
            },
            item: function(data, escape) {
                if (!data.value) {
                    return '<span class="text-gray-500 italic">' + escape(data.text) + '</span>';
                }
                var codeBadge = data.code ? '<span class="font-mono text-[10px] bg-gray-100 text-gray-700 px-1 py-0.5 rounded mr-1 font-semibold">' + escape(data.code) + '</span>' : '';
                return '<div class="truncate text-xs">' + codeBadge + '<span class="font-medium text-gray-900">' + escape(data.path || data.text) + '</span></div>';
            },
            no_results: function(data, escape) {
                return '<div class="text-xs text-gray-500 p-2 italic text-center">Tidak ditemukan akun "' + escape(data.input) + '"</div>';
            }
        }
    });

    selectEl.closest('.ts-wrapper')?.classList.add('report-ts-select');
    window.reportAccountTomSelect = ts;

    // Type filter sync
    var filterTypeEl = document.getElementById('filterAccountType');
    if (filterTypeEl) {
        var syncOptions = function(selectedType, keepValue) {
            selectedType = (selectedType || '').toUpperCase().trim();
            var currentVal = keepValue ? ts.getValue() : '';

            ts.clear(true);
            ts.clearOptions();

            ts.addOption({
                value: '',
                text: 'Semua Akun (Sesuai Tipe)',
                code: '',
                type: '',
                path: 'Semua Akun (Sesuai Tipe)'
            });

            var filtered = (!selectedType)
                ? allAccountsData
                : allAccountsData.filter(function(a) { return (a.account_type || '').toUpperCase() === selectedType; });

            filtered.forEach(function(a) {
                ts.addOption({
                    value: String(a.id),
                    text: (a.code ? '[' + a.code + '] ' : '') + a.resolved_path,
                    code: a.code || '',
                    type: a.account_type || '',
                    path: a.resolved_path || a.name
                });
            });

            if (currentVal && filtered.some(function(a) { return String(a.id) === String(currentVal); })) {
                ts.setValue(currentVal, true);
            } else {
                ts.setValue('', true);
            }
            ts.refreshOptions(false);
        };

        filterTypeEl.addEventListener('change', function() {
            syncOptions(this.value, false);
        });

        // Initial sync if a type is already selected on page load
        if (filterTypeEl.value) {
            syncOptions(filterTypeEl.value, true);
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initAccountSearchSelect();

    var btnExpand = document.getElementById('btnToggleAllCardsExpand');
    if (btnExpand) btnExpand.addEventListener('click', function() { toggleAllCards(true); });

    var btnCollapse = document.getElementById('btnToggleAllCardsCollapse');
    if (btnCollapse) btnCollapse.addEventListener('click', function() { toggleAllCards(false); });

    var tabGrouped = document.getElementById('tabGroupedBtn');
    if (tabGrouped) tabGrouped.addEventListener('click', function() { switchView('grouped'); });

    var tabSummary = document.getElementById('tabSummaryBtn');
    if (tabSummary) tabSummary.addEventListener('click', function() { switchView('summary'); });
});
</script>

<div class="flex flex-col gap-5">

    <!-- Print Only Header -->
    <div class="print-header">
        <h1 class="text-xl font-bold uppercase tracking-wide">Laporan Transaksi Berdasarkan Akun (GnuCash)</h1>
        <p class="text-xs text-gray-600">
            Periode: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/Y') : 'Awal' }} s/d {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d/m/Y') : 'Sekarang' }}
            | Kategori: {{ $selectedAccountType ? ($accountTypes[$selectedAccountType] ?? $selectedAccountType) : 'Semua Kategori' }}
            | Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
        </p>
    </div>

    <!-- Top Action & Info Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 no-print">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Laporan Transaksi Berdasarkan Akun</h1>
                        <p class="text-xs text-gray-500">Laporan buku besar per akun akuntansi GnuCash dengan mutasi debit, kredit, dan saldo berjalan.</p>
                    </div>
                </div>
            </div>

            <!-- Navigation and Export Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard.gnucash.accounts.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                        <line x1="2" x2="22" y1="10" y2="10"/>
                    </svg>
                    Bagan Akun (COA)
                </a>

                <a href="{{ route('dashboard.gnucash.transactions.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/>
                        <path d="M8 11h8"/><path d="M8 7h6"/>
                    </svg>
                    Jurnal Transaksi
                </a>

                <button type="button" onclick="window.print()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Cetak
                </button>

                <a href="{{ route('dashboard.gnucash.reports.export', request()->query()) }}"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Export Excel (.xlsx)
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('dashboard.gnucash.reports.index') }}" id="reportFilterForm" class="mt-5 pt-4 border-t border-gray-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                <!-- Filter Tanggal Dari -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="from_date" id="filterFromDate" value="{{ $fromDate }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50" />
                </div>

                <!-- Filter Tanggal Sampai -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Sampai</label>
                    <input type="date" name="to_date" id="filterToDate" value="{{ $toDate }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50" />
                </div>

                <!-- Filter Kategori / Account Type -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tipe Akun (account_type)</label>
                    <select name="account_type" id="filterAccountType"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50 text-gray-700 font-medium">
                        <option value="">Semua Tipe Akun</option>
                        @foreach($accountTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" {{ $selectedAccountType === $typeKey ? 'selected' : '' }}>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Akun Spesifik -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Akun Spesifik</label>
                    <select name="account_id" id="filterAccountId"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50 text-gray-700 font-medium">
                        <option value="">Semua Akun (Sesuai Tipe)</option>
                        @foreach($accountOptions as $opt)
                            <option value="{{ $opt->id }}"
                                    data-code="{{ $opt->code ?? '' }}"
                                    data-type="{{ strtoupper($opt->account_type) }}"
                                    data-path="{{ $opt->resolved_path ?: $opt->name }}"
                                    {{ (string)$selectedAccountId === (string)$opt->id ? 'selected' : '' }}>
                                {{ $opt->code ? "[$opt->code] " : "" }}{{ $opt->resolved_path }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Row 2: Search + Quick Date Buttons + Submit -->
            <div class="mt-3 pt-3 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="relative w-full md:w-80">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari keterangan, ref, memo..."
                           class="w-full border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50" />
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-2 text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <!-- Quick Date Range Presets -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] text-gray-500 font-medium mr-1">Preset:</span>
                    <button type="button" id="btnPresetAll" onclick="setDatePreset('all')"
                            class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Semua
                    </button>
                    <button type="button" id="btnPresetThisMonth" onclick="setDatePreset('this_month')"
                            class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Bulan Ini
                    </button>
                    <button type="button" id="btnPresetLastMonth" onclick="setDatePreset('last_month')"
                            class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Bulan Lalu
                    </button>
                    <button type="button" id="btnPresetThisYear" onclick="setDatePreset('this_year')"
                            class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Tahun Ini
                    </button>
                </div>

                <!-- Submit & Reset -->
                <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                    <a href="{{ route('dashboard.gnucash.reports.index') }}"
                       class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition text-center">
                        Reset
                    </a>
                    <button type="submit"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- KPI / Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Akun Terlibat -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Akun Terlibat</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-xl font-bold text-gray-800">
                    {{ $isSingleAccount ? 1 : number_format($totalAllAccounts ?? 0, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-gray-500">akun</span>
            </div>
        </div>

        <!-- Jumlah Transaksi -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Transaksi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-xl font-bold text-gray-800">
                    {{ number_format($isSingleAccount ? $totalTxCount : ($totalAllTx ?? 0), 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-gray-500">entri</span>
            </div>
        </div>

        <!-- Mutasi Debit / Tambah -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Mutasi Debit / Tambah</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-base sm:text-lg font-bold text-emerald-700">
                    Rp {{ number_format($isSingleAccount ? $totalDebit : ($grandTotalDebit ?? 0), 2, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Mutasi Kredit / Kurang -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Mutasi Kredit / Kurang</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-base sm:text-lg font-bold text-rose-700">
                    Rp {{ number_format($isSingleAccount ? $totalCredit : ($grandTotalCredit ?? 0), 2, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Mutasi Bersih / Saldo Akhir -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm col-span-2 lg:col-span-1 flex flex-col justify-between">
            <span class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">
                {{ $isSingleAccount ? 'Saldo Akhir' : 'Total Saldo Akhir' }}
            </span>
            <div class="mt-2 flex items-baseline justify-between">
                @php
                    $valFinal = $isSingleAccount ? $closingBalance : ($grandClosing ?? 0);
                    $isNegFinal = $valFinal < -0.001;
                @endphp
                <span class="text-base sm:text-lg font-bold {{ $isNegFinal ? 'text-red-600' : 'text-gray-800' }}">
                    {{ ($isNegFinal ? '-Rp ' : 'Rp ') . number_format(abs($valFinal), 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- SCENARIO A: SINGLE ACCOUNT REPORT (FOCUSED VIEW)          --}}
    {{-- ========================================================= --}}
    @if($isSingleAccount)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Account Banner -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-emerald-50 via-teal-50 to-white border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        @if($singleAccount->code)
                            <span class="px-2 py-0.5 text-xs font-mono font-bold bg-white text-gray-800 border border-gray-300 rounded-lg shadow-2xs">
                                {{ $singleAccount->code }}
                            </span>
                        @endif
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">{{ $singleAccount->name }}</h2>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                            {{ $singleAccount->account_type }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 font-mono">
                        {{ $singleAccount->full_path }}
                    </p>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="text-right">
                        <span class="text-gray-400 block text-[11px]">Saldo Awal</span>
                        <span class="font-bold text-gray-800">
                            {{ ($openingBalance < 0 ? '-Rp ' : 'Rp ') . number_format(abs($openingBalance), 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="text-right border-l pl-4 border-gray-200">
                        <span class="text-gray-400 block text-[11px]">Saldo Akhir</span>
                        <span class="font-bold {{ $closingBalance < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                            {{ ($closingBalance < 0 ? '-Rp ' : 'Rp ') . number_format(abs($closingBalance), 2, ',', '.') }}
                        </span>
                    </div>
                    <a href="{{ route('dashboard.gnucash.accounts.ledger', $singleAccount->id) }}"
                       class="ml-2 px-3 py-1.5 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 text-gray-700 font-semibold text-xs transition shadow-2xs no-print">
                        Buka Register
                    </a>
                </div>
            </div>

            <!-- Transaction Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead>
                        <tr class="bg-gray-50/90 text-gray-700 font-bold border-b border-gray-200">
                            <th class="py-2.5 px-3 text-left w-24">Tanggal</th>
                            <th class="py-2.5 px-3 text-left w-20">No. Ref</th>
                            <th class="py-2.5 px-3 text-left">Keterangan Transaksi</th>
                            <th class="py-2.5 px-3 text-left">Akun Lawan / Transfer</th>
                            <th class="py-2.5 px-3 text-left w-36">Memo</th>
                            <th class="py-2.5 px-3 text-right w-28">Debit / Tambah</th>
                            <th class="py-2.5 px-3 text-right w-28">Kredit / Kurang</th>
                            <th class="py-2.5 px-3 text-right w-32">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <!-- Saldo Awal Row -->
                        <tr class="bg-gray-50/60 font-semibold text-gray-600 italic">
                            <td class="py-2 px-3" colspan="2">
                                {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2 px-3" colspan="5">
                                Saldo Awal {{ $fromDate ? "per $fromDate" : "Awal Buku" }}
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-bold text-gray-800">
                                {{ ($openingBalance < 0 ? '-Rp ' : 'Rp ') . number_format(abs($openingBalance), 2, ',', '.') }}
                            </td>
                        </tr>

                        @forelse($rows as $row)
                            @php
                                $tx = $row['tx'];
                                $debit = $row['debit'];
                                $credit = $row['credit'];
                                $bal = $row['balance'];
                            @endphp
                            <tr class="hover:bg-emerald-50/40 transition">
                                <td class="py-2 px-3 text-gray-600 whitespace-nowrap">
                                    {{ $tx && $tx->post_date ? \Carbon\Carbon::parse($tx->post_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2 px-3 text-gray-500 font-mono text-[11px] whitespace-nowrap">
                                    {{ $tx && $tx->num ? $tx->num : '-' }}
                                </td>
                                <td class="py-2 px-3 font-medium text-gray-800">
                                    {{ $tx ? $tx->description : '-' }}
                                </td>
                                <td class="py-2 px-3 text-gray-600 text-[11px]">
                                    <span class="font-mono text-gray-700 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">
                                        {{ $row['transfer'] }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-gray-500 italic text-[11px]">
                                    {{ $row['split']->memo ?: '-' }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-emerald-700 font-medium whitespace-nowrap">
                                    {{ $debit > 0 ? 'Rp ' . number_format($debit, 2, ',', '.') : '-' }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-rose-700 font-medium whitespace-nowrap">
                                    {{ $credit > 0 ? 'Rp ' . number_format($credit, 2, ',', '.') : '-' }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold whitespace-nowrap {{ $bal < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ ($bal < 0 ? '-Rp ' : 'Rp ') . number_format(abs($bal), 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">
                                    Tidak ada transaksi untuk akun ini dalam periode tanggal yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-300 font-bold text-gray-800">
                        <tr>
                            <td colspan="5" class="py-2.5 px-3 text-right">Total Transaksi Halaman Ini:</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-700">
                                Rp {{ number_format($pageDebit, 2, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-rose-700">
                                Rp {{ number_format($pageCredit, 2, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono">
                                {{ ($closingBalance < 0 ? '-Rp ' : 'Rp ') . number_format(abs($closingBalance), 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 no-print flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <span class="text-gray-500">
                    Menampilkan {{ $splits->firstItem() ?? 0 }} - {{ $splits->lastItem() ?? 0 }} dari {{ $splits->total() }} entri transaksi
                </span>
                <div>
                    {{ $splits->links() }}
                </div>
            </div>
        </div>

    {{-- ========================================================= --}}
    {{-- SCENARIO B: MULTI-ACCOUNT REPORT (GROUPED / SUMMARY)      --}}
    {{-- ========================================================= --}}
    @else
        <!-- View Mode Navigation (Grouped Details vs Summary Table) -->
        <div class="flex items-center justify-between no-print">
            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs font-semibold">
                <button type="button" id="tabGroupedBtn" onclick="switchView('grouped')"
                        class="px-3.5 py-1.5 rounded-lg transition {{ $viewMode !== 'summary' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                    Rincian Transaksi per Akun
                </button>
                <button type="button" id="tabSummaryBtn" onclick="switchView('summary')"
                        class="px-3.5 py-1.5 rounded-lg transition {{ $viewMode === 'summary' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                    Tabel Rekap Akun ({{ count($accountsSummary) }})
                </button>
            </div>

            <!-- Expand / Collapse All Controls for Grouped View -->
            <div id="groupedControls" class="flex items-center gap-2 {{ $viewMode === 'summary' ? 'hidden' : '' }}">
                <button type="button" id="btnToggleAllCardsExpand" onclick="toggleAllCards(true)"
                        class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Buka Semua Akun
                </button>
                <button type="button" id="btnToggleAllCardsCollapse" onclick="toggleAllCards(false)"
                        class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Tutup Semua Akun
                </button>
            </div>
        </div>

        {{-- TAB 1: GROUPED TRANSACTION DETAILS PER ACCOUNT --}}
        <div id="groupedContainer" class="flex flex-col gap-4 {{ $viewMode === 'summary' ? 'hidden' : '' }}">
            @if(count($groupedDetails) === 0)
                <div class="bg-white rounded-2xl p-8 border border-gray-200 text-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-700">Tidak ada transaksi yang cocok</h3>
                    <p class="text-xs text-gray-500 mt-1">Coba ubah filter rentang tanggal atau tipe akun yang dipilih.</p>
                </div>
            @else
                @foreach($groupedDetails as $accId => $data)
                    @php
                        $acc = $data['account'];
                        $isCredit = $data['isCredit'];
                        $opening = $data['openingBalance'];
                        $closing = $data['closingBalance'];
                        $debit = $data['totalDebit'];
                        $credit = $data['totalCredit'];
                        $txCount = $data['txCount'];
                        $rows = $data['rows'];
                    @endphp

                    <div class="account-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" data-acc-id="{{ $accId }}">
                        <!-- Account Card Header (Collapsible) -->
                        <div class="p-3.5 sm:p-4 bg-gray-50/70 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer select-none"
                             onclick="toggleCard('{{ $accId }}')">
                            <div class="flex items-center gap-2">
                                <button type="button" class="chevron-btn text-gray-400 hover:text-gray-700 transition-transform duration-200" id="chevron-{{ $accId }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </button>
                                @if($acc->code)
                                    <span class="px-2 py-0.5 text-xs font-mono font-bold bg-white text-gray-700 border border-gray-300 rounded-lg shadow-2xs">
                                        {{ $acc->code }}
                                    </span>
                                @endif
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                                        {{ $acc->name }}
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                            {{ $acc->account_type }}
                                        </span>
                                    </h3>
                                    <p class="text-[11px] text-gray-400 font-mono mt-0.5">
                                        {{ $acc->full_path }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 text-xs">
                                <div>
                                    <span class="text-gray-400 text-[10px] block">Saldo Awal</span>
                                    <span class="font-bold text-gray-700 font-mono text-[11px]">
                                        {{ ($opening < 0 ? '-Rp ' : 'Rp ') . number_format(abs($opening), 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="border-l pl-3 border-gray-200">
                                    <span class="text-gray-400 text-[10px] block">Debit / Tambah</span>
                                    <span class="font-bold text-emerald-700 font-mono text-[11px]">
                                        Rp {{ number_format($debit, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="border-l pl-3 border-gray-200">
                                    <span class="text-gray-400 text-[10px] block">Kredit / Kurang</span>
                                    <span class="font-bold text-rose-700 font-mono text-[11px]">
                                        Rp {{ number_format($credit, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="border-l pl-3 border-gray-200">
                                    <span class="text-gray-400 text-[10px] block">Saldo Akhir</span>
                                    <span class="font-bold font-mono text-[11px] {{ $closing < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ ($closing < 0 ? '-Rp ' : 'Rp ') . number_format(abs($closing), 2, ',', '.') }}
                                    </span>
                                </div>
                                <a href="{{ route('dashboard.gnucash.reports.index', array_merge(request()->query(), ['account_id' => $acc->id])) }}"
                                   onclick="event.stopPropagation()"
                                   class="ml-2 px-2.5 py-1 text-[11px] bg-white border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-700 font-semibold transition no-print"
                                   title="Fokus laporan ke akun ini">
                                    Rincian Penuh
                                </a>
                            </div>
                        </div>

                        <!-- Card Body: Transactions Table -->
                        <div id="body-{{ $accId }}" class="card-content overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead>
                                    <tr class="bg-gray-50/70 text-gray-600 font-bold border-b border-gray-200">
                                        <th class="py-2 px-3 text-left w-24">Tanggal</th>
                                        <th class="py-2 px-3 text-left w-20">No. Ref</th>
                                        <th class="py-2 px-3 text-left">Keterangan Transaksi</th>
                                        <th class="py-2 px-3 text-left">Akun Lawan / Transfer</th>
                                        <th class="py-2 px-3 text-left w-32">Memo</th>
                                        <th class="py-2 px-3 text-right w-28">Debit / Tambah</th>
                                        <th class="py-2 px-3 text-right w-28">Kredit / Kurang</th>
                                        <th class="py-2 px-3 text-right w-32">Saldo Berjalan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <!-- Saldo Awal -->
                                    <tr class="bg-gray-50/40 font-semibold text-gray-500 italic text-[11px]">
                                        <td class="py-1.5 px-3" colspan="2">
                                            {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="py-1.5 px-3" colspan="5">
                                            Saldo Awal {{ $fromDate ? "per $fromDate" : "Awal Buku" }}
                                        </td>
                                        <td class="py-1.5 px-3 text-right font-mono font-bold text-gray-700">
                                            {{ ($opening < 0 ? '-Rp ' : 'Rp ') . number_format(abs($opening), 2, ',', '.') }}
                                        </td>
                                    </tr>

                                    @forelse($rows as $r)
                                        @php
                                            $tx = $r['tx'];
                                            $rDeb = $r['debit'];
                                            $rCred = $r['credit'];
                                            $rBal = $r['balance'];
                                        @endphp
                                        <tr class="hover:bg-gray-50/70 transition">
                                            <td class="py-1.5 px-3 text-gray-600 whitespace-nowrap">
                                                {{ $tx && $tx->post_date ? \Carbon\Carbon::parse($tx->post_date)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="py-1.5 px-3 text-gray-400 font-mono text-[11px] whitespace-nowrap">
                                                {{ $tx && $tx->num ? $tx->num : '-' }}
                                            </td>
                                            <td class="py-1.5 px-3 font-medium text-gray-800">
                                                {{ $tx ? $tx->description : '-' }}
                                            </td>
                                            <td class="py-1.5 px-3 text-gray-600 text-[11px]">
                                                <span class="font-mono text-gray-700 bg-gray-50 px-1 py-0.5 rounded border border-gray-100">
                                                    {{ $r['transfer'] }}
                                                </span>
                                            </td>
                                            <td class="py-1.5 px-3 text-gray-400 italic text-[11px]">
                                                {{ $r['split']->memo ?: '-' }}
                                            </td>
                                            <td class="py-1.5 px-3 text-right font-mono text-emerald-700 font-medium whitespace-nowrap">
                                                {{ $rDeb > 0 ? 'Rp ' . number_format($rDeb, 2, ',', '.') : '-' }}
                                            </td>
                                            <td class="py-1.5 px-3 text-right font-mono text-rose-700 font-medium whitespace-nowrap">
                                                {{ $rCred > 0 ? 'Rp ' . number_format($rCred, 2, ',', '.') : '-' }}
                                            </td>
                                            <td class="py-1.5 px-3 text-right font-mono font-bold whitespace-nowrap {{ $rBal < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                                {{ ($rBal < 0 ? '-Rp ' : 'Rp ') . number_format(abs($rBal), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-4 text-center text-gray-400 text-xs italic">
                                                Tidak ada transaksi dalam rentang tanggal ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-200 font-bold text-gray-800 text-[11px]">
                                    <tr>
                                        <td colspan="5" class="py-2 px-3 text-right">Subtotal Akun {{ $acc->name }}:</td>
                                        <td class="py-2 px-3 text-right font-mono text-emerald-700">
                                            Rp {{ number_format($debit, 2, ',', '.') }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-mono text-rose-700">
                                            Rp {{ number_format($credit, 2, ',', '.') }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-mono">
                                            {{ ($closing < 0 ? '-Rp ' : 'Rp ') . number_format(abs($closing), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            @if(!empty($data['hasMore']))
                                <div class="p-3 bg-amber-50/80 border-t border-amber-200 text-center text-xs text-amber-900 flex flex-col sm:flex-row items-center justify-between gap-2 px-4 no-print">
                                    <div class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        <span>
                                            Menampilkan <strong>50 transaksi pertama</strong> dari total <strong>{{ number_format($data['totalSplitsCount'], 0, ',', '.') }}</strong> transaksi akun ini.
                                        </span>
                                    </div>
                                    <a href="{{ route('dashboard.gnucash.reports.index', array_merge(request()->query(), ['account_id' => $acc->id])) }}"
                                       class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold transition text-xs shadow-2xs whitespace-nowrap">
                                        Lihat Semua Transaksi Akun Ini &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Multi-account Pagination -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm no-print flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <span class="text-gray-500">
                        Menampilkan akun ke-{{ $paginatedAccounts->firstItem() ?? 0 }} sampai {{ $paginatedAccounts->lastItem() ?? 0 }} dari {{ $paginatedAccounts->total() }} akun
                    </span>
                    <div>
                        {{ $paginatedAccounts->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB 2: CONSOLIDATED SUMMARY TABLE --}}
        <div id="summaryContainer" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden {{ $viewMode !== 'summary' ? 'hidden' : '' }}">
            <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Tabel Rekapitulasi Mutasi Akun</h3>
                    <p class="text-xs text-gray-500">Ringkasan saldo awal, mutasi debit/kredit, dan saldo akhir untuk setiap akun.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-white border border-gray-200 rounded-xl text-gray-700">
                    Total: {{ count($accountsSummary) }} Akun
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                            <th class="py-2.5 px-3 text-left w-20">Kode</th>
                            <th class="py-2.5 px-3 text-left">Nama Akun & Jalur Hierarki</th>
                            <th class="py-2.5 px-3 text-left w-24">Tipe Akun</th>
                            <th class="py-2.5 px-3 text-right w-32">Saldo Awal</th>
                            <th class="py-2.5 px-3 text-right w-32">Total Debit</th>
                            <th class="py-2.5 px-3 text-right w-32">Total Kredit</th>
                            <th class="py-2.5 px-3 text-right w-32">Saldo Akhir</th>
                            <th class="py-2.5 px-3 text-center w-20">Transaksi</th>
                            <th class="py-2.5 px-3 text-center w-20 no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($accountsSummary as $item)
                            @php
                                $acc = $item['account'];
                                $open = $item['openingBalance'];
                                $deb = $item['totalDebit'];
                                $cred = $item['totalCredit'];
                                $close = $item['closingBalance'];
                            @endphp
                            <tr class="hover:bg-emerald-50/40 transition">
                                <td class="py-2 px-3 font-mono font-semibold text-gray-600">
                                    {{ $acc->code ?: '-' }}
                                </td>
                                <td class="py-2 px-3">
                                    <div class="font-bold text-gray-800">{{ $acc->name }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $acc->full_path }}</div>
                                </td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-100 text-gray-700">
                                        {{ $acc->account_type }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-gray-700 whitespace-nowrap">
                                    {{ ($open < 0 ? '-Rp ' : 'Rp ') . number_format(abs($open), 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-emerald-700 font-medium whitespace-nowrap">
                                    Rp {{ number_format($deb, 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-rose-700 font-medium whitespace-nowrap">
                                    Rp {{ number_format($cred, 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold whitespace-nowrap {{ $close < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ ($close < 0 ? '-Rp ' : 'Rp ') . number_format(abs($close), 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-center font-mono text-gray-600">
                                    {{ number_format($item['txCount'], 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-center no-print whitespace-nowrap">
                                    <a href="{{ route('dashboard.gnucash.reports.index', array_merge(request()->query(), ['account_id' => $acc->id])) }}"
                                       class="px-2 py-1 text-[11px] bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg transition border border-emerald-200 shadow-2xs">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-400">
                                    Tidak ada data akun yang sesuai dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-100/90 font-bold text-gray-800 border-t-2 border-gray-300">
                        <tr>
                            <td colspan="3" class="py-2.5 px-3 text-right uppercase tracking-wider text-xs">Grand Total:</td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs">
                                {{ ($grandOpening < 0 ? '-Rp ' : 'Rp ') . number_format(abs($grandOpening), 2, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-800 text-xs">
                                Rp {{ number_format($grandTotalDebit, 2, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-rose-800 text-xs">
                                Rp {{ number_format($grandTotalCredit, 2, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs {{ $grandClosing < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ ($grandClosing < 0 ? '-Rp ' : 'Rp ') . number_format(abs($grandClosing), 2, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono text-xs">
                                {{ number_format($totalAllTx, 0, ',', '.') }}
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
