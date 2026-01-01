<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $jobId;
    public $update;

    public function __construct(array $data, string $jobId, bool $update)
    {
        $this->data  = $data;
        $this->jobId = $jobId;
        $this->update = $update;
    }

    public function handle()
    {
        DB::beginTransaction();

        try {
            $total = count($this->data);
            if ($total === 0) {
                Log::warning("⚠️ Job {$this->jobId}: Tidak ada data untuk diproses.");
                return;
            }


            if($this->update)
            {
                // 🔹 1. Ambil semua parent ID yang mau di-nonaktifkan
                $parentIds = collect($this->data)->pluck('parent')->unique()->values()->all();
    
                // 🔹 2. Update semua parent sekaligus
                $affected = DB::table('heads')
                    ->whereIn('id', $parentIds)
                    ->update(['status' => 0]);
    
                Log::info("🧩 Job {$this->jobId}: {$affected} parent di-update (status=0).");
            }


            // 🔹 3. Bulk insert langsung semua data
            DB::table('heads')->insert($this->data);

            // 🔹 4. Simpan progress 100%
            Cache::put("job-progress-{$this->jobId}", 100, now()->addMinutes(10));

            DB::commit();
            Log::info("✅ Job {$this->jobId} selesai. Total data: {$total}");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("❌ Job {$this->jobId} gagal: " . $e->getMessage());
            throw $e;
        }
    }
}
