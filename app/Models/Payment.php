<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'name',
        'nominal',
        'jatuh_tempo',
        'app',
        'auto_renew',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
        'jatuh_tempo' => 'date',
    ];
}
