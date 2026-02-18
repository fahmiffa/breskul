<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'soal_id',
        'mapel_id',
        'teach_id',
    ];

    protected $casts = [
        'soal_id' => 'array',
    ];

    public function guru()
    {
        return $this->belongsTo(Teach::class, 'teach_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    /**
     * Ujian hasMany Soal
     * Because soal_id is stored as an array in Ujian, 
     * this relationship retrieves those specific questions.
     */
    public function soals()
    {
        // Many-to-Many would be cleaner, but following the "soal_id as field" request.
        // We can simulate the collection.
        return Soal::whereIn('id', $this->soal_id ?? []);
    }
}
