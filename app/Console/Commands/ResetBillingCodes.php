<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use Illuminate\Support\Facades\Log;
use App\Models\DailyUniqueCode;
use DB;

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
            
            $now = now();
            $oneDay = $now->addDay()->toDateString();
            DailyUniqueCode::doesntHave('bill')->where('is_used',0)->delete();
            DailyUniqueCode::whereHas('bill',function($q)
            {
                $q->where('status',0);
                })->where('date',$now->toDateString())->delete();
                
        // $oneDay = now()->toDateString();
        if (DailyUniqueCode::where('date', $oneDay)->count() === 0) {
             DB::transaction(function () use ($oneDay) {
                // Double check inside transaction
                if (DailyUniqueCode::where('date', $oneDay)->count() === 0) {
                    $codes = [];
                    // Generate codes 001 to 999 (avoid 000 if preferred, or include it)
                    // Request asked for 3 digits, usually 1-999 or 0-999.
                    for ($i = 1; $i < 1000; $i++) {
                        $codes[] = [
                            'code' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                            'date' => $oneDay,
                            'is_used' => false,
                            'created_at' => now()->addDay(),
                            'updated_at' => now()->addDay(),
                        ];
                    }
                    DailyUniqueCode::insert($codes);
                }
             });
        }
        Log::info('reset kode unik');
    }
}
