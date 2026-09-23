<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogSaldo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'keterangan',
        'tipe',
        'nominal',
        'saldo_id',
        'students_id',
    ];

    public function saldo()
    {
        return $this->belongsTo(Saldo::class, 'saldo_id');
    }

    public function student()
    {
        return $this->belongsTo(Students::class, 'students_id');
    }
}
