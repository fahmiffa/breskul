<?php
namespace App\Imports;

use App\Models\AcademicYears;
use DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Hash;

class MuridImport implements ToCollection
{
    protected $someVariable;

    // Terima variable lewat constructor
    public function __construct($someVariable)
    {
        $this->someVariable = $someVariable;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $dataExceptFirstRow = $collection->slice(1);

        $akademik = AcademicYears::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->where('status', 1)
            ->first();

        if (! $akademik) {
            throw new \Exception('Data Semester masih kosong.');
        }
        
        foreach ($dataExceptFirstRow as $row) {
            $name = isset($row[1]) && trim((string) $row[1]) !== '' ? trim((string) $row[1]) : null;
            $col2 = isset($row[2]) ? trim((string) $row[2]) : '';
            $col3 = isset($row[3]) ? trim((string) $row[3]) : '';

            if (in_array(strtoupper($col2), ['L', 'P']) && !in_array(strtoupper($col3), ['L', 'P'])) {
                $genderRaw = $col2;
                $nis = $col3 !== '' ? $col3 : null;
            } else {
                $nis = $col2 !== '' ? $col2 : null;
                $genderRaw = $col3 !== '' ? $col3 : null;
            }

            if ($name && $nis) {
                $genderUpper = strtoupper((string) $genderRaw);
                $gender = ($genderUpper === 'L' || $genderUpper === '1') ? 1 : 2;

                $userId = DB::table('users')->insertGetId([
                    'name'        => $name,
                    'username'    => UserName($name),
                    'password'    => Hash::make('breskul'),
                    'role'        => 2,
                    'status'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $studentId = DB::table('students')->insertGetId([
                    'name'       => $name,
                    'user'       => $userId,
                    'app'        => auth()->user()->app->id,
                    'gender'     => $gender,
                    'nis'        => $nis,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // Masukkan ke tabel heads
                DB::table('heads')->insert([
                    'student_id'  => $studentId,
                    'app'         => auth()->user()->app->id,
                    'academic_id' => $akademik->id,
                    'class_id'    => $this->someVariable,
                    'status'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

            }

        }
    }
}
