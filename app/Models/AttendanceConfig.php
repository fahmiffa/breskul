<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceConfig extends Model
{
    protected $fillable = [
        'app',
        'name',
        'jabatan_id',
        'role',
        'clock_in_start',
        'clock_in_end',
        'clock_out_start',
        'clock_out_end',
        'lat',
        'lng',
        'radius',
    ];

    public function jabatan()
    {
        return $this->belongsTo(\App\Models\Jabatan::class);
    }

    public function appData()
    {
        return $this->belongsTo(\App\Models\App::class, 'app', 'id');
    }
}
