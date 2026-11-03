<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class GnucashImporterService
{
    /**
     * Parse and import a .gnucash XML file into database.
     *
     * @param string $filePath Absolute path to the .gnucash file
     * @param bool $fresh If true, truncate existing gnucash tables
     * @param callable|null $progressCallback Callback receiving (int $percentage, string $statusMessage)
     * @return array Summary of imported items
     * @throws Exception
     */
    public function import(string $filePath, bool $fresh = false, ?callable $progressCallback = null): array
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        if (!file_exists($filePath)) {
            throw new Exception("File .gnucash tidak ditemukan di: {$filePath}");
        }

        $report = function (int $progress, string $status) use ($progressCallback) {
            if ($progressCallback) {
                call_user_func($progressCallback, $progress, $status);
            }
        };

        $report(5, 'Membaca dan mendekompresi file GnuCash...');
        $startTime = microtime(true);

        $raw = file_get_contents($filePath);
        if ($raw === false) {
            throw new Exception("Gagal membaca file di path: {$filePath}");
        }

        // Check for gzip compression header (0x1f 0x8b)
        if (substr($raw, 0, 2) === "\x1f\x8b") {
            $xmlString = gzdecode($raw);
            if ($xmlString === false) {
                throw new Exception("Gagal mendekompresi file GnuCash (gzip format korup).");
            }
        } else {
            $xmlString = $raw;
        }

        $report(10, 'Mem-parsing struktur XML GnuCash...');
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);
        if ($xml === false) {
            $errMsgs = [];
            foreach (libxml_get_errors() as $err) {
                $errMsgs[] = trim($err->message);
            }
            libxml_clear_errors();
            throw new Exception("Gagal mem-parsing XML: " . implode('; ', $errMsgs));
        }

        $ns = $xml->getNamespaces(true);
        if (!isset($ns['gnc']) || !isset($xml->children($ns['gnc'])->book)) {
            throw new Exception("Format XML tidak valid atau namespace GnuCash tidak ditemukan.");
        }

        $book = $xml->children($ns['gnc'])->book;

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            if ($fresh) {
                $report(15, 'Mengosongkan tabel GnuCash lama...');
                DB::table('gnucash_splits')->truncate();
                DB::table('gnucash_transactions')->truncate();
                DB::table('gnucash_accounts')->truncate();
                DB::table('gnucash_commodities')->truncate();
            }

            $now = now();

            // 1. COMMODITIES
            $report(18, 'Mengimpor Komoditas / Mata Uang...');
            $commodities = [];
            if (isset($book->children($ns['gnc'])->commodity)) {
                foreach ($book->children($ns['gnc'])->commodity as $cmdty) {
                    $cNs = $cmdty->children($ns['cmdty']);
                    $id = (string) $cNs->id;
                    $space = (string) $cNs->space;
                    $name = (string) $cNs->name;
                    $frac = (int) $cNs->fraction ?: 100;

                    $commodities[] = [
                        'id' => $id,
                        'namespace' => $space ?: 'CURRENCY',
                        'mnemonic' => $id,
                        'fullname' => $name ?: $id,
                        'fraction' => $frac,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($commodities, 100) as $chunk) {
                DB::table('gnucash_commodities')->upsert(
                    $chunk,
                    ['id'],
                    ['namespace', 'mnemonic', 'fullname', 'fraction', 'updated_at']
                );
            }

            // 2. ACCOUNTS
            $report(25, 'Mengimpor Bagan Akun (COA)...');
            $accounts = [];
            if (isset($book->children($ns['gnc'])->account)) {
                foreach ($book->children($ns['gnc'])->account as $acc) {
                    $actNs = $acc->children($ns['act']);
                    $id = (string) $actNs->id;
                    $name = (string) $actNs->name;
                    $type = (string) $actNs->type;
                    $code = (string) $actNs->code ?: null;
                    $desc = (string) $actNs->description ?: null;
                    $parent = (string) $actNs->parent ?: null;
                    $scu = (int) $actNs->{'commodity-scu'} ?: 100;

                    $commodityId = null;
                    if (isset($actNs->commodity)) {
                        $cNs = $actNs->commodity->children($ns['cmdty']);
                        $commodityId = (string) $cNs->id ?: null;
                    }

                    $isPlaceholder = false;
                    $color = null;
                    $notes = null;
                    if (isset($actNs->slots)) {
                        foreach ($actNs->slots->children() as $slot) {
                            $key = (string) $slot->children($ns['slot'])->key;
                            $val = (string) $slot->children($ns['slot'])->value;
                            if ($key === 'placeholder' && $val === 'true') {
                                $isPlaceholder = true;
                            } elseif ($key === 'color') {
                                $color = $val;
                            } elseif ($key === 'notes') {
                                $notes = $val;
                            }
                        }
                    }

                    $accounts[] = [
                        'id' => $id,
                        'name' => $name,
                        'account_type' => $type,
                        'commodity_id' => $commodityId,
                        'commodity_scu' => $scu,
                        'code' => $code,
                        'description' => $desc,
                        'parent_id' => $parent,
                        'placeholder' => $isPlaceholder ? 1 : 0,
                        'color' => $color,
                        'notes' => $notes,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($accounts, 200) as $chunk) {
                DB::table('gnucash_accounts')->upsert(
                    $chunk,
                    ['id'],
                    ['name', 'account_type', 'commodity_id', 'commodity_scu', 'code', 'description', 'parent_id', 'placeholder', 'color', 'notes', 'updated_at']
                );
            }

            // 3. TRANSACTIONS & SPLITS
            $txList = isset($book->children($ns['gnc'])->transaction) ? $book->children($ns['gnc'])->transaction : [];
            $totalTx = count($txList);
            $report(35, "Menyiapkan {$totalTx} transaksi untuk diimpor...");

            $txRows = [];
            $splitRows = [];
            $totalSplitsCount = 0;
            $processedTx = 0;
            $lastReportedPct = 35;

            foreach ($txList as $tx) {
                $trnNs = $tx->children($ns['trn']);
                $txId = (string) $trnNs->id;
                $num = (string) $trnNs->num ?: null;
                $desc = (string) $trnNs->description ?: null;

                $currency = 'IDR';
                if (isset($trnNs->currency)) {
                    $cNs = $trnNs->currency->children($ns['cmdty']);
                    $currency = (string) $cNs->id ?: 'IDR';
                }

                // Dates
                $postDateRaw = (string) $trnNs->{'date-posted'}->children($ns['ts'])->date;
                $postDate = $postDateRaw ? substr($postDateRaw, 0, 19) : $now->format('Y-m-d H:i:s');

                $enterDateRaw = isset($trnNs->{'date-entered'}) ? (string) $trnNs->{'date-entered'}->children($ns['ts'])->date : null;
                $enterDate = $enterDateRaw ? substr($enterDateRaw, 0, 19) : null;

                // Slots
                $notes = null;
                if (isset($trnNs->slots)) {
                    foreach ($trnNs->slots->children() as $slot) {
                        $key = (string) $slot->children($ns['slot'])->key;
                        $val = (string) $slot->children($ns['slot'])->value;
                        if ($key === 'notes' && !empty($val)) {
                            $notes = $val;
                        }
                    }
                }

                $txRows[] = [
                    'id' => $txId,
                    'currency' => $currency,
                    'num' => $num,
                    'post_date' => $postDate,
                    'enter_date' => $enterDate,
                    'description' => $desc,
                    'notes' => $notes,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Splits
                if (isset($trnNs->splits)) {
                    foreach ($trnNs->splits->children($ns['trn'])->split as $sp) {
                        $spNs = $sp->children($ns['split']);
                        $spId = (string) $spNs->id;
                        $spAcc = (string) $spNs->account;
                        $spMemo = (string) $spNs->memo ?: null;
                        $spAction = (string) $spNs->action ?: null;
                        $spReconcile = (string) $spNs->{'reconciled-state'} ?: 'n';

                        $reconcileDate = null;
                        if (isset($spNs->{'reconcile-date'})) {
                            $rdRaw = (string) $spNs->{'reconcile-date'}->children($ns['ts'])->date;
                            if ($rdRaw) {
                                $reconcileDate = substr($rdRaw, 0, 19);
                            }
                        }

                        $rawVal = (string) $spNs->value;
                        $valFloat = 0.0;
                        if (str_contains($rawVal, '/')) {
                            [$numerator, $denominator] = explode('/', $rawVal, 2);
                            $den = (float) $denominator;
                            $valFloat = $den != 0 ? ((float) $numerator / $den) : 0.0;
                        } else {
                            $valFloat = (float) $rawVal;
                        }

                        $rawQty = (string) $spNs->quantity;
                        $qtyFloat = 0.0;
                        if (str_contains($rawQty, '/')) {
                            [$numerator, $denominator] = explode('/', $rawQty, 2);
                            $den = (float) $denominator;
                            $qtyFloat = $den != 0 ? ((float) $numerator / $den) : 0.0;
                        } else {
                            $qtyFloat = (float) $rawQty;
                        }

                        $splitRows[] = [
                            'id' => $spId,
                            'transaction_id' => $txId,
                            'account_id' => $spAcc,
                            'memo' => $spMemo,
                            'action' => $spAction,
                            'reconcile_state' => $spReconcile,
                            'reconcile_date' => $reconcileDate,
                            'value' => $valFloat,
                            'quantity' => $qtyFloat,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $totalSplitsCount++;
                    }
                }

                // Batch upsert to keep memory low
                if (count($txRows) >= 500) {
                    DB::table('gnucash_transactions')->upsert(
                        $txRows,
                        ['id'],
                        ['currency', 'num', 'post_date', 'enter_date', 'description', 'notes', 'updated_at']
                    );
                    $txRows = [];
                }

                if (count($splitRows) >= 1000) {
                    DB::table('gnucash_splits')->upsert(
                        $splitRows,
                        ['id'],
                        ['transaction_id', 'account_id', 'memo', 'action', 'reconcile_state', 'reconcile_date', 'value', 'quantity', 'updated_at']
                    );
                    $splitRows = [];
                }

                $processedTx++;

                if ($totalTx > 0) {
                    // Progress from 35% to 92%
                    $currentPct = 35 + (int) floor(($processedTx / $totalTx) * 57);
                    if ($currentPct >= $lastReportedPct + 3 || $processedTx === $totalTx) {
                        $lastReportedPct = $currentPct;
                        $report($currentPct, "Mengimpor transaksi ({$processedTx}/{$totalTx})...");
                    }
                }
            }

            // Flush remaining transactions and splits
            $report(94, 'Menyelesaikan sisa transaksi dan rincian jurnal...');
            if (!empty($txRows)) {
                DB::table('gnucash_transactions')->upsert(
                    $txRows,
                    ['id'],
                    ['currency', 'num', 'post_date', 'enter_date', 'description', 'notes', 'updated_at']
                );
            }

            if (!empty($splitRows)) {
                foreach (array_chunk($splitRows, 2000) as $chunk) {
                    DB::table('gnucash_splits')->upsert(
                        $chunk,
                        ['id'],
                        ['transaction_id', 'account_id', 'memo', 'action', 'reconcile_state', 'reconcile_date', 'value', 'quantity', 'updated_at']
                    );
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            $report(100, "Import selesai dalam {$duration} detik.");

            return [
                'duration' => $duration,
                'commodities' => count($commodities),
                'accounts' => count($accounts),
                'transactions' => $totalTx,
                'splits' => $totalSplitsCount,
            ];
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
