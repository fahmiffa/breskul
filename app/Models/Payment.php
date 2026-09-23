<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'name',
        'nominal',
        'app',
        'auto_renew',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
    ];
}
