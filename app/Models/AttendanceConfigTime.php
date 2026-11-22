<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceConfigTime extends Model
{
    protected $fillable = [
        'attendance_config_id',
        'day',
        'clock_in_start',
        'clock_in_end',
        'clock_out_start',
        'clock_out_end',
        'lat',
        'lng',
        'radius',
    ];

    public function attendanceConfig()
    {
        return $this->belongsTo(AttendanceConfig::class);
    }
}
