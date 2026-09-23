<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Saldo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nominal',
        'students_id',
        'app_id',
    ];

    public function student()
    {
        return $this->belongsTo(Students::class, 'students_id');
    }

    public function logs()
    {
        return $this->hasMany(LogSaldo::class, 'saldo_id')->latest();
    }

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id');
    }
}
