<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kas';

    protected $fillable = [
        'tipe',
        'nominal',
        'keterangan',
        'kategori',
        'tanggal',
        'app',
        'user_id',
        'referensi',
    ];

    protected $casts = [
        'nominal'  => 'decimal:2',
        'tanggal'  => 'date',
    ];

    protected $appends = [
        'formatted_nominal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedNominalAttribute()
    {
        return 'Rp ' . number_format($this->nominal ?? 0, 0, ',', '.');
    }
}
