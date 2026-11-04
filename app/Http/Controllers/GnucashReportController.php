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
     * Display the Account Transaction Report (Laporan Transaksi Berdasarkan Akun).
     */
    public function index(Request $request)
    {
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
}
