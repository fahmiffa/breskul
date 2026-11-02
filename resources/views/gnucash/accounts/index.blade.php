@extends('base.layout')
@section('title', $title)

@section('content')
<div class="flex flex-col gap-5">

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-xs px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('err') || session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="font-medium">{{ session('err') ?: session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Top Action & Info Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-green-100 text-green-700 flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                            <line x1="2" x2="22" y1="10" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Bagan Akun (Chart of Accounts)</h1>
                        <p class="text-xs text-gray-500">Struktur hierarki akun akuntansi GnuCash berserta saldo berjalan dan akumulasi sub-akun.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard.gnucash.accounts.create') }}"
                   class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tambah Akun
                </a>

                <a href="{{ route('dashboard.gnucash.transactions.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/>
                        <path d="M8 11h8"/><path d="M8 7h6"/>
                    </svg>
                    Jurnal Transaksi
                </a>

                <button type="button" id="btnExpandAll"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="7 11 12 16 17 11"></polyline>
                        <polyline points="7 6 12 11 17 6"></polyline>
                    </svg>
                    Buka Semua
                </button>

                <button type="button" id="btnCollapseAll"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl flex items-center gap-1.5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="17 13 12 8 7 13"></polyline>
                        <polyline points="17 18 12 13 7 18"></polyline>
                    </svg>
                    Tutup Semua
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="mt-4 pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" id="accountSearch" placeholder="Cari akun, sub-akun, kode, atau deskripsi..."
                       class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-gray-50/50" />
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-2.5 text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select id="typeFilter" class="w-full sm:w-auto border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500 bg-gray-50/50 text-gray-700 font-medium">
                    <option value="">Semua Kategori</option>
                    <option value="ASSET">Assets / Aset</option>
                    <option value="EQUITY">Equity / Ekuitas</option>
                    <option value="LIABILITY">Liabilities / Hutang</option>
                    <option value="INCOME">Pendapatan / Income</option>
                    <option value="EXPENSE">Pengeluaran / Expenses</option>
                </select>

                <button type="button" id="btnResetFilter" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold px-3 py-2 rounded-xl transition">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Main Tree Table Card (matching induk.png) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm select-none" id="accountsTreeTable">
                <thead>
                    <tr class="bg-gray-50/90 text-gray-700 font-bold border-b border-gray-200">
                        <th class="py-3 px-4 text-left w-7/12 tracking-wide font-semibold">Nama Akun</th>
                        <th class="py-3 px-4 text-left w-3/12 tracking-wide font-semibold">Deskripsi</th>
                        <th class="py-3 px-4 text-right w-2/12 tracking-wide font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white" id="accountsTbody">
                    @forelse($accounts as $acc)
                        @php
                            $bal = $acc->recursive_balance;
                            $isNegative = $bal < -0.001;
                            $isZero = abs($bal) < 0.001;
                            $formattedTotal = ($isNegative ? '-Rp' : 'Rp') . number_format(abs($bal), 2, ',', '.');
                            // In GnuCash, amounts with 00 decimals often shown as ,00 or truncated
                            $hasChildren = $acc->has_children;
                            $depth = $acc->depth;
                            $paddingLeft = $depth * 1.5 + 0.5; // in rem
                        @endphp
                        <tr class="account-row group hover:bg-green-50/50 transition-colors {{ $depth === 0 ? 'bg-gray-50/40 font-semibold' : '' }}"
                            data-id="{{ $acc->id }}"
                            data-parent-id="{{ $acc->parent_id }}"
                            data-ancestors="{{ $acc->ancestor_ids_str }}"
                            data-depth="{{ $depth }}"
                            data-has-children="{{ $hasChildren ? '1' : '0' }}"
                            data-type="{{ strtoupper($acc->account_type) }}"
                            data-open="true">

                            <!-- Nama Akun -->
                            <td class="py-2.5 px-4 text-gray-800">
                                <div class="flex items-center" style="padding-left: {{ $paddingLeft }}rem;">
                                    @if($hasChildren)
                                        <!-- Expand/Collapse Chevron -->
                                        <button type="button"
                                                class="toggle-chevron w-5 h-5 flex items-center justify-center text-gray-500 hover:text-green-700 hover:bg-gray-100 rounded mr-1.5 focus:outline-none transition-transform"
                                                title="Klik untuk membuka/menutup sub-akun">
                                            <svg class="chevron-icon w-3.5 h-3.5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <span class="account-name font-medium text-gray-900 cursor-pointer toggle-row hover:text-green-700">
                                            {{ $acc->name }}
                                        </span>
                                    @else
                                        <!-- Leaf Account (No Dropdown - opens ledger sub.png) -->
                                        <span class="w-5 h-5 flex items-center justify-center text-gray-400 mr-1.5 opacity-60">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            </svg>
                                        </span>
                                        <a href="{{ route('dashboard.gnucash.accounts.ledger', $acc->id) }}"
                                           class="account-name text-green-700 hover:text-green-900 hover:underline font-medium flex items-center gap-1.5"
                                           title="Buka Buku Besar akun ini (seperti sub.png)">
                                            <span>{{ $acc->name }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-hover:opacity-100 transition-opacity text-green-600">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                            </svg>
                                        </a>
                                    @endif

                                    @if($acc->code)
                                        <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-500">{{ $acc->code }}</span>
                                    @endif

                                    <!-- Quick In-Row Actions on Hover -->
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity ml-2">
                                        @if($hasChildren)
                                            <a href="{{ route('dashboard.gnucash.accounts.create', ['parent_id' => $acc->id]) }}"
                                               class="px-1.5 py-0.5 rounded bg-green-50 hover:bg-green-100 text-green-700 text-[10px] font-semibold border border-green-200"
                                               title="Tambah Sub-Akun di bawah {{ $acc->name }}">
                                                + Sub
                                            </a>
                                        @endif
                                        <a href="{{ route('dashboard.gnucash.accounts.edit', $acc->id) }}"
                                           class="p-0.5 text-gray-400 hover:text-green-700 rounded transition"
                                           title="Edit Akun {{ $acc->name }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-2.5 px-4 text-gray-500 text-xs">
                                <span class="account-desc line-clamp-1" title="{{ $acc->description }}">
                                    {{ $acc->description ?: '' }}
                                </span>
                            </td>

                            <!-- Total (Balance) -->
                            <td class="py-2.5 px-4 text-right font-mono font-medium whitespace-nowrap {{ $isNegative ? 'text-red-600' : ($isZero ? 'text-gray-400' : 'text-gray-800') }}">
                                {{ $formattedTotal }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center text-gray-400">
                                Tidak ada data akun.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bottom Status Bar (matching induk.png footer) -->
        <div class="bg-gray-100/90 border-t border-gray-200 px-5 py-3 flex flex-col md:flex-row md:items-center justify-between text-xs text-gray-700 gap-2">
            <div class="font-semibold text-gray-600 flex items-center gap-4">
                <span>Rp. Total Keseluruhan:</span>
                <span class="text-gray-500 font-normal">Total {{ $totalAccounts }} Akun</span>
            </div>

            <div class="flex flex-wrap items-center gap-6 font-mono">
                <!-- Aset Bersih (Net Assets) -->
                <div class="flex items-center gap-1.5">
                    <span class="text-gray-600 font-sans font-semibold">Aset Bersih:</span>
                    <span class="font-bold {{ $netAssets < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ ($netAssets < 0 ? '-Rp' : 'Rp') . number_format(abs($netAssets), 2, ',', '.') }}
                    </span>
                </div>

                <!-- Laba (Profit / Loss) -->
                <div class="flex items-center gap-1.5">
                    <span class="text-gray-600 font-sans font-semibold">Laba:</span>
                    <span class="font-bold {{ $profitYear < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ ($profitYear < 0 ? '-Rp' : 'Rp') . number_format(abs($profitYear), 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Interactive Client-side Tree Controller (Expand, Collapse, Search, Filter) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('accountsTreeTable');
    const rows = Array.from(document.querySelectorAll('.account-row'));
    const searchInput = document.getElementById('accountSearch');
    const typeFilter = document.getElementById('typeFilter');
    const btnExpandAll = document.getElementById('btnExpandAll');
    const btnCollapseAll = document.getElementById('btnCollapseAll');
    const btnResetFilter = document.getElementById('btnResetFilter');

    // Build parent-to-children and child-to-ancestors map in JS
    const rowMap = new Map();
    rows.forEach(row => {
        const id = row.getAttribute('data-id');
        rowMap.set(id, row);
    });

    // Toggle single parent
    function toggleParent(row, forceState = null) {
        const id = row.getAttribute('data-id');
        const hasChildren = row.getAttribute('data-has-children') === '1';
        if (!hasChildren) return;

        const chevron = row.querySelector('.chevron-icon');
        const currentState = row.getAttribute('data-open') === 'true';
        const newState = forceState !== null ? forceState : !currentState;

        row.setAttribute('data-open', newState ? 'true' : 'false');
        if (chevron) {
            chevron.style.transform = newState ? 'rotate(0deg)' : 'rotate(-90deg)';
        }

        // Apply visibility to children recursively
        updateChildrenVisibility();
    }

    // Update visibility of all rows based on parent open states
    function updateChildrenVisibility() {
        // A row is visible if all its ancestors are open (and matches search/filter)
        const isSearching = searchInput.value.trim().length > 0;
        const selectedType = typeFilter.value.trim();

        rows.forEach(row => {
            const depth = parseInt(row.getAttribute('data-depth'), 10);
            const ancestorsStr = row.getAttribute('data-ancestors');
            const ancestors = ancestorsStr ? ancestorsStr.split(',') : [];

            let parentClosed = false;
            for (const ancId of ancestors) {
                const ancRow = rowMap.get(ancId);
                if (ancRow && ancRow.getAttribute('data-open') === 'false') {
                    parentClosed = true;
                    break;
                }
            }

            if (parentClosed && !isSearching) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
            }
        });
    }

    // Attach click listeners to chevrons and parent rows
    rows.forEach(row => {
        const hasChildren = row.getAttribute('data-has-children') === '1';
        if (hasChildren) {
            const chevronBtn = row.querySelector('.toggle-chevron');
            const rowTitle = row.querySelector('.toggle-row');

            if (chevronBtn) {
                chevronBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleParent(row);
                });
            }
            if (rowTitle) {
                rowTitle.addEventListener('click', () => {
                    toggleParent(row);
                });
            }
        }
    });

    // Expand All
    btnExpandAll.addEventListener('click', () => {
        rows.forEach(row => {
            if (row.getAttribute('data-has-children') === '1') {
                row.setAttribute('data-open', 'true');
                const chevron = row.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
            row.style.display = '';
        });
    });

    // Collapse All (only keep depth 0 open, close depth 0+ children)
    btnCollapseAll.addEventListener('click', () => {
        rows.forEach(row => {
            const depth = parseInt(row.getAttribute('data-depth'), 10);
            if (row.getAttribute('data-has-children') === '1') {
                row.setAttribute('data-open', 'false');
                const chevron = row.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(-90deg)';
            }
            if (depth > 0) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
            }
        });
    });

    // Live Search Filter
    function applyFilter() {
        const query = searchInput.value.toLowerCase().trim();
        const selectedType = typeFilter.value.trim().toUpperCase();

        if (!query && !selectedType) {
            updateChildrenVisibility();
            return;
        }

        const matchedIds = new Set();
        const visibleAncestorIds = new Set();

        rows.forEach(row => {
            const name = (row.querySelector('.account-name')?.textContent || '').toLowerCase();
            const desc = (row.querySelector('.account-desc')?.textContent || '').toLowerCase();
            const type = row.getAttribute('data-type') || '';

            const matchQuery = !query || name.includes(query) || desc.includes(query);
            const matchType = !selectedType || type === selectedType;

            if (matchQuery && matchType) {
                const id = row.getAttribute('data-id');
                matchedIds.add(id);

                // Add all ancestors to be visible and opened
                const ancestorsStr = row.getAttribute('data-ancestors');
                if (ancestorsStr) {
                    ancestorsStr.split(',').forEach(ancId => {
                        if (ancId) visibleAncestorIds.add(ancId);
                    });
                }
            }
        });

        // Show matched rows and their ancestors; expand ancestors
        rows.forEach(row => {
            const id = row.getAttribute('data-id');
            if (matchedIds.has(id) || visibleAncestorIds.has(id)) {
                row.style.display = '';
                if (visibleAncestorIds.has(id)) {
                    row.setAttribute('data-open', 'true');
                    const chevron = row.querySelector('.chevron-icon');
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                }
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', applyFilter);
    typeFilter.addEventListener('change', applyFilter);

    btnResetFilter.addEventListener('click', () => {
        searchInput.value = '';
        typeFilter.value = '';
        btnExpandAll.click();
    });
});
</script>
@endsection
