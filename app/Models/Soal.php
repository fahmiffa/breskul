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

        // Ensure answers is an array (logic handling for Request/Collection)
        $answers = is_array($answers) ? $answers : (method_exists($answers, 'toArray') ? $answers->toArray() : (array)$answers);

        $soals = self::whereIn('id', $soalIds)->get();
        $correctCount = 0;
        $total = count($soals);

        foreach ($soals as $soal) {
            // Robust key lookup (integer or string)
            $studentAnswer = $answers[$soal->id] ?? $answers[(string)$soal->id] ?? null;
            if ($studentAnswer === null) continue;

            $submittedKey = strtoupper(trim(strip_tags($studentAnswer)));
            $correctValue = trim(strip_tags($soal->jawaban));

            // 1. Check if direct key (e.g. "A") matches the answer
            if (strcasecmp($submittedKey, $correctValue) === 0) {
                $correctCount++;
                continue;
            }

            // 2. Check resolved value for Multiple Choice
            if ($soal->tipe == 'Pilihan ganda') {
                $submittedValue = null;
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
                }

                if ($submittedValue !== null) {
                    $resolvedValue = trim(strip_tags($submittedValue));
                    if (strcasecmp($resolvedValue, $correctValue) === 0) {
                        $correctCount++;
                    }
                }
            }
        }

        return $total > 0 ? ($correctCount / $total) * 100 : 0;
    }
}
