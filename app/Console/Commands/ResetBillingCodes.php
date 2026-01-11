<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use Illuminate\Support\Facades\Log;

class ResetBillingCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset unique codes for unpaid bills at 00:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('START billing:reset: Cleaning up unpaid bills unique codes.');
        
        // Query bills where status is 0 (Unpaid) and have a unique_code
        // We do typically reset them so they can get a NEW code for the NEW day if they try to pay again.
        $count = Bill::where('status', 0)
            ->whereNotNull('unique_code')
            ->update([
                'unique_code' => null,
                'qris_data' => null,
                'qris_expired_at' => null // Clear expiry too
            ]);

        $this->info("Reset complete. {$count} bills updated.");
        Log::info("END billing:reset: {$count} bills updated.");
    }
}
