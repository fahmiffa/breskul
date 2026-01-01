<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classes extends Model
{
    use SoftDeletes;
      protected $hidden  = ['created_at', 'updated_at', 'deleted_at'];

    public function jadwal()
    {
        return $this->hasMany(MapelDay::class, 'class_id', 'id');
    }
}
