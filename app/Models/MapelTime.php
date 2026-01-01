<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapelTime extends Model
{
    protected $table  = 'mapel_times';
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
}
