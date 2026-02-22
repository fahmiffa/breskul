<?php

namespace App\Imports;

use App\Models\Soal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;

class SoalImport implements ToCollection
{
    protected $teach_id;

    public function __construct($teach_id)
    {
        $this->teach_id = $teach_id;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // Skip header row
        $dataRows = $collection->slice(1);

        foreach ($dataRows as $row) {
            // Minimal data check: Question and Answer must exist
            if (!empty($row[1]) && !empty($row[8])) {
                $tipe = $row[2] ?? 'Pilihan ganda';

                Soal::create([
                    'teach_id' => $this->teach_id,
                    'nama'     => $row[1],
                    'tipe'     => $tipe,
                    'opsi_a'   => $row[3] ?? null,
                    'opsi_b'   => $row[4] ?? null,
                    'opsi_c'   => $row[5] ?? null,
                    'opsi_d'   => $row[6] ?? null,
                    'opsi_e'   => $row[7] ?? null,
                    'jawaban'  => $row[8],
                ]);
            }
        }
    }
}
