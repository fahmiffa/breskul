<?php

namespace App\Http\Controllers;

use App\Jobs\ImportGnucashJob;
use App\Models\GnucashAccount;
use App\Models\GnucashSplit;
use App\Models\GnucashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GnucashTransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $title = "Jurnal Transaksi Keuangan";

        $query = GnucashTransaction::with(['splits.account']);

        // Search description or num
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('num', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('splits', function ($sq) use ($search) {
                      $sq->where('memo', 'like', "%{$search}%");
                  });
            });
        }

        // Filter date range
        if ($request->filled('from_date')) {
            $query->where('post_date', '>=', $request->from_date . ' 00:00:00');
        }
        if ($request->filled('to_date')) {
            $query->where('post_date', '<=', $request->to_date . ' 23:59:59');
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $query->whereHas('splits', function ($q) use ($request) {
                $q->where('account_id', $request->account_id);
            });
        }

        $transactions = $query->orderBy('post_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        $accounts = GnucashAccount::where('account_type', '!=', 'ROOT')
            ->orderBy('name')
            ->get(['id', 'name', 'account_type', 'code']);

        return view('gnucash.transactions.index', [
            'title' => $title,
            'transactions' => $transactions,
            'accounts' => $accounts,
            'search' => $request->search ?? '',
            'fromDate' => $request->from_date ?? '',
            'toDate' => $request->to_date ?? '',
            'selectedAccountId' => $request->account_id ?? '',
        ]);
    }

    /**
     * Get non-placeholder accounts sorted by hierarchical path.
     */
    private function getAccountOptions()
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

        return $allAccounts->filter(function($a) {
            return $a->account_type !== 'ROOT' && !$a->placeholder;
        })->map(function($a) use (&$resolvePath) {
            $a->resolved_path = $resolvePath($a);
            return $a;
        })->sortBy('resolved_path')->values();
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request)
    {
        $accounts = $this->getAccountOptions();

        $selectedAccountId = $request->get('account_id', '');

        $splits = old('splits', [
            ['account_id' => $selectedAccountId, 'memo' => '', 'debit' => '', 'credit' => ''],
            ['account_id' => '', 'memo' => '', 'debit' => '', 'credit' => ''],
        ]);

        return view('gnucash.transactions.form', [
            'action' => 'Tambah Transaksi',
            'title' => 'Form Tambah Transaksi Jurnal',
            'transaction' => new GnucashTransaction([
                'post_date' => now(),
            ]),
            'splits' => $splits,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'post_date' => 'required|date',
            'description' => 'required|string|max:500',
            'num' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'splits' => 'required|array|min:2',
            'splits.*.account_id' => 'required|exists:gnucash_accounts,id',
            'splits.*.memo' => 'nullable|string|max:500',
            'splits.*.debit' => 'nullable|numeric|min:0',
            'splits.*.credit' => 'nullable|numeric|min:0',
        ], [
            'post_date.required' => 'Tanggal transaksi wajib diisi.',
            'description.required' => 'Deskripsi / Keterangan transaksi wajib diisi.',
            'splits.min' => 'Transaksi akuntansi double-entry minimal memerlukan 2 baris split (Debit & Kredit).',
            'splits.*.account_id.required' => 'Akun pada baris split wajib dipilih.',
        ]);

        // Validate double-entry balance
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $validSplits = [];

        foreach ($request->splits as $row) {
            $debit = (float) ($row['debit'] ?? 0);
            $credit = (float) ($row['credit'] ?? 0);

            if ($debit == 0 && $credit == 0) {
                continue;
            }

            $totalDebit += $debit;
            $totalCredit += $credit;

            $validSplits[] = [
                'account_id' => $row['account_id'],
                'memo' => $row['memo'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'value' => $debit > 0 ? $debit : -$credit,
            ];
        }

        if (count($validSplits) < 2) {
            return back()->withInput()->with('err', 'Minimal harus ada 2 baris split dengan nominal yang terisi.');
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            $diff = abs($totalDebit - $totalCredit);
            return back()->withInput()->with('err', "Jurnal tidak seimbang! Total Debit (Rp " . number_format($totalDebit, 2, ',', '.') . ") tidak sama dengan Total Kredit (Rp " . number_format($totalCredit, 2, ',', '.') . "). Selisih: Rp " . number_format($diff, 2, ',', '.'));
        }

        $now = now();
        $txId = str_replace('-', '', (string) Str::uuid());

        DB::transaction(function () use ($request, $txId, $validSplits, $now) {
            GnucashTransaction::create([
                'id' => $txId,
                'currency' => 'IDR',
                'num' => $request->num ?: null,
                'post_date' => $request->post_date,
                'enter_date' => $now,
                'description' => $request->description,
                'notes' => $request->notes ?: null,
            ]);

            foreach ($validSplits as $sp) {
                GnucashSplit::create([
                    'id' => str_replace('-', '', (string) Str::uuid()),
                    'transaction_id' => $txId,
                    'account_id' => $sp['account_id'],
                    'memo' => $sp['memo'],
                    'action' => null,
                    'reconcile_state' => 'n',
                    'value' => $sp['value'],
                    'quantity' => $sp['value'],
                ]);
            }
        });

        return redirect()->route('dashboard.gnucash.transactions.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * Display the specified transaction.
     */
    public function show($id)
    {
        $transaction = GnucashTransaction::with(['splits.account.parent'])->findOrFail($id);

        return view('gnucash.transactions.show', [
            'title' => "Detail Transaksi: {$transaction->description}",
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit($id)
    {
        $transaction = GnucashTransaction::with('splits.account')->findOrFail($id);

        $accounts = $this->getAccountOptions();

        $splits = [];
        foreach ($transaction->splits as $sp) {
            $val = (float) $sp->value;
            $splits[] = [
                'id' => $sp->id,
                'account_id' => $sp->account_id,
                'memo' => $sp->memo ?? '',
                'debit' => $val > 0 ? $val : '',
                'credit' => $val < 0 ? abs($val) : '',
            ];
        }

        return view('gnucash.transactions.form', [
            'action' => 'Edit Transaksi',
            'title' => 'Form Edit Transaksi Jurnal',
            'transaction' => $transaction,
            'splits' => old('splits', $splits),
            'accounts' => $accounts,
        ]);
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(Request $request, $id)
    {
        $transaction = GnucashTransaction::findOrFail($id);

        $request->validate([
            'post_date' => 'required|date',
            'description' => 'required|string|max:500',
            'num' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'splits' => 'required|array|min:2',
            'splits.*.account_id' => 'required|exists:gnucash_accounts,id',
            'splits.*.memo' => 'nullable|string|max:500',
            'splits.*.debit' => 'nullable|numeric|min:0',
            'splits.*.credit' => 'nullable|numeric|min:0',
        ]);

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $validSplits = [];

        foreach ($request->splits as $row) {
            $debit = (float) ($row['debit'] ?? 0);
            $credit = (float) ($row['credit'] ?? 0);

            if ($debit == 0 && $credit == 0) {
                continue;
            }

            $totalDebit += $debit;
            $totalCredit += $credit;

            $validSplits[] = [
                'account_id' => $row['account_id'],
                'memo' => $row['memo'] ?? null,
                'value' => $debit > 0 ? $debit : -$credit,
            ];
        }

        if (count($validSplits) < 2) {
            return back()->withInput()->with('err', 'Minimal harus ada 2 baris split.');
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            $diff = abs($totalDebit - $totalCredit);
            return back()->withInput()->with('err', "Jurnal tidak seimbang! Total Debit tidak sama dengan Total Kredit. Selisih: Rp " . number_format($diff, 2, ',', '.'));
        }

        DB::transaction(function () use ($transaction, $request, $validSplits) {
            $transaction->update([
                'post_date' => $request->post_date,
                'description' => $request->description,
                'num' => $request->num ?: null,
                'notes' => $request->notes ?: null,
            ]);

            // Replace splits
            $transaction->splits()->delete();

            foreach ($validSplits as $sp) {
                GnucashSplit::create([
                    'id' => str_replace('-', '', (string) Str::uuid()),
                    'transaction_id' => $transaction->id,
                    'account_id' => $sp['account_id'],
                    'memo' => $sp['memo'],
                    'reconcile_state' => 'n',
                    'value' => $sp['value'],
                    'quantity' => $sp['value'],
                ]);
            }
        });

        return redirect()->route('dashboard.gnucash.transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy($id)
    {
        $transaction = GnucashTransaction::findOrFail($id);
        $transaction->delete(); // splits cascade deleted

        return redirect()->route('dashboard.gnucash.transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Form to upload and re-import a .gnucash file.
     */
    public function importForm()
    {
        return view('gnucash.import', [
            'title' => 'Import File GnuCash (.gnucash)',
        ]);
    }

    /**
     * Process web upload of a .gnucash file via background job queue.
     */
    public function import(Request $request)
    {
        // 1. Validasi apakah ada file yang diterima server
        if (!$request->hasFile('gnucash_file')) {
            $maxUpload = ini_get('upload_max_filesize');
            $maxPost = ini_get('post_max_size');
            $contentLength = $request->header('Content-Length');
            $sizeMsg = $contentLength ? round($contentLength / 1024 / 1024, 2) . ' MB' : 'tidak diketahui';

            Log::warning("Upload GnuCash gagal: file tidak terdeteksi. Ukuran request: {$sizeMsg}. Batas PHP: upload_max_filesize={$maxUpload}, post_max_size={$maxPost}");

            $msg = "File tidak terupload ke server. Ukuran file yang dikirim ({$sizeMsg}) kemungkinan melebihi batas upload_max_filesize ({$maxUpload}) atau post_max_size ({$maxPost}) di server.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('err', $msg);
        }

        $file = $request->file('gnucash_file');

        // 2. Validasi integritas upload PHP
        if (!$file->isValid()) {
            $errorMsg = $file->getErrorMessage();
            Log::warning("Upload GnuCash invalid: " . $errorMsg);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal mengunggah file: ' . $errorMsg], 422);
            }
            return back()->with('err', 'Gagal mengunggah file: ' . $errorMsg);
        }

        $fresh = $request->boolean('fresh');

        Log::info("Gnucash upload received: " . $file->getClientOriginalName() . " (" . $file->getSize() . " bytes)");

        try {
            // Simpan file ke direktori public (public/uploads/gnucash) agar tidak terkendala permission storage
            $targetDir = public_path('uploads/gnucash');
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }
            @chmod($targetDir, 0777);

            $ext = $file->getClientOriginalExtension() ?: 'gnucash';
            $fileName = Str::uuid() . '_' . time() . '.' . $ext;
            $file->move($targetDir, $fileName);
            $fullPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
            @chmod($fullPath, 0666);

            if (!file_exists($fullPath)) {
                throw new \Exception("File berhasil dipindahkan tapi tidak ditemukan di target: {$fullPath}");
            }

            Log::info("File tersimpan fisik di: {$fullPath} (" . filesize($fullPath) . " bytes)");

            // Gunakan path relatif (uploads/gnucash/...) agar aman di berbagai environment
            $relativePath = 'uploads/gnucash/' . $fileName;

            $jobId = (string) Str::uuid();

            Cache::put("job-progress-{$jobId}", 0, now()->addMinutes(30));
            Cache::put("job-status-{$jobId}", 'File diunggah, menunggu antrean worker...', now()->addMinutes(30));

            // Pastikan tidak menggunakan driver 'sync' agar request web langsung selesai (< 1 detik)
            // dan tidak terkena timeout Cloudflare (Error 524)
            $queueConnection = config('queue.default') === 'sync' ? 'database' : config('queue.default');
            ImportGnucashJob::dispatch($relativePath, $fresh, $jobId)->onConnection($queueConnection);

            Log::info("Gnucash import dispatched to queue '{$queueConnection}' with jobId: {$jobId}. Path: {$relativePath}");

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Import telah dimasukkan ke dalam antrean (job queue).',
                    'jobId' => $jobId,
                ]);
            }

            return redirect()->route('dashboard.gnucash.transactions.index')
                ->with('success', 'Import file GnuCash sedang diproses di background!');
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memulai job import: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('err', 'Gagal memulai import: ' . $e->getMessage());
        }
    }
}
