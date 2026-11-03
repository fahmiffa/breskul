<?php

namespace App\Console\Commands;

use App\Services\GnucashImporterService;
use Illuminate\Console\Command;
use Throwable;

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
    public function handle(GnucashImporterService $importer)
    {
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

        try {
            $lastStatus = '';
            $result = $importer->import(
                $filePath,
                (bool) $this->option('fresh'),
                function (int $progress, string $status) use (&$lastStatus) {
                    if ($status !== $lastStatus) {
                        $this->line("[{$progress}%] {$status}");
                        $lastStatus = $status;
                    }
                }
            );

            $this->newLine();
            $this->info("==========================================");
            $this->info("IMPORT GNUCASH BERHASIL SELESAI!");
            $this->info("Waktu eksekusi: {$result['duration']} detik");
            $this->info("- Komoditas    : " . $result['commodities']);
            $this->info("- Akun (COA)   : " . $result['accounts']);
            $this->info("- Transaksi    : " . $result['transactions']);
            $this->info("- Splits       : " . $result['splits']);
            $this->info("==========================================");

            return 0;
        } catch (Throwable $e) {
            $this->error("Gagal mengimpor file: " . $e->getMessage());
            return 1;
        }
    }
}
