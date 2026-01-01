<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teach extends Model
{
    use SoftDeletes;
    protected $appends = ['jenis'];
    protected $hidden  = ['created_at', 'updated_at' . 'deleted_at'];

    public function getjenisAttribute()
    {
        return $this->gender == 1 ? "laki-laki" : "Perempuan";
    }
}
