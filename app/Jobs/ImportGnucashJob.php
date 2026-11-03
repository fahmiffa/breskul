<?php

namespace App\Jobs;

use App\Services\GnucashImporterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportGnucashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Path to the uploaded file relative to storage/app.
     *
     * @var string
     */
    protected string $storedFilePath;

    /**
     * Whether to truncate existing tables.
     *
     * @var bool
     */
    protected bool $fresh;

    /**
     * Unique ID to track job progress.
     *
     * @var string
     */
    protected string $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $storedFilePath, bool $fresh, string $jobId)
    {
        $this->storedFilePath = $storedFilePath;
        $this->fresh = $fresh;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(GnucashImporterService $importer): void
    {
        $absolutePath = Storage::disk('local')->path($this->storedFilePath);

        try {
            Cache::put("job-progress-{$this->jobId}", 0, now()->addMinutes(30));
            Cache::put("job-status-{$this->jobId}", 'Memulai pembacaan file GnuCash...', now()->addMinutes(30));

            $result = $importer->import(
                $absolutePath,
                $this->fresh,
                function (int $progress, string $status) {
                    Cache::put("job-progress-{$this->jobId}", $progress, now()->addMinutes(30));
                    Cache::put("job-status-{$this->jobId}", $status, now()->addMinutes(30));
                }
            );

            Cache::put("job-progress-{$this->jobId}", 100, now()->addMinutes(30));
            Cache::put("job-status-{$this->jobId}", 'Import berhasil diselesaikan!', now()->addMinutes(30));
            Cache::put("job-result-{$this->jobId}", $result, now()->addMinutes(30));

            Log::info("ImportGnucashJob [{$this->jobId}] berhasil diselesaikan.", $result);
        } catch (Throwable $e) {
            Log::error("ImportGnucashJob [{$this->jobId}] gagal: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put("job-progress-{$this->jobId}", -1, now()->addMinutes(30));
            Cache::put("job-error-{$this->jobId}", $e->getMessage(), now()->addMinutes(30));
            Cache::put("job-status-{$this->jobId}", 'Gagal: ' . $e->getMessage(), now()->addMinutes(30));

            throw $e;
        } finally {
            // Hapus file temporary di storage setelah selesai diproses
            if (Storage::disk('local')->exists($this->storedFilePath)) {
                Storage::disk('local')->delete($this->storedFilePath);
            }
        }
    }
}
