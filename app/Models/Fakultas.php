<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fakultas extends Model
{
    use SoftDeletes;
    protected $table = 'fakultas';
    protected $guarded = [];

    public function prodis()
    {
        return $this->hasMany(Prodi::class, 'fakultas_id');
    }
}
