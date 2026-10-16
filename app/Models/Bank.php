<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'name',
        'number',
    ];

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id');
    }
}
