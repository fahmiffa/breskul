<?php

namespace App\Http\Controllers;

use App\Models\GnucashAccount;
use App\Models\GnucashSplit;
use App\Models\GnucashTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GnucashReportController extends Controller
{
    /**
     * Account types list with user-friendly Indonesian labels.
     */
    protected array $accountTypes = [
        'ASSET'     => 'Aset / Aktiva (ASSET)',
        'BANK'      => 'Kas di Bank (BANK)',
        'CASH'      => 'Kas Tunai (CASH)',
        'LIABILITY' => 'Kewajiban / Hutang (LIABILITY)',
        'EQUITY'    => 'Ekuitas / Modal (EQUITY)',
        'INCOME'    => 'Pendapatan (INCOME)',
        'EXPENSE'   => 'Beban / Pengeluaran (EXPENSE)',
        'CREDIT'    => 'Kredit / Kartu Kredit (CREDIT)',
    ];

    /**
     * Display the Account Transaction Report (Laporan Transaksi Berdasarkan Akun) or Cash Flow (Laporan Arus Kas).
     */
    public function index(Request $request)
    {
        $reportType = $request->get('report_type', 'transactions');
        if (!in_array($reportType, ['transactions', 'cash_flow'])) {
            $reportType = 'transactions';
        }

        if ($reportType === 'cash_flow') {
            return $this->renderCashFlowReport($request);
        }

        $title = "Laporan Transaksi Berdasarkan Akun";

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $accountType = strtoupper(trim((string)$request->get('account_type', '')));
        $selectedAccountId = $request->get('account_id');
        $search = trim((string)$request->get('search', ''));
        $viewMode = $request->get('view', 'grouped'); // 'grouped' or 'summary'
        $perPage = max(5, min(100, (int)$request->get('per_page', 10)));

        // Preload all accounts for hierarchy and dropdown
        $allAccounts = GnucashAccount::where('account_type', '!=', 'ROOT')->get()->keyBy('id');
        $accountOptions = $this->getAccountOptions($allAccounts);

        // Pre-build path resolver
        $resolvePath = function ($acc) use (&$resolvePath, &$allAccounts) {
            if (!$acc) return '-';
            $parts = [$acc->name];
            $curr = $acc;
            while ($curr && $curr->parent_id && isset($allAccounts[$curr->parent_id])) {
                $curr = $allAccounts[$curr->parent_id];
                if ($curr->account_type === 'ROOT') break;
                $parts[] = $curr->name;
            }
            return implode(':', array_reverse($parts));
        };

        // Check if single account mode
        $isSingleAccount = !empty($selectedAccountId) && isset($allAccounts[$selectedAccountId]);

        if ($isSingleAccount) {
            return $this->renderSingleAccountReport(
                $allAccounts[$selectedAccountId],
                $allAccounts,
                $accountOptions,
                $resolvePath,
                $fromDate,
                $toDate,
                $accountType,
                $search,
                $request
            );
        }

        return $this->renderMultiAccountReport(
            $allAccounts,
            $accountOptions,
            $resolvePath,
            $fromDate,
            $toDate,
            $accountType,
            $search,
            $viewMode,
            $perPage,
            $request
        );
    }

    /**
     * Render report for a single selected account with full transaction details.
     */
    protected function renderSingleAccountReport(
        GnucashAccount $account,
        $allAccounts,
        $accountOptions,
        $resolvePath,
        $fromDate,
        $toDate,
        $accountType,
        $search,
        Request $request
    ) {
        $title = "Laporan Transaksi: {$account->name}";
        $isCreditNormal = in_array(strtoupper($account->account_type), ['LIABILITY', 'EQUITY', 'INCOME', 'CREDIT', 'PAYABLE']);

        // 1. Calculate opening balance before $fromDate
        $openingNet = 0.0;
        if ($fromDate) {
            $openingNet = (float) DB::table('gnucash_splits as s')
                ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
                ->where('s.account_id', $account->id)
                ->where('t.post_date', '<', $fromDate . ' 00:00:00')
                ->sum('s.value');
        }
        $openingBalance = $isCreditNormal ? -$openingNet : $openingNet;

        // 2. Query splits for period
        $splitsQuery = GnucashSplit::with('transaction')
            ->join('gnucash_transactions as t', 'gnucash_splits.transaction_id', '=', 't.id')
            ->where('gnucash_splits.account_id', $account->id)
            ->select('gnucash_splits.*');

        if ($fromDate) {
            $splitsQuery->where('t.post_date', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $splitsQuery->where('t.post_date', '<=', $toDate . ' 23:59:59');
        }
        if ($search) {
            $splitsQuery->where(function ($q) use ($search) {
                $q->where('t.description', 'like', "%{$search}%")
                  ->orWhere('t.num', 'like', "%{$search}%")
                  ->orWhere('gnucash_splits.memo', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->get('per_page', 50);
        $splits = $splitsQuery->orderBy('t.post_date', 'asc')
            ->orderBy('gnucash_splits.id', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // 3. Offset running balance if page > 1
        $page = (int) $request->get('page', 1);
        $runningBalance = $openingBalance;
        if ($page > 1) {
            $offsetCount = ($page - 1) * $perPage;
            $prevSplitsSum = (float) DB::table('gnucash_splits as s')
                ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
                ->where('s.account_id', $account->id)
                ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
                ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
                ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('t.description', 'like', "%{$search}%")
                    ->orWhere('t.num', 'like', "%{$search}%")
                    ->orWhere('s.memo', 'like', "%{$search}%")))
                ->orderBy('t.post_date', 'asc')
                ->orderBy('s.id', 'asc')
                ->limit($offsetCount)
                ->sum('s.value');

            $runningBalance += ($isCreditNormal ? -$prevSplitsSum : $prevSplitsSum);
        }

        // 4. Preload other splits for transaction counterpart
        $txIds = $splits->pluck('transaction_id')->unique();
        $otherSplitsGrouped = GnucashSplit::whereIn('transaction_id', $txIds)
            ->get()
            ->groupBy('transaction_id');

        $rows = [];
        $pageDebit = 0.0;
        $pageCredit = 0.0;

        foreach ($splits as $split) {
            $val = (float) $split->value;
            $transfer = $this->resolveTransfer($split, $otherSplitsGrouped, $allAccounts);

            if ($isCreditNormal) {
                $tambah = $val < 0 ? abs($val) : 0.0;
                $kurang = $val > 0 ? $val : 0.0;
            } else {
                $tambah = $val > 0 ? $val : 0.0;
                $kurang = $val < 0 ? abs($val) : 0.0;
            }

            $runningBalance += ($tambah - $kurang);
            $pageDebit += $tambah;
            $pageCredit += $kurang;

            $rows[] = [
                'split' => $split,
                'tx' => $split->transaction,
                'transfer' => $transfer,
                'debit' => $tambah,
                'credit' => $kurang,
                'balance' => $runningBalance,
            ];
        }

        // Global period totals for this account
        $totalStats = DB::table('gnucash_splits as s')
            ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
            ->where('s.account_id', $account->id)
            ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
            ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
            ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('t.description', 'like', "%{$search}%")
                ->orWhere('t.num', 'like', "%{$search}%")
                ->orWhere('s.memo', 'like', "%{$search}%")))
            ->select(
                DB::raw('COUNT(s.id) as tx_count'),
                DB::raw('SUM(CASE WHEN s.value > 0 THEN s.value ELSE 0 END) as sum_pos'),
                DB::raw('SUM(CASE WHEN s.value < 0 THEN ABS(s.value) ELSE 0 END) as sum_neg'),
                DB::raw('SUM(s.value) as net_val')
            )
            ->first();

        $totalTxCount = $totalStats ? (int) $totalStats->tx_count : 0;
        $totalDebit = $isCreditNormal ? (float) ($totalStats->sum_neg ?? 0) : (float) ($totalStats->sum_pos ?? 0);
        $totalCredit = $isCreditNormal ? (float) ($totalStats->sum_pos ?? 0) : (float) ($totalStats->sum_neg ?? 0);
        $closingBalance = $openingBalance + ($totalDebit - $totalCredit);

        return view('gnucash.reports.index', [
            'reportType' => 'transactions',
            'title' => $title,
            'isSingleAccount' => true,
            'singleAccount' => $account,
            'isCreditNormal' => $isCreditNormal,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'totalTxCount' => $totalTxCount,
            'splits' => $splits,
            'rows' => $rows,
            'pageDebit' => $pageDebit,
            'pageCredit' => $pageCredit,
            'accountTypes' => $this->accountTypes,
            'accountOptions' => $accountOptions,
            'accountOptionsJson' => $this->getAccountOptionsJson($accountOptions),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'selectedAccountType' => $accountType,
            'selectedAccountId' => $account->id,
            'search' => $search,
            'viewMode' => 'single',
        ]);
    }

    /**
     * Render multi-account grouped & summary report.
     */
    protected function renderMultiAccountReport(
        $allAccounts,
        $accountOptions,
        $resolvePath,
        $fromDate,
        $toDate,
        $accountType,
        $search,
        $viewMode,
        $perPage,
        Request $request
    ) {
        $title = "Laporan Transaksi Berdasarkan Akun";

        // 1. Filter accounts based on account_type
        $matchingAccounts = $allAccounts->filter(function ($acc) use ($accountType) {
            if ($accountType !== '' && strtoupper($acc->account_type) !== $accountType) {
                return false;
            }
            return true;
        });

        $matchingAccountIds = $matchingAccounts->pluck('id')->toArray();

        // 2. Query period activity per account
        $activityQuery = DB::table('gnucash_splits as s')
            ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
            ->whereIn('s.account_id', $matchingAccountIds);

        if ($fromDate) {
            $activityQuery->where('t.post_date', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $activityQuery->where('t.post_date', '<=', $toDate . ' 23:59:59');
        }
        if ($search) {
            $activityQuery->where(function ($q) use ($search) {
                $q->where('t.description', 'like', "%{$search}%")
                  ->orWhere('t.num', 'like', "%{$search}%")
                  ->orWhere('s.memo', 'like', "%{$search}%");
            });
        }

        $activities = $activityQuery->select(
            's.account_id',
            DB::raw('COUNT(s.id) as tx_count'),
            DB::raw('SUM(CASE WHEN s.value > 0 THEN s.value ELSE 0 END) as sum_pos'),
            DB::raw('SUM(CASE WHEN s.value < 0 THEN ABS(s.value) ELSE 0 END) as sum_neg'),
            DB::raw('SUM(s.value) as net_value')
        )->groupBy('s.account_id')->get()->keyBy('account_id');

        // 3. Query opening balances prior to $fromDate
        $openings = collect();
        if ($fromDate) {
            $openings = DB::table('gnucash_splits as s')
                ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
                ->whereIn('s.account_id', $matchingAccountIds)
                ->where('t.post_date', '<', $fromDate . ' 00:00:00')
                ->select('s.account_id', DB::raw('SUM(s.value) as opening_net'))
                ->groupBy('s.account_id')
                ->pluck('opening_net', 'account_id');
        }

        // 4. Build consolidated account summaries
        $accountsSummary = [];
        $totalAllAccounts = 0;
        $totalAllTx = 0;
        $grandTotalDebit = 0.0;
        $grandTotalCredit = 0.0;
        $grandOpening = 0.0;
        $grandClosing = 0.0;

        foreach ($matchingAccounts as $acc) {
            $isCredit = in_array(strtoupper($acc->account_type), ['LIABILITY', 'EQUITY', 'INCOME', 'CREDIT', 'PAYABLE']);
            $rawOpen = (float) ($openings[$acc->id] ?? 0.0);
            $act = $activities[$acc->id] ?? null;

            $txCount = $act ? (int) $act->tx_count : 0;
            $debit = $act ? ($isCredit ? (float) $act->sum_neg : (float) $act->sum_pos) : 0.0;
            $credit = $act ? ($isCredit ? (float) $act->sum_pos : (float) $act->sum_neg) : 0.0;
            $openingBalance = $isCredit ? -$rawOpen : $rawOpen;
            $closingBalance = $openingBalance + ($debit - $credit);

            // Skip accounts with no activity and zero opening balance
            if ($txCount === 0 && abs($openingBalance) < 0.001) {
                continue;
            }

            $totalAllAccounts++;
            $totalAllTx += $txCount;
            $grandTotalDebit += $debit;
            $grandTotalCredit += $credit;
            $grandOpening += $openingBalance;
            $grandClosing += $closingBalance;

            $accountsSummary[] = [
                'account' => $acc,
                'isCredit' => $isCredit,
                'openingBalance' => $openingBalance,
                'totalDebit' => $debit,
                'totalCredit' => $credit,
                'closingBalance' => $closingBalance,
                'txCount' => $txCount,
            ];
        }

        // Sort by account code then name
        usort($accountsSummary, function ($a, $b) {
            $codeA = $a['account']->code ?: '999999';
            $codeB = $b['account']->code ?: '999999';
            if ($codeA !== $codeB) return strnatcasecmp($codeA, $codeB);
            return strnatcasecmp($a['account']->name, $b['account']->name);
        });

        // 5. Paginate accounts for the grouped view
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $accountCollection = collect($accountsSummary);
        $currentPageAccounts = $accountCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedAccounts = new LengthAwarePaginator(
            $currentPageAccounts,
            $accountCollection->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // 6. For the current page accounts, load transactions and running balances
        $groupedDetails = [];
        $pageAccountIds = $currentPageAccounts->pluck('account.id')->toArray();

        if (count($pageAccountIds) > 0) {
            $pageSplits = GnucashSplit::with('transaction')
                ->join('gnucash_transactions as t', 'gnucash_splits.transaction_id', '=', 't.id')
                ->whereIn('gnucash_splits.account_id', $pageAccountIds)
                ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
                ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
                ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('t.description', 'like', "%{$search}%")
                    ->orWhere('t.num', 'like', "%{$search}%")
                    ->orWhere('gnucash_splits.memo', 'like', "%{$search}%")))
                ->orderBy('t.post_date', 'asc')
                ->orderBy('gnucash_splits.id', 'asc')
                ->select('gnucash_splits.*')
                ->get()
                ->groupBy('account_id');

            // Counterparts
            $txIds = $pageSplits->flatten()->pluck('transaction_id')->unique();
            $otherSplitsGrouped = GnucashSplit::whereIn('transaction_id', $txIds)
                ->get()
                ->groupBy('transaction_id');

            foreach ($currentPageAccounts as $accItem) {
                $acc = $accItem['account'];
                $isCredit = $accItem['isCredit'];
                $splitsForAcc = $pageSplits[$acc->id] ?? collect();
                $runningBalance = $accItem['openingBalance'];

                $rowList = [];
                $limitPreview = 50;
                $hasMore = $splitsForAcc->count() > $limitPreview;
                $displaySplits = $splitsForAcc->take($limitPreview);

                foreach ($splitsForAcc as $split) {
                    $val = (float) $split->value;

                    if ($isCredit) {
                        $tambah = $val < 0 ? abs($val) : 0.0;
                        $kurang = $val > 0 ? $val : 0.0;
                    } else {
                        $tambah = $val > 0 ? $val : 0.0;
                        $kurang = $val < 0 ? abs($val) : 0.0;
                    }

                    $runningBalance += ($tambah - $kurang);

                    if (count($rowList) < $limitPreview) {
                        $transfer = $this->resolveTransfer($split, $otherSplitsGrouped, $allAccounts);
                        $rowList[] = [
                            'split' => $split,
                            'tx' => $split->transaction,
                            'transfer' => $transfer,
                            'debit' => $tambah,
                            'credit' => $kurang,
                            'balance' => $runningBalance,
                        ];
                    }
                }

                $groupedDetails[$acc->id] = [
                    'account' => $acc,
                    'isCredit' => $isCredit,
                    'openingBalance' => $accItem['openingBalance'],
                    'closingBalance' => $accItem['closingBalance'],
                    'totalDebit' => $accItem['totalDebit'],
                    'totalCredit' => $accItem['totalCredit'],
                    'txCount' => $accItem['txCount'],
                    'hasMore' => $hasMore,
                    'totalSplitsCount' => $splitsForAcc->count(),
                    'rows' => $rowList,
                ];
            }
        }

        return view('gnucash.reports.index', [
            'reportType' => 'transactions',
            'title' => $title,
            'isSingleAccount' => false,
            'accountsSummary' => $accountsSummary,
            'paginatedAccounts' => $paginatedAccounts,
            'groupedDetails' => $groupedDetails,
            'totalAllAccounts' => $totalAllAccounts,
            'totalAllTx' => $totalAllTx,
            'grandTotalDebit' => $grandTotalDebit,
            'grandTotalCredit' => $grandTotalCredit,
            'grandOpening' => $grandOpening,
            'grandClosing' => $grandClosing,
            'accountTypes' => $this->accountTypes,
            'accountOptions' => $accountOptions,
            'accountOptionsJson' => $this->getAccountOptionsJson($accountOptions),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'selectedAccountType' => $accountType,
            'selectedAccountId' => '',
            'search' => $search,
            'viewMode' => $viewMode,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Export the filtered transaction report to an Excel spreadsheet.
     */
    public function export(Request $request): StreamedResponse
    {
        $reportType = $request->get('report_type', 'transactions');
        if ($reportType === 'cash_flow') {
            return $this->exportCashFlow($request);
        }

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $accountType = strtoupper(trim((string)$request->get('account_type', '')));
        $selectedAccountId = $request->get('account_id');
        $search = trim((string)$request->get('search', ''));

        $allAccounts = GnucashAccount::where('account_type', '!=', 'ROOT')->get()->keyBy('id');

        $resolvePath = function ($acc) use (&$resolvePath, &$allAccounts) {
            if (!$acc) return '-';
            $parts = [$acc->name];
            $curr = $acc;
            while ($curr && $curr->parent_id && isset($allAccounts[$curr->parent_id])) {
                $curr = $allAccounts[$curr->parent_id];
                if ($curr->account_type === 'ROOT') break;
                $parts[] = $curr->name;
            }
            return implode(':', array_reverse($parts));
        };

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Breskul Accounting')
            ->setTitle('Laporan Transaksi Akun GnuCash');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // Title Block
        $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI BERDASARKAN AKUN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF166534'));

        $periodText = ($fromDate ? $fromDate : 'Awal') . ' s/d ' . ($toDate ? $toDate : 'Sekarang');
        $typeText = $accountType ? ($this->accountTypes[$accountType] ?? $accountType) : 'Semua Kategori';

        $sheet->setCellValue('A2', "Periode: {$periodText} | Kategori: {$typeText} | Tanggal Cetak: " . date('d/m/Y H:i'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF4B5563'));

        $rowIdx = 4;

        // Determine target accounts
        if (!empty($selectedAccountId) && isset($allAccounts[$selectedAccountId])) {
            $targetAccounts = collect([$allAccounts[$selectedAccountId]]);
        } else {
            $targetAccounts = $allAccounts->filter(function ($a) use ($accountType) {
                return $accountType === '' || strtoupper($a->account_type) === $accountType;
            })->sortBy(fn($a) => ($a->code ?: '999999') . '_' . $a->name);
        }

        $targetIds = $targetAccounts->pluck('id')->toArray();

        // Preload openings
        $openings = collect();
        if ($fromDate) {
            $openings = DB::table('gnucash_splits as s')
                ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
                ->whereIn('s.account_id', $targetIds)
                ->where('t.post_date', '<', $fromDate . ' 00:00:00')
                ->select('s.account_id', DB::raw('SUM(s.value) as val'))
                ->groupBy('s.account_id')
                ->pluck('val', 'account_id');
        }

        // Preload all splits in period
        $allSplits = GnucashSplit::with('transaction')
            ->join('gnucash_transactions as t', 'gnucash_splits.transaction_id', '=', 't.id')
            ->whereIn('gnucash_splits.account_id', $targetIds)
            ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
            ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
            ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('t.description', 'like', "%{$search}%")
                ->orWhere('t.num', 'like', "%{$search}%")
                ->orWhere('gnucash_splits.memo', 'like', "%{$search}%")))
            ->orderBy('t.post_date', 'asc')
            ->orderBy('gnucash_splits.id', 'asc')
            ->select('gnucash_splits.*')
            ->get()
            ->groupBy('account_id');

        $txIds = $allSplits->flatten()->pluck('transaction_id')->unique();
        $otherSplitsGrouped = GnucashSplit::whereIn('transaction_id', $txIds)
            ->get()
            ->groupBy('transaction_id');

        $accountsRendered = 0;

        foreach ($targetAccounts as $acc) {
            $isCredit = in_array(strtoupper($acc->account_type), ['LIABILITY', 'EQUITY', 'INCOME', 'CREDIT', 'PAYABLE']);
            $rawOpen = (float) ($openings[$acc->id] ?? 0.0);
            $openingBalance = $isCredit ? -$rawOpen : $rawOpen;

            $splitsForAcc = $allSplits[$acc->id] ?? collect();
            if ($splitsForAcc->count() === 0 && abs($openingBalance) < 0.001) {
                continue;
            }

            $accountsRendered++;

            // Account Header Row
            $sheet->setCellValue("A{$rowIdx}", "AKUN: " . ($acc->code ? "[$acc->code] " : "") . $acc->name . " ({$acc->account_type})");
            $sheet->mergeCells("A{$rowIdx}:E{$rowIdx}");
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0F2FE');

            $sheet->setCellValue("F{$rowIdx}", "Saldo Awal:");
            $sheet->setCellValue("H{$rowIdx}", $openingBalance);
            $sheet->getStyle("H{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00;0.00');
            $rowIdx++;

            // Table Header
            $headers = ['Tanggal', 'No. Ref', 'Keterangan Transaksi', 'Akun Lawan / Transfer', 'Memo', 'Debit / Tambah', 'Kredit / Kurang', 'Saldo Berjalan'];
            $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue("{$colLetters[$i]}{$rowIdx}", $h);
            }
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF166534');
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $rowIdx++;

            $runningBalance = $openingBalance;
            $accDebit = 0.0;
            $accCredit = 0.0;

            if ($splitsForAcc->count() === 0) {
                $sheet->setCellValue("A{$rowIdx}", "Tidak ada transaksi dalam periode ini.");
                $sheet->mergeCells("A{$rowIdx}:H{$rowIdx}");
                $sheet->getStyle("A{$rowIdx}")->getFont()->setItalic(true)->setSize(9);
                $rowIdx++;
            } else {
                foreach ($splitsForAcc as $split) {
                    $tx = $split->transaction;
                    $val = (float) $split->value;
                    $transfer = $this->resolveTransfer($split, $otherSplitsGrouped, $allAccounts);

                    if ($isCredit) {
                        $tambah = $val < 0 ? abs($val) : 0.0;
                        $kurang = $val > 0 ? $val : 0.0;
                    } else {
                        $tambah = $val > 0 ? $val : 0.0;
                        $kurang = $val < 0 ? abs($val) : 0.0;
                    }

                    $runningBalance += ($tambah - $kurang);
                    $accDebit += $tambah;
                    $accCredit += $kurang;

                    $postDateStr = $tx && $tx->post_date ? Carbon::parse($tx->post_date)->format('d/m/Y') : '-';

                    $sheet->setCellValue("A{$rowIdx}", $postDateStr);
                    $sheet->setCellValue("B{$rowIdx}", $tx ? $tx->num : '');
                    $sheet->setCellValue("C{$rowIdx}", $tx ? $tx->description : '');
                    $sheet->setCellValue("D{$rowIdx}", $transfer);
                    $sheet->setCellValue("E{$rowIdx}", $split->memo ?? '');
                    $sheet->setCellValue("F{$rowIdx}", $tambah > 0 ? $tambah : 0);
                    $sheet->setCellValue("G{$rowIdx}", $kurang > 0 ? $kurang : 0);
                    $sheet->setCellValue("H{$rowIdx}", $runningBalance);

                    $sheet->getStyle("F{$rowIdx}:H{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00;"-"');
                    $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getFont()->setSize(9);

                    $rowIdx++;
                }
            }

            // Subtotal Row for this account
            $sheet->setCellValue("A{$rowIdx}", "Subtotal Akun: {$acc->name}");
            $sheet->mergeCells("A{$rowIdx}:E{$rowIdx}");
            $sheet->setCellValue("F{$rowIdx}", $accDebit);
            $sheet->setCellValue("G{$rowIdx}", $accCredit);
            $sheet->setCellValue("H{$rowIdx}", $runningBalance);

            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6');
            $sheet->getStyle("F{$rowIdx}:H{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00;0.00');

            // Bottom border
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

            $rowIdx += 2; // Spacing between accounts
        }

        if ($accountsRendered === 0) {
            $sheet->setCellValue("A{$rowIdx}", "Tidak ditemukan transaksi atau saldo akun yang sesuai dengan filter yang dipilih.");
            $sheet->mergeCells("A{$rowIdx}:H{$rowIdx}");
            $sheet->getStyle("A{$rowIdx}")->getFont()->setItalic(true);
        }

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Transaksi_Gnucash_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Resolve hierarchical full path options for dropdown.
     */
    protected function getAccountOptions($allAccounts)
    {
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

        return $allAccounts->map(function ($a) use (&$resolvePath) {
            $a->resolved_path = $resolvePath($a);
            return $a;
        })->sortBy('resolved_path')->values();
    }

    /**
     * Get JSON representation of account options for searchable dropdown.
     */
    protected function getAccountOptionsJson($accountOptions): string
    {
        $data = $accountOptions->map(function ($a) {
            return [
                'id'            => (string) $a->id,
                'name'          => (string) $a->name,
                'code'          => (string) ($a->code ?? ''),
                'account_type'  => strtoupper((string) ($a->account_type ?? '')),
                'resolved_path' => (string) ($a->resolved_path ?? $a->name),
            ];
        })->values()->all();

        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Resolve counterpart transfer text for a split.
     */
    protected function resolveTransfer($split, $otherSplitsGrouped, $allAccounts): string
    {
        $others = ($otherSplitsGrouped[$split->transaction_id] ?? collect())->where('id', '!=', $split->id);

        if ($others->count() === 1) {
            $other = $others->first();
            $acc = $allAccounts[$other->account_id] ?? null;
            if (!$acc) return '-';
            $parts = [$acc->name];
            $curr = $acc;
            while ($curr && $curr->parent_id && isset($allAccounts[$curr->parent_id])) {
                $curr = $allAccounts[$curr->parent_id];
                if ($curr->account_type === 'ROOT') break;
                $parts[] = $curr->name;
            }
            return implode(':', array_reverse($parts));
        } elseif ($others->count() > 1) {
            return '-- Transaksi Split --';
        }

        return '--';
    }

    /**
     * Render the Cash Flow Report (Laporan Arus Kas).
     */
    protected function renderCashFlowReport(Request $request)
    {
        $title = "Laporan Arus Kas (Cash Flow)";

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $selectedAccountId = $request->get('account_id');
        $search = trim((string)$request->get('search', ''));
        $viewMode = $request->get('view', 'summary'); // 'summary' or 'detail'
        $perPage = max(10, min(100, (int)$request->get('per_page', 50)));

        // Preload all accounts
        $allAccounts = GnucashAccount::where('account_type', '!=', 'ROOT')->get()->keyBy('id');

        $resolvePath = function ($acc) use (&$resolvePath, &$allAccounts) {
            if (!$acc) return '-';
            $parts = [$acc->name];
            $curr = $acc;
            while ($curr && $curr->parent_id && isset($allAccounts[$curr->parent_id])) {
                $curr = $allAccounts[$curr->parent_id];
                if ($curr->account_type === 'ROOT') break;
                $parts[] = $curr->name;
            }
            return implode(' : ', array_reverse($parts));
        };

        // Filter Cash & Bank accounts only
        $cashAccounts = $allAccounts->filter(function ($a) {
            return in_array(strtoupper($a->account_type), ['CASH', 'BANK']);
        })->sortBy(fn($a) => ($a->code ?: '999999') . '_' . $a->name);

        $cashAccountOptions = $cashAccounts->map(function ($a) use (&$resolvePath) {
            $a->resolved_path = $resolvePath($a);
            return $a;
        })->values();

        $cashAccountOptionsJson = json_encode($cashAccountOptions->map(function ($a) {
            return [
                'id'            => (string) $a->id,
                'name'          => (string) $a->name,
                'code'          => (string) ($a->code ?? ''),
                'account_type'  => strtoupper((string) ($a->account_type ?? '')),
                'resolved_path' => (string) ($a->resolved_path ?? $a->name),
            ];
        })->values()->all(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        // Check if single cash account mode
        $isSingleAccount = !empty($selectedAccountId) && isset($cashAccounts[$selectedAccountId]);
        $activeAccountIds = $isSingleAccount ? [$selectedAccountId] : $cashAccounts->pluck('id')->toArray();
        $selectedAccount = $isSingleAccount ? $cashAccounts[$selectedAccountId] : null;

        // 1. Opening Balances
        $openingCashBalance = 0.0;
        $openingPerAccount = [];
        if ($fromDate) {
            $openingRows = DB::table('gnucash_splits as s')
                ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
                ->whereIn('s.account_id', $cashAccounts->pluck('id')->toArray())
                ->where('t.post_date', '<', $fromDate . ' 00:00:00')
                ->select('s.account_id', DB::raw('SUM(s.value) as total_val'))
                ->groupBy('s.account_id')
                ->get();

            foreach ($openingRows as $r) {
                $openingPerAccount[$r->account_id] = (float) $r->total_val;
            }

            if ($isSingleAccount) {
                $openingCashBalance = $openingPerAccount[$selectedAccountId] ?? 0.0;
            } else {
                $openingCashBalance = array_sum($openingPerAccount);
            }
        }

        // 2. Aggregate Cash Stats for period
        $statsQuery = DB::table('gnucash_splits as s')
            ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
            ->whereIn('s.account_id', $activeAccountIds);

        if ($fromDate) {
            $statsQuery->where('t.post_date', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $statsQuery->where('t.post_date', '<=', $toDate . ' 23:59:59');
        }
        if ($search) {
            $statsQuery->where(function ($q) use ($search) {
                $q->where('t.description', 'like', "%{$search}%")
                  ->orWhere('t.num', 'like', "%{$search}%")
                  ->orWhere('s.memo', 'like', "%{$search}%");
            });
        }

        $cashStats = (clone $statsQuery)->select(
            DB::raw('COUNT(s.id) as total_tx'),
            DB::raw('SUM(CASE WHEN s.value > 0 THEN s.value ELSE 0 END) as total_in'),
            DB::raw('SUM(CASE WHEN s.value < 0 THEN ABS(s.value) ELSE 0 END) as total_out')
        )->first();

        $totalInflow = (float) ($cashStats->total_in ?? 0.0);
        $totalOutflow = (float) ($cashStats->total_out ?? 0.0);
        $netCashFlow = $totalInflow - $totalOutflow;
        $closingCashBalance = $openingCashBalance + $netCashFlow;
        $totalCashTx = (int) ($cashStats->total_tx ?? 0);

        // 3. Per-account reconciliation table (Daftar Saldo Kas & Bank)
        $periodPerAccount = DB::table('gnucash_splits as s')
            ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
            ->whereIn('s.account_id', $cashAccounts->pluck('id')->toArray())
            ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
            ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
            ->select(
                's.account_id',
                DB::raw('COUNT(s.id) as tx_count'),
                DB::raw('SUM(CASE WHEN s.value > 0 THEN s.value ELSE 0 END) as sum_in'),
                DB::raw('SUM(CASE WHEN s.value < 0 THEN ABS(s.value) ELSE 0 END) as sum_out')
            )
            ->groupBy('s.account_id')
            ->get()
            ->keyBy('account_id');

        $accountsReconciliation = [];
        foreach ($cashAccounts as $acc) {
            $op = (float) ($openingPerAccount[$acc->id] ?? 0.0);
            $stat = $periodPerAccount->get($acc->id);
            $in = (float) ($stat->sum_in ?? 0.0);
            $out = (float) ($stat->sum_out ?? 0.0);
            $cl = $op + ($in - $out);
            $cnt = (int) ($stat->tx_count ?? 0);

            $accountsReconciliation[] = [
                'account' => $acc,
                'opening' => $op,
                'inflow'  => $in,
                'outflow' => $out,
                'net'     => $in - $out,
                'closing' => $cl,
                'tx_count'=> $cnt,
            ];
        }

        // 4. Pos Penerimaan & Pos Pengeluaran (Category Breakdown)
        $cashTxQuery = DB::table('gnucash_splits as s')
            ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
            ->whereIn('s.account_id', $activeAccountIds);

        if ($fromDate) $cashTxQuery->where('t.post_date', '>=', $fromDate . ' 00:00:00');
        if ($toDate) $cashTxQuery->where('t.post_date', '<=', $toDate . ' 23:59:59');
        if ($search) {
            $cashTxQuery->where(function ($q) use ($search) {
                $q->where('t.description', 'like', "%{$search}%")
                  ->orWhere('t.num', 'like', "%{$search}%")
                  ->orWhere('s.memo', 'like', "%{$search}%");
            });
        }

        $cashTxIds = $cashTxQuery->distinct()->pluck('s.transaction_id')->toArray();

        $inflowCategories = [];
        $outflowCategories = [];

        if (!empty($cashTxIds)) {
            $chunks = array_chunk($cashTxIds, 1000);
            foreach ($chunks as $chunk) {
                $allTxSplits = DB::table('gnucash_splits')
                    ->whereIn('transaction_id', $chunk)
                    ->get()
                    ->groupBy('transaction_id');

                foreach ($chunk as $txId) {
                    $splits = $allTxSplits->get($txId);
                    if (!$splits) continue;

                    $cSplits = $splits->filter(fn($s) => in_array($s->account_id, $activeAccountIds));
                    $otherSplits = $splits->filter(fn($s) => !in_array($s->account_id, $activeAccountIds));

                    $cashNet = $cSplits->sum('value');

                    if ($otherSplits->isEmpty()) {
                        // Transfer between cash accounts
                        if ($isSingleAccount) {
                            $otherCash = $splits->filter(fn($s) => $s->account_id != $selectedAccountId);
                            if ($otherCash->count() === 1) {
                                $otherAcc = $allAccounts->get($otherCash->first()->account_id);
                                $name = "Transfer: " . ($otherAcc ? $otherAcc->name : 'Kas/Bank Lain');
                                if ($cashNet > 0) {
                                    $inflowCategories[$name] = ($inflowCategories[$name] ?? ['name' => $name, 'amount' => 0, 'count' => 0, 'type' => 'TRANSFER']);
                                    $inflowCategories[$name]['amount'] += $cashNet;
                                    $inflowCategories[$name]['count']++;
                                } elseif ($cashNet < 0) {
                                    $outflowCategories[$name] = ($outflowCategories[$name] ?? ['name' => $name, 'amount' => 0, 'count' => 0, 'type' => 'TRANSFER']);
                                    $outflowCategories[$name]['amount'] += abs($cashNet);
                                    $outflowCategories[$name]['count']++;
                                }
                            }
                        }
                        continue;
                    }

                    if ($cashNet > 0) {
                        foreach ($otherSplits as $os) {
                            $amt = abs((float) $os->value);
                            if ($amt == 0) continue;
                            $acc = $allAccounts->get($os->account_id);
                            $accName = $acc ? $acc->name : 'Lain-lain';
                            $accType = $acc ? $acc->account_type : 'UNKNOWN';
                            $inflowCategories[$accName] = ($inflowCategories[$accName] ?? ['name' => $accName, 'amount' => 0, 'count' => 0, 'type' => $accType]);
                            $inflowCategories[$accName]['amount'] += $amt;
                            $inflowCategories[$accName]['count']++;
                        }
                    } elseif ($cashNet < 0) {
                        foreach ($otherSplits as $os) {
                            $amt = abs((float) $os->value);
                            if ($amt == 0) continue;
                            $acc = $allAccounts->get($os->account_id);
                            $accName = $acc ? $acc->name : 'Lain-lain';
                            $accType = $acc ? $acc->account_type : 'UNKNOWN';
                            $outflowCategories[$accName] = ($outflowCategories[$accName] ?? ['name' => $accName, 'amount' => 0, 'count' => 0, 'type' => $accType]);
                            $outflowCategories[$accName]['amount'] += $amt;
                            $outflowCategories[$accName]['count']++;
                        }
                    }
                }
            }
        }

        uasort($inflowCategories, fn($a, $b) => $b['amount'] <=> $a['amount']);
        uasort($outflowCategories, fn($a, $b) => $b['amount'] <=> $a['amount']);

        // 5. Detailed Cash Mutations (Paginated)
        $detailQuery = GnucashSplit::with('transaction')
            ->join('gnucash_transactions as t', 'gnucash_splits.transaction_id', '=', 't.id')
            ->whereIn('gnucash_splits.account_id', $activeAccountIds)
            ->select('gnucash_splits.*');

        if ($fromDate) $detailQuery->where('t.post_date', '>=', $fromDate . ' 00:00:00');
        if ($toDate) $detailQuery->where('t.post_date', '<=', $toDate . ' 23:59:59');
        if ($search) {
            $detailQuery->where(function ($q) use ($search) {
                $q->where('t.description', 'like', "%{$search}%")
                  ->orWhere('t.num', 'like', "%{$search}%")
                  ->orWhere('gnucash_splits.memo', 'like', "%{$search}%");
            });
        }

        $paginatedSplits = $detailQuery->orderBy('t.post_date', 'asc')
            ->orderBy('gnucash_splits.id', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $pageTxIds = collect($paginatedSplits->items())->pluck('transaction_id')->unique()->toArray();
        $otherSplitsForPage = collect();
        if (!empty($pageTxIds)) {
            $otherSplitsForPage = DB::table('gnucash_splits')
                ->whereIn('transaction_id', $pageTxIds)
                ->get()
                ->groupBy('transaction_id');
        }

        $priorNet = 0.0;
        if ($paginatedSplits->currentPage() > 1 && count($paginatedSplits->items()) > 0) {
            $firstItem = $paginatedSplits->items()[0];
            $priorNet = (float) DB::table('gnucash_splits as s')
                ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
                ->whereIn('s.account_id', $activeAccountIds)
                ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
                ->where(function($q) use ($firstItem) {
                    $q->where('t.post_date', '<', $firstItem->transaction->post_date)
                      ->orWhere(function($sub) use ($firstItem) {
                          $sub->where('t.post_date', '=', $firstItem->transaction->post_date)
                              ->where('s.id', '<', $firstItem->id);
                      });
                })
                ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('t.description', 'like', "%{$search}%")
                    ->orWhere('t.num', 'like', "%{$search}%")
                    ->orWhere('s.memo', 'like', "%{$search}%")))
                ->sum('s.value');
        }

        $runningBalance = $openingCashBalance + $priorNet;
        $detailRows = [];

        foreach ($paginatedSplits as $sp) {
            $val = (float) $sp->value;
            $inflow = $val > 0 ? $val : 0.0;
            $outflow = $val < 0 ? abs($val) : 0.0;
            $runningBalance += ($inflow - $outflow);

            $acc = $allAccounts->get($sp->account_id);
            $transfer = $this->resolveTransfer($sp, $otherSplitsForPage, $allAccounts);

            $detailRows[] = [
                'split' => $sp,
                'tx' => $sp->transaction,
                'account' => $acc,
                'transfer' => $transfer,
                'inflow' => $inflow,
                'outflow' => $outflow,
                'running_balance' => $runningBalance,
            ];
        }

        return view('gnucash.reports.index', [
            'reportType' => 'cash_flow',
            'title' => $title,
            'isSingleCashAccount' => $isSingleAccount,
            'selectedCashAccount' => $selectedAccount,
            'cashAccounts' => $cashAccounts,
            'cashAccountOptions' => $cashAccountOptions,
            'cashAccountOptionsJson' => $cashAccountOptionsJson,
            'accountOptions' => $cashAccountOptions,
            'accountOptionsJson' => $cashAccountOptionsJson,
            'selectedAccountType' => '',
            'accountTypes' => $this->accountTypes,
            'openingCashBalance' => $openingCashBalance,
            'closingCashBalance' => $closingCashBalance,
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
            'netCashFlow' => $netCashFlow,
            'totalCashTx' => $totalCashTx,
            'accountsReconciliation' => $accountsReconciliation,
            'inflowCategories' => $inflowCategories,
            'outflowCategories' => $outflowCategories,
            'paginatedSplits' => $paginatedSplits,
            'detailRows' => $detailRows,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'selectedAccountId' => $selectedAccountId ?? '',
            'search' => $search,
            'viewMode' => $viewMode,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Export Cash Flow Report to Excel.
     */
    protected function exportCashFlow(Request $request): StreamedResponse
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $selectedAccountId = $request->get('account_id');
        $search = trim((string)$request->get('search', ''));

        $allAccounts = GnucashAccount::where('account_type', '!=', 'ROOT')->get()->keyBy('id');
        $cashAccounts = $allAccounts->filter(fn($a) => in_array(strtoupper($a->account_type), ['CASH', 'BANK']))
            ->sortBy(fn($a) => ($a->code ?: '999999') . '_' . $a->name);

        $isSingleAccount = !empty($selectedAccountId) && isset($cashAccounts[$selectedAccountId]);
        $activeAccountIds = $isSingleAccount ? [$selectedAccountId] : $cashAccounts->pluck('id')->toArray();
        $selectedAccount = $isSingleAccount ? $cashAccounts[$selectedAccountId] : null;

        // Opening balance
        $openingCashBalance = 0.0;
        $openingPerAccount = [];
        if ($fromDate) {
            $openingRows = DB::table('gnucash_splits as s')
                ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
                ->whereIn('s.account_id', $cashAccounts->pluck('id')->toArray())
                ->where('t.post_date', '<', $fromDate . ' 00:00:00')
                ->select('s.account_id', DB::raw('SUM(s.value) as total_val'))
                ->groupBy('s.account_id')
                ->get();

            foreach ($openingRows as $r) {
                $openingPerAccount[$r->account_id] = (float) $r->total_val;
            }

            $openingCashBalance = $isSingleAccount ? ($openingPerAccount[$selectedAccountId] ?? 0.0) : array_sum($openingPerAccount);
        }

        // Totals
        $statsQuery = DB::table('gnucash_splits as s')
            ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
            ->whereIn('s.account_id', $activeAccountIds)
            ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
            ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
            ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('t.description', 'like', "%{$search}%")
                ->orWhere('t.num', 'like', "%{$search}%")
                ->orWhere('s.memo', 'like', "%{$search}%")));

        $cashStats = (clone $statsQuery)->select(
            DB::raw('COUNT(s.id) as total_tx'),
            DB::raw('SUM(CASE WHEN s.value > 0 THEN s.value ELSE 0 END) as total_in'),
            DB::raw('SUM(CASE WHEN s.value < 0 THEN ABS(s.value) ELSE 0 END) as total_out')
        )->first();

        $totalInflow = (float) ($cashStats->total_in ?? 0.0);
        $totalOutflow = (float) ($cashStats->total_out ?? 0.0);
        $netCashFlow = $totalInflow - $totalOutflow;
        $closingCashBalance = $openingCashBalance + $netCashFlow;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Breskul Accounting')->setTitle('Laporan Arus Kas (Cash Flow)');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Arus Kas');

        // Header Title
        $sheet->setCellValue('A1', 'LAPORAN ARUS KAS (CASH FLOW STATEMENT)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF166534'));

        $accountTitle = $isSingleAccount ? $selectedAccount->name : 'Semua Rekening Kas & Bank';
        $periodText = ($fromDate ?: 'Awal') . ' s/d ' . ($toDate ?: 'Sekarang');
        $sheet->setCellValue('A2', "Periode: {$periodText} | Rekening: {$accountTitle} | Dicetak: " . date('d/m/Y H:i'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF4B5563'));

        // Ringkasan Eksekutif Box
        $sheet->setCellValue('A4', 'RINGKASAN ARUS KAS');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);

        $summaryHeaders = ['Saldo Awal Kas', 'Total Penerimaan Kas', 'Total Pengeluaran Kas', 'Arus Kas Bersih (Net Flow)', 'Saldo Akhir Kas'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($summaryHeaders as $i => $h) {
            $sheet->setCellValue($cols[$i] . '5', $h);
            $sheet->getStyle($cols[$i] . '5')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
            $sheet->getStyle($cols[$i] . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15803D');
            $sheet->getStyle($cols[$i] . '5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('A6', $openingCashBalance);
        $sheet->setCellValue('B6', $totalInflow);
        $sheet->setCellValue('C6', $totalOutflow);
        $sheet->setCellValue('D6', $netCashFlow);
        $sheet->setCellValue('E6', $closingCashBalance);

        foreach ($cols as $c) {
            $sheet->getStyle($c . '6')->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($c . '6')->getFont()->setBold(true);
            $sheet->getStyle($c . '6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Section: Rekonsiliasi Rekening
        $rowIdx = 8;
        $sheet->setCellValue('A' . $rowIdx, 'POSISI SALDO TIAP REKENING KAS & BANK');
        $sheet->getStyle('A' . $rowIdx)->getFont()->setBold(true)->setSize(11);
        $rowIdx++;

        $reconHeaders = ['Nama Rekening / Akun', 'Tipe Akun', 'Saldo Awal', 'Kas Masuk', 'Kas Keluar', 'Arus Bersih', 'Saldo Akhir', 'Jumlah Tx'];
        $reconCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($reconHeaders as $i => $h) {
            $sheet->setCellValue($reconCols[$i] . $rowIdx, $h);
            $sheet->getStyle($reconCols[$i] . $rowIdx)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
            $sheet->getStyle($reconCols[$i] . $rowIdx)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF374151');
        }
        $rowIdx++;

        $periodPerAccount = DB::table('gnucash_splits as s')
            ->join('gnucash_transactions as t', 's.transaction_id', '=', 't.id')
            ->whereIn('s.account_id', $cashAccounts->pluck('id')->toArray())
            ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
            ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
            ->select('s.account_id', DB::raw('COUNT(s.id) as cnt'), DB::raw('SUM(CASE WHEN s.value > 0 THEN s.value ELSE 0 END) as in_val'), DB::raw('SUM(CASE WHEN s.value < 0 THEN ABS(s.value) ELSE 0 END) as out_val'))
            ->groupBy('s.account_id')
            ->get()->keyBy('account_id');

        foreach ($cashAccounts as $ca) {
            $op = (float) ($openingPerAccount[$ca->id] ?? 0.0);
            $stat = $periodPerAccount->get($ca->id);
            $in = (float) ($stat->in_val ?? 0.0);
            $out = (float) ($stat->out_val ?? 0.0);
            $cl = $op + ($in - $out);
            $cnt = (int) ($stat->cnt ?? 0);

            $sheet->setCellValue('A' . $rowIdx, ($ca->code ? "[{$ca->code}] " : "") . $ca->name);
            $sheet->setCellValue('B' . $rowIdx, $ca->account_type);
            $sheet->setCellValue('C' . $rowIdx, $op);
            $sheet->setCellValue('D' . $rowIdx, $in);
            $sheet->setCellValue('E' . $rowIdx, $out);
            $sheet->setCellValue('F' . $rowIdx, $in - $out);
            $sheet->setCellValue('G' . $rowIdx, $cl);
            $sheet->setCellValue('H' . $rowIdx, $cnt);

            $sheet->getStyle('C' . $rowIdx . ':G' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIdx++;
        }

        // Section: Rincian Mutasi Arus Kas
        $rowIdx += 2;
        $sheet->setCellValue('A' . $rowIdx, 'RINCIAN MUTASI ARUS KAS');
        $sheet->getStyle('A' . $rowIdx)->getFont()->setBold(true)->setSize(11);
        $rowIdx++;

        $mutHeaders = ['Tanggal', 'No. Ref', 'Rekening Kas/Bank', 'Lawan Akun / Kategori', 'Keterangan Transaksi', 'Memo', 'Kas Masuk (Inflow)', 'Kas Keluar (Outflow)'];
        foreach ($mutHeaders as $i => $h) {
            $sheet->setCellValue($reconCols[$i] . $rowIdx, $h);
            $sheet->getStyle($reconCols[$i] . $rowIdx)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
            $sheet->getStyle($reconCols[$i] . $rowIdx)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF047857');
        }
        $rowIdx++;

        $detailSplitsQuery = GnucashSplit::with('transaction')
            ->join('gnucash_transactions as t', 'gnucash_splits.transaction_id', '=', 't.id')
            ->whereIn('gnucash_splits.account_id', $activeAccountIds)
            ->when($fromDate, fn($q) => $q->where('t.post_date', '>=', $fromDate . ' 00:00:00'))
            ->when($toDate, fn($q) => $q->where('t.post_date', '<=', $toDate . ' 23:59:59'))
            ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('t.description', 'like', "%{$search}%")
                ->orWhere('t.num', 'like', "%{$search}%")
                ->orWhere('gnucash_splits.memo', 'like', "%{$search}%")))
            ->select('gnucash_splits.*')
            ->orderBy('t.post_date', 'asc')
            ->orderBy('gnucash_splits.id', 'asc');

        $detailSplitsQuery->chunk(500, function ($splitsChunk) use (&$rowIdx, $sheet, $allAccounts) {
            $txIds = $splitsChunk->pluck('transaction_id')->unique()->toArray();
            $otherSplitsGrouped = DB::table('gnucash_splits')->whereIn('transaction_id', $txIds)->get()->groupBy('transaction_id');

            foreach ($splitsChunk as $sp) {
                $val = (float) $sp->value;
                $inflow = $val > 0 ? $val : 0.0;
                $outflow = $val < 0 ? abs($val) : 0.0;
                $tx = $sp->transaction;
                $acc = $allAccounts->get($sp->account_id);
                $transfer = $this->resolveTransfer($sp, $otherSplitsGrouped, $allAccounts);

                $sheet->setCellValue('A' . $rowIdx, $tx->post_date ? \Carbon\Carbon::parse($tx->post_date)->format('d/m/Y') : '-');
                $sheet->setCellValue('B' . $rowIdx, $tx->num ?: '-');
                $sheet->setCellValue('C' . $rowIdx, $acc ? $acc->name : '-');
                $sheet->setCellValue('D' . $rowIdx, $transfer);
                $sheet->setCellValue('E' . $rowIdx, $tx->description ?: '-');
                $sheet->setCellValue('F' . $rowIdx, $sp->memo ?: '-');
                $sheet->setCellValue('G' . $rowIdx, $inflow);
                $sheet->setCellValue('H' . $rowIdx, $outflow);

                $sheet->getStyle('G' . $rowIdx . ':H' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');
                $rowIdx++;
            }
        });

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'laporan-arus-kas-' . date('Ymd-His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
