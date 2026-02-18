<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
        'teach_id',
        'jawaban',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
    ];

    public function guru()
    {
        return $this->belongsTo(Teach::class, 'teach_id');
    }

    /**
     * Menghitung score otomatis berdasarkan jawaban student
     * 
     * @param array $soalIds List ID soal yang dikerjakan
     * @param array $answers Mapping [soal_id => jawaban_student]
     * @return float Score dalam persentase (0-100)
     */
    public static function calculateScore($soalIds, $answers)
    {
        if (empty($soalIds)) return 0;

        $soals = self::whereIn('id', $soalIds)->get();
        $correctCount = 0;
        $total = count($soals);

        foreach ($soals as $soal) {
            $submittedKey = strtoupper($answers[$soal->id] ?? '');

            // Resolusi kunci A-E ke nilai opsinya
            $submittedValue = null;
            if ($soal->tipe == 'Pilihan ganda') {
                switch ($submittedKey) {
                    case 'A':
                        $submittedValue = $soal->opsi_a;
                        break;
                    case 'B':
                        $submittedValue = $soal->opsi_b;
                        break;
                    case 'C':
                        $submittedValue = $soal->opsi_c;
                        break;
                    case 'D':
                        $submittedValue = $soal->opsi_d;
                        break;
                    case 'E':
                        $submittedValue = $soal->opsi_e;
                        break;
                    default:
                        $submittedValue = $submittedKey; // Jika dikirim teks langsung
                }
            } else {
                $submittedValue = $answers[$soal->id] ?? '';
            }

            // Bandingkan dengan jawaban di database (case-insensitive & trimmed)
            $correctValue = trim($soal->jawaban);
            $studentValue = trim($submittedValue);

            if (strcasecmp($studentValue, $correctValue) === 0) {
                $correctCount++;
            }
        }

        return $total > 0 ? ($correctCount / $total) * 100 : 0;
    }
}
