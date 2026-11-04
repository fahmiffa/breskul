{{-- Cash Flow Report Partial View --}}

<!-- Top Filter Form for Cash Flow -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 no-print">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Laporan Arus Kas (Cash Flow)</h1>
                    <p class="text-xs text-gray-500">
                        {{ $isSingleCashAccount ? "Fokus Rekening: {$selectedCashAccount->name}" : "Rekapitulasi penerimaan, pengeluaran, saldo awal & saldo akhir seluruh kas tunai dan bank." }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Cetak
            </button>

            <a href="{{ route('dashboard.gnucash.reports.export', array_merge(request()->query(), ['report_type' => 'cash_flow'])) }}"
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
        <input type="hidden" name="report_type" value="cash_flow">
        <input type="hidden" name="view" value="{{ $viewMode }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <!-- Filter Tanggal Mulai -->
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

            <!-- Filter Rekening Kas/Bank (Searchable) -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Rekening Kas / Bank</label>
                <select name="account_id" id="filterAccountId"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50 text-gray-700 font-medium">
                    <option value="">Semua Rekening Kas & Bank</option>
                    @foreach($cashAccountOptions as $opt)
                        <option value="{{ $opt->id }}"
                                data-code="{{ $opt->code ?? '' }}"
                                data-type="{{ strtoupper($opt->account_type) }}"
                                data-path="{{ $opt->resolved_path ?: $opt->name }}"
                                {{ (string)$selectedAccountId === (string)$opt->id ? 'selected' : '' }}>
                            {{ $opt->code ? "[$opt->code] " : "" }}{{ $opt->resolved_path ?: $opt->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Row 2: Search + Quick Date Buttons + Submit -->
        <div class="mt-3 pt-3 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="relative w-full md:w-80">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari keterangan, nomor ref, memo..."
                       class="w-full border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50" />
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-2 text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <!-- Quick Date Range Presets -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[11px] text-gray-500 font-medium mr-1">Preset:</span>
                <button type="button" onclick="setDatePreset('all')"
                        class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Semua
                </button>
                <button type="button" onclick="setDatePreset('this_month')"
                        class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Bulan Ini
                </button>
                <button type="button" onclick="setDatePreset('last_month')"
                        class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Bulan Lalu
                </button>
                <button type="button" onclick="setDatePreset('this_year')"
                        class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Tahun Ini
                </button>
            </div>

            <!-- Submit & Reset -->
            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <a href="{{ route('dashboard.gnucash.reports.index', ['report_type' => 'cash_flow']) }}"
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

<!-- KPI / Summary Cards for Cash Flow -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
    <!-- 1. Saldo Awal Kas -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Saldo Awal Kas & Bank</span>
        <div class="mt-2 flex items-baseline justify-between">
            <span class="text-lg lg:text-xl font-bold font-mono {{ $openingCashBalance < 0 ? 'text-rose-600' : 'text-gray-800' }}">
                {{ ($openingCashBalance < 0 ? '-Rp ' : 'Rp ') . number_format(abs($openingCashBalance), 2, ',', '.') }}
            </span>
        </div>
        <span class="text-[10px] text-gray-400 mt-1">Sebelum {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/Y') : 'awal' }}</span>
    </div>

    <!-- 2. Kas Masuk (Inflow) -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between border-l-4 border-l-emerald-500">
        <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline>
            </svg>
            Penerimaan Kas (Masuk)
        </span>
        <div class="mt-2 flex items-baseline justify-between">
            <span class="text-lg lg:text-xl font-bold font-mono text-emerald-700">
                Rp {{ number_format($totalInflow, 2, ',', '.') }}
            </span>
        </div>
        <span class="text-[10px] text-emerald-600/80 mt-1">{{ count($inflowCategories) }} pos sumber masuk</span>
    </div>

    <!-- 3. Kas Keluar (Outflow) -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between border-l-4 border-l-rose-500">
        <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline>
            </svg>
            Pengeluaran Kas (Keluar)
        </span>
        <div class="mt-2 flex items-baseline justify-between">
            <span class="text-lg lg:text-xl font-bold font-mono text-rose-700">
                Rp {{ number_format($totalOutflow, 2, ',', '.') }}
            </span>
        </div>
        <span class="text-[10px] text-rose-600/80 mt-1">{{ count($outflowCategories) }} pos pengeluaran</span>
    </div>

    <!-- 4. Arus Kas Bersih (Net Flow) -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
        <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Arus Kas Bersih (Net)</span>
        <div class="mt-2 flex items-baseline justify-between">
            <span class="text-lg lg:text-xl font-bold font-mono {{ $netCashFlow >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                {{ ($netCashFlow < 0 ? '-Rp ' : '+Rp ') . number_format(abs($netCashFlow), 2, ',', '.') }}
            </span>
        </div>
        <span class="text-[10px] font-semibold mt-1 {{ $netCashFlow >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">
            {{ $netCashFlow >= 0 ? 'Surplus Kas' : 'Defisit Kas' }} ({{ number_format($totalCashTx, 0, ',', '.') }} tx)
        </span>
    </div>

    <!-- 5. Saldo Akhir Kas & Bank -->
    <div class="bg-emerald-800 text-white rounded-2xl p-4 shadow-sm flex flex-col justify-between col-span-2 lg:col-span-1">
        <span class="text-[11px] font-semibold text-emerald-200 uppercase tracking-wider">Saldo Akhir Kas & Bank</span>
        <div class="mt-2 flex items-baseline justify-between">
            <span class="text-lg lg:text-xl font-bold font-mono text-white">
                {{ ($closingCashBalance < 0 ? '-Rp ' : 'Rp ') . number_format(abs($closingCashBalance), 2, ',', '.') }}
            </span>
        </div>
        <span class="text-[10px] text-emerald-200 mt-1">Per {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d/m/Y') : 'saat ini' }}</span>
    </div>
</div>

<!-- View Switcher (Ringkasan Pos vs Rincian Mutasi) -->
<div class="flex items-center justify-between gap-3 no-print">
    <div class="flex items-center gap-1.5 p-1 bg-white rounded-xl border border-gray-200 shadow-2xs text-xs font-semibold">
        <a href="{{ route('dashboard.gnucash.reports.index', array_merge(request()->query(), ['view' => 'summary'])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ $viewMode === 'summary' ? 'bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
            Pos Penerimaan, Pengeluaran & Rekonsiliasi
        </a>
        <a href="{{ route('dashboard.gnucash.reports.index', array_merge(request()->query(), ['view' => 'detail'])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ $viewMode === 'detail' ? 'bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
            Rincian Mutasi Transaksi Kas ({{ number_format($totalCashTx, 0, ',', '.') }})
        </a>
    </div>

    @if($isSingleCashAccount)
        <a href="{{ route('dashboard.gnucash.reports.index', array_merge(request()->except('account_id'), ['report_type' => 'cash_flow'])) }}"
           class="text-xs text-emerald-700 hover:text-emerald-800 font-semibold flex items-center gap-1 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs transition">
            &larr; Tampilkan Semua Kas & Bank
        </a>
    @endif
</div>

@if($viewMode === 'summary')
    <!-- SUMMARY MODE: Inflows, Outflows, and Account Reconciliation -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- 1. Pos Penerimaan Kas (Cash Inflows) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-sm font-bold text-gray-800">Pos Penerimaan Kas (Inflows)</h2>
                </div>
                <span class="text-xs font-mono font-bold text-emerald-700">
                    Rp {{ number_format($totalInflow, 2, ',', '.') }}
                </span>
            </div>

            <div class="p-4 overflow-y-auto max-h-[500px] flex-1 divide-y divide-gray-50">
                @forelse($inflowCategories as $catName => $cat)
                    @php
                        $pct = $totalInflow > 0 ? ($cat['amount'] / $totalInflow) * 100 : 0;
                    @endphp
                    <div class="py-2.5 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <div class="flex items-center gap-1.5 truncate max-w-[65%]">
                                <span class="font-medium text-gray-800 truncate" title="{{ $catName }}">{{ $catName }}</span>
                                <span class="px-1.5 py-0.2 text-[9px] font-semibold rounded bg-gray-100 text-gray-600">
                                    {{ $cat['type'] }}
                                </span>
                            </div>
                            <div class="text-right whitespace-nowrap">
                                <span class="font-mono font-semibold text-emerald-700">
                                    Rp {{ number_format($cat['amount'], 2, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono ml-1">({{ number_format($pct, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-xs italic">
                        Tidak ada transaksi penerimaan kas pada periode ini.
                    </div>
                @endforelse
            </div>
            <div class="p-3 bg-gray-50 border-t border-gray-100 text-[11px] text-gray-500 flex justify-between font-medium">
                <span>Total {{ count($inflowCategories) }} pos sumber masuk</span>
                <span class="font-mono font-bold text-gray-800">100%</span>
            </div>
        </div>

        <!-- 2. Pos Pengeluaran Kas (Cash Outflows) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-rose-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <h2 class="text-sm font-bold text-gray-800">Pos Pengeluaran Kas (Outflows)</h2>
                </div>
                <span class="text-xs font-mono font-bold text-rose-700">
                    Rp {{ number_format($totalOutflow, 2, ',', '.') }}
                </span>
            </div>

            <div class="p-4 overflow-y-auto max-h-[500px] flex-1 divide-y divide-gray-50">
                @forelse($outflowCategories as $catName => $cat)
                    @php
                        $pct = $totalOutflow > 0 ? ($cat['amount'] / $totalOutflow) * 100 : 0;
                    @endphp
                    <div class="py-2.5 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <div class="flex items-center gap-1.5 truncate max-w-[65%]">
                                <span class="font-medium text-gray-800 truncate" title="{{ $catName }}">{{ $catName }}</span>
                                <span class="px-1.5 py-0.2 text-[9px] font-semibold rounded bg-gray-100 text-gray-600">
                                    {{ $cat['type'] }}
                                </span>
                            </div>
                            <div class="text-right whitespace-nowrap">
                                <span class="font-mono font-semibold text-rose-700">
                                    Rp {{ number_format($cat['amount'], 2, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono ml-1">({{ number_format($pct, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-xs italic">
                        Tidak ada transaksi pengeluaran kas pada periode ini.
                    </div>
                @endforelse
            </div>
            <div class="p-3 bg-gray-50 border-t border-gray-100 text-[11px] text-gray-500 flex justify-between font-medium">
                <span>Total {{ count($outflowCategories) }} pos pengeluaran</span>
                <span class="font-mono font-bold text-gray-800">100%</span>
            </div>
        </div>
    </div>

    <!-- 3. Rekonsiliasi Saldo Tiap Rekening Kas & Bank -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-gray-800">Posisi Saldo Tiap Rekening Kas & Bank</h2>
                <p class="text-[11px] text-gray-500">Daftar buku kas & rekening bank dengan rincian saldo awal, mutasi masuk/keluar, dan saldo akhir.</p>
            </div>
            <span class="px-2.5 py-1 text-xs rounded-lg bg-gray-100 text-gray-700 font-semibold w-fit">
                {{ count($accountsReconciliation) }} Rekening Kas/Bank Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50/80 text-gray-600 font-semibold border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4">Nama Rekening / Akun</th>
                        <th class="py-3 px-3">Tipe</th>
                        <th class="py-3 px-4 text-right">Saldo Awal</th>
                        <th class="py-3 px-4 text-right text-emerald-700">Kas Masuk (+)</th>
                        <th class="py-3 px-4 text-right text-rose-700">Kas Keluar (-)</th>
                        <th class="py-3 px-4 text-right">Arus Bersih (Net)</th>
                        <th class="py-3 px-4 text-right font-bold text-gray-900">Saldo Akhir</th>
                        <th class="py-3 px-3 text-center">Mutasi</th>
                        <th class="py-3 px-3 text-center no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $sumOpen = 0; $sumIn = 0; $sumOut = 0; $sumNet = 0; $sumClose = 0; $sumTx = 0;
                    @endphp
                    @foreach($accountsReconciliation as $row)
                        @php
                            $acc = $row['account'];
                            $sumOpen += $row['opening'];
                            $sumIn += $row['inflow'];
                            $sumOut += $row['outflow'];
                            $sumNet += $row['net'];
                            $sumClose += $row['closing'];
                            $sumTx += $row['tx_count'];
                            $isRowSelected = ($selectedAccountId === $acc->id);
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition {{ $isRowSelected ? 'bg-emerald-50/50 font-semibold' : '' }}">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900 flex items-center gap-1.5">
                                    @if($acc->code)
                                        <span class="font-mono text-[10px] bg-gray-100 text-gray-600 px-1 py-0.2 rounded font-semibold">{{ $acc->code }}</span>
                                    @endif
                                    <span>{{ $acc->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $acc->account_type === 'BANK' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $acc->account_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700 whitespace-nowrap">
                                {{ ($row['opening'] < 0 ? '-Rp ' : 'Rp ') . number_format(abs($row['opening']), 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-700 font-medium whitespace-nowrap">
                                Rp {{ number_format($row['inflow'], 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-rose-700 font-medium whitespace-nowrap">
                                Rp {{ number_format($row['outflow'], 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-medium whitespace-nowrap {{ $row['net'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ ($row['net'] < 0 ? '-Rp ' : '+Rp ') . number_format(abs($row['net']), 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap {{ $row['closing'] < 0 ? 'text-rose-600' : 'text-gray-900' }}">
                                {{ ($row['closing'] < 0 ? '-Rp ' : 'Rp ') . number_format(abs($row['closing']), 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-gray-500">
                                {{ number_format($row['tx_count'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center no-print whitespace-nowrap">
                                <a href="{{ route('dashboard.gnucash.reports.index', array_merge(request()->query(), ['report_type' => 'cash_flow', 'account_id' => $acc->id])) }}"
                                   class="px-2.5 py-1 text-[11px] rounded-lg transition font-semibold {{ $isRowSelected ? 'bg-emerald-600 text-white' : 'bg-gray-100 hover:bg-emerald-100 text-gray-700 hover:text-emerald-800' }}">
                                    {{ $isRowSelected ? 'Aktif' : 'Fokus' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 font-bold text-gray-800 border-t-2 border-gray-300">
                    <tr>
                        <td colspan="2" class="py-3 px-4 uppercase text-xs tracking-wider">Total Seluruh Kas & Bank:</td>
                        <td class="py-3 px-4 text-right font-mono">
                            {{ ($sumOpen < 0 ? '-Rp ' : 'Rp ') . number_format(abs($sumOpen), 2, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-800">
                            Rp {{ number_format($sumIn, 2, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-rose-800">
                            Rp {{ number_format($sumOut, 2, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono {{ $sumNet >= 0 ? 'text-emerald-800' : 'text-rose-700' }}">
                            {{ ($sumNet < 0 ? '-Rp ' : '+Rp ') . number_format(abs($sumNet), 2, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono {{ $sumClose < 0 ? 'text-rose-700' : 'text-gray-900' }}">
                            {{ ($sumClose < 0 ? '-Rp ' : 'Rp ') . number_format(abs($sumClose), 2, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-center font-mono">
                            {{ number_format($sumTx, 0, ',', '.') }}
                        </td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

@else
    <!-- DETAIL MODE: Paginated Transaction Register -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-gray-800">Rincian Mutasi Arus Kas</h2>
                <p class="text-[11px] text-gray-500">Daftar transaksi kas & bank secara kronologis dengan saldo berjalan.</p>
            </div>
            <div class="text-xs text-gray-500 font-medium">
                Menampilkan {{ $paginatedSplits->firstItem() ?? 0 }} - {{ $paginatedSplits->lastItem() ?? 0 }} dari {{ number_format($paginatedSplits->total(), 0, ',', '.') }} transaksi
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-emerald-800 text-white font-semibold">
                    <tr>
                        <th class="py-3 px-3.5 w-24">Tanggal</th>
                        <th class="py-3 px-2.5 w-24">No. Ref</th>
                        <th class="py-3 px-3">Rekening Kas/Bank</th>
                        <th class="py-3 px-4">Kategori / Lawan Akun</th>
                        <th class="py-3 px-4">Deskripsi Transaksi</th>
                        <th class="py-3 px-3.5 text-right w-32">Penerimaan (+)</th>
                        <th class="py-3 px-3.5 text-right w-32">Pengeluaran (-)</th>
                        <th class="py-3 px-3.5 text-right w-36">Saldo Kas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($detailRows as $row)
                        @php
                            $sp = $row['split'];
                            $tx = $row['tx'];
                            $acc = $row['account'];
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-2.5 px-3.5 font-mono text-gray-600 whitespace-nowrap">
                                {{ $tx->post_date ? \Carbon\Carbon::parse($tx->post_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2.5 px-2.5 font-mono text-gray-500 whitespace-nowrap">
                                {{ $tx->num ?: '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="font-medium text-gray-800">{{ $acc ? $acc->name : '-' }}</span>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="font-mono text-gray-700 bg-gray-50 px-2 py-0.5 rounded border border-gray-100" title="{{ $row['transfer'] }}">
                                    {{ $row['transfer'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-gray-800">
                                <div class="font-medium">{{ $tx->description ?: '-' }}</div>
                                @if($sp->memo && $sp->memo !== $tx->description)
                                    <div class="text-[10px] text-gray-400 italic">Memo: {{ $sp->memo }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3.5 text-right font-mono text-emerald-700 whitespace-nowrap font-medium">
                                {{ $row['inflow'] > 0 ? 'Rp ' . number_format($row['inflow'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="py-2.5 px-3.5 text-right font-mono text-rose-700 whitespace-nowrap font-medium">
                                {{ $row['outflow'] > 0 ? 'Rp ' . number_format($row['outflow'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="py-2.5 px-3.5 text-right font-mono font-bold whitespace-nowrap {{ $row['running_balance'] < 0 ? 'text-rose-600' : 'text-gray-900' }}">
                                {{ ($row['running_balance'] < 0 ? '-Rp ' : 'Rp ') . number_format(abs($row['running_balance']), 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400 italic">
                                Tidak ada mutasi kas & bank yang ditemukan untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedSplits->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50 pagination-container no-print">
                {{ $paginatedSplits->links() }}
            </div>
        @endif
    </div>
@endif
