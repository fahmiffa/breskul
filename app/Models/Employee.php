<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['app_id', 'name', 'alamat', 'jenis_kelamin', 'user_id', 'jabatan_id'];

    protected $appends = ['jenis'];

    public function getJenisAttribute()
    {
        return $this->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id', 'id');
    }

    public function attendanceConfig()
    {
        return $this->hasOne(AttendanceConfig::class);
    }
}
