<?php

namespace App\Http\Controllers;

use App\Models\GnucashAccount;
use App\Models\GnucashSplit;
use App\Models\GnucashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GnucashAccountController extends Controller
{
    /**
     * Display a listing of accounts (Chart of Accounts Tree View matching GnuCash).
     */
    public function index(Request $request)
    {
        $title = "Bagan Akun (Chart of Accounts)";

        // 1. Fetch all accounts from database
        $rawAccounts = GnucashAccount::all();

        // 2. Fetch split aggregations per account in single query
        $rawSplits = DB::table('gnucash_splits')
            ->select('account_id', DB::raw('SUM(value) as total_val'), DB::raw('COUNT(*) as count_val'))
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        // 3. Find ROOT account
        $root = $rawAccounts->firstWhere('account_type', 'ROOT');
        $rootId = $root ? $root->id : null;

        // 4. Index accounts by parent_id and by id
        $childrenByParent = [];
        $accountsById = [];
        foreach ($rawAccounts as $acc) {
            $accountsById[$acc->id] = $acc;
            $pId = $acc->parent_id ?: '__root__';
            $childrenByParent[$pId][] = $acc;
        }

        // 5. Calculate direct balance for every account
        foreach ($rawAccounts as $acc) {
            $sp = $rawSplits[$acc->id] ?? null;
            $val = $sp ? (float) $sp->total_val : 0.0;
            $cnt = $sp ? (int) $sp->count_val : 0;

            $isCredit = in_array(strtoupper($acc->account_type), ['LIABILITY', 'EQUITY', 'INCOME', 'CREDIT', 'PAYABLE']);
            $acc->direct_balance = $isCredit ? -$val : $val;
            $acc->splits_count = $cnt;
            $acc->has_children = isset($childrenByParent[$acc->id]) && count($childrenByParent[$acc->id]) > 0;
            $acc->is_leaf = !$acc->has_children;
        }

        // 6. Helper to compute recursive balance (self + all sub-accounts)
        $computeRecursive = function ($acc) use (&$computeRecursive, &$childrenByParent) {
            $tot = $acc->direct_balance;
            if (isset($childrenByParent[$acc->id])) {
                foreach ($childrenByParent[$acc->id] as $child) {
                    $tot += $computeRecursive($child);
                }
            }
            $acc->recursive_balance = $tot;
            return $tot;
        };

        // 7. Get top-level accounts under ROOT (or rootless accounts)
        $topAccounts = [];
        if ($rootId && isset($childrenByParent[$rootId])) {
            $topAccounts = array_merge($topAccounts, $childrenByParent[$rootId]);
        }
        if (isset($childrenByParent['__root__'])) {
            $topAccounts = array_merge($topAccounts, array_filter($childrenByParent['__root__'], fn($a) => strtoupper($a->account_type) !== 'ROOT'));
        }

        // Sorting top level: Assets, Equity, Liabilities, Pendapatan, Pengeluaran, others
        $orderMap = [
            'ASSET' => 1,
            'EQUITY' => 2,
            'LIABILITY' => 3,
            'INCOME' => 4,
            'EXPENSE' => 5,
        ];

        usort($topAccounts, function ($a, $b) use ($orderMap) {
            $oa = $orderMap[strtoupper($a->account_type)] ?? 99;
            $ob = $orderMap[strtoupper($b->account_type)] ?? 99;
            if ($oa !== $ob) return $oa <=> $ob;
            return strnatcasecmp($a->name, $b->name);
        });

        // Compute recursive totals starting from top-level
        foreach ($topAccounts as $ta) {
            $computeRecursive($ta);
        }

        // 8. Flatten tree in depth-first order
        $flattenTree = function ($accounts, $depth = 0, $ancestorIds = []) use (&$flattenTree, &$childrenByParent) {
            $flat = [];
            foreach ($accounts as $acc) {
                $acc->depth = $depth;
                $acc->ancestor_ids_list = $ancestorIds;
                $acc->ancestor_ids_str = implode(',', $ancestorIds);
                $flat[] = $acc;

                if (isset($childrenByParent[$acc->id])) {
                    $children = $childrenByParent[$acc->id];
                    // Sort children alphabetically by name
                    usort($children, function ($a, $b) {
                        return strnatcasecmp($a->name, $b->name);
                    });

                    $newAncestors = array_merge($ancestorIds, [$acc->id]);
                    $subFlat = $flattenTree($children, $depth + 1, $newAncestors);
                    $flat = array_merge($flat, $subFlat);
                }
            }
            return $flat;
        };

        $flatTree = $flattenTree($topAccounts, 0, []);

        // 9. Overall Summary Metrics (matching induk.png bottom status bar)
        $assetsTotal = (float) DB::table('gnucash_splits')
            ->join('gnucash_accounts', 'gnucash_splits.account_id', '=', 'gnucash_accounts.id')
            ->whereIn('gnucash_accounts.account_type', ['ASSET', 'BANK', 'CASH'])
            ->sum('gnucash_splits.value');

        $liabilitiesTotal = (float) DB::table('gnucash_splits')
            ->join('gnucash_accounts', 'gnucash_splits.account_id', '=', 'gnucash_accounts.id')
            ->whereIn('gnucash_accounts.account_type', ['LIABILITY', 'CREDIT', 'PAYABLE'])
            ->sum('gnucash_splits.value');

        // Net Assets: Total Assets - Total Liabilities
        $netAssets = $assetsTotal - (-$liabilitiesTotal);

        // Current Year Profit (2026 or current year)
        $currentYear = 2026;
        $incYear = (float) DB::table('gnucash_splits')
            ->join('gnucash_accounts', 'gnucash_splits.account_id', '=', 'gnucash_accounts.id')
            ->join('gnucash_transactions', 'gnucash_splits.transaction_id', '=', 'gnucash_transactions.id')
            ->where('gnucash_accounts.account_type', 'INCOME')
            ->whereYear('gnucash_transactions.post_date', $currentYear)
            ->sum('gnucash_splits.value');

        $expYear = (float) DB::table('gnucash_splits')
            ->join('gnucash_accounts', 'gnucash_splits.account_id', '=', 'gnucash_accounts.id')
            ->join('gnucash_transactions', 'gnucash_splits.transaction_id', '=', 'gnucash_transactions.id')
            ->where('gnucash_accounts.account_type', 'EXPENSE')
            ->whereYear('gnucash_transactions.post_date', $currentYear)
            ->sum('gnucash_splits.value');

        $profitYear = (-$incYear) - $expYear;

        // Group summary totals by primary categories
        $typeTotals = [];
        $types = ['ASSET', 'BANK', 'CASH', 'LIABILITY', 'EQUITY', 'INCOME', 'EXPENSE'];
        foreach ($flatTree as $acc) {
            $t = strtoupper($acc->account_type);
            if (!isset($typeTotals[$t])) {
                $typeTotals[$t] = ['count' => 0, 'balance' => 0.0];
            }
            $typeTotals[$t]['count']++;
            $typeTotals[$t]['balance'] += $acc->direct_balance;
        }

        return view('gnucash.accounts.index', [
            'title' => $title,
            'accounts' => $flatTree,
            'typeTotals' => $typeTotals,
            'types' => $types,
            'netAssets' => $netAssets,
            'profitYear' => $profitYear,
            'currentYear' => $currentYear,
            'totalAccounts' => count($flatTree),
            'totalTransactions' => GnucashTransaction::count(),
            'search' => $request->search ?? '',
            'selectedType' => $request->type ?? '',
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create(Request $request)
    {
        $allAccounts = GnucashAccount::all()->keyBy('id');
        $resolvePath = function ($acc) use (&$resolvePath, &$allAccounts) {
            if (!$acc) return '';
            $parts = [$acc->name];
            $curr = $acc;
            while ($curr && $curr->parent_id && isset($allAccounts[$curr->parent_id])) {
                $curr = $allAccounts[$curr->parent_id];
                if ($curr->account_type === 'ROOT') break;
                $parts[] = $curr->name;
            }
            return implode(' : ', array_reverse($parts));
        };

        $parentOptions = GnucashAccount::where('account_type', '!=', 'ROOT')
            ->get()
            ->map(function ($acc) use ($resolvePath) {
                return [
                    'id' => $acc->id,
                    'name' => $acc->name,
                    'code' => $acc->code,
                    'account_type' => strtoupper($acc->account_type),
                    'full_path' => $resolvePath($acc),
                ];
            })
            ->sortBy('full_path')
            ->values()
            ->toArray();

        $types = ['ASSET', 'BANK', 'CASH', 'LIABILITY', 'EQUITY', 'INCOME', 'EXPENSE', 'CREDIT'];

        $selectedParentId = $request->get('parent_id', '');
        $defaultType = 'ASSET';

        if ($selectedParentId) {
            $parent = GnucashAccount::find($selectedParentId);
            if ($parent) {
                $pType = strtoupper($parent->account_type);
                if (in_array($pType, ['ASSET', 'BANK', 'CASH'])) {
                    $defaultType = 'ASSET';
                } elseif (in_array($pType, ['LIABILITY', 'CREDIT', 'PAYABLE'])) {
                    $defaultType = 'LIABILITY';
                } elseif ($pType === 'INCOME') {
                    $defaultType = 'INCOME';
                } elseif ($pType === 'EXPENSE') {
                    $defaultType = 'EXPENSE';
                } elseif ($pType === 'EQUITY') {
                    $defaultType = 'EQUITY';
                }
            }
        }

        $account = new GnucashAccount([
            'parent_id' => $selectedParentId,
            'account_type' => $defaultType,
        ]);

        return view('gnucash.accounts.form', [
            'action' => 'Tambah Akun Rekening',
            'title' => 'Form Tambah Akun Rekening',
            'account' => $account,
            'parents' => $parentOptions,
            'parentOptions' => $parentOptions,
            'types' => $types,
            'selectedParentId' => $selectedParentId,
        ]);
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_type' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:gnucash_accounts,id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'placeholder' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama akun wajib diisi.',
            'account_type.required' => 'Tipe akun wajib dipilih.',
        ]);

        $id = str_replace('-', '', (string) Str::uuid());

        GnucashAccount::create([
            'id' => $id,
            'name' => $request->name,
            'account_type' => strtoupper($request->account_type),
            'parent_id' => $request->parent_id ?: null,
            'code' => $request->code ?: null,
            'description' => $request->description ?: null,
            'placeholder' => $request->boolean('placeholder'),
            'commodity_scu' => 100,
        ]);

        return redirect()->route('dashboard.gnucash.accounts.index')
            ->with('success', "Akun '{$request->name}' berhasil ditambahkan.");
    }

    /**
     * Display the specified account ledger (Buku Besar / Register matching sub.png).
     */
    public function ledger(Request $request, $id)
    {
        $account = GnucashAccount::with('parent')->findOrFail($id);
        $title = "{$account->name} - Buku Besar";

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = trim($request->get('search', ''));

        $isCreditNormal = in_array(strtoupper($account->account_type), ['LIABILITY', 'EQUITY', 'INCOME', 'CREDIT', 'PAYABLE']);

        // Load all accounts indexed by id to resolve transfer full paths quickly
        $allAccounts = GnucashAccount::all()->keyBy('id');
        $resolvePath = function ($acc) use (&$resolvePath, &$allAccounts) {
            if (!$acc) return '';
            $parts = [$acc->name];
            $curr = $acc;
            while ($curr && $curr->parent_id && isset($allAccounts[$curr->parent_id])) {
                $curr = $allAccounts[$curr->parent_id];
                if ($curr->account_type === 'ROOT') break;
                $parts[] = $curr->name;
            }
            return implode(':', array_reverse($parts));
        };

        // 1. Calculate opening balance before fromDate
        $openingNet = 0.0;
        if ($fromDate) {
            $openingNet = (float) DB::table('gnucash_splits')
                ->join('gnucash_transactions', 'gnucash_splits.transaction_id', '=', 'gnucash_transactions.id')
                ->where('gnucash_splits.account_id', $account->id)
                ->where('gnucash_transactions.post_date', '<', $fromDate . ' 00:00:00')
                ->sum('gnucash_splits.value');
        }
        $openingBalance = $isCreditNormal ? -$openingNet : $openingNet;

        // 2. Query splits for this account
        $query = GnucashSplit::with(['transaction'])
            ->join('gnucash_transactions', 'gnucash_splits.transaction_id', '=', 'gnucash_transactions.id')
            ->where('gnucash_splits.account_id', $account->id)
            ->select('gnucash_splits.*');

        if ($fromDate) {
            $query->where('gnucash_transactions.post_date', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $query->where('gnucash_transactions.post_date', '<=', $toDate . ' 23:59:59');
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('gnucash_transactions.description', 'like', "%{$search}%")
                  ->orWhere('gnucash_transactions.num', 'like', "%{$search}%")
                  ->orWhere('gnucash_splits.memo', 'like', "%{$search}%");
            });
        }

        // Default pagination: 100 entries per page
        $perPage = (int) $request->get('per_page', 100);
        $splits = $query->orderBy('gnucash_transactions.post_date', 'asc')
            ->orderBy('gnucash_splits.id', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // 3. Offset running balance if on page > 1
        $page = (int) $request->get('page', 1);
        $runningBalance = $openingBalance;

        if ($page > 1) {
            $offsetCount = ($page - 1) * $perPage;
            $prevSplitsSum = (float) DB::table('gnucash_splits')
                ->join('gnucash_transactions', 'gnucash_splits.transaction_id', '=', 'gnucash_transactions.id')
                ->where('gnucash_splits.account_id', $account->id)
                ->when($fromDate, fn($q) => $q->where('gnucash_transactions.post_date', '>=', $fromDate . ' 00:00:00'))
                ->when($toDate, fn($q) => $q->where('gnucash_transactions.post_date', '<=', $toDate . ' 23:59:59'))
                ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('gnucash_transactions.description', 'like', "%{$search}%")
                    ->orWhere('gnucash_transactions.num', 'like', "%{$search}%")
                    ->orWhere('gnucash_splits.memo', 'like', "%{$search}%")))
                ->orderBy('gnucash_transactions.post_date', 'asc')
                ->orderBy('gnucash_splits.id', 'asc')
                ->limit($offsetCount)
                ->sum('gnucash_splits.value');

            $runningBalance += ($isCreditNormal ? -$prevSplitsSum : $prevSplitsSum);
        }

        // 4. Preload other splits for the current page transactions to determine Transfer account
        $txIds = $splits->pluck('transaction_id')->unique();
        $otherSplitsGrouped = GnucashSplit::whereIn('transaction_id', $txIds)
            ->get()
            ->groupBy('transaction_id');

        $rows = [];
        $pageTotalTambah = 0.0;
        $pageTotalKurang = 0.0;

        foreach ($splits as $split) {
            $tx = $split->transaction;
            $val = (float) $split->value;

            // Opposing splits in the same transaction
            $others = ($otherSplitsGrouped[$split->transaction_id] ?? collect())->where('id', '!=', $split->id);

            if ($others->count() === 1) {
                $otherSplit = $others->first();
                $otherAcc = $allAccounts[$otherSplit->account_id] ?? null;
                $transferText = $resolvePath($otherAcc);
                $isMultiSplit = false;
            } elseif ($others->count() > 1) {
                $transferText = '-- Transaksi Split --';
                $isMultiSplit = true;
            } else {
                $transferText = '--';
                $isMultiSplit = false;
            }

            // Tambah vs Kurang based on account normal balance
            if ($isCreditNormal) {
                $tambah = $val < 0 ? abs($val) : 0.0;
                $kurang = $val > 0 ? $val : 0.0;
                $runningBalance += ($tambah - $kurang);
            } else {
                $tambah = $val > 0 ? $val : 0.0;
                $kurang = $val < 0 ? abs($val) : 0.0;
                $runningBalance += ($tambah - $kurang);
            }

            $pageTotalTambah += $tambah;
            $pageTotalKurang += $kurang;

            $rows[] = [
                'split' => $split,
                'tx' => $tx,
                'transfer' => $transferText,
                'isMultiSplit' => $isMultiSplit,
                'allSplits' => $isMultiSplit ? ($otherSplitsGrouped[$split->transaction_id] ?? collect()) : null,
                'tambah' => $tambah,
                'kurang' => $kurang,
                'balance' => $runningBalance,
            ];
        }

        // 5. Global Footer Summary Metrics for this account (matching sub.png status bar):
        // Saat ini, Masa Depan, Klir, Rekonsiliasi, Minimum Diproyeksikan
        $allAccountSplits = GnucashSplit::where('account_id', $account->id)
            ->join('gnucash_transactions', 'gnucash_splits.transaction_id', '=', 'gnucash_transactions.id')
            ->orderBy('gnucash_transactions.post_date', 'asc')
            ->orderBy('gnucash_splits.id', 'asc')
            ->select('gnucash_splits.value', 'gnucash_splits.reconcile_state', 'gnucash_transactions.post_date')
            ->get();

        $now = now();
        $saatIni = 0.0;
        $masaDepan = 0.0;
        $klir = 0.0;
        $rekonsiliasi = 0.0;
        $minDiproyeksikan = $allAccountSplits->count() > 0 ? PHP_FLOAT_MAX : 0.0;
        $runTotal = 0.0;

        foreach ($allAccountSplits as $sp) {
            $v = (float) $sp->value;
            $delta = $isCreditNormal ? -$v : $v;
            $runTotal += $delta;

            if ($runTotal < $minDiproyeksikan) {
                $minDiproyeksikan = $runTotal;
            }

            if ($sp->post_date <= $now) {
                $saatIni += $delta;
            }
            $masaDepan += $delta;

            if (in_array($sp->reconcile_state, ['c', 'y'])) {
                $klir += $delta;
            }
            if ($sp->reconcile_state === 'y') {
                $rekonsiliasi += $delta;
            }
        }
        if ($minDiproyeksikan === PHP_FLOAT_MAX) {
            $minDiproyeksikan = 0.0;
        }

        $accountFullPath = $resolvePath($account);

        // Filter valid counterpart accounts for the quick entry dropdown
        $transferAccounts = $allAccounts->filter(function($a) use ($account) {
            return $a->id !== $account->id && $a->account_type !== 'ROOT' && !$a->placeholder;
        })->map(function($a) use (&$resolvePath) {
            $a->resolved_path = $resolvePath($a);
            return $a;
        })->sortBy('resolved_path');

        return view('gnucash.accounts.ledger', [
            'title' => $title,
            'account' => $account,
            'accountFullPath' => $accountFullPath,
            'splits' => $splits,
            'rows' => $rows,
            'openingBalance' => $openingBalance,
            'pageTotalTambah' => $pageTotalTambah,
            'pageTotalKurang' => $pageTotalKurang,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'search' => $search,
            'isCreditNormal' => $isCreditNormal,
            'saatIni' => $saatIni,
            'masaDepan' => $masaDepan,
            'klir' => $klir,
            'rekonsiliasi' => $rekonsiliasi,
            'minDiproyeksikan' => $minDiproyeksikan,
            'allAccounts' => $allAccounts,
            'transferAccounts' => $transferAccounts,
        ]);
    }

    /**
     * Store a quick 2-split transaction directly from the account ledger register.
     */
    public function quickTransaction(Request $request, $id)
    {
        $account = GnucashAccount::findOrFail($id);

        $request->validate([
            'post_date' => 'required|date',
            'description' => 'required|string|max:500',
            'transfer_account_id' => 'required|exists:gnucash_accounts,id|different:id',
            'num' => 'nullable|string|max:100',
            'tambah' => 'nullable|numeric|min:0',
            'kurang' => 'nullable|numeric|min:0',
        ], [
            'transfer_account_id.required' => 'Akun transfer (lawan) wajib dipilih.',
            'transfer_account_id.different' => 'Akun transfer tidak boleh sama dengan akun saat ini.',
            'description.required' => 'Keterangan transaksi wajib diisi.',
            'post_date.required' => 'Tanggal transaksi wajib diisi.',
        ]);

        $tambah = (float) ($request->tambah ?? 0);
        $kurang = (float) ($request->kurang ?? 0);

        if ($tambah <= 0 && $kurang <= 0) {
            return back()->withInput()->with('err', 'Harap isi salah satu nominal: Tambah atau Kurang.');
        }

        if ($tambah > 0 && $kurang > 0) {
            return back()->withInput()->with('err', 'Hanya boleh mengisi salah satu: Tambah atau Kurang, tidak keduanya.');
        }

        $isCreditNormal = in_array(strtoupper($account->account_type), ['LIABILITY', 'EQUITY', 'INCOME', 'CREDIT', 'PAYABLE']);

        // Calculate split value for current account
        // If normal DEBIT: Tambah => +val (debit), Kurang => -val (credit)
        // If normal CREDIT: Tambah => -val (credit), Kurang => +val (debit)
        if ($isCreditNormal) {
            $currentSplitValue = $tambah > 0 ? -$tambah : $kurang;
        } else {
            $currentSplitValue = $tambah > 0 ? $tambah : -$kurang;
        }

        // Transfer split value must be exact opposite so sum == 0 (Strict Double-Entry)
        $transferSplitValue = -$currentSplitValue;

        $now = now();
        $txId = str_replace('-', '', (string) Str::uuid());

        DB::transaction(function () use ($request, $account, $txId, $currentSplitValue, $transferSplitValue, $now) {
            GnucashTransaction::create([
                'id' => $txId,
                'currency' => 'IDR',
                'num' => $request->num ?: null,
                'post_date' => $request->post_date,
                'enter_date' => $now,
                'description' => $request->description,
                'notes' => null,
            ]);

            // Split 1: Current Account
            GnucashSplit::create([
                'id' => str_replace('-', '', (string) Str::uuid()),
                'transaction_id' => $txId,
                'account_id' => $account->id,
                'memo' => null,
                'action' => null,
                'reconcile_state' => 'n',
                'value' => $currentSplitValue,
                'quantity' => $currentSplitValue,
            ]);

            // Split 2: Transfer Counterpart Account
            GnucashSplit::create([
                'id' => str_replace('-', '', (string) Str::uuid()),
                'transaction_id' => $txId,
                'account_id' => $request->transfer_account_id,
                'memo' => null,
                'action' => null,
                'reconcile_state' => 'n',
                'value' => $transferSplitValue,
                'quantity' => $transferSplitValue,
            ]);
        });

        return redirect()->route('dashboard.gnucash.accounts.ledger', $account->id)
            ->with('success', "Transaksi '{$request->description}' berhasil dicatat.");
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit($id)
    {
        $account = GnucashAccount::findOrFail($id);

        $allAccounts = GnucashAccount::all()->keyBy('id');
        $resolvePath = function ($acc) use (&$resolvePath, &$allAccounts) {
            if (!$acc) return '';
            $parts = [$acc->name];
            $curr = $acc;
            while ($curr && $curr->parent_id && isset($allAccounts[$curr->parent_id])) {
                $curr = $allAccounts[$curr->parent_id];
                if ($curr->account_type === 'ROOT') break;
                $parts[] = $curr->name;
            }
            return implode(' : ', array_reverse($parts));
        };

        $parentOptions = GnucashAccount::where('account_type', '!=', 'ROOT')
            ->where('id', '!=', $id)
            ->get()
            ->map(function ($acc) use ($resolvePath) {
                return [
                    'id' => $acc->id,
                    'name' => $acc->name,
                    'code' => $acc->code,
                    'account_type' => strtoupper($acc->account_type),
                    'full_path' => $resolvePath($acc),
                ];
            })
            ->sortBy('full_path')
            ->values()
            ->toArray();

        $types = ['ASSET', 'BANK', 'CASH', 'LIABILITY', 'EQUITY', 'INCOME', 'EXPENSE', 'CREDIT'];

        return view('gnucash.accounts.form', [
            'action' => 'Edit Akun Rekening',
            'title' => 'Form Edit Akun Rekening',
            'account' => $account,
            'parents' => $parentOptions,
            'parentOptions' => $parentOptions,
            'types' => $types,
            'selectedParentId' => $account->parent_id,
        ]);
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, $id)
    {
        $account = GnucashAccount::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'account_type' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:gnucash_accounts,id|different:id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'placeholder' => 'nullable|boolean',
        ]);

        $account->update([
            'name' => $request->name,
            'account_type' => strtoupper($request->account_type),
            'parent_id' => $request->parent_id ?: null,
            'code' => $request->code ?: null,
            'description' => $request->description ?: null,
            'placeholder' => $request->boolean('placeholder'),
        ]);

        return redirect()->route('dashboard.gnucash.accounts.index')
            ->with('success', "Akun '{$account->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy($id)
    {
        $account = GnucashAccount::findOrFail($id);

        if ($account->children()->count() > 0) {
            return redirect()->back()
                ->with('error', "Akun '{$account->name}' tidak dapat dihapus karena memiliki sub-akun.");
        }

        if ($account->splits()->count() > 0) {
            return redirect()->back()
                ->with('error', "Akun '{$account->name}' tidak dapat dihapus karena sudah memiliki transaksi terkait.");
        }

        $account->delete();

        return redirect()->route('dashboard.gnucash.accounts.index')
            ->with('success', "Akun '{$account->name}' berhasil dihapus.");
    }
}
