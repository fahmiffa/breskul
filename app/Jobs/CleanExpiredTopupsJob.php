<?php

namespace App\Jobs;

use App\Services\TopupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanExpiredTopupsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika terjadi error.
     */
    public int $tries = 3;

    /**
     * Execute the job.
     */
    public function handle(TopupService $topupService): void
    {
        Log::info('CleanExpiredTopupsJob: Memulai proses auto delete data topup expired via Queue Worker.');

        $deletedCount = $topupService->deleteExpiredTopups();

        Log::info("CleanExpiredTopupsJob: Selesai. Sebanyak {$deletedCount} data topup telah di-soft-delete.");
    }
}
