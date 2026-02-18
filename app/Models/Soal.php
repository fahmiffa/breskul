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
            $submittedAnswer = $answers[$soal->id] ?? null;
            if ($submittedAnswer == $soal->jawaban) {
                $correctCount++;
            }
        }

        return $total > 0 ? ($correctCount / $total) * 100 : 0;
    }
}
