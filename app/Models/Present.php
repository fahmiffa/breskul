<?php
namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Present extends Model
{
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'waktu'];
    protected $appends = ['time', 'name', 'tipe'];   

    public function gettimeAttribute()
    {
        $date = Carbon::parse($this->waktu)
            ->locale('id');
        return $date->translatedFormat('l, d F Y H:i:s');
    }

    public function getNameAttribute()
    {
        return $this->murid?->name ?? $this->employee?->name ?? '-';
    }

    public function getTipeAttribute()
    {
        return $this->employee_id ? 'Karyawan' : 'Murid';
    }

    public function murid()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
