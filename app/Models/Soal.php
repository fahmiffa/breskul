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
}
