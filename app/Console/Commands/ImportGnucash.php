<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ImportGnucash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gnucash:import {path? : Path to .gnucash file} {--fresh : Truncate existing tables before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import GnuCash accounting data from a .gnucash XML file into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $filePath = $this->argument('path');

        if (!$filePath) {
            // Check default locations
            $candidates = [
                base_path('base.gnucash'),
                base_path('../base.gnucash'),
                dirname(base_path()) . DIRECTORY_SEPARATOR . 'base.gnucash',
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    $filePath = $cand;
                    break;
                }
            }
        }

        if (!$filePath || !file_exists($filePath)) {
            $this->error("File .gnucash tidak ditemukan di: " . ($filePath ?? 'default paths'));
            return 1;
        }

        $this->info("Memulai pembacaan file: {$filePath}");
        $startTime = microtime(true);

        $raw = file_get_contents($filePath);
        if (substr($raw, 0, 2) === "\x1f\x8b") {
            $this->line("Mendekompresi file gzip...");
            $xmlString = gzdecode($raw);
        } else {
            $xmlString = $raw;
        }

        $this->line("Mem-parsing XML GnuCash...");
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);
        if ($xml === false) {
            $this->error("Gagal mem-parsing XML GnuCash.");
            foreach (libxml_get_errors() as $err) {
                $this->error($err->message);
            }
            return 1;
        }

        $ns = $xml->getNamespaces(true);
        $book = $xml->children($ns['gnc'])->book;

        if ($this->option('fresh')) {
            $this->warn("Mengosongkan tabel GnuCash lama (--fresh)...");
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('gnucash_splits')->truncate();
            DB::table('gnucash_transactions')->truncate();
            DB::table('gnucash_accounts')->truncate();
            DB::table('gnucash_commodities')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $now = now();

        // 1. COMMODITIES
        $this->info("1. Mengimpor Komoditas / Mata Uang...");
        $commodities = [];
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

        foreach (array_chunk($commodities, 100) as $chunk) {
            DB::table('gnucash_commodities')->upsert(
                $chunk,
                ['id'],
                ['namespace', 'mnemonic', 'fullname', 'fraction', 'updated_at']
            );
        }
        $this->info("   -> " . count($commodities) . " komoditas berhasil diimpor.");

        // 2. ACCOUNTS
        $this->info("2. Mengimpor Akun (Chart of Accounts)...");
        $accounts = [];
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

            // Check slots
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

        foreach (array_chunk($accounts, 200) as $chunk) {
            DB::table('gnucash_accounts')->upsert(
                $chunk,
                ['id'],
                ['name', 'account_type', 'commodity_id', 'commodity_scu', 'code', 'description', 'parent_id', 'placeholder', 'color', 'notes', 'updated_at']
            );
        }
        $this->info("   -> " . count($accounts) . " akun berhasil diimpor.");

        // 3. TRANSACTIONS & SPLITS
        $this->info("3. Membaca Transaksi dan Splits...");
        $txList = $book->children($ns['gnc'])->transaction;
        $totalTx = count($txList);
        $this->info("   -> Total transaksi terdeteksi: {$totalTx}");

        $txRows = [];
        $splitRows = [];
        $totalSplitsCount = 0;

        $bar = $this->output->createProgressBar($totalTx);
        $bar->start();

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

            // Flush chunk if large to keep memory lean
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

            $bar->advance();
        }

        // Flush remaining transactions and splits
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

        $bar->finish();
        $this->newLine();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("==========================================");
        $this->info("IMPORT GNUCASH BERHASIL SELESAI!");
        $this->info("Waktu eksekusi: {$duration} detik");
        $this->info("- Komoditas    : " . count($commodities));
        $this->info("- Akun (COA)   : " . count($accounts));
        $this->info("- Transaksi    : {$totalTx}");
        $this->info("- Splits       : {$totalSplitsCount}");
        $this->info("==========================================");

        return 0;
    }
}
