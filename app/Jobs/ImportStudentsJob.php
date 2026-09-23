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
        $isSchoolMode = config('app.school_mode', true);

        DB::beginTransaction();
        try {
            foreach ($dataRows as $row) {
                // Template order:
                // [0]=No, [1]=Nama, [2]=NIS, [3]=Jenis Kelamin (L/P), [4]=Alamat, [5]=No HP Siswa, [6]=No HP Orang Tua
                $name = isset($row[1]) && trim((string) $row[1]) !== '' ? trim((string) $row[1]) : null;

                $col2 = isset($row[2]) ? trim((string) $row[2]) : '';
                $col3 = isset($row[3]) ? trim((string) $row[3]) : '';

                // Deteksi otomatis jika file masih menggunakan urutan kolom lama (Gender di kolom 2, NIS di kolom 3)
                if (in_array(strtoupper($col2), ['L', 'P']) && !in_array(strtoupper($col3), ['L', 'P'])) {
                    $genderRaw = $col2;
                    $nis = $col3 !== '' ? $col3 : null;
                } else {
                    $nis = $col2 !== '' ? $col2 : null;
                    $genderRaw = $col3 !== '' ? $col3 : null;
                }

                $alamat = isset($row[4]) && trim((string) $row[4]) !== '' ? trim((string) $row[4]) : null;
                $hpSiswa = isset($row[5]) && trim((string) $row[5]) !== '' ? trim((string) $row[5]) : null;
                $hpParent = isset($row[6]) && trim((string) $row[6]) !== '' ? trim((string) $row[6]) : null;

                if ($name && $nis) {
                    $genderUpper = strtoupper((string) $genderRaw);
                    if ($genderUpper === 'L' || $genderUpper === '1' || strtolower($genderUpper) === 'laki-laki') {
                        $gender = 1;
                    } elseif ($genderUpper === 'P' || $genderUpper === '2' || strtolower($genderUpper) === 'perempuan') {
                        $gender = 2;
                    } else {
                        $gender = null;
                    }

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
                        'gender'     => $gender,
                        'nis'        => $nis,
                        'alamat'     => $alamat,
                        'hp_siswa'   => $hpSiswa,
                        'hp_parent'  => $hpParent,
                        'boarding'   => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('heads')->insert([
                        'student_id'  => $studentId,
                        'app'         => $this->appId,
                        'academic_id' => $akademik->id,
                        'class_id'    => $isSchoolMode ? $this->classId : null,
                        'prodi_id'    => ! $isSchoolMode ? $this->classId : null,
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


