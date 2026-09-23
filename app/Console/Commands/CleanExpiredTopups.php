<?php

namespace App\Console\Commands;

use App\Jobs\CleanExpiredTopupsJob;
use App\Services\TopupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanExpiredTopups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topup:clean-expired {--queue : Kirim tugas ke queue worker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan auto delete untuk data topup yang sudah sama dengan atau melebihi waktu expired';

    /**
     * Execute the console command.
     */
    public function handle(TopupService $topupService): void
    {
        if ($this->option('queue')) {
            CleanExpiredTopupsJob::dispatch();
            $this->info('Tugas CleanExpiredTopupsJob berhasil dikirim ke Queue Worker.');
            Log::info('Artisan topup:clean-expired: CleanExpiredTopupsJob dikirim ke antrean.');
            return;
        }

        $this->info('Menjalankan pembersihan topup expired secara sinkron...');
        $count = $topupService->deleteExpiredTopups();
        $this->info("Pembersihan selesai: {$count} data topup expired berhasil di-soft-delete.");
    }
}
