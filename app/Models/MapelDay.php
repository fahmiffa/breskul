<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapelDay extends Model
{
    protected $table   = 'mapel_days';
    protected $appends = ['hari'];
    protected $hidden  = ['created_at', 'updated_at', 'deleted_at', 'day'];

    public function time()
    {
        return $this->hasMany(MapelTime::class, 'mapelday_id', 'id');
    }

    public function kelas()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function gethariAttribute()
    {
        return convertHari($this->day);
    }

}
