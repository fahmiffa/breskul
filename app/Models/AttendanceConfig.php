<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceConfig extends Model
{
    protected $fillable = [
        'app',
        'role',
        'clock_in_start',
        'clock_in_end',
        'clock_out_start',
        'clock_out_end',
        'lat',
        'lng',
        'radius',
    ];
}
