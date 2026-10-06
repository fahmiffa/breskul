<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = ['name', 'app_id'];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function attendanceConfigs()
    {
        return $this->hasMany(AttendanceConfig::class, 'jabatan_id');
    }
}
