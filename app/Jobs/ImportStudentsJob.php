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
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $storedFilePath;
    protected int $classId;
    protected int $appId;
    protected string $jobId;

    public function __construct(string $storedFilePath, int $classId, int $appId, string $jobId)
    {
        $this->storedFilePath = $storedFilePath;
        $this->classId = $classId;
        $this->appId = $appId;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        $absolutePath = storage_path('app/' . ltrim($this->storedFilePath, '/'));

        $spreadsheet = IOFactory::load($absolutePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header
        $dataRows = array_slice($rows, 1);
        $total = count($dataRows);

        if ($total === 0) {
            Cache::put("job-progress-{$this->jobId}", 100, now()->addMinutes(10));
            return;
        }

        // Get active akademik for this app
        $akademik = DB::table('academicyears')
            ->where('app', $this->appId)
            ->where('status', 1)
            ->latest('id')
            ->first();

        if (! $akademik) {
            Log::warning("Job {$this->jobId}: Tidak ada semester aktif.");
            Cache::put("job-progress-{$this->jobId}", 100, now()->addMinutes(10));
            return;
        }

        $processed = 0;

        DB::beginTransaction();
        try {
            foreach ($dataRows as $row) {
                // Expecting: [0]=no, [1]=name, [2]=gender L/P, [3]=nis
                $name = $row[1] ?? null;
                $genderRaw = $row[2] ?? null;
                $nis = $row[3] ?? null;

                if ($name && $genderRaw && $nis) {
                    $username = self::usernameFromName($name);

                    $userId = DB::table('users')->insertGetId([
                        'name'       => $name,
                        'username'   => $username,
                        'password'   => Hash::make('breskul'),
                        'role'       => 2,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $studentId = DB::table('students')->insertGetId([
                        'name'       => $name,
                        'user'       => $userId,
                        'app'        => $this->appId,
                        'gender'     => ($genderRaw === 'L') ? 1 : 0,
                        'nis'        => $nis,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('heads')->insert([
                        'student_id'  => $studentId,
                        'app'         => $this->appId,
                        'academic_id' => $akademik->id,
                        'class_id'    => $this->classId,
                        'status'      => 1,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                $processed++;
                $progress = (int) floor(($processed / $total) * 100);
                Cache::put("job-progress-{$this->jobId}", $progress, now()->addMinutes(10));
            }

            DB::commit();
            Cache::put("job-progress-{$this->jobId}", 100, now()->addMinutes(10));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Job {$this->jobId} gagal: " . $e->getMessage());
            throw $e;
        }
    }

    protected static function usernameFromName(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '', $name));
        if ($base === '') {
            $base = 'user';
        }
        $username = $base;
        $i = 1;
        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }
        return $username;
    }
}


