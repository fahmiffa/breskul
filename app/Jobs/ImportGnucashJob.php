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
     * Jumlah percobaan maksimal jika job gagal.
     * Cukup 1 kali agar tidak menimpa / truncate berulang kali jika XML corrupt.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Waktu maksimal eksekusi job (detik).
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Path file upload (bisa absolute path di public/uploads atau relative disk).
     *
     * @var string
     */
    protected string $storedFilePath;

    /**
     * Pilihan apakah truncate tabel lama.
     *
     * @var bool
     */
    protected bool $fresh;

    /**
     * Unique ID untuk tracking progres job.
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
        $resolvedPath = $this->resolvePath($this->storedFilePath);

        try {
            Cache::put("job-progress-{$this->jobId}", 0, now()->addMinutes(30));
            Cache::put("job-status-{$this->jobId}", 'Memulai pembacaan file GnuCash...', now()->addMinutes(30));

            $result = $importer->import(
                $resolvedPath,
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
            $this->cleanupFile($resolvedPath);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Cache::put("job-progress-{$this->jobId}", -1, now()->addMinutes(30));
        Cache::put("job-error-{$this->jobId}", $exception ? $exception->getMessage() : 'Job gagal diproses worker.', now()->addMinutes(30));
        Cache::put("job-status-{$this->jobId}", 'Gagal: ' . ($exception ? $exception->getMessage() : 'Terjadi kegagalan.'), now()->addMinutes(30));

        $resolvedPath = $this->resolvePath($this->storedFilePath);
        $this->cleanupFile($resolvedPath);
    }

    /**
     * Temukan lokasi fisik file di disk.
     */
    protected function resolvePath(string $path): string
    {
        if (file_exists($path)) {
            return $path;
        }

        $candidates = [
            public_path($path),
            public_path('uploads/gnucash/' . basename($path)),
            Storage::disk('local')->path($path),
            storage_path('app/' . ltrim($path, '/')),
        ];

        foreach ($candidates as $cand) {
            if (file_exists($cand)) {
                return $cand;
            }
        }

        return $path;
    }

    /**
     * Bersihkan file upload temporary setelah proses selesai.
     */
    protected function cleanupFile(string $path): void
    {
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }

        try {
            if (Storage::disk('local')->exists($this->storedFilePath)) {
                Storage::disk('local')->delete($this->storedFilePath);
            }
        } catch (Throwable $e) {
            // Abaikan jika tidak ada di disk local
        }
    }
}
