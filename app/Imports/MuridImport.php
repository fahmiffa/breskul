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
            if($row[1] && $row[2] && $row[3])
            {
               $userId =  DB::table('users')->insertGetId([
                    'name'        => $row[1],
                    'username'    => UserName($row[1]),
                    'password'    => Hash::make('breskul'),
                    'role'        => 2,
                    'status'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $studentId = DB::table('students')->insertGetId([
                    'name'       => $row[1],
                    'user'       => $userId,
                    'app'        => auth()->user()->app->id,
                    'gender'     => $row[2] == "L" ? 1 : 0,
                    'nis'        => $row[3],
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
