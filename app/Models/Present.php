<?php
namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Present extends Model
{
    protected $hidden = ['created_at', 'updated_at' , 'deleted_at','waktu'];
    protected $appends = ['time'];   

    public function gettimeAttribute()
    {
            $date = Carbon::parse($this->waktu)
                ->locale('id');
            return $date->translatedFormat('l, d F Y H:i:s');
    }

    public function murid()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }
}
